<?

/**
 * @package xpotronix
 * @version 2.0 - Areco 
 * @copyright Copyright &copy; 2003-2011, Eduardo Spotorno
 * @author Eduardo Spotorno
 *
 * Licensed under GPL v3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

define( 'NO_OP',     'n' );
define( 'INSERT_OP', 'i' );
define( 'UPDATE_OP', 'u' );
define( 'DELETE_OP', 'd' );
define( 'NOT_VALID', 'v' );
define( 'NOT_FOUND', 'f' );
define( 'NO_PERMS',  'p' );
define( 'DB_ERROR',  'e' );

require_once 'xpattr.class.php';
require_once 'xpquery.class.php';
require_once 'xpiterator.class.php';
require_once 'xpmessages.class.php';
require_once 'xpkey.class.php';
require_once 'xpsearch.class.php';

class xpDataObject extends xp {


	// identification

	private $name;
	private $type;

	// nombre de la clase 

	var $class_name;

	// array atributos
	var $attr;
	var $metadata;
	var $data;
	protected $aliases = array();
	var $track_modified = true;
	var $invalid_attrs;
	var $constructed;

	// parametros especificos para este objeto
	var $extra_param;

	// features
	var $feat;

	// base de datos
	protected $db;

	// tabla asociada
	var $table_name;
	var $uniq_tables_array;

	// autonumeric
	private $autonumeric_field;

	// estatus del objeto 
	var $__new;
	var $loaded;
	var $modified;
	var $transac_status;
	var $record_count;

	// consulta del objeto;
	var $sql;

	// recordset de la ultima consulta
	var $recordset;

	// paginador del recordset
	var $pager;
	var $total_records;

	// claves
	var $primary_key;
	var $foreign_key;

	// search obj

	var $search;

	// control consulta
	var $order_array;
	var $queries;
	var $queries_array;

	// procesos
	var $processes;

	// flags
	protected $flags = array();

	// acl
	protected $temp_acl = array();


	// sincronizacion

	function table_exists( $table_name ) {/*{{{*/

		$dd = NewDataDictionary( $this->db );
		$mt = $dd->MetaTables();
		return array_search( $table_name, $mt ); 
	}/*}}}*/

	// constructores y funciones magicas

	function sync_create() {/*{{{*/

		require_once 'xpsync.class.php';
		$sync = new xpsync( $this );
		return $sync->sync_create();
	}/*}}}*/

	function sync_data() {/*{{{*/

		require_once 'xpsync.class.php';
		$sync = new xpsync( $this );
		return $sync->sync_data();
	}/*}}}*/

	function __construct( $model = null, $metadata = null ) {/*{{{*/

		global $xpdoc; 

		$this->set_model( $model );
		$this->set_metadata( $metadata );
		$this->db( (string) $this->metadata['dbi'] );

		$this->name or $this->name = $this->class_name;

		// print '<pre>'; print_r( $this->debug_backtrace());

		$this->feat = new xpconfig( $xpdoc->feat ); // featureas locales
		$this->set_features();

                $this->feat->set_time_limit 
			and set_time_limit( $this->feat->set_time_limit ) 
			and M()->info("tiempo maximo de ejecucion en {$this->feat->set_time_limit} segundos");

		$this->set_acl();

		if ( $this->is_virtual() ) {

			$this->acl['add'] = false;
			$this->acl['edit'] = true;
			$this->acl['delete'] = false;
		}
		
		$this->sql = new DBQuery( $this->db );

		// control consulta

		$this->primary_key = array();
		$this->foreign_key = array();

		$this->order_array = array();

		$this->search = new xpsearch( $this );

		$this->load_obj_attrs();

		$this->config_primary_key();
		$this->config_foreign_key();

		$this->load_processes();


		// los parametros especificos del objeto tienen precedencia sobre los globales

		$xpdoc and isset( $xpdoc->extra_param['_'] ) and  $this->extra_param = $xpdoc->extra_param['_'];
		$xpdoc and isset( $xpdoc->extra_param[$this->class_name] ) and $this->extra_param = $xpdoc->extra_param[$this->class_name] ;

		// registro este objeto en obj_collection

		$xpdoc and $xpdoc->obj_collection[$this->class_name][] = $this;

		$this->constructed = true; // __construct fue llamado OK

		$this->set_flag( 'main_sql', true );
		$this->set_flag( 'validate', true );
		$this->set_flag( 'check', true );

		return $this->init();

	}/*}}}*/ 

	function __destruct() {/*{{{*/

		// elimino la referencia circular para que no me genere memory leaks (espero)

		if ( $this->attr ) 
			foreach( $this->attr as $key => $attr ) 
				unset ( $this->attr[$key]->obj );

	}/*}}}*/

		function __get( $var_name ) {/*{{{*/

		/*
		echo '<pre>';
		print_r( debug_backtrace( false ) );

		print $var_name; 

		print_r( $this->data );

		print 'hola'; exit;

		*/

		if ( $this->check_vars and !array_key_exists( $var_name, $this->data ) ) {

			if ( is_array( $this->aliases ) and !array_key_exists( $var_name, $this->aliases ) ) { 

				M()->error( "no encontre el atributo [$this->class_name::$var_name]" );
				M()->line(1);
				return null;

			} else {

				$var_name = $this->aliases[$var_name]->name;
			}
		}
			

		return @$this->data[$var_name] ;

	}/*}}}*/

	function __set( $var_name, $var_value ) {/*{{{*/

		// si es una array o un object, asocia directamente 
		// (solo para el funcionamiento de la clase)

		if ( is_object( $var_value ) or is_array( $var_value ) ) {

			$this->$var_name = $var_value;
			( $var_name == 'data' ) or M()->warn( "asignando una tipo complejo a un dato simple $this->class_name::$var_name" );

		}

		else if ( $attr = $this->get_attr( $var_name ) ) {

			$attr->value = $var_value;

		} else if ( array_key_exists( $var_name, $this->aliases ) ) {

			M()->error( "asignando por alias: $this->class_name::$var_name = $var_value" );

			$this->aliases[$var_name]->value = $var_value;

		} else M()->error( "no encontre el atributo [$this->class_name::$var_name]" );

		return $var_value;
	}/*}}}*/

	function set_flag( $name, $value ) {/*{{{*/

		return $this->flags[$name] = $value;
	}/*}}}*/

	function get_flag( $name ) {/*{{{*/

		if ( !in_array( $name, $this->flags ) ) return null;
		return $this->flags[$name];
	}/*}}}*/

	function get_name() {/*{{{*/

		return $this->class_name;

	}/*}}}*/

	function get_autonumeric_field() {/*{{{*/

		return $this->autonumeric_field;
	}/*}}}*/

	function set_autonumeric_field( $af ) {/*{{{*/

		return $this->autonumeric_field = $af;

	}/*}}}*/

	// configuracion del objeto

	function db( $db_handler = null ) {/*{{{*/

		global $xpdoc;

		if ( is_object( $db_handler ) ) $this->db = $db_handler;

		else if ( $dbi = $xpdoc->db_instance( $db_handler ) ) $this->db = $dbi;

		else M()->fatal('No encuentro la base de datos'); 

		return $this->db;

	}/*}}}*/

	function get_db() {/*{{{*/

		return $this->db;
	}/*}}}*/

	function set_table_name( $table_name ) {/*{{{*/

		return $this->table_name = $table_name;

	}/*}}}*/ 

	function get_module_path( $path = null ) {/*{{{*/

		return 'modules/' . $this->class_name. '/' . $path;

	}/*}}}*/

	function load_processes() {/*{{{*/

		$file = $this->get_module_path( $this->class_name . '.processes.xml' );

		@$this->processes = simplexml_load_file( $file ) or 
			( $this->processes = new SimpleXMLElement( "<processes/>" ) and 
			M()->info("No hay processos definidos para la clase {$this->class_name} en $file: ") );

		return $this->processes;

	}/*}}}*/

	function load_model() {/*{{{*/

		$file = $this->get_module_path( $this->class_name . '.model.xml' );

		( $this->model = simplexml_load_file( $file ) 
			and M()->info('cargado el modelo para la clase '. $this->class_name ) )
		or M()->fatal("No puedo cargar la descripcion del modelo del achivo $file" );

	}/*}}}*/ 

	function set_model( $model = null ) {/*{{{*/

		global $xpdoc;

		$this->model = $model or 
		( $xpdoc and 
			$xpdoc->model and
			$tmp = $xpdoc->model->xpath( "//obj[@name='{$this->class_name}']" ) and
			$this->model = array_shift( $tmp ) 
		) or
			$this->load_model(); 

		$this->name = (string) $this->model['name'];
		$this->type = (string) $this->model['type'];

		@$this->parent_node = array_shift( $this->model->xpath( ".." ) ) 
			and $this->parent_name = (string) $this->parent_node['name']
			and $this->parent = $xpdoc->instances[$this->parent_name];

		M()->info('OK');

	}/*}}}*/ 

		function load_metadata() {/*{{{*/

		$file = $this->get_module_path( $this->class_name . '.metadata.xml' );

		( $metadata = simplexml_load_file( $file ) 
			and @$this->metadata = array_shift( $metadata->xpath("/application/obj[@name='{$this->class_name}']") ) 
			and M()->info("metadata para el objeto {$this->class_name} OK" ) ) 
		or M()->fatal("No puedo cargar la metadata de $file: ");

	}/*}}}*/

	function set_metadata( $metadata = null ) {/*{{{*/

		global $xpdoc;

		$this->metadata = $metadata 
		or ( $xpdoc 
			and $xpdoc->metadata 
			and @$this->metadata = array_shift ( $xpdoc->metadata->xpath( "/application/obj[@name='{$this->class_name}']" ) ) ) 
		or $this->load_metadata(); 

		M()->info('OK');

	}/*}}}*/ 

	function set_features() {/*{{{*/

		@$feat = array_shift( $this->metadata->xpath("feat") )
		and M()->info('Encontre features en el metadata, haciendo override')
		and $this->feat->override( $feat );
	}/*}}}*/

	// acl

	function set_acl() {/*{{{*/

		global $xpdoc;

		if ( ! ( isset( $xpdoc->user ) or isset( $xpdoc->user->user_id ) ) ) {

			M()->info( "no hay un usuario definido" );
			return;
		}

		if ( $xpdoc->perms ) {

			$this->acl = $xpdoc->perms->get_module_permissions( $this->class_name, $xpdoc->user->user_id );
			M()->info("usuario: {$xpdoc->user->user_id}, objeto: {$this->class_name}, permisos:". serialize( $this->acl ) );

			// if ( $this->class_name == 'web_session' ) { echo '<pre>'; print_r( $this->acl ); exit; }

		} else M()->info('todavia no esta cargado el subsistema de ACL');


	}/*}}}*/

	function push_privileges( $acl ) {/*{{{*/

		array_push( $this->temp_acl, $this->acl );

		return $this->acl = $acl;

	}/*}}}*/

	function pop_privileges() {/*{{{*/

		$this->acl = array_pop( $this->temp_acl );

		return $this->acl;

	}/*}}}*/

		function has_role( $role ) {/*{{{*/

		global $xpdoc;
		return $xpdoc->has_role( $role );

	}/*}}}*/

	function can( $action ) {/*{{{*/
		return (boolean) @$this->acl[$action];
	}/*}}}*/

	function has_access( $action ) {/*{{{*/

		return (boolean) @$this->acl[$action];
	}/*}}}*/

	function is_virtual() {/*{{{*/

		return $this->metadata['virtual'];
	}/*}}}*/

	function is_view() {/*{{{*/

		return (boolean) count( $this->model->xpath( "queries//query[@name='main_sql']/sql" ));
	}/*}}}*/

	// attr

	function attr( $node_or_name, $type = null ) {/*{{{*/

		if ( is_object( $node_or_name ) ) { 

			$node = $node_or_name;
			$name = (string) $node['name'];
			$type = (string) $node['type'];

		} else {

			$name = $node_or_name;
			$type = ($type) ? $type : 'xpstring';
			$node = new SimpleXMLElement( "<attr name=\"$name\" type=\"$type\"/>" );
		}

		$class_name = $this->map_node_type( $node );
		require_once "datatypes/$class_name.class.php";
		$this->attr[$name] = new $class_name( $node );

		// print $node->asXML(); exit;

		$this->attr[$name]->obj = $this;
		$this->attr[$name]->type = $class_name;

		if ( (bool) $node['alias'] ) {

			$alias = (string) $node['alias'];
			$this->aliases[$alias] = $this->attr[$name];
			M()->info( "alias [$this->class_name::$alias] para attr [$this->class_name::$name]" );
		}

		// autonumeric
		if ( (int) $node['auto_increment'] == 1 ) {

			$this->autonumeric_field = $name;
			M()->info( "autonumeric: [$this->class_name::$name]" );
		}

		M()->debug("agrego el atributo $this->class_name::$name con el datatype $class_name" );
		return $this->attr[$name]; 

	}/*}}}*/

	function get_attr( $name ) {/*{{{*/

		if ( !$name )
			return null;

		if ( isset( $this->attr[(string)$name] ) ) 
			return $this->attr[$name];

		else if ( array_key_exists( $name, $this->aliases ) )
			return $this->aliases[$name];

		else 	
			return null;

	}/*}}}*/

	function load_obj_attrs() {/*{{{*/

		if ( count( $nodes = $this->metadata->xpath( "attr" ) ) ) {

			foreach ( $nodes as $node ) 
				$this->attr( $node ) ;

			$this->reset();

		} else 
			M()->info("no encuentro atributos del objeto {$this->class_name} en el metadata" );

		return $this;

	}/*}}}*/

	function map_node_type( $node ) {/*{{{*/

		global $xpdoc;

		M()->debug( "Solicitando tipo {$node['type']} del attr {$node['table']}::{$node['name']}" );

		if ( ! $node['type'] ) return 'xpstring'; 
		if ( $node['entry_help'] ) return 'xpentry_help';
		if ( strtolower( substr( $node['type'], 0, 2 )) == 'xp' ) return (string) $node['type'];

		$type = null;

		if ( ! is_object( $xpdoc->datatypes ) )
			$xpdoc->load_datatypes();

		@$type = array_shift( $xpdoc->datatypes->xpath( "xtype[type/@name='{$node['type']}']/@name" ) )
		or M()->warn( "No encuentro el tipo de datos {$node['type']} del attr {$node['table']}::{$node['name']}" );
	
		return $type ? (string) $type : 'xpstring';

	}/*}}}*/

	function has_attr_type( $types ) {/*{{{*/

		foreach ( $this->attr as $key => $attr ) 

			if ( strstr( $types, $attr->type ) ) return true;

		return false;
	}/*}}}*/

	function protect_all( $not_these = null ) {/*{{{*/

		array_unique( $not_these );

		foreach( $this->attr as $key => $attr ) {

			if ( in_array( $key, $not_these ) ) continue;

			$this->attr[$key]->display = 'protect';

			}

	}/*}}}*/

	// load

	function load( $key = null , $where = null ) {/*{{{*/

		if ( $key === null and $where === null ) 
			$key = $this->primary_key;

		$objs = $this->loadc( $key, $where );

		$this->reset(); 

		if ( count( $objs ) ) {

			$this->data = array_shift( $objs );
			$this->set_primary_key();
			$this->loaded = true;
			return $this;
		}

		return null; 

	}/*}}}*/

	function loadc( $key = null, $where = null, $order = null ) {/*{{{*/

		$this->sql->clear();

		if ( !$this->acl ) 
			$this->set_acl();

		$this->sql_prepare();

		// el loadc() vacio carga la consulta actual paginada
		if ( $key === null ) {

			M()->debug( "recibiendo una clave nula. no hay criterios definidos. Aplicando la busqueda global" ); 
			$this->set_search(); // global search

		} else {

			if ( is_array( $key ) ) {

				try { 
					M()->debug( 'la clave es un array: '. serialize( $key ) ); 

				} catch( Exception $e ) {

					M()->error( "En el array de busqueda hay objetos o valores complejos. NO puedo buscar" );
					return null;
				} 

				$search = $key;

			} else {

				$search = array();

				M()->debug( "la clave es un escalar $key" );

				if ( count( $this->primary_key ) > 1 ) 
					M()->warn( 'escalar para una clave compuesta: el resultado puede ser multiple' );

				reset( $this->primary_key );
				$search[key( $this->primary_key )] = $key;
			} 

			// M()->debug( 'clave a buscar: '. serialize( $search ) );
			$this->set_const( $this->search->process( $search ) );

		} // else !$key

		if ( is_string( $where ) ) {

			M()->debug( "recibiendo una directiva para WHERE: $where" );
			$this->sql->addWhere( $where );
		}

		// $this->set_const( $this->set_foreign_key() );
		// $this->set_const( $this->set_user_key() );

		$this->set_order( $order );
		$this->main_sql();
		return $this->page();

	}/*}}}*/

	function load_array_recordset() {/*{{{*/

			$objs = array();
			$objs_count = 0;

			while ( !$this->recordset->EOF ) {

				$this->reset();
				$this->bind_data( $this->recordset->fields );

				$this->set_primary_key();
				$objs[$this->guess_primary_key()] = $this->data;
				
				$this->recordset->MoveNext();
				$objs_count ++;

			} 

			// echo '<pre>'; print_r( $objs ); echo '</pre>';

			M()->info( $this->class_name. ' cargados/contados: ' . count( $objs ). '/'.$objs_count);

			if ( count( $objs ) < $objs_count ) 

				M()->warn( 'en '. $this->class_name .' se han cargado menos items que los que se contaron. Revise la clave primaria (que deben ser valores unicos)' );

			return $objs;
	}/*}}}*/

	function load_page( $key = null, $where = null ) {/*{{{*/

		return new xpIterator( $this, $this->loadc( $key, $where ), false );

	}/*}}}*/

	function load_set( $key = null, $where = null, $order = null ) {/*{{{*/

		return new xpIterator( $this, $this->loadc( $key, $where, $order ), true );

	}/*}}}*/

	// sql query

	function replace_table_name( $from, $to, $string ) {/*{{{*/

		$from = (string) $from;
		$to = (string) $to;
		$string = (string) $string;

		if ( !$from or $from == '' or $from == $to ) 
			return $string;

		M()->info( "from $from, to: $to, string: $string" );

		$ret = str_replace( $this->quote_name( $from ).'.', $this->quote_name( $to ).'.', $string );
		M()->info( "ret: $ret" );

		$ret = str_replace( "$from.", "$to.", $ret );
		M()->info( "ret: $ret" );

		return $ret;
	}/*}}}*/

	function sql_prepare () {/*{{{*/

		$this->sql->clear();

		// para que no se repitan las tablas entre table y alias
		// print $this->model->asXML(); ob_flush(); 

		$this->uniq_tables();
		$this->uniq_tables( $this->table_name );

		// busca la consulta principal
		$fq = $this->feat->query_name;
		$query_name = $fq ? $this->feat->query_name : 'main_sql';
		@$main_sql = array_shift ( $this->model->xpath( "queries//query[@name='$query_name']" ) );

		$main_sql or M()->fatal( "no encuentro la query $query_name para el objeto {$this->class_name}" );

		if ( $main_sql->sql ) {

			M()->info( "este objeto tiene definida una vista" );
			return;
		}

		M()->info( "query $query_name para el objeto {$this->class_name}" );

		// busco los atributos con su entry help
		// y arma la vista


		if ( $this->feat->load_full_query ) {

			foreach( $this->metadata->xpath( "attr[@entry_help]") as $attr ) {

				// el query que se corresponde al entry_help
				$query_name = (string) $attr['entry_help'];
				foreach( $main_sql->xpath( ".//query[@name='$query_name']" ) as $query )  {

					// agrego los label para cada uno de los entry helpers

					if ( $this->feat->add_labels ) {

						$attr_name = $attr['name']. '_label';
						$_attr = $this->attr( $attr_name );
						$_attr->alias_of = (string) $query->label;
						$_attr->display = 'sql'; // solo para ser accedido al momento de la consulta

						// $this->sql->addQuery( sprintf( "%s as %s", $query->label, $this->quote_name( $attr_name ) ) );
					}

					// agrego el join del from
					$this->uniq_tables($query->from, $query->alias) or $this->sql->addJoin( 
							(string) $query->from, 
							$this->quote_name( (string) $query->alias ),
							sprintf( "%s=%s", $this->quote_name( "{$this->table_name}.{$attr['name']}" ), $this->quote_name( $query->id ) ).
							( $query->on ? ' AND '. $query->on : null ), 
							(string) "left" );

					// agrego el resto de los joins del query
					foreach ( $query->xpath( "join" ) as $join )
						$this->uniq_tables( $join['table'], $join['alias'] ) or $this->sql->addJoin( 
								(string) $join['table'], 
								$this->quote_name( (string) $join['alias'] ),
								(string) $join->where, 
								(string) $join['type'] );
				}
			}
		}

		if ( $this->queries_array ) {

			$protect_list_attr = array();

			foreach( $this->queries_array as $query_name ) {

				M()->debug( "agrego query $query_name" );

				foreach( $this->model->xpath( "queries//query[@name='$query_name']" ) as $query_xml ) {

					// cuando "sumo" queries, tengo que cambiar el alias por la tabla original

					$r_table = (string) $query_xml->from;
					$r_alias = (string) $query_xml->alias;

					M()->debug( "encontre query {$query_xml['name']}" );

					foreach ( $query_xml->xpath( "join" ) as $join )
						$this->uniq_tables( $join['table'], $join['alias'] ) or $this->sql->addJoin( 
								(string) $join['table'], 
								$this->quote_name( (string) $join['alias'] ),
								$this->replace_table_name( $r_alias, $r_table, (string) $join->where), 
								(string) $join['type'] );

					foreach( $query_xml->xpath( "on" ) as $on ) 
						$this->sql->addWhere( $this->replace_table_name( $r_alias, $r_table, (string) $on ) );

					foreach ( $query_xml->xpath( "where" ) as $where )
						$this->sql->addWhere( $this->replace_table_name( $r_alias, $r_table, (string) $where ) );

					foreach ( $query_xml->xpath( "order_by" ) as $order )
						$this->sql->addOrder( $this->quote_order( $this->replace_table_name( $r_alias, $r_table,  (string) $order ) ) );

					// atributos de la consulta

					foreach ( $query_xml->xpath( "id" ) as $id ) {
						$this->attr( 'id' )->alias_of = $this->replace_table_name( $r_alias, $r_table, (string) $id );
						array_push( $protect_list_attr, 'id' );
						}

					foreach ( $query_xml->xpath( "label" ) as $label ) {
						$this->attr( '_label' )->alias_of = $this->replace_table_name( $r_alias, $r_table, (string) $label );
						array_push( $protect_list_attr, '_label' );
						}

					foreach ( $query_xml->xpath( "attr" ) as $attr ) {

						// primero lo busco a ver si esta en la lista y solo quiero que aparezca ...

						$name = (string) $attr['name'];

						$attr = null;

						if ( $attr = $this->get_attr($name) ) {
							M()->debug( "encontre el attr $name" );
							$attr->display = '';
						} else {

							$attr = $this->attr( $name )->display = '';

							if ( (string) $attr['alias_of'] )
								$this->attr($name)->alias_of = (string) $attr['alias_of'];

						}
						array_push( $protect_list_attr, $name );
					}

					// echo "<pre>"; print_r( $attr->data ); echo "</pre>"; ob_flush(); exit;
					// cada <attr/> del query como campo adicional
					// echo $query_xml->asxml(); exit;
				}
			}

			if ( count( $protect_list_attr ) ) 
				$this->protect_all( $protect_list_attr );
		}

		// crea la consulta principal
		// agrego la tabla y su alias, si esta definido

		if ( $alias = (string) $main_sql->alias ) 
			$this->sql->addTable( $this->table_name, $alias );
		else
			$this->sql->addTable( $this->table_name );

		// agrego el where del main_sql
		if ( $main_sql->where )
			$this->sql->addWhere( (string) $main_sql->where );

		// agrego el order_by 
		if ( $main_sql->order_by )
			$this->sql->addOrder( $this->quote_order( $this->replace_table_name( (string) $main_sql->alias, $this->table_name,  (string) $order ) ) );


		// cargo los campos desde los atributos del objeto
		foreach( $this->attr as $key => $attr ) {

			if ( ( $attr->virtual and !$attr->alias_of ) or ( $this->attr == 'ignore' ) ) continue;

			$field_name = $this->quote_name( sprintf( "%s.%s", $this->table_name, $attr->name ) );

			if ( $attr->alias_of ) 
				$this->sql->addQuery( sprintf( "%s as %s", $attr->alias_of, $this->quote_name( $attr->name ) ) );
			else
				$this->sql->addQuery( $field_name );
		}

		// comentados para debug
		// if ( $this->class_name == 'dtNotificacionActor' ) { $this->metadata(); exit; }
		// if ( $this->class_name == 'REMPLES' ) { echo '<pre>'; $this->debug_object(); print_r( $this->sql ) ; echo '</pre>'; exit; }
		// $this->debug_object(); ob_flush(); exit;

	}/*}}}*/

	function set_search() {/*{{{*/

		global $xpdoc;

		// print_r( debug_backtrace( false ) ); exit;
		// print 'hola'; $xpdoc->search; exit;


		if ( ( $xpdoc->search )
			and array_key_exists($this->class_name, $xpdoc->search)
			and is_array( $xpdoc->search[$this->class_name] ) ) {

			M()->info( 'search key: aplicando busqueda global con '. serialize( $xpdoc->search ) );

			$search = new xpsearch( $this );
			$search->match_type = $xpdoc->feat->match_type ? $xpdoc->feat->match_type : 'anywhere';

			$this->set_const( $search->process( $xpdoc->search[$this->class_name] ) );
		}
	}/*}}}*/

	function quote_name( $name = null ) {/*{{{*/

		if ( $name ) 
			return $this->sql->quote_name( $name );
		else return null;
	}/*}}}*/

	function quote_order( $order ) {/*{{{*/

		$res = array();

		$order = preg_replace('/[\r\n\s]+/xms', ' ', trim($order));
		$order = preg_replace('/\s*,\s*/s', ',', trim($order));

		$ao = explode( ',', $order );


		foreach( $ao as $a ) {

			@list( $field, $dir ) = explode( ' ', $a );
			$res[] = $this->quote_name( $field ). ' '. $dir;
		}

		$tmp = implode( ',', $res );

		// echo '<pre>'; print_r( $tmp ); exit;

		return $tmp;

	}/*}}}*/

	function set_order( $order = null ) {/*{{{*/

		global $xpdoc;

		is_array( $order ) or @$order = $xpdoc->order[$this->class_name]; 

		if ( is_array( $order ) ) 

			foreach ( $order as $key => $data ) 

				foreach ( $this->attr as $name => $attr )

					if ( $name == $key )

						$this->order_array[$key] = $data;


		if ( count( $this->order_array ) ) {

			foreach ( $this->order_array as $key => $asc_desc ) {

				// si es un alias, el nombre no lleva el prefijo de la tabla

				$field = ( !$this->attr[$key]->alias_of ) ? 
					$this->attr[$key]->table. '.'. $this->attr[$key]->name : 
					$this->attr[$key]->name;

				if ( $this->attr[$key]->is_entry_help() ) 

					$field = $this->attr[$key]->name. '_label'; 

				$this->sql->addOrder( $this->quote_order( $field ). " $asc_desc" ) ;

			}
		}

	}/*}}}*/ 

	function db_type() {/*{{{*/

		return $this->db->databaseType;
	}/*}}}*/

	function set_const( $const ) {/*{{{*/

		if ( ! count( $const ) ) return;

		if ( @$this->foreign_key['@set'] == 'variables' ) {

			$comma = array();

			foreach( $const as $key => $vars )

				$comma = array_merge( $comma, $vars );

			$sql = "SET ". implode( ',', $comma );

			$this->db->Execute( $sql );


		} else { 

			/* carga los OR en la consulta sql */

			if ( is_array ( @$const['OR'] ) and count( $or_array = $const['OR'] ) ) {

				$constraint_fn = ( $this->db_type() == 'mssql' or $this->db_type() == 'sybase' ) ? 'addWhere' : 'addHaving' ;

				$this->sql->$constraint_fn( '( '. implode( ' OR ', $or_array ). ' )' );
			}


			/* carga los where en la consulta sql */

			if ( is_array ( @$const['where'] ) ) 

				foreach ( $const['where'] as $where ) 

					$this->sql->addWhere( $where );

			/* carga los having en la consulta sql */

			if ( is_array( @$const['having'] ) )

				foreach ( $const['having'] as $having ) 

					$this->sql->addHaving( $having );
		}


		// M()->debug( "sql_query: ". $this->sql->prepare() ) ;

	}/*}}}*/ 

	function page ( $page = null ) {/*{{{*/

		/*

		echo '<pre>'; 
		$this->db->Execute( "SET @ID_Proceso = '17af85533edfbc84cf33c0a1ddc1ef9d'" );
		print_r ( $this->db->Execute( "call getFreeTime( @ID_Proceso )" ) );

		exit;

		*/

		global $xpdoc, $ADODB_COUNTRECS;

		if ( ! $this->pager )
			$this->pager = array( 'pr' => 0, 'cp' => 1 );

		$pr =& $this->pager['pr'];
		$cp =& $this->pager['cp'];

		if ( isset( $xpdoc->pager ) and array_key_exists ( $this->class_name, $xpdoc->pager ) and is_array( $xpdoc->pager[$this->class_name] ) ) {

			isset( $xpdoc->pager[$this->class_name]['pr'] ) and $pr = $xpdoc->pager[$this->class_name]['pr'];
			isset( $xpdoc->pager[$this->class_name]['cp'] ) and $cp = $xpdoc->pager[$this->class_name]['cp'];
		}

		if ( $this->feat->page_rows !== null  and !$pr ) 
			$pr = $this->feat->page_rows;

		if ( $page !== null ) $cp = $page;

		M()->info( "object: {$this->class_name}, page_rows: $pr, current_page: $cp" );

		$savec = $ADODB_COUNTRECS;

		if ( $this->feat->count_recs ) 
			$ADODB_COUNTRECS = true;

		if ( $sql_code = $this->model->xpath( "queries//query[@name='main_sql']/sql" ) ) {

			M()->info( 'ejecutando '. count( $sql_code ). ' fragmentos de codigo SQL para la vista '. $this->class_name );

			// DEBUG: para que las vistas puedan ser paginadas, ordenadas, etc, deben tomar la parte de WHERE/HAVING/ORDER de sql->prepare()
			// Debe ser aplicada solamente sobre la ultima

		} else { 

			if ( $this->db_type() == 'mssql' or $this->db_type() == 'sybase' ) {

				$sql_code = array( $this->sql );

			} else {

				$sql_code = array( $this->sql->prepare() );

				if ( $this->feat->full_sql_log )
					M()->debug( $sql_code[0] );
				else 
					M()->debug( 'SELECT ... '. stristr( $sql_code[0], 'FROM' ) );

			}
		}


		foreach( $sql_code as $sql_p ) {

			if ( $pr ) {

				if ( $this->db_type() == 'mssql' or $this->db_type() == 'sybase' ) {

					// hace la paginacion via consulta para los motores que no tienen LIMIT (mssql)
					$ADODB_COUNTRECS = false;
					$this->recordset = $this->paged_query( $sql_p, $pr, $cp, $this->feat->db_cache_time );

				} else {

					if ($this->feat->db_cache_time)
						$this->recordset = $this->db->CachePageExecute( $this->feat->db_cache_time, (string) $sql_p, $pr, $cp );
					else
						$this->recordset = $this->db->PageExecute( (string) $sql_p, $pr, $cp );
				}

			} else {

				if ($this->feat->db_cache_time)
					$this->recordset = $this->db->CacheExecute( $this->feat->db_cache_time, $sql_p );
				else
					$this->recordset = $this->db->Execute( (string) $sql_p );
			}
		}

		$ADODB_COUNTRECS = $savec;



		if ( $this->recordset ) {
			if ( $this->db_type() == 'mssql' or $this->db_type() == 'sybase' )
				$this->total_records = $this->recordset->fields['__TotalRows'];
			else
				$this->total_records = $pr ? $this->recordset->MaxRecordCount() : $this->recordset->RecordCount();
			}
		else
			$this->total_records = -1;

		M()->debug('total_records: '. $this->total_records );

		if ( $this->total_records == -1 ) {

			$this->loaded = false;

			M()->error( sprintf( "Error al seleccionar (%d): %s", $this->ErrorNo(), $this->ErrorMsg(), $this->sql->prepare() )) ;
			return null;

		} else if ( $this->total_records == 0 ) {

			// print_r( $this->primary_key ); exit;
			$this->loaded = false;
			M()->info( 'no encontre registros en: '. $this->sql->prepare() ) ;
			return null;

		} else {

			return $this->load_array_recordset();

		}

	}/*}}}*/

	function paged_query( $sql, $pr, $cp, $time ) {/*{{{*/

		/*
		
		tomado de http://blog.pengoworks.com/index.cfm/2008/6/10/Pagination-your-data-in-MSSQL-2005
		
		-- create the Common Table Expression, which is a table called "pagination"
		with pagination as
		(
		    -- your normal query goes here, but you put your ORDER BY clause in the rowNo declaration
		    select
		        row_number() over (order by department, employee) as rowNo,
		        -- a list of the column you want to retrieve
		        employeeId, employee, department 
		    from 
		        Employee
		    where 
		        disabled = 0
		)
		-- we now query the CTE table
		select 
		    -- add an additional column which contains the total number of records in the query
		    *, (select count(*) from pagination) as totalResults
		from
		    pagination
		where 
		    RowNo between 11 and 20     
		order by
		    rowNo
		*/

		$offset = $pr * ($cp-1);

		M()->info( "offset: $offset" );

		$q = array();

		$q[] = "WITH PAGINATION AS (";

			$q[] = "SELECT ROW_NUMBER() ";
			$q[] = " OVER (";

			if ( $sql->make_order_clause() )
				$q[] = $sql->make_order_clause();
			else
				$q[] = $sql->make_order_clause( $this->get_primary_key_array() );

			$q[] = ") AS __RowNumber,";

			$q[] = $sql->prepareSelectFields();
			$q[] = "FROM [$this->table_name]";

			$q[] = $sql->make_join();
			$q[] = $sql->make_where_clause();
			$q[] = $sql->make_group_clause();
			$q[] = $sql->make_having_clause();

		$q[] = ")";

		$q[] = "SELECT *, (SELECT COUNT(*) FROM PAGINATION) as __TotalRows FROM PAGINATION WHERE __RowNumber BETWEEN $offset AND ". (string) ($offset + $pr). "  ORDER BY __RowNumber";

		$query = implode( ' ', $q );

		// print $query; exit;

		M()->info( $query );

		if ( $time )
			return $this->db->CacheExecute( $time, $query );
		else
			return $this->db->Execute( $query );

	}/*}}}*/

	function execute( $sql = null ) {/*{{{*/

		// DEBUG: habria que hacer una funcion para poder detectar si hace un INSERT_OP, UPDATE_OP, etc.
		// probablemente con una regexp

		$sql or $sql = $this->sql->prepare();

		if ( ( $rs = $this->db->Execute( (string) $sql ) ) ) {

			M()->info( "ejecutado $sql" );
			return $rs;

		} else {

			M()->error( sprintf( "Error al ejecutar (%d): %s %s", $this->ErrorNo(), $this->ErrorMsg(), $this->sql->prepare() )) ;
			return null;
		}
	}/*}}}*/

	function add_query( $query ) {/*{{{*/

		$this->queries_array = explode( ",", $query );

	}/*}}}*/

	function uniq_tables( $table = null, $alias = null ) {/*{{{*/

		if ( !$table ) {
			$this->uniq_tables_array = array();
			return;
		}

		$table = (string) $table;
		$alias = (string) $alias;

		$ret = false;

		// que no repita el nombre de la tabla
		// para referenciar a la misma tabla usar un alias

		$table_name = (string) $alias ? (string) $alias : (string) $table ;

		if ( in_array( $table_name, $this->uniq_tables_array ) )
			$ret = true;
		else 
			array_push( $this->uniq_tables_array, $table_name );

		return $ret;
	}/*}}}*/

	// bind

	public function bind( $hash, $track_modified = false ) {/*{{{*/

		return $this->bind_data( $hash, $track_modified );
	}/*}}}*/

	private function bind_data( $hash, $track_modified = false ) {/*{{{*/

		if ( !is_array( $hash ) ) {
			M()->error(  get_class( $this ) . "::bind(): el parametro no es un array" );
			return null;
		} 


		$tm = $this->track_modified;

		$this->track_modified = $track_modified;
		$this->bind_hash( $hash );
		$this->track_modified = $tm;

		return $this;

	}/*}}}*/

	private function bind_hash( $hash ) {/*{{{*/

		foreach ( $this->attr as $key => $attr ) 
			$attr->bind( $hash );

		return $this;

	}/*}}}*/

	public function bind_store( $hash, $track_modified = true ) {/*{{{*/

		if ( $this->bind_hash( $hash, $track_modified ) ) 
			return $this->store();
		else {
			M()->error( 'no pude hacer bind_store' );
			return null;
		}

	}/*}}}*/

	// manipulacion datos

	function store( $validate = true ) {/*{{{*/

		// $this->debug_object();

		if ( !$this->modified ) return $this->transac_status = NO_OP;

		if ( $validate and $this->get_flag( 'check' ) and ( ! $this->check() ) ) {

			M()->info( "el metodo {$this->class_name}::check ha devuelto falso. No puedo guardar" );
			M()->user( "Datos inválidos o incompletos: no se pueden guardar", $this->class_name, 'not_valid' );
			$this->transac_status = NOT_VALID;

		} else { 

			$this->transac_status = $this->loaded ? $this->update() : $this->insert();

			$this->loaded = true;
			$this->set_primary_key();

			$this->post_check();
		}

		return $this->transac_status;
	}/*}}}*/

	function insert () {/*{{{*/

		if ( !$this->modified ) return $this->transac_status = NO_OP;

		global $xpdoc;

		if ( ! $this->has_access( 'add' ) ) {

			M()->user( 'No tiene permisos para agregar el objeto '. $this->class_name );
			return $this->transac_status = NO_PERMS;
		}

		$this->sql->clear();

		$this->sql->addTable ( $this->table_name ) ;

		/* genera la consulta con los valores modificados del objeto */

		foreach ( $this->attr as $key => $attr ) {

			if ( $attr->virtual or $attr->alias_of ) continue;
			if ( !$attr->modified ) continue;

			// M()->debug( "Asignando {$attr->name} = {$attr->value}" );

			$this->sql->addInsert( $attr->name, $attr->encode());

		}

		/* registra el where en la consulta con los valores de la clave primaria */

		foreach ( $this->primary_key as $key => $value ) {

			$attr = $this->get_attr( $key );

			if ( $value === null )
				$this->sql->addWhere( $key . ' IS NULL' );  
			else
				$this->sql->addWhere( $key . '=\''. $attr->encode( $value ). '\'' );  

		}

		M()->debug( 'insertando ' . $this->sql->prepare() );

		if ( !$this->sql->Exec() ) {

			if ( $this->ErrorNo() == 1062 and $this->feat->try_update_on_fail ) {

				// DEBUG: debe chequear si la clave primaria esta completa (ej. autonumeric) si no, no lo puede hacer

				M()->info( 'Probando un update on key fail' );

				return $this->update();

			} else {

				// DEBUG: por ahora le manda el mensaje sql al usuario para que se entretenga ... :)
				// pero hay que poner una funcion de un handler sql serio con mensajes traducidos de acuerdo a la implementacion
				// aunque sea traducir los m[as significativos en una funcion M()->db_error

				M()->user( sprintf( "Error al agregar (%d): %s", $tmp = $this->ErrorNo(), $this->ErrorMsg())) ;
				return ( $this->transac_status = DB_ERROR );
			}


		} else {

			if ( $af = $this->get_autonumeric_field() ) {

				/* para autonumerico, actualiza la clave con su valor */

				$attr = $this->get_attr( $af );
				$attr->value = $this->db->Insert_ID();
				M()->debug( $attr->name. ": ". $attr->value );
			}

			return ( $this->transac_status = INSERT_OP );
		}
	}/*}}}*/

	function replace( $modifiers = null ) {/*{{{*/

		if ( !$this->modified ) return $this->transac_status = NO_OP;

		global $xpdoc;

		if ( ! $this->has_access( 'add' ) ) {

			M()->user( 'No tiene permisos para agregar el objeto '. $this->class_name );
			return $this->transac_status = NO_PERMS;
		}

		$this->sql->clear();

		$modifiers and $this->sql->modifiers = $modifiers;

		$this->sql->addTable ( $this->table_name ) ;

		/* genera la consulta con los valores modificados del objeto */

		foreach ( $this->attr as $key => $attr ) {

			if ( $attr->virtual or $attr->alias_of ) continue;
			if ( !$attr->modified ) continue;

			// M()->debug( "Asignando {$attr->name} = {$attr->value}" );

			$this->sql->addReplace( $attr->name, $attr->encode() );

		}

		/* registra el where en la consulta con los valores de la clave primaria */

		foreach ( $this->primary_key as $key => $value ) {

			$attr = $this->get_attr( $key );

			if ( $value === null )
				$this->sql->addWhere( $key . ' IS NULL' );  
			else
				$this->sql->addWhere( $key . '=\''. $attr->encode( $value ). '\'' );  

		}

		M()->debug( 'reemplazando ' . $this->sql->prepare() );

		if ( !$this->sql->Exec() ) {
	
				unset( $this->sql->modifiers );

				// DEBUG: por ahora le manda el mensaje sql al usuario para que se entretenga ... :)
				// pero hay que poner una funcion de un handler sql serio con mensajes traducidos de acuerdo a la implementacion
				// aunque sea traducir los m[as significativos en una funcion M()->db_error

				M()->user( sprintf( "Error al agregar (%d): %s", $tmp = $this->ErrorNo(), $this->ErrorMsg())) ;
				return ( $this->transac_status = DB_ERROR );

		} else {

			unset( $this->sql->modifiers );

			if ( $af = $this->get_autonumeric_field() ) {

				$attr = $this->get_attr( $af );
				$attr->value = $this->db->Insert_ID();
				M()->debug( $attr->name. ": ". $attr->value );
			}

			return ( $this->transac_status = INSERT_OP );
		}
	}/*}}}*/

	function update () {/*{{{*/

		if ( !$this->modified ) return $this->transac_status = NO_OP;

		global $xpdoc;

		if ( ! $this->has_access( 'edit' ) ) {

			M()->user( 'No tiene permisos para modificar el objeto '. $this->class_name );
			return $this->transac_status = NO_PERMS;
		}

		$this->sql->clear();

		$this->sql->addTable ( $this->table_name ) ;

		/* genera la consulta con los valores modificados del objeto */

		foreach ( $this->attr as $key => $attr ) {

			if ( $attr->virtual or $attr->alias_of ) continue;
			if ( !$attr->modified ) continue;

			$this->sql->addUpdate( $attr->name, $attr->encode() );

		}
		

		/* registra el where en la consulta con los valores de la clave primaria */

		foreach ( $this->primary_key as $key => $value ) {

			$attr = $this->get_attr( $key );
			
			if ( $value === null )
				$this->sql->addWhere( $key . ' IS NULL' );  
			else
				$this->sql->addWhere( $key . '=\''. $attr->encode( $value ). '\'' );  
		}

		// DEBUG: comentado por que le manda 'LOCK IN SHARE MODE' y no debe ...
		// por el bugfix del adodb
		// $this->sql->setLimit( 1 );

		M()->debug( 'update ' . $this->sql->prepare() );

		// $this->debug_object( $this->sql ); exit; }

		if ( !$this->sql->Exec() ) {

			if ( $this->ErrorNo() == 1062 )
				M()->user( 'Datos duplicados en el ingreso, no se puede guardar' );
			else
				M()->user( sprintf( "Error al actualizar (%d): %s en %s", $this->ErrorNo(), $this->ErrorMsg(), $this->sql->prepare())) ;

			return ( $this->transac_status = DB_ERROR );

		} else {

			return ( $this->transac_status = UPDATE_OP );
		}
	}/*}}}*/

	function delete() {/*{{{*/

		global $xpdoc;

		if ( ! $this->has_access( 'delete' ) ) {

			M()->user( 'No tiene permisos para elminar el objeto '. $this->class_name );
			return $this->transac_status = NO_PERMS;
		}

		if ( ! $this->pre_delete() ) {

			M()->user( 'No puede borrar este objeto '. $this->class_name );
			return $this->transac_status = NOT_VALID;
		}

		$this->sql->clear();

		$this->sql->setDelete( $this->table_name );

		foreach ( $this->primary_key as $field => $value ) 

			$this->sql->addWhere( $this->quote_name( "{$this->table_name}.$field" ). sprintf( $value === null ? 'IS NULL' : "='%s'", $value ) ) ;

		foreach ( $this->model->xpath( "obj[foreign_key/@type='wired']" ) as $obj ) {

			// borra recursivamente los hijos que el foreign_key/@type='wired' en el model

			$child_name = (string) $obj['name'];
			M()->debug( "a borrar $child_name" );

			$cond = array();
			foreach ( $obj->xpath( "foreign_key/ref" ) as $ref )
				$cond[(string)$ref['local']] = $this->$ref['remote'];

			$childs = $xpdoc->get_instance( $child_name );

			foreach( $childs->load_set( $cond ) as $child )
				$child->delete(); 

		}


		if ( $this->sql->Exec() ) {

			M()->info( "objeto {$this->class_name} borrado(s)" );

			$this->post_delete();

			$this->reset(); // el reset lo hago despues del post delete para que esten las variables disponibles al momento del metodo

			return ( $this->transac_status = DELETE_OP );

		} else {

			M()->error( sprintf( "No puedo eliminar. Error (%d): %s ", $this->ErrorNo(), $this->ErrorMsg() ) ); 
			return null;
		}


	}/*}}}*/

	// reset

	function reset() {/*{{{*/

		// deja el objeto en blanco

		M()->debug( "reset objeto $this->class_name" );
		
		if ( ! is_array( $this->attr ) ) {

			M()->warn( "$this->class_name: objeto sin atributos" );
			return $this;
		}

		unset( $this->data );
		$this->data = array();

		foreach( $this->attr as $attr ) 
			$this->data[$attr->name] = null;

		$this->loaded = false;
		$this->set_modified( false ); // attrs

		return $this;

	}/*}}}*/

	function get_data( $data ) {/*{{{*/

		return $this->data;

	}/*}}}*/

	function set_data( $data ) {/*{{{*/

		// DEBUG: funcion interna, ojo que no valida si es array();

		return $this->data = $data;

	}/*}}}*/

	function set_modified( $value ) {/*{{{*/

		$this->modified = $value;
		foreach( $this->attr as $key => $attr ) $attr->modified = $value;

	}/*}}}*/

	function get_modified_attrs() {/*{{{*/

		$ret = array();

		foreach( $this->attr as $attr ) 

			if ( $attr->modified )

				$ret[] = $attr;

		return $ret;
	}/*}}}*/

	// funciones para la serializacion

	function serialize ( $flags ) {/*{{{*/

		global $xpdoc;

		$any = $flags & DS_ANY;
		$normalized = $flags & DS_NORMALIZED;
		$recursive = $flags & DS_RECURSIVE;
		$blank = $flags & DS_BLANK;
		$defaults = $flags & DS_DEFAULTS;

		$e = $normalized ? 'class' : $this->feat->container_tag;


		$xc = new SimpleXMLElement( "<$e/>" ); 
		$xc['name'] = $this->class_name;

		if ( is_object( $xpdoc->perms ) and ( ! ( $this->can('list') and $this->can('view') ) ) ) {

			M()->info( "acceso denegado para el objeto {$this->class_name}" );
			$xc['msg']='ACC_DENIED';
			return $xc;
		}

                if ( $this->is_virtual() and ! $this->is_view() ) {

                        M()->info( "no puedo serializar el objeto virtual {$this->class_name}" );
			$xc['total_records'] = 0;
                        $xc['msg']='IS_VIRTUAL';
                        return $xc;
                }

		$objs = null;

		if ( $any or $normalized or $recursive ) {

			if ( $recursive && is_object( $this->parent ) )
				$fk = $this->get_foreign_key();
			else
				$fk = null;

			$objs = $this->load_page( $fk ); 

		} else M()->warn( "no se ha definido ni DS_ANY ni DS_NORMALIZED ni DS_RECURSIVE: no hay registros a serializar. Revise directiva 'include_dataset'" );

		$xc['total_records'] = $this->total_records ;
		$xc['xmlns'] = null;
		$xc['page_rows'] = $this->pager['pr'];
		$xc['current_page'] = $this->pager['cp'];

		M()->info( "serialize con any: $any, normalized: $normalized, recursive: $recursive, blank: $blank, defaults: $defaults, con cantidad de objetos: ". count( $objs ) );
		
		if ( $blank and ( $any or $normalized or $recursive ) and ( $objs ) )

			{ $flags = $flags ^ DS_BLANK; }
		
		if ( $objs ) {

			foreach ( $objs as $obj ) {

				$obj->prepare_data(); // DEBUG: prepare_data deberia ir directamente en el iterador?
				simplexml_append( $xc, $obj->serialize_row( $flags ) );
			} 

		} else if ( $blank ) {  

			simplexml_append( $xc, $this->serialize_row( $flags ) );
		} 

		return $xc;

	}/*}}}*/

	function serialize_row( $flags = null, $params = null ) {/*{{{*/

		$normalized = $flags & DS_NORMALIZED;
		$recursive = $flags & DS_RECURSIVE;
		$blank = $flags & DS_BLANK;
		$defaults = $flags & DS_DEFAULTS;


		global $xpdoc;

		// $this->debug_object();

		M()->debug( "serializando instancia objeto {$this->class_name}" );

		$xobj = new SimpleXMLElement( $normalized ? '<obj/>' : "<{$this->class_name}/>" );
		if ( $normalized ) $xobj['name'] = $this->class_name;


		if ( $blank ) {

			$this->reset();
			$this->fill_primary_key( true );
			$this->set_primary_key();
			$this->assign_foreign_key();
			$defaults and $this->defaults();
		}

		$xobj['ID'] = $this->pack_primary_key();
		$xobj['edit'] = (string) $this->has_access('edit');
		$xobj['delete'] = (string) $this->has_access('delete');
		$xobj['new'] = (string) (bool) $blank;


		foreach ( $this->attr as $key => $attr ) {

			// print $attr->name;
			// print $attr->value;

			if ( $attr->display == 'protect' or $attr->display == 'ignore' or $attr->display == 'sql' ) continue;
			if ( !$blank and $attr->value === null and $this->feat->ignore_null_fields ) continue;


			simplexml_append( $xobj, $this->serialize_attr( $attr, $flags ) );
		}

		if ( $recursive ) {

			$iname = null;

			foreach ( $this->model->xpath( "obj" ) as $obj ) {

				$iname = (string) $obj['name'];

				if ( ! isset( $xpdoc->instances[$iname] ) ) 
					M()->warn( "instancia $iname ignorada: no la encuentro");
				else {

					M()->info( "recursivo, hacia el objeto $iname" );

					if ( is_array( $params ) and is_array( $params['ignore'] ) and ( in_array( $iname, $params['ignore'] ) ) )
						continue;
					else
						simplexml_append( $xobj, $xpdoc->instances[$iname]->serialize( $flags ) );

				} 
			}
		}

		return $xobj;

	}/*}}}*/ 

	function serialize_attr( $attr, $flags ) {/*{{{*/

		$normalized = $flags & DS_NORMALIZED;
		$defaults = $flags & DS_DEFAULTS;

		$attr_name = $attr->name;
		$value = $attr->serialize();

		/* value */

		try {
			$xattr = new SimpleXMLElement( $normalized ? "<attr name=\"$attr_name\">$value</attr>" : "<$attr_name>$value</$attr_name>" );

		} catch (Exception $e)  { 

			M()->warn( 'problemas con la codificacion en la serializacion, verificar la codificacion de caracteres de la base de datos y la aplicacion. Forzando la codificacion a utf8' );
			$value = utf8_encode( $value );

			try {
				$xattr = new SimpleXMLElement( $normalized ? "<attr name=\"$attr_name\">$value</attr>" : "<$attr_name>$value</$attr_name>" );

			} catch (Exception $e)  { 

				M()->fatal( "No puedo serializar el objeto {$this->class_name} con la clave [". $this->pack_primary_key(). "] en el atributo [$attr_name] con el valor [$value]. Motivo: ". $e->getMessage() );
			}
		}

		/* label */

		if ( $attr->label ) {

			// $attr->label and $xattr['label'] = utf8_encode( $attr->serialize( $attr->label ) );
			// $attr->label and $xattr['label'] = $attr->serialize( $attr->label );
			// $attr->label and $xattr['label'] = $attr->label;
			// $attr->label and $xattr['label'] = utf8_decode( $attr->label );

			$xattr->addAttribute( 'label', $attr->label );
			// M()->user( "label " . $attr->label );
		}


		return $xattr;
	}/*}}}*/

	// respuestas xml

	function init_xml_obj( $node ) {/*{{{*/

		global $xpdoc;

		if ( !count( $this->primary_key ) ) {

			$msg = "objeto $this->class_name sin clave primaria, no se puede guardar. Consulte con el administrador de la aplicacion.";
			M()->error( $msg );
			M()->user( $msg );
			$xpdoc->current_process->halt();
			return;
		}

		$this->reset();

		$key_str = (string) $node['ID'];
		$new = (string) $node['new'];
		$complete_key = pow( 2, count( $this->primary_key ) );

		/*

		if ( $this->class_name == 'gacl_aco_map' ) {

			print $key_str = "^^";
			print "<br/>";

			print_r( $key = $this->unpack_primary_key( $key_str ) );
			print "<br/>";

			print $this->check_key( $key );
			exit;

		}

		*/
 
		M()->debug( "objeto: {$this->class_name}, clave recibida: $key_str, nuevo: $new" );

		// print $this->pp( $node );

		if ( $new == '1' ) {

			$this->__new == true;

			if ( $this->check_key( $tmp = $this->unpack_primary_key( $key_str ) ) == $complete_key ) {

				M()->debug('new and key_str packed');
				$this->bind_data( $tmp, true );

			} else if ( $this->check_key( $tmp = $this->get_primary_key_node( $node ) ) == $complete_key ) {

				M()->debug('new and key_str from node');
				$this->bind_data( $tmp, true );

			} else {

				M()->debug( 'new and key_str undefined' );
				$this->fill_primary_key();

			}

		} else if ( $new == '0' ) {

			$err_msg = "el cliente indica que el registro existe pero no puedo encontrarlo en la base de datos. Verifique las claves. No puedo guardar";
			$usr_msg = "no se pudieron guardar los cambios. Consulte con el administrador";

			if ( $key_str ) {

				M()->debug('not new and key_str defined');

				if ( ! $this->load( $this->unpack_primary_key( $key_str ) ) ) {

					M()->error( $err_msg );
					M()->user( $usr_msg );
					$xpdoc->current_process->halt();
					return;
				}

			} else {

				M()->debug('not new and key_str undefined');

				if ( ! $this->load( $this->get_primary_key_node( $node ) ) ) {

					M()->error( $err_msg );
					M()->user( $usr_msg );
					$xpdoc->current_process->halt();
					return;
				}
			} 

		} else {

			M()->debug('undefined new and key_str undefined');
			$this->load( $this->get_primary_key_node( $node ) );

			if ( !$this->loaded ) 
				$this->fill_primary_key();

		}

		$this->__new == !$this->loaded;
		$this->set_primary_key();
		$this->assign_foreign_key();

		/*

		if ( $this->class_name == 'gacl_aco_map' ) {
			print_r( $this->primary_key );
			$this->debug_object( $this->primary_key ); exit;
		}
		*/

		// if ( $this->class_name == 'dtProceso' ) $this->debug_object(); exit;

		return $this;

	}/*}}}*/

	function store_xml_response( $node = null ) {/*{{{*/

		// if ( $this->class_name == 'dtProceso' ) $this->debug_object(); exit;

		global $xpdoc;

		if ( $this->modified ) {

			$this->store();

			// si hay un problema de validacion, proceso o un 'NOT_FOUND' detiene el proceso
			// DEBUG: esto deberia ser parametrizable

			if ( strstr( 'vfp', $this->transac_status ) and is_object( $xpdoc->current_process ) ) 
				$xpdoc->current_process->halt();

			M()->response( $this, $node );
		}

		return $this->transac_status;

	}/*}}}*/

	function delete_xml_response( $node ) {/*{{{*/

		if ( $this->loaded ) {
			$this->delete();
			M()->response( $this, $node );
		} else {
			M()->user('objeto no encontrado');
			M()->status('ERR');
		}

	}/*}}}*/

	// manejo de claves 

	function config_primary_key() {/*{{{*/ 

		/* carga la configuracion para la clave primaria desde el modelo */

		if ( !$this->model ) 
			M()->fatal( "no hay modelo definido: no puedo cargar la clave primaria" );

		$refs = $this->model->xpath( "primary_key/primary" );

		foreach( $refs as $ref ) {

			$name = (string) $ref['name'];
			$this->primary_key[$name] = null;
			$attr = $this->get_attr( $name );
			$attr->primary = true;
		}

		if ( !count( $this->primary_key ) )  
			M()->info( "el objeto {$this->name} no tiene clave primaria definida" );

		return $this;

		// echo "<pre>"; print_r( $this->primary_key ); echo "</pre>"; ob_flush(); exit;

	}/*}}}*/ 

	function config_foreign_key() {/*{{{*/ 

		/* carga la configuracion para la clave foranea desde el modelo */

		$xfk = array_shift( $this->model->xpath( "foreign_key" ) );

		if ( isset( $xfk['set'] ) )
			$this->foreign_key['@set'] = (string) $xfk['set'];

		if ( is_object( $xfk ) ) {
			foreach( $xfk->xpath( "ref" ) as $ref ) {

				$local = (string) $ref['local'];
				$remote = (string) $ref['remote'];

				if ( ! isset( $this->foreign_key['@set'] ) )
					if ( ! $attr = $this->get_attr( $local ) ) M()->warn( "atributo 'local' {$this->name}::$local no existe" );

				if ( ! $this->parent->get_attr( $remote ) ) M()->warn( "atributo 'remote' {$this->parent->name}::$remote no existe" );

				$attr->foreign = true;

				$this->foreign_key[$local] = new xpkey( $this->parent->table_name, $remote );
				M()->debug( "local: $local, remote: $remote, set: ". @$this->foreign_key['@set'] );
			}
		}

	}/*}}}*/

	function set_primary_key() {/*{{{*/

		if ( count( $this->primary_key ) )  
			foreach( $this->primary_key as $name => $pk )
				if ( $name ) 
					$this->primary_key[$name] = $this->$name;
				else 
					M()->error( "el nombre de la variable de la clave es nulo!" );

		else M()->warn( "objeto $this->class_name sin clave primaria: cambiar la configuracion de la tabla o configure databases.xml" );

		return $this->primary_key;

	}/*}}}*/

	function get_primary_key_node( $node ) {/*{{{*/

		$ret = array();

		if ( count( $this->primary_key ) )  
			foreach( $this->primary_key as $name => $pk ) 
					$ret[$name] = (string) $node->$name;

		return $ret;

	}/*}}}*/

	function get_primary_key() {/*{{{*/

		$ret = array();

		if ( count( $this->primary_key ) )  
			foreach( $this->primary_key as $name => $pk ) 
					$ret[$name] = (string) $this->$name;
		return $ret;

	}/*}}}*/

	function get_primary_key_array() {/*{{{*/

		$ret = array();

		if ( count( $this->primary_key ) )  
			foreach( array_keys( $this->primary_key ) as $name ) 
					$ret[] = (string) $name;
		return $ret;

	}/*}}}*/
//
	function set_foreign_key() {/*{{{*/

		global $xpdoc;

		/* si req_object esta definida, entonces esta pidiendo un objeto dentro de un modelo que no esta cargado */

		if ( isset ( $xpdoc->req_object ) ) return array();

		else return $this->search->process( $this->get_foreign_key() );

	}/*}}}*/

	function get_foreign_key() {/*{{{*/

		$ret = array();

		foreach ( $this->foreign_key as $local => $fk ) {

			if ( $local{0} == '@' ) continue; // variables de configuracion, ej @set

			$value = $this->parent->{$fk->remote};

			/*
			if ( ! $attr = $this->get_attr( $local ) ) {

				M()->warn( "no encuentro el attr $local" ); 
				continue;
			}
			*/

			M()->debug( "valor del foreign_key: {$this->table_name}::$local = $value" ); 

			$ret[$local] = $value;
		}

		return $ret;
	}/*}}}*/

	function assign_foreign_key() {/*{{{*/

		// DEBUG: esto se usa solamente en SOAP

		$this->bind( $this->get_foreign_key(), true );

	}/*}}}*/

	function fill_primary_key( $force = false ) {/*{{{*/

		/* llena la clave primaria con hashes segun el tipo */

		foreach ( $this->primary_key as $key => $data ) {

			if ( $key == $this->get_autonumeric_field() ) {

				M()->info( "$key es autonumerico, no puedo llenar" );
				continue;
			}

			if ( ! ( $attr = $this->get_attr( $key ) ) ) {

				M()->warn( "el atributo $key no existe en {$this->class_name}" );
				continue;
			}

			if ( $attr->value and ! $force ) {

				M()->info( "$key tiene valor y no puedo forzar" );
				continue;
			}

			// distintas alternativas para rellenar claves primarias segun el tipo

			if ( ( $attr->type == 'xpstring' or $attr->type == 'xpentry_help' ) and $attr->length == 32 )
				$attr->value = $this->get_hash();

			else if ( ( $attr->type == 'xpstring' or $attr->type == 'xpentry_help' ) and $attr->length == 13 )
				$attr->value = uniqid();

			else M()->info( 'no puedo llenar el atributo '.$attr->name.' de la clave primaria para el tipo '. $attr->type. ' en el objeto ' . $this->class_name );

		}

	}/*}}}*/

	function guess_primary_key() {/*{{{*/

		// si no tiene clave primaria, genera una temporal

		return ( count( $this->primary_key ) ) ? 
			$this->pack_primary_key(): 
			$this->get_hash();
	}/*}}}*/

	function is_primary_key( $attr_name ) {/*{{{*/

		if ( $attr = $this->get_attr( $attr_name ) )
			return $attr->is_primary_key();
		else
			return null;

	}/*}}}*/

	function is_foreign_key( $attr_name ) {/*{{{*/

		if ( $attr = $this->get_attr( $attr_name ) )
			return $attr->is_foreign_key();
		else
			return null;

	}/*}}}*/

	function pack_primary_key() {/*{{{*/

		/* devuelve un string con las claves separado por $this->feat->key_delimiter */

		$pk = $this->primary_key;
		$packed = '';
		$i = 0;

		foreach ( $pk as $key => $value ) {
			$packed .= $value;
			if ( ++ $i < count( $pk )  ) $packed .= $this->feat->key_delimiter; 
		}

		return $packed;

	}/*}}}*/

	function unpack_primary_key( $str_key ) {/*{{{*/

		M()->info( "recibo el str_key ". $str_key );

		$values = explode( $this->feat->key_delimiter, (string) $str_key );

		$keys = array();

		$i = 0;

		foreach ( $this->primary_key as $key => $data )
			$keys[$key] = $values[$i++];

		M()->debug( 'devulevo claves: '. serialize( $keys ) );

		return $keys;

	}/*}}}*/

	function check_key( $key_array ) {/*{{{*/

		$i = (int) 0;
		$status = 0;

		foreach ( $key_array as $key => $data ) {

			$data and $status |= pow( 2, $i );
			$i++;
		}

		$complete = pow( 2, count( $key_array ) );

		if ( !$status ) $m = 'nula';
		else if ( $status < $complete ) $m = 'incompleta';
		else $m = 'completa';

		M()->info( "clave $m: ". decbin( $status ) );

		return $status;

	}/*}}}*/

	// metadata

	// funciones virtuales

	function init() {/*{{{*/
		return $this;
	}/*}}}*/

	function configure() {/*{{{*/
		return $this;
	}/*}}}*/

		function defaults() {/*{{{*/

		// valores por omision


	}/*}}}*/

 	function metadata() {/*{{{*/

		$xom = new SimpleXMLElement( '<obj/>' );

		foreach( $this->metadata->attributes() as $key => $value )
			$xom[$key] = $value;

		if ( count( $this->metadata->primary_key ) )
			simplexml_append( $xom, $this->metadata->primary_key );

		if ( count( $this->attr ) )
			foreach( $this->attr as $key => $attr ) 
				simplexml_append( $xom, $attr->metadata() );

		if ( $xof = $this->feat->get_xml() )
			simplexml_append( $xom, $xof );

		if ( count( $this->metadata->index ) )
			foreach( $this->metadata->index as $index ) 
				simplexml_append( $xom, $index );

		simplexml_append( $xom, array2xml( 'acl', $this->acl ) );

		simplexml_append( $xom, $this->processes );

		if ( file_exists( ( $extra_js_files = $this->get_module_path(). '/js.xml' ) ) ) {

			$files = $xom->addChild('files');
			$files['type'] = 'js';

			$js_files = simplexml_load_file( $extra_js_files );
			foreach( $js_files->xpath( "//file" ) as $js_xml_file ) 
				simplexml_append( $files, $js_xml_file );

			simplexml_append( $xom, $files );
		}

		return $xom;

	}/*}}}*/

function main_sql () {/*{{{*/
		return $this;
	}/*}}}*/

	function prepare_data() {/*{{{*/
		return $this;
	}/*}}}*/

	function check() {/*{{{*/

		// check() debe devolver obligatoriamente un valor para continuar

		if ( ! $this->validate() ) return false;

			return true;

	}/*}}}*/

	function add_invalid( $attr_name ) {/*{{{*/

		if ( in_array( $attr_name, $this->invalid_attrs ) ) 
			return null;

		return $this->invalid_attrs[] = $attr_name;

	}/*}}}*/

	function is_valid() {/*{{{*/

		return ! (bool) count( $this->invalid_attrs ); 

	}/*}}}*/

	function validate() {/*{{{*/

		if ( $this->get_flag( 'validate' ) == false ) 
			return true;

		$this->invalid_attrs = array();

		// funcion simple para validar
		$valid = true;

                foreach( $this->attr as $attr ) {

			if ( ! $attr->validate() ) {

				$valid = false;
				$this->add_invalid( $attr->name );
			}

			else if ( $attr->modified and !$attr->filter() ){

				$valid = false;
				$this->add_invalid( $attr->name );
			}

		}

		(! M()->status() ) and M()->status( $valid ? 'OK' : 'ERR' );

		return $valid;

	}/*}}}*/

	function post_check() {/*{{{*/

		return $this;

	}/*}}}*/

	function pre_delete() {/*{{{*/

		return true;

	}/*}}}*/

	function post_delete() {/*{{{*/

		return true;
	}/*}}}*/

	// transaccion

	function start_db_transaction( $param ) {/*{{{*/

		
		M()->mem_stats();
		M()->info( 'iniciando transacciones de base de datos' );
		$this->db->StartTrans();
	
	}/*}}}*/

	function complete_db_transaction( $param ) {/*{{{*/
	
		M()->mem_stats();
		M()->info( 'completando transacciones de base de datos' );
		$this->db->CompleteTrans();

	}/*}}}*/

	function ErrorNo() {/*{{{*/
		return $this->db->ErrorNo();
	}/*}}}*/

	function ErrorMsg() {/*{{{*/
		return $this->db->ErrorMsg();
	}/*}}}*/

	function is_new() {/*{{{*/

		return $this->__new;

	}/*}}}*/

	function translate() {/*{{{*/

		return $this->metadata['translate'] ? $this->metadata['translate'] : $this->class_name;

	}/*}}}*/

	function last_op() {/*{{{*/

		return $this->transac_status;

	}/*}}}*/

	// funciones utilitarias

	function escape( $str ) {/*{{{*/
		return substr($this->db->qstr( $str ), 1, -1);
	}/*}}}*/

	function xpdoc() {/*{{{*/

		global $xpdoc;
		return $xpdoc;

	}/*}}}*/

	// debug

	function debug_object() {/*{{{*/


		$primary_key = serialize( $this->primary_key );
		$foreign_key = serialize( $this->foreign_key );

		$new = $this->__new ? 'si' : 'no';
		$loaded = $this->loaded ? 'si' : 'no';
		$modified = $this->modified ? 'si' : 'no';

		print "<h1>Object: {$this->name}</h1>";
		print "<hr/>";
		print "<table border=\"1\">
				<tr><td>name:</td><td>{$this->name}</td></tr>
				<tr><td>type:</td><td>{$this->type}</td></tr>
				<tr><td>class_name:</td><td>{$this->class_name}</td></tr>
				<tr><td>table_name:</td><td>{$this->table_name}</td></tr>
				<tr><td>autonumeric_field:</td><td>{$this->autonumeric_field}</td></tr>
				<tr><td>__new:</td><td>{$new}</td></tr>
				<tr><td>loaded:</td><td>{$loaded}</td></tr>
				<tr><td>modified:</td><td>{$modified}</td></tr>
				<tr><td>transac_status:</td><td>{$this->transac_status}</td></tr>
				<tr><td>record_count:</td><td>{$this->record_count}</td></tr>
				<tr><td>current_page:</td><td>{$this->pager['cp']}</td></tr>
				<tr><td>total_records:</td><td>{$this->total_records}</td></tr>
				<tr><td>primary_key:</td><td>{$primary_key}</td></tr>
				<tr><td>foreign_key:</td><td>{$foreign_key}</td></tr>
			</table>";

		print "<h1>Attributes</h1>";
		print "<table border=\"1\">
				<tr><td>Variable</td>
					<td>Type</td>
					<td>Value</td>
					<td>Label</td>
					<td>validate</td>
					<td>filters</td>
					<td>display</td>
					<td>modified</td>
					<td>primary</td>
					<td>foreign</td>
					<td>virtual</td>
					<td>entry_help</td>
					<td>alias_of</td></tr>";

		foreach ( $this->attr as $attr ) {
			print "<tr>";
			print "<td>"; print $attr->name; print "</td>";
			print "<td>"; print get_class( $this->get_attr( $attr->name ) ); print "</td>"; 
			print "<td>"; print $attr->serialize(); print "</td>"; 
			print "<td>"; print $attr->label; print "</td>"; 
			print "<td>"; print $attr->validate; print "</td>"; 
			print "<td>"; print $attr->filters; print "</td>"; 
			print "<td>"; print $attr->display; print "</td>"; 
			print "<td>"; print ($attr->modified? 'true': 'false'); print "</td>"; 
			print "<td>"; print ($attr->primary? 'true': 'false'); print "</td>"; 
			print "<td>"; print ($attr->foreign? 'true': 'false'); print "</td>"; 
			print "<td>"; print ($attr->virtual? 'true': 'false'); print "</td>"; 
			print "<td>"; print $attr->entry_help; print "</td>"; 
			print "<td>"; print $attr->alias_of; print "</td>"; 
			print "</tr>";
		}
		echo "</table>";

		print "<h1>Trace</h1>";
		print $this->debug_backtrace();

	}/*}}}*/

} // xpDataObject


// vim600: fdm=marker sw=3 ts=8 ai:

?>
