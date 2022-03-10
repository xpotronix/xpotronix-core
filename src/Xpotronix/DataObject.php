<?php

/**
 * @package xpotronix
 * @version 2.0 - Areco 
 * @copyright Copyright &copy; 2003-2011, Eduardo Spotorno
 * @author Eduardo Spotorno
 *
 * Licensed under GPL v3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

namespace Xpotronix;

define( 'NO_OP',      'n' );
define( 'INSERT_OP',  'i' );
define( 'UPDATE_OP',  'u' );
define( 'REPLACE_OP', 'r' );
define( 'DELETE_OP',  'd' );
define( 'NOT_VALID',  'v' );
define( 'NOT_FOUND',  'f' );
define( 'NO_PERMS',   'p' );
define( 'DB_ERROR',   'e' );

class DataObject extends Base {


	// identification

	private $name;
	private $type;

	// nombre de la clase 

	var $namespace;
	var $class_name;
	var $id_hash;

	// array atributos
	var $attr;
	var $metadata;
	var $data;
	protected $aliases = [];
	var $track_modified = true;
	var $invalid_attrs;
	var $constructed;

	// parametros especificos para este objeto
	var $extra_param;

	// features
	var $feat;

	// base de datos
	var $db = null;

	// tabla asociada
	var $table_name;
	var $prefix;
	var $uniq_tables_array;

	// autonumeric
	private $autonumeric_field;

	// estatus del objeto 
	private $__new;
	private $__loaded;
	var $modified;
	var $transac_status;
	var $record_count;

	// consulta del objeto;
	var $sql;
	var $xsql;
	var $a_sql;
	var $affected_records;

	// recordset de la ultima consulta
	var $recordset;

	// paginador del recordset
	var $pager;
	var $total_records;
	var $last_page;

	// claves
	var $primary_key;
	var $foreign_key;

	// search obj

	var $search;
	var $search_keys = [];

	// control consulta
	var $order_array;
	var $queries;
	var $queries_array;

	// procesos
	var $processes;

	// flags
	protected $flags = [];

	// acl
	protected $temp_acl = [];


	const DATATYPE_CLASS_PREFIX = '\\Xpotronix\\DataTypes\\';


	// sincronizacion

	function table_exists( string $table_name ) {/*{{{*/

		$dd = NewDataDictionary( $this->db() );
		$mt = $dd->MetaTables();
		return array_search( $table_name, $mt ); 
	}/*}}}*/

	// constructores y funciones magicas

	function sync_create() {/*{{{*/

		$sync = new Sync( $this );
		return $sync->sync_create();
	}/*}}}*/

	function sync_data() {/*{{{*/

		$sync = new Sync( $this );
		return $sync->sync_data();
	}/*}}}*/

	function sync_info() {/*{{{*/

		$sync = new Sync( $this );
		return $sync->sync_info();
	}/*}}}*/

	function __construct( $model = null, $metadata = null ) {/*{{{*/

		global $xpdoc; 

		list( $this->namespace, $this->class_name ) = explode( '\\', get_class( $this ) );

		M()->info( "namespace\class_name: {$this->namespace}\\{$this->class_name}" );

		$this->table_name = $this->class_name;
		
		if ( ! $this->set_model( $model ) ) 
			return null;

		$this->set_metadata( $metadata );

		// $this->db( (string) $this->metadata['dbi'] );

		$this->name or $this->name = $this->class_name;

		// print '<pre>'; print_r( $this->debug_backtrace());

		$this->feat = new Config( $xpdoc->feat ); // featureas locales
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
		
		// control consulta

		$this->order_array = [];

		$this->search = new Search( $this );

		$this->load_obj_attrs();

		$this->config_primary_key();
		$this->config_foreign_key();

		$this->load_processes();


		// los parametros especificos del objeto tienen precedencia sobre los globales

		$xpdoc and isset( $xpdoc->extra_param['_'] ) and  $this->extra_param = $xpdoc->extra_param['_'];
		$xpdoc and isset( $xpdoc->extra_param[$this->class_name] ) and $this->extra_param = $xpdoc->extra_param[$this->class_name] ;

		// registro este objeto en obj_collection

		$this->id_hash = $this->get_hash();

		$xpdoc and $xpdoc->obj_collection[$this->class_name][$this->id_hash] = $this;

		$this->constructed = true; // __construct fue llamado OK

		$this->set_flag( 'main_sql', true );
		$this->set_flag( 'validate', true );
		$this->set_flag( 'check', true );
		$this->set_flag( 'post_check', true );
		$this->set_flag( 'set_global_search', true );

		

		return $this->init();

	}/*}}}*/ 

	function __destruct() {/*{{{*/

		// elimino la referencia circular para que no me genere memory leaks (espero)
		//
		
		global $xpdoc;

		if ( $this->attr ) 
			foreach( $this->attr as $key => $attr ) 
				unset ( $this->attr[$key]->obj );

		M()->info( "llamando a destruct para el objeto $this->class_name con el ID $this->id_hash" );

		$xpdoc->remove_obj_collection( $this->class_name, $this->id_hash );

	}/*}}}*/

	function destroy() {/*{{{*/

		$this->__destruct();
	
	}/*}}}*/

		function __get( $var_name ) {/*{{{*/

		/*
		echo '<pre>';
		print_r( debug_backtrace( false ) );
		print $var_name; 
		print_r( $this->data );
		*/

		if ( array_key_exists( $var_name, $this->data ) )
			return $this->data[$var_name];

		if ( is_array( $this->aliases ) and array_key_exists( $var_name, $this->aliases ) )  
			return $var_name = $this->aliases[$var_name]->name;

		if ( $this->check_vars ) {

			M()->error( "No encontre el atributo [$this->class_name::$var_name] al evaluar" );
			M()->line(1);
			// print_r( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) ); exit;
			return null;
		}

	}/*}}}*/

	function __set( $var_name, $var_value ) {/*{{{*/

		// si es una array o un object, asocia directamente 
		// (solo para el funcionamiento de la clase)

		if ( is_object( $var_value ) or is_array( $var_value ) ) {

			$this->$var_name = $var_value;
			( $var_name == 'data' or $var_name == 'sql' or $var_name == 'recordset' or $var_name == 'db' ) 
				or M()->warn( "asignando una tipo complejo a un dato simple $this->class_name::$var_name" );
		}

		else if ( $attr = $this->get_attr( $var_name ) ) {

			$attr->value = $var_value;

		} else if ( array_key_exists( $var_name, $this->aliases ) ) {

			M()->error( "asignando por alias: $this->class_name::$var_name = $var_value" );

			$this->aliases[$var_name]->value = $var_value;

		} else { 

			M()->error( "No encontre el atributo [$this->class_name::$var_name] al asignar" );

			// $this->debug_backtrace(); exit;
		}

		return $var_value;
	}/*}}}*/

	function set_flag( $name, $value ) {/*{{{*/

		M()->info( "{$this->class_name}:$name=". ($value ? 'true': 'false') );

		return $this->flags[$name] = $value;

	}/*}}}*/

	function get_flag( $name ) {/*{{{*/

		if ( !in_array( $name, $this->flags ) ) 
			$value = null;
		else
			$value = $this->flags[$name];
		
		M()->info( "{$this->class_name}:$name=". ($value ? 'true': 'false') );

		return $value;

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

	function db( $dbi = null ) {/*{{{*/

		global $xpdoc;

		$dbi or $dbi = (string) $this->metadata['dbi'];

		M()->debug( "dbi para la clase $this->class_name: ". ( $dbi ? $dbi: '(default)' ) );

		$this->db = null;

		/* devuelve una instancia abierta */

		if ( $this->db = $xpdoc->dbm->instance( $dbi ) ) 
			M()->info( "instancia encontrada $dbi" );
		else
			M()->error( "No encuentro la base de datos para la clase $this->class_name" ); 

		return $this->db;

	}/*}}}*/

	function get_db() {/*{{{*/

		return $this->db;
	}/*}}}*/

	function set_table_name( $table_name, $prefix = null ) {/*{{{*/

		$prefix and $this->prefix = $prefix;
		$this->table_name = $table_name;
		return $this->prefix.$this->table_name;

	}/*}}}*/ 

	function get_module_path( $path = null ) {/*{{{*/

		return 'modules/' . $this->class_name. '/' . $path;

	}/*}}}*/

	function load_processes() {/*{{{*/

		$file = $this->get_module_path( $this->class_name . '.processes.xml' );

		@$this->processes = simplexml_load_file( $file ) or 
			( $this->processes = new \SimpleXMLElement( "<processes/>" ) and 
			M()->info("No hay processos definidos para la clase {$this->class_name} en $file: ") );

		return $this->processes;

	}/*}}}*/

	function load_model() {/*{{{*/

		$file = $this->get_module_path( $this->class_name . '.model.xml' );

		$model = null;

		( @$model = simplexml_load_file( $file ) 
			and M()->info('cargado el modelo para la clase '. $this->class_name ) )
		or M()->error("No puedo cargar la descripcion del modelo del achivo $file" );

		return $model;

	}/*}}}*/ 

	function set_model( $model = null ) {/*{{{*/

		global $xpdoc;

		$this->model = $model or 
		( $xpdoc and 
			$xpdoc->model and
			$tmp = $xpdoc->model->xpath( "//obj[@name='{$this->class_name}']" ) and
			$this->model = array_shift( $tmp ) 
		) or
			$this->model = $this->load_model(); 

		$this->name = (string) $this->model['name'];
		$this->type = (string) $this->model['type'];

		@$this->parent_node = array_shift( $this->model->xpath( ".." ) ) 
			and $this->parent_name = (string) $this->parent_node['name']
			and $this->parent = $xpdoc->instances[$this->parent_name];

		M()->info('OK');

		return $this->model;

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

			$message = [];
			foreach( $this->acl as $action => $value) {
				$value and $message[] = "$action";
			}

			M()->info("usuario: {$xpdoc->user->user_username} [{$xpdoc->user->user_id}], objeto: {$this->class_name}, permisos:". implode( ',', $message ) );

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

	function has_role() {/*{{{*/

		global $xpdoc;
		return $xpdoc->has_role( func_get_args() );

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

	function has_sql() {/*{{{*/

		$fq = $this->feat->query_name;
		$query_name = $fq ? $fq : 'main_sql';

		/* DEBUG: tiene que estar fijo en la activacion del modelo */

		M()->debug( $ret = (int) count( $this->model->xpath( "queries/query[@name='$query_name']" )) );
			
		return $ret;

	}/*}}}*/

	function persistent() {/*{{{*/

		/*

		if ( $this->class_name == 'deudor_e' ) {

			print "<pre>";
			$query_name = 'main_sql';
			print_r( $this->model->xpath( "queries/query[@name='$query_name']" ) );
			print_r( $this->metadata );
			exit;
		}*/

		return (boolean) ( ( ! $this->is_virtual() ) or $this->has_sql() or $this->count_queries() );

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
			$node = new \SimpleXMLElement( "<attr name=\"$name\" type=\"$type\"/>" );
		}

		M()->info( "attr: $name, node: ". $node->asXML() );

		$class_name = $this->map_type_to_class( $node );
		$full_class_name = self::DATATYPE_CLASS_PREFIX . $class_name;

		$this->attr[$name] = new $full_class_name( $node );

		// print $node->asXML(); exit;

		$this->attr[$name]->obj = $this;


		/* DEBUG: aca pisa el tipo de datos a xpentry_help (no deberia) */
		$this->attr[$name]->type = ( $class_name == 'xpEntryHelp' ) ? 
			'xpentry_help': 
			$type;

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

		else if ( isset( $this->attr[(string)$name] ) ) 
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

	function map_type_to_class( $node ) {/*{{{*/

		if ( ! $node['type'] ) return 'xpString'; 
		if ( $node['entry_help'] ) return 'xpEntryHelp';

		global $xpdoc;

		$class = null;
		$type  = $node['type'];
		$table = $node['table'];
		$name  = $node['name'];

		M()->debug( "Solicitando tipo [$type] del attr [$table::$name]" );

		is_object( $xpdoc->datatypes ) or $xpdoc->load_datatypes();

		@$class = array_shift( $xpdoc->datatypes->xpath( "xtype[@name='$type']/@class" ) )
		or @$class = array_shift( $xpdoc->datatypes->xpath( "xtype[type/@name='$type']/@class" ) )
		or M()->error( "No encuentro el tipo de dato [$type] del attr [$table::$name]" );
	
		return $class ? (string) $class : 'xpString';

	}/*}}}*/

	function has_attr_type( $types ) {/*{{{*/

		foreach ( $this->attr as $key => $attr ) 

			if ( strstr( $types, $attr->type ) ) return true;

		return false;
	}/*}}}*/

	function change_attr( $attr_attr, $value, $except = null ) {/*{{{*/

		if ( ! is_array( $except ) )
			$except = explode( ',', $except );

		array_unique( $except );

		foreach( $this->attr as $key => $attr ) {

			if ( in_array( $key, $except ) ) continue;

			$this->attr[$key]->$attr_attr = $value;

			}

	}/*}}}*/

	function hide_all( $except = null ) {/*{{{*/

		$this->change_attr( 'display', 'hide', $except );

	}/*}}}*/

	function protect_all( $except = null ) {/*{{{*/

		$this->change_attr( 'display', 'protect', $except );

	}/*}}}*/

	/* load */

	function load( $key = null , $where = null, $order = null ) {/*{{{*/

		if ( $key === null and $where === null ) 
			$key = $this->primary_key;

		$objs = $this->loadc( $key, $where, $order, 1 );

		$this->reset(); 

		if ( is_array( $objs ) and count( $objs ) ) {

			$this->data = array_shift( $objs );
			$this->set_primary_key();
			$this->loaded( true );
			$this->is_new( false );
			return $this;
		}

		return null; 

	}/*}}}*/

	function loadc( $key = null, $where = null, $order = null, $page = null ) {/*{{{*/

		/* M()->debug( "key: ". json_encode( $key ).", 
		where: ". json_encode( $where ). ", 
		order: ". json_encode( $order ). ", 
		page: $page" ); */

		/* dbi */

		if ( !$this->db() ) {

			M()->error( "No hay manejador de base de datos, cancelando loadc" );
			$this->total_records = -2;
			return;
		}

		$this->sql = $this->sql_generate();

		$this->set_keys( $key );

		if ( is_string( $where ) ) {

			M()->debug( "recibiendo una directiva para WHERE: $where" );
			$this->sql->addWhere( $where );
		}

		$this->set_order( $order );

		/* page */
		$recs = $this->page( $page );

		is_array( $recs ) and M()->debug( "devolviendo ". count( $recs ). " registros" );

		return $recs;

	}/*}}}*/

	function set_keys( $key ) {/*{{{*/
	
		/* global search aplicado a la consulta actual */

		if ( $this->get_flag('set_global_search') ) {

			M()->info( "{$this->class_name} aplicando busqueda global" );
			$this->set_global_search();

		} else {

			M()->info( "{$this->class_name} NO aplicando busqueda global" );
		}

		/* resuelve la clave de busqueda */

		if ( $key === null ) {

			M()->debug( "recibiendo una clave nula. no hay criterios definidos. Aplicando solo la busqueda global" ); 

		} else {

			if ( is_array( $key ) ) {

				try { 
					M()->debug( 'la clave es un array: '. json_encode( $key ) ); 

				} catch( \Exception $e ) {

					M()->error( "En el array de busqueda hay objetos o valores complejos. NO puedo buscar" );
					return null;
				} 

				$search = $key;

			} else {

				$search = [];

				M()->debug( "la clave es un escalar $key" );

				if ( count( $this->primary_key ) > 1 ) 
					M()->warn( 'escalar para una clave compuesta: el resultado puede ser multiple' );

				reset( $this->primary_key );
				$search[key( $this->primary_key )] = $key;
			} 

			$this->add_search_key( $this->search->process( $search, null, $this->as_variables() ) );
		}

	}/*}}}*/

	function add_search_key( $key ) {/*{{{*/

		M()->debug( "add_search_key: ". json_encode( $key ) );

		if ( !empty( $key ) ) {
		
			return array_push( $this->search_keys, $key );
		
		}
	}/*}}}*/

	function load_array_recordset( $rows ) {/*{{{*/

			if ( $rows === null ) {
			
				M()->error( "No hubo respuesta en el resultado" );
				return null;
			}

			$objs = [];
			$objs_count = 0;

			// M()->mem_stats( 'entro a load_array_recordset' );

			// $rows = $this->recordset->fetchAll();

			// while ( $row = $this->recordset->fetch() ) {
			foreach ( $rows as $row ) {

				$this->reset();
				$this->bind_data( $row );

				$this->set_primary_key();
				$objs[$this->guess_primary_key()] = $this->data;

				$objs_count++;
			} 

			$count = count( $objs );

			// echo '<pre>'; print_r( $objs ); echo '</pre>';

			M()->info( "$this->class_name cargados/contados: [$count/$objs_count]" );

			if ( $count != $objs_count ) 

				M()->warn( "en $this->class_name se han cargado menos items que los que se contaron. Revise la clave primaria (que deben ser valores unicos) [$count/$objs_count] " );

			// unset( $this->recordset );

			// M()->mem_stats('salgo de load_array_recordset');

			return $objs;
	}/*}}}*/

	function load_page( $key = null, $where = null ) {/*{{{*/

		M()->debug( "key: ". json_encode( $key ).", where: $where" );

		return new Iterator( $this, $key, $where, null, false );

	}/*}}}*/

	function load_set( $key = null, $where = null, $order = null ) {/*{{{*/

		return new Iterator( $this, $key, $where, $order, true );

	}/*}}}*/

	// sql query

	function set_dbquery( $sql, $xsql, $assign_attributes = true ) {/*{{{*/

		/* en esta funcion se asignan los atributos modificadores de la consulta
			definidos en database.xml
			auto_where, auto_having, auto_group, auto_order */

		M()->debug( $xsql->asXML() );

		if ( $assign_attributes ) {
			M()->debug( "assign_attributes = true" );
			foreach( $xsql->attributes() as $key => $value ) {

				M()->debug( "key: $key = $value" );
				$sql->$key = (( strtolower( $value ) == 'no' ) ? false : true );
			}

		} else M()->debug( "assign_attributes = false" );
		
		$sql->addSql( (string) $xsql );

	}/*}}}*/

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

	function get_table_name() {/*{{{*/

		$r = $this->prefix. $this->table_name;
		M()->info( $r );

		return $r;

	}/*}}}*/

	function get_update_name() {/*{{{*/

		$tn = ( $t = $this->metadata['update'] ) ? $t : $this->table_name;

		$r = $this->prefix. $tn;
		M()->info( $r );

		return $r;

	}/*}}}*/

	function sql_generate () {/*{{{*/

		// is_object( $sql ) or 
		$sql = new DBQuery( $this->db );

		// para que no se repitan las tablas entre table y alias
		// print $this->model->asXML(); ob_flush(); 

		$this->uniq_tables(); // reset
		$this->uniq_tables( $this->get_table_name() );

		// busca la consulta principal
		$fq = $this->feat->query_name;
		$query_name = $fq ? $this->feat->query_name : 'main_sql';

		@$this->xsql = array_shift ( $this->model->xpath( "queries/query[@name='$query_name']" ) );

		$this->xsql or M()->fatal( "no encuentro la query $query_name para el objeto {$this->class_name}" );

		M()->info( "query $query_name para el objeto {$this->class_name}" );

		// echo '<pre>'; var_dump ( count( $this->xsql->sql ) ); exit;

		if ( count( $this->xsql->sql ) == 1 ) {

			M()->info( "el objeto $this->class_name tiene definida una vista sql" );
			return $sql;

		} else if ( count( $this->xsql->sql ) > 1 ) {

			M()->info( "el objeto $this->class_name tiene multiples sentencias sql" );
			return $sql;

		} else 	M()->info( "el objeto $this->class_name no tiene sentencias sql definidas" );

		
		/* modifiers */
		foreach( $this->xsql->modifiers as $modifier )
			$sql->addModifiers( (string) $modifier );

		/* join principales del query */
		foreach ( $this->xsql->join as $join )
			$this->uniq_tables( $join['table'], $join['alias'] ) 
			or $sql->addJoin( 
				(string) $join['table'], 
				$this->quote_name( (string) $join['alias'] ),
				(string) $join->where, 
				(string) $join['type'] );

		/* group by */
		foreach( $this->xsql->group_by as $group_by )
			$sql->addGroup( (string) $group_by );
			
		/* joins de los entry_helpers */
		if ( $this->feat->load_full_query ) {

			M()->info( "load_full_query" );

			foreach( $this->metadata->xpath( "attr[@entry_help]") as $attr ) {

				// el query que se corresponde al entry_help
				$query_name = (string) $attr['entry_help'];

				foreach( $this->xsql->xpath( ".//query[@name='$query_name']" ) as $query )  {

					// agrego los label para cada uno de los entry helpers

					if ( $this->feat->add_labels ) {

						$attr_name = $attr['name']. '_label';
						$_attr = $this->attr( $attr_name );
						$_attr->alias_of = (string) $query->label;
						$_attr->display = 'sql'; // solo para ser accedido al momento de la consulta

					}

					/* si el join del entry helper esta baseado en un alias_of, usar eso para la parte izquierda del join */

					$fn = ( $attr['alias_of'] ) ? $attr['alias_of'] : $this->quote_name( $this->get_table_name(). ".". $attr['name'] );

					// agrego el join del from
					$this->uniq_tables($query->from, $query->alias) or $sql->addJoin( 
							(string) $query->from, 
							$this->quote_name( (string) $query->alias ),
							sprintf( "%s=%s", $fn, $this->quote_name( $query->id ) ).
							( $query->on ? ' AND '. $query->on : null ), 
							(string) "left" );

					// agrego el resto de los joins del query
					foreach ( $query->xpath( "join" ) as $join )
						$this->uniq_tables( $join['table'], $join['alias'] ) or $sql->addJoin( 
								(string) $join['table'], 
								$this->quote_name( (string) $join['alias'] ),
								(string) $join->where, 
								(string) $join['type'] );
				}
			}
		}

		/* if ( $this->class_name == 'cliente' ) { $this->debug_object(); exit; } */

		$protect_list_attr = [];

		/* joins de queries asociadas */

		if ( $this->queries_array ) {

			M()->info( "queries_array" );

			foreach( $this->queries_array as $query_name ) {

				M()->debug( "agrego query [$query_name]" );

				foreach( $this->model->xpath( "queries/query[@name='$query_name']" ) as $query_xml ) {

					// cuando "sumo" queries, tengo que cambiar el alias por la tabla original

					$r_table = (string) $query_xml->from;
					$r_alias = (string) $query_xml->alias;

					M()->debug( "encontre query [{$query_xml['name']}]" );

					foreach ( $query_xml->xpath( "join" ) as $join )
						$this->uniq_tables( $join['table'], $join['alias'] ) or $sql->addJoin( 
								(string) $join['table'], 
								$this->quote_name( (string) $join['alias'] ),
								$this->replace_table_name( $r_alias, $r_table, (string) $join->where), 
								(string) $join['type'] );

					foreach( $query_xml->xpath( "on" ) as $on ) 
						$sql->addWhere( $this->replace_table_name( $r_alias, $r_table, (string) $on ) );

					foreach ( $query_xml->xpath( "where" ) as $where )
						$sql->addWhere( $this->replace_table_name( $r_alias, $r_table, (string) $where ) );

					foreach ( $query_xml->xpath( "order_by" ) as $order )
						$sql->addOrder( $this->quote_order( $this->replace_table_name( $r_alias, $r_table,  (string) $order ) ) );

					// atributos de la consulta

					foreach ( $query_xml->xpath( "id" ) as $id ) {
						$this->attr( 'id' )->alias_of = $this->replace_table_name( $r_alias, $r_table, (string) $id );
						array_push( $protect_list_attr, 'id' );
						}

					foreach ( $query_xml->xpath( "label" ) as $label ) {
						$this->attr( '_label' )->alias_of = $this->replace_table_name( $r_alias, $r_table, (string) $label );
						array_push( $protect_list_attr, '_label' );
						}

					foreach ( $query_xml->xpath( "attr|field" ) as $xattr ) {

						/* crea un atributo, puede ser en base a un attr o field */

						$name = (string) $xattr['name'];
						$attr = $this->get_attr($name) or $attr = $this->attr( $name );
						( $t = (string) $xattr['alias_of'] ) and $attr->alias_of = $t;
						$attr->display = null;

						array_push( $protect_list_attr, $name );
					}

					// echo "<pre>"; print_r( $attr->data ); echo "</pre>"; ob_flush(); exit;
					// cada <attr/> del query como campo adicional
					// echo $query_xml->asxml(); exit;
				}
			}

		}

		/* if ( $this->class_name == 'formato' ) {print_r( $protect_list_attr ); exit;} */

		// crea la consulta principal
		// agrego la tabla y su alias, si esta definido

		foreach ( $this->xsql->xpath( "attr|field" ) as $xattr ) {

			/* crea un atributo, puede ser en base a un attr o field */

			$name = (string) $xattr['name'];
			$attr = $this->get_attr($name) or $attr = $this->attr( $name );
			( $t = (string) $xattr['alias_of'] ) and $attr->alias_of = $t;
			$attr->display = null;

			array_push( $protect_list_attr, $name );
		}

		if ( count( $protect_list_attr ) ) 
			$this->protect_all( $protect_list_attr );

		/* DEBUG: queda el $this->table_name con el nombre del xsql->from */

		if ( $sql_table_name = (string) $this->xsql->from )
			$this->table_name = $sql_table_name;
		else
			$sql_table_name = $this->get_table_name();

		$sql_table_alias = (string) $this->xsql->alias;

		M()->info( "sql_table_name: $sql_table_name, sql_table_alias: $sql_table_alias" );

		$sql->addTable( $sql_table_name, $sql_table_alias );

		// agrego el where del main_sql
		if ( $this->xsql->where )
			$sql->addWhere( (string) $this->xsql->where );

		// agrego el order_by 
		if ( $this->xsql->order_by )
			$sql->addOrder( $this->quote_order( $this->replace_table_name( (string) $this->xsql->alias, $this->get_table_name(),  (string) $order ) ) );

		if ( $this->class_name == 'formato' ) { $this->get_attr('precio')->display=''; }

		// cargo los campos desde los atributos del objeto
		if ( ! count( $this->attr ) ) 
			M()->warn( "clase $this->class_name sin atributos" );
		else
		foreach( $this->attr as $key => $attr ) {

			if ( 	( $attr->display == 'ignore' ) or 
				( $attr->virtual and !$attr->alias_of ) or
				( $attr->binary == 1 and $this->feat->blob_load == false ) ) {
				M()->info( "ignorando contenido atributo $this->class_name::$attr->name" );
				continue;
			}

			/* if ( $this->class_name == '_empleado' ) { print $key. "<br/>"; } */

			$field_name = $this->quote_name( sprintf( "%s.%s", $this->get_table_name(), $attr->name ) );

			if ( $attr->alias_of ) 
				$this->feat->load_full_query and $sql->addQuery( $attr->alias_of, $this->quote_name( $attr->name ) );
			else
				$sql->addQuery( $field_name );
		}

		// comentados para debug
		// if ( $this->class_name == 'dtNotificacionActor' ) { $this->metadata(); exit; }
		// if ( $this->class_name == 'sessions' ) { echo '<pre>'; $this->debug_object(); print_r( $sql ) ; echo '</pre>'; echo '<pre>'; print( "SQL STATEMENT: ". $sql->prepare() ); echo '</pre>'; exit; }
		// if ( $this->class_name == 'formato' ) { echo '<p>'; print_r( $sql->prepare() ); echo '</p>'; exit; }
		// if ( $this->class_name == '_empleado' ) { $this->debug_object(); ob_flush(); exit; }

		return $sql;

	}/*}}}*/

	function set_global_search() {/*{{{*/

		global $xpdoc;

		M()->debug( "xpdoc->search: ". json_encode( $xpdoc->search ) );

		if ( ( $xpdoc->search )
			and array_key_exists($this->class_name, $xpdoc->search)
			and is_array( $xpdoc->search[$this->class_name] ) ) {


			M()->info( 'search key: aplicando busqueda global con '. json_encode( $xpdoc->search ) );

			$search = new Search( $this );
			$search->match_type = $xpdoc->feat->match_type ? $xpdoc->feat->match_type : 'anywhere';

			$this->add_search_key( $search->process( $xpdoc->search[$this->class_name], null, $this->as_variables() ) );
		}

		/*		
		echo '<pre>'; 
		print_r ( $xpdoc->search );
		print_r ( $this->search );
		// print_r( debug_backtrace( true ) ); 
		exit;
		*/

	}/*}}}*/

	function quote_name( $name = null ) {/*{{{*/

		if ( $name ) 
			return $this->db->quote_name( $name );
		else return null;

	}/*}}}*/

	function quote_order( $order ) {/*{{{*/

		$res = [];

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

		M()->info( 'order: '. json_encode( $order ) );

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

				$field = ( $this->attr[$key]->alias_of or $this->has_sql() ) ? 
					$this->attr[$key]->name:
					$this->attr[$key]->table. '.'. $this->attr[$key]->name;

				if ( $this->attr[$key]->is_entry_help() ) 

					$field = $this->attr[$key]->name. '_label'; 


				if ( $asc_desc == '' or strtoupper( substr( $asc_desc, 0, 3 ) ) == 'ASC'  )
					$asc_desc = 'ASC';

				else if ( strtoupper( substr( $asc_desc, 0, 4 ) ) == 'DESC' )
					$asc_desc = 'DESC';
				else
					$asc_desc = ''; 

				$this->sql->addOrder( $this->quote_order( $field ). " $asc_desc" ) ;

			}

		} else if ( $this->model->order_by ) {

			M()->debug( "order by: {$this->model->order_by}" );
			// DEBUG: no hace quote por ahora
			$this->sql->addOrder( $this->model->order_by );
		}


	}/*}}}*/ 

	function db_type() {/*{{{*/

		return $this->db->databaseType;

	}/*}}}*/

	function set_variables( $search = null ) {/*{{{*/
	
		if ( $this->as_variables() ) {

			/* por ahora solo mysql */

			$set = [];

			foreach( $search as $key => $vars )

				$set = array_merge( $set, $vars );

			$sql = "SET @". implode( ',@', $set );

			M()->debug( "$sql" );

			try {
				$this->db->Execute( $sql );

			} catch ( \PDOException $e ) {

				M()->db_error( $this->db, 'set_variables', "Error al asignar variables: [$sql]" );
			}
		}
	
	}/*}}}*/

	function set_const( $search = null ) {/*{{{*/

		if ( ! count( $search ) ) return;

		M()->debug( 'constraints: '. json_encode( $search ) );

		if ( ! $this->as_variables() ) {

			/* carga los OR en la consulta sql */

			if ( is_array ( @$search['OR'] ) and count( $or_array = $search['OR'] ) ) {

				$constraint_fn = ( $this->db_type() == 'dblib' ) ? 'addWhere' : 'addHaving' ;

				$this->sql->$constraint_fn( '( '. implode( ' OR ', $or_array ). ' )' );
			}


			/* carga los where en la consulta sql */

			if ( is_array ( @$search['where'] ) ) 

				foreach ( $search['where'] as $where ) 

					$this->sql->addWhere( $where );

			/* carga los having en la consulta sql */

			if ( is_array( @$search['having'] ) )

				foreach ( $search['having'] as $having ) 

					$this->sql->addHaving( $having );
		}


		// M()->debug( "sql_query: ". $this->sql->prepare() ) ;

	}/*}}}*/ 

	function as_variables() {/*{{{*/

		$ret = false;

		if ( isset( $this->xsql->sql['set'] ) ) {

			$ret = $this->xsql->sql['set'] == 'variables';
		} 

		M()->debug( "as_variables: $ret" );

		return $ret;
	
	}/*}}}*/

	function set_page ( $page = null, $page_rows = null ) {/*{{{*/
	
		global $xpdoc;

		$page && M()->debug( "class_name: $this->class_name, page: $page" );

		$cn = $this->class_name;

		// $cn == '_licencia' and xdebug_start_trace('/tmp/xpotronix-trace.xt');

		/* a) configura el rango de la paginacion */

		is_array( $this->pager ) or $this->pager = [ 'pr' => 0, 'cp' => 1 ];

		$pr =& $this->pager['pr'];
		$cp =& $this->pager['cp'];

		if ( isset( $xpdoc->pager ) and array_key_exists ( $cn, $xpdoc->pager ) and is_array( $xpdoc->pager[$cn] ) ) {

			isset( $xpdoc->pager[$cn]['pr'] ) and $pr = $xpdoc->pager[$cn]['pr'];
			isset( $xpdoc->pager[$cn]['cp'] ) and $cp = $xpdoc->pager[$cn]['cp'];
		}

		M()->debug( "pager: ". json_encode( $this->pager ) );
		M()->debug( "xpdoc::pager: ". json_encode( $xpdoc->pager ) );

		if ( $this->feat->page_rows !== null  and !$pr ) 
			$pr = $this->feat->page_rows;

		if ( $page !== null ) $cp = $page;

		M()->info( "object: {$cn}, page_rows: $pr, current_page: $cp" );

		return [ $cp, $pr ];
	}/*}}}*/

	function log_sql( $i, $sql_text, $truncate = false ) {/*{{{*/

		$msg = "SQL[$i]: ";

		if ( $truncate ) 

			$msg .= "SELECT ... ". substr( $sql_text, strpos( $sql_text, 'FROM ' ) );

		else
			$msg .= $sql_text;

		M()->debug( $msg );

	}/*}}}*/

	function load_xsql_frags() {/*{{{*/
	
		$xsql_frags = count( $this->xsql->sql ); 
		M()->debug( "# fragmentos xsql: $xsql_frags" );

		$i = 1;
		foreach( $this->xsql->sql as $xsql ) {

			if ( ! trim( $xsql."" ) ) {
				$i++;
				continue;
			}

			if ( $i < $xsql_frags ) {

				$sql = new DBQuery( $this->db() );
				$this->set_dbquery( $sql, $xsql, false );

				$sql->auto_where  = false;
				$sql->auto_order  = false;
				$sql->auto_group  = false;
				$sql->auto_having = false;

			} else { 

				$sql = $this->sql;
				$this->set_dbquery( $sql, $xsql );
			}

			$this->a_sql[] = $sql;
			$i++;

		}

		$sql_frags = count( $this->a_sql );

		M()->debug( "# fragmentos SQL: $sql_frags"  );

		return $sql_frags;
	
	}/*}}}*/

	function page ( $page = null, $page_rows = null ) {/*{{{*/

		list( $cp, $pr ) = $this->set_page( $page, $page_rows );

		$this->a_sql = [];

		/* # fragmentos definidos en database.xml */

		( $xsql_frags = $this->load_xsql_frags() ) or 
			$this->a_sql = [ $this->sql ];

		/* # fragmentos a ejecutar */

		$sql_frags = count( $this->a_sql );

		M()->debug( "sql_frags: $sql_frags, xsql_frags: $xsql_frags" );
		M()->debug( "no_where_check: {$this->feat->no_where_check}" );

		$i = 1;
		$rows = null;

		foreach( $this->a_sql as $sql ) {

			$this->sql = $sql;

			foreach ( $this->search_keys as $search ) {

				$this->set_variables( $search );
			}

			try {

				if ( $i < $sql_frags ) {


					/* todos los fragmentos menos el ultimo */

					M()->debug( "fragmento #$i/$sql_frags (sin paginar)" );

					/* primeros: sin paginar */
					$sql_text = ( $this->db_type() == 'dblib' ) ?
						$sql->prepare( false ):
						$sql->prepare( false, $this->feat->count_rows );
		
					$this->log_sql( $i, $sql_text );

					$this->recordset = $this->db->Execute( $sql_text );
					$i++;

					continue; /* DEBUG: si no corta el loop */

				} else {

					/* ultimo fragmento */

					M()->debug( "ultimo fragmento #$i/$sql_frags" );

					foreach ( $this->search_keys as $search ) {
					
						$this->set_const( $search );
						M()->info( 'clave a buscar: '. json_encode( $search ) ); 
					}

					M()->debug( "ejecutando main_sql()" );
					$this->main_sql();

					if ( $this->db_type() == 'dblib' ) {
					
						$sql_text =  ( $xsql_frags ) ? 
							$sql->prepare() : 
							$this->prepare_dblib( $pr, $cp );

						/* if ( $this->class_name == 'tta_resumen' ) { echo '<pre>'; print_r( $sql_text ); exit; } */
					
					} else {
					
						$sql_text = $sql->prepare( false, $this->feat->count_rows );
					}
 
					if ( ! $sql_text ) {
					
						M()->error( "sql_text vacio, revisar database.xml, ignorando" );
						continue;
					}


					if ( ( $this->sql->where === null and $this->sql->having === null ) 
						and $this->feat->no_where_check ) {

						M()->user( "tabla sin condicion de WHERE o HAVING, no se puede procesar" );
						$this->total_records = -3;
						return;
					}


					/* Ejecuta el SQL */

					if ( $pr ) {

						/* override main_sql del objeto */
						M()->debug( "paged_query (paginado)" );
						$this->recordset = $this->paged_query( $sql_text, $pr, $cp );

					} else  {

						M()->debug( "Execute (sin paginar)" );
						$this->recordset = $this->db->Execute( $sql_text );
					}

					$this->log_sql( $i, $sql_text );

					$rows = $this->recordset->fetchAll();
					$row_count = count( $rows );
					$this->recordset->closeCursor();
					$this->search_keys = [];
					$this->a_sql = [];
				}

			} catch ( \PDOException $e ) {

				$this->total_records = -1;
				$this->last_page = null;
				$this->loaded( false );
				$this->search_keys = [];

				M()->db_error( $this->db, 'SELECT', $sql_text );
				return null;
			}
		}

		/* d) calcula el record count */

		$this->total_records = null;
		$this->last_page = null;

		if ( $this->feat->count_rows ) {

			if ( $this->db_type() == 'dblib' ) {
				// $r = $this->recordset->fetch( \PDO::FETCH_ASSOC );
				@$r = $rows[0];
			}
			else {
				$r = $this->db->Execute( 'SELECT FOUND_ROWS() as __TotalRows' )->fetch( \PDO::FETCH_ASSOC );
			}

			@$this->total_records = $r['__TotalRows'] or 
			$this->total_records = $row_count;
		} 

		M()->debug('total_records: '. $this->total_records );

		/* e) prepara el return: si hay datos devuelve un recordset con los datos, si no, null */

		if ( $this->total_records === 0 ) {

			// print_r( $this->primary_key ); exit;
			$this->loaded( false );
			$this->last_page = true;
			// M()->info( 'no encontre registros en: '. $this->sql->prepare() ) ;
			return null;

		} else {

			$this->last_page = (bool) ( $this->total_records < ( $pr * $cp) );

			// M()->debug( "last_page: ". ( $this->last_page ? 'true': 'false' ) );
			// M()->mem_stats( 'salgo de page' );
			return $this->load_array_recordset( $rows );
		}

		// $cn == '_licencia' and xdebug_stop_trace();

	}/*}}}*/

	function paged_query( $sql, $pr, $cp ) {/*{{{*/

		$ret = null;


	
		if ( $this->db_type() == 'dblib' ) {

			M()->debug( "paged_query (dblib) con pr: $pr y cp: $cp" );
			$ret = $this->db->Execute( $sql );

		} else {

			M()->debug( "PageExecute con pr: $pr y cp: $cp" );
			$ret = $this->db->PageExecute( $sql, $pr, $cp );

		}

		if ( false and $this->class_name == 'rliquid_sumariza_aporte' ) 
		{ echo "<pre>"; print_r( $ret->fetchAll() ); exit; }

		return $ret;

	}/*}}}*/

	function prepare_dblib( $pr, $cp ) {/*{{{*/

		/*
		
		tomado de http://blog.pengoworks.com/index.cfm/2008/6/10/Pagination-your-data-in-MSSQL-2005
		
		-- create the Common Table Expression, which is a table called "pagination"

		WITH PAGINATION AS
		(
			SELECT

			ROW_NUMBER() ORDER ( ORDER BY field1, ... ) AS __RowNumber,

				field1, field2, ..., fieldN 
			FROM
				Employee
			WHERE
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


		$sql_text = null;

		$sql = $this->sql;

		if ( isset( $sql->sql[0] ) ) {

			$sql_text = array_shift( $sql->sql );
		}

		$offset = $pr * ( $cp - 1 );

		M()->info( "offset: $offset, page_rows: $pr" );

		$q = [];

		$q[] = "WITH PAGINATION AS (";

			$q[] = "SELECT ROW_NUMBER() ";
			$q[] = " OVER (";

			if ( $sql->make_order_clause() )
				$q[] = $sql->make_order_clause();
			else
				$q[] = $sql->make_order_clause( $this->get_primary_key_array( true ) );

			$q[] = ") AS __RowNumber,";


			$q[] = $sql->prepareSelectFields();
			$q[] = "FROM [". $this->get_table_name()."]";

			$q[] = $sql->make_join();
			$q[] = $sql->make_where_clause();
			$q[] = $sql->make_group_clause();
			$q[] = $sql->make_having_clause();


		$q[] = ")";

		$q[] = "SELECT *, (SELECT COUNT(*) FROM PAGINATION) as __TotalRows FROM PAGINATION WHERE __RowNumber BETWEEN $offset AND ". (string) ($offset + $pr). "  ORDER BY __RowNumber";

		$query = implode( ' ', $q );

		// print $query; exit;

		M()->info( $query );

		return $query;

	}/*}}}*/

	function execute( $sql = null ) {/*{{{*/

		if ( !$this->db() ) {
			$this->total_records = -2;
			return null;
		}

		$sql or $sql = $this->sql->prepare();

		try {
			M()->info( "ejecutado $sql" );
			$rs = $this->db->Execute( (string) $sql );

		} catch ( \PDOException $e ) {

			M()->error( sprintf( "Error al ejecutar (%d): %s %s", $this->ErrorNo(), $this->ErrorMsg(), $sql )) ;
			return null;
		}

		return $rs;

	}/*}}}*/

	function add_query( $query ) {/*{{{*/

		$this->queries_array = explode( ",", $query );

	}/*}}}*/

	function uniq_tables( $table = null, $alias = null ) {/*{{{*/

		if ( !$table ) {
			$this->uniq_tables_array = [];
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

	private function bind_data( array $hash, bool $track_modified = false ) {/*{{{*/

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

		if ( ! $this->db() ) {
			$this->total_records = -2;
			return $this->transact_status = DB_ERROR;
		}

		if ( !$this->modified ) return $this->transac_status = NO_OP;

		if ( $validate and $this->get_flag( 'check' ) and ( ! $this->check() ) ) {

			M()->info( "el metodo {$this->class_name}::check ha devuelto falso. No puedo guardar" );
			M()->user( "Datos inválidos o incompletos: no se pueden guardar", $this->class_name, 'not_valid' );
			$this->transac_status = NOT_VALID;

		} else { 

			$this->transac_status = ( $this->loaded() ) ? $this->update() : $this->insert();

			$this->loaded( true );
			$this->set_primary_key();

			if ( $this->get_flag( 'post_check' ) and ( in_array( $this->transac_status, array( INSERT_OP, UPDATE_OP, REPLACE_OP ) ) ) ) {

				M()->debug( "llamando a $this->class_name::post_check()" );
				$this->post_check();
				M()->debug( "volviendo de  $this->class_name::post_check()" );
			}
		}

		$this->feat->load_after_store and $this->load();

		return $this->transac_status;
	}/*}}}*/

	function insert () {/*{{{*/

		if ( $this->is_virtual() ) {

			M()->error( "Haciendo INSERT sobre un objeto virtual $this->class_name, revise tables.xml" );
			return $this->transac_status = NO_OP;
		}

		if ( ! $this->db() ) {
			$this->total_records = -2;
			return null;
		}

		if ( !$this->modified ) return $this->transac_status = NO_OP;

		global $xpdoc;

		if ( ! $this->has_access( 'add' ) ) {

			M()->user( 'No tiene permisos para agregar el objeto '. $this->class_name );
			// M()->user( print_r( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) ) );
			return $this->transac_status = NO_PERMS;
		}

		$this->sql = new DBQuery( $this->db );

		$this->sql->addTable ( $this->get_update_name() ) ;

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

		$sql = $this->sql->prepare();

		M()->debug( "insertando: $sql"  );

		try {
			$this->affected_records = $this->db->Execute( $sql );

		} catch( \PDOException $e ) {

			if ( $this->ErrorNo() == 1062  ) {
				if ( $this->feat->try_update_on_fail ) {

					// DEBUG: debe chequear si la clave primaria esta completa (ej. autonumeric) si no, no lo puede hacer
					M()->info( 'Haciendo update() con try_update_on_fail' );
					return $this->update();
				}

				else {
					M()->user( "Datos duplicados en el ingreso, no se puede guardar" );
					return ( $this->transac_status = DB_ERROR );
				}
			} else {

				M()->db_error( $this->db, 'INSERT', $sql );
				return ( $this->transac_status = DB_ERROR );
			}
		}

		if ( $af = $this->get_autonumeric_field() ) {

			/* para autonumerico, actualiza la clave con su valor */

			$attr = $this->get_attr( $af );
			$attr->value = $this->db->Insert_ID();
			M()->debug( $attr->name. ": ". $attr->value );
		}

		return ( $this->transac_status = INSERT_OP );
	}/*}}}*/

	function replace( $modifiers = null ) {/*{{{*/

		if ( $this->is_virtual() ) {

			M()->error( "Haciendo REPLACE sobre un objeto virtual $this->class_name, revise tables.xml" );
			return $this->transac_status = NO_OP;
		}

		if ( ! $this->db() ) {
			$this->total_records = -2;
			return $this->transact_status = DB_ERROR;
		}


		if ( !$this->modified ) return $this->transac_status = NO_OP;

		global $xpdoc;

		if ( ! $this->has_access( 'add' ) ) {

			M()->user( 'No tiene permisos para agregar el objeto '. $this->class_name );
			return $this->transac_status = NO_PERMS;
		}

		$this->sql = new DBQuery( $this->db );

		$modifiers and $this->sql->addModifiers( $modifiers );

		$this->sql->addTable ( $this->get_update_name() ) ;

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

		$sql = $this->sql->prepare();

		M()->debug( "reemplazando: $sql" );

		try {
			$this->affected_records = $this->db->Execute( $sql );

		} catch ( \PDOException $e ) {

			// unset( $this->sql->modifiers );

			M()->db_error( $this->db, 'REPLACE', $sql );
			return ( $this->transac_status = DB_ERROR );
		}

		// unset( $this->sql->modifiers );

		if ( $af = $this->get_autonumeric_field() ) {

			$attr = $this->get_attr( $af );
			$attr->value = $this->db->Insert_ID();
			M()->debug( $attr->name. ": ". $attr->value );
		}

		return ( $this->transac_status = REPLACE_OP );

	}/*}}}*/

	function update () {/*{{{*/

		if ( $this->is_virtual() ) {

			M()->error( "Haciendo UPDATE sobre un objeto virtual $this->class_name, revise tables.xml" );
			return $this->transac_status = NO_OP;
		}

		if ( ! $this->db() ) {
			$this->total_records = -2;
			return $this->transact_status = DB_ERROR;
		}

		if ( !$this->modified ) return $this->transac_status = NO_OP;

		global $xpdoc;

		if ( ! $this->has_access( 'edit' ) ) {

			M()->user( 'No tiene permisos para modificar el objeto '. $this->class_name );
			return $this->transac_status = NO_PERMS;
		}

		$this->sql = new DBQuery( $this->db );

		$this->sql->addTable ( $this->get_update_name() ) ;

		/* genera la consulta con los valores modificados del objeto */

		$mods = false;

		foreach ( $this->attr as $key => $attr ) {

			if ( $attr->virtual or $attr->alias_of ) continue;
			if ( !$attr->modified ) continue;

			$this->sql->addUpdate( $attr->name, $attr->encode() );

			$mods or $mods = true;
		}

		if ( !$mods ) return NO_OP;
		
		
		/* registra el where en la consulta con los valores de la clave primaria */

		foreach ( $this->primary_key as $key => $value ) {

			$attr = $this->get_attr( $key );
			
			if ( $value === null )
				$this->sql->addWhere( $key . ' IS NULL' );  
			else
				$this->sql->addWhere( $key . '=\''. $attr->encode( $value ). '\'' );  
		}

		$sql = $this->sql->prepare();

		M()->debug( 'update ' . $sql );

		// $this->debug_object( $this->sql ); exit;

		try {
			$this->affected_records = $this->db->Execute( $sql );

		} catch ( \PDOException $e ) {

			if ( $this->ErrorNo() == 1062 )
				M()->user( 'Datos duplicados en el ingreso, no se puede guardar' );

			M()->db_error( $this->db, 'UPDATE', $sql );
			return ( $this->transac_status = DB_ERROR );
		}

		return ( $this->transac_status = UPDATE_OP );

	}/*}}}*/

	function delete() {/*{{{*/

		if ( $this->is_virtual() ) {

			M()->error( "Haciendo DELETE sobre un objeto virtual $this->class_name, revise tables.xml" );
			return $this->transac_status = NO_OP;
		}

		if ( ! $this->db() ) {
			$this->total_records = -2;
			return $this->transact_status = DB_ERROR;
		}

		global $xpdoc;

		if ( ! $this->has_access( 'delete' ) ) {

			M()->user( 'No tiene permisos para elminar el objeto '. $this->class_name );
			return $this->transac_status = NO_PERMS;
		}

		if ( ! $this->pre_delete() ) {

			M()->user( 'No puede borrar este objeto '. $this->class_name );
			return $this->transac_status = NOT_VALID;
		}

		foreach ( $this->model->xpath( "obj[foreign_key/@type='wired']" ) as $obj ) {

			/* borra recursivamente los childs que el foreign_key/@type='wired' en el model */

			$child_name = (string) $obj['name'];

			$cond = [];

			foreach ( $obj->xpath( "foreign_key/ref" ) as $ref ) {

				$local = (string) $ref['local'];
				$remote = (string) $ref['remote'];

				$cond[$local] = $this->$remote;

				M()->info( "cond/local: $local, remote: $remote = {$cond[$local]}" );
			}

			M()->debug( "borrando recursivamente $child_name" );

			$childs = $xpdoc->get_instance( $child_name );

			foreach( $childs->load_set( $cond ) as $child ) {
			
				$child->delete(); 
			
			}
		}


		$this->sql = new DBQuery( $this->db );

		$this->sql->setDelete( $this->get_update_name() );

		foreach ( $this->primary_key as $field => $value ) {
		
			$this->sql->addWhere( 
				$this->quote_name( $this->get_update_name(). '.'. $field ). 
				sprintf( $value === null ? 'IS NULL' : "='%s'", $value ) ) ;
		}

		$sql = $this->sql->prepare();

		try {
			$this->affected_records = $this->db->Execute( $sql );

		} catch ( \PDOException $e ) {  

			M()->db_error( $this->db, 'DELETE', $sql ); 
			return ( $this->transac_status = DB_ERROR );
		}

		M()->info( "objeto {$this->class_name} borrado(s)" );

		$this->post_delete();

		$this->reset(); // el reset lo hago despues del post delete para que esten las variables disponibles al momento del metodo

		return ( $this->transac_status = DELETE_OP );

	}/*}}}*/

	// reset

	function reset() {/*{{{*/

		// deja el objeto en blanco

		// M()->debug( "reset objeto $this->class_name" );
		
		if ( ! is_array( $this->attr ) ) {

			M()->warn( "clase $this->class_name sin atributos" );
			return $this;
		}

		unset( $this->data );
		$this->data = [];

		foreach( $this->attr as $attr ) 
			$this->data[$attr->name] = null;

		$this->loaded( false );
		$this->is_new( true );
		$this->set_modified( false ); // attrs

		$this->pager = array( 'pr' => 0, 'cp' => 1 );

		return $this;

	}/*}}}*/

	function get_data() {/*{{{*/

		return $this->data;

	}/*}}}*/

	function set_data( $data ) {/*{{{*/

		// DEBUG: funcion interna, ojo que no valida si es [];

		return $this->data = $data;

	}/*}}}*/

	function set_modified( $value ) {/*{{{*/

		$this->modified = $value;
		foreach( $this->attr as $key => $attr ) $attr->modified = $value;

	}/*}}}*/

	function set_props( $prop, $value, $attr_names = null ) {/*{{{*/

		if ( $attr_names ) {

			$attrs = split( ',', $attr_names );

			foreach( $attrs as $key => $attr ) 
				$this->get_attr( trim( $key ) )->$prop = $value;

		} else {

			foreach( $this->attr as $key => $attr ) 
			$attr->$prop = $value;
		}

		return $this;

	}/*}}}*/

	function get_modified_attrs() {/*{{{*/

		$ret = [];

		foreach( $this->attr as $attr ) 

			if ( $attr->modified )

				$ret[] = $attr;

		return $ret;
	}/*}}}*/

	/* funciones para la serializacion */

	function serialize ( $flags = null ) {/*{{{*/

		$s = new Serialize( $this, $flags );
		return $s->serialize( $flags );

	}/*}}}*/

	function serialize_row ( $flags = null, $params = [] ) {/*{{{*/

		$s = new Serialize( $this, $flags );
		return $s->serialize_row( $flags, $params );

	}/*}}}*/

	function json() {/*{{{*/

		$s = new Json( $this );
		return $s->serialize();

	}/*}}}*/

	function csv() {/*{{{*/

		$s = new Csv( $this );
		return $s->serialize();

	}/*}}}*/

	function compose( string $template ) {/*{{{*/

		return string_parse( $template, (object) $this->get_data() ); 

	}/*}}}*/

	/* respuestas xml */

	function init_xml_obj( $node ) {/*{{{*/

		/* DEBUG: hay que arreglar esta funcion las condiciones de key_str y new */

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


		// print $node->asXML(); exit;

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

			$this->is_new( true );

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

			$this->is_new( false );

		} else {

			M()->debug('undefined new and key_str undefined');
			$this->load( $this->get_primary_key_node( $node ) );

			if ( $this->loaded() ) 
				$this->is_new( false );
			else
				$this->fill_primary_key();

		}

		$this->set_primary_key();
		$this->foreign_key and $this->foreign_key->assign();

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

		if ( $this->loaded() ) {
			$this->delete();
			M()->response( $this, $node );
		} else {
			M()->user('objeto no encontrado');
			M()->status('ERR');
		}

	}/*}}}*/

	function bind_post_vars() {/*{{{*/

		global $xpdoc;

		$ok = true;

		foreach( $xpdoc->http->get_post_vars() as $var_name ) {

			$value = $xpdoc->http->$var_name;

			if ( $attr = $this->get_attr( $var_name ) ) {

				if  ( $attr->type == 'xpdate' or $attr->type == 'xpdatetime' ) {

					if ( $value ) {

					   $ret = $attr->human( $value );
						M()->info( "date_human: $ret" );

						if ( $ret === null ) {
			   
							M()->user( "$attr->name: fecha inválida '$value'" );
							$ok = false;
				   
						} else {

							$attr->value = $ret;
						}
					} 				

				} else {

					$attr->value = $attr->unserialize( $value );
				}

				M()->info( "$var_name: {$value}" );
			}
		}

		return $ok;

	}/*}}}*/

	/* keys handling */ 
	/* primary */

	function config_primary_key() {/*{{{*/ 

		/* carga la configuracion para la clave primaria desde el modelo */

		if ( !$this->model ) 
			M()->fatal( "no hay modelo definido: no puedo cargar la clave primaria" );

		$refs = $this->model->xpath( "primary_key/primary" );
	
		is_array( $this->primary_key ) or $this->primary_key = [];

		foreach( $refs as $ref ) {

			$name = (string) $ref['name'];
			$this->primary_key[$name] = null;
			$attr = $this->get_attr( $name );


			if ( !$attr ) {

				M()->error( "no encuentro el atributo $name en el objeto $this->class_name" );
				return;
			} else
				$attr->primary = true;
		}

		if ( !count( $this->primary_key ) ){

			/* print( $this->model->asXML() ); */
			$this->is_virtual() or M()->info( "el objeto {$this->name} no tiene clave primaria definida" );
		}

		return $this;

		// echo "<pre>"; print_r( $this->primary_key ); echo "</pre>"; ob_flush(); exit;

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

		$ret = [];

		if ( count( $this->primary_key ) )  
			foreach( $this->primary_key as $name => $pk ) 
				$ret[$name] = (string) $node->$name;
		return $ret;

	}/*}}}*/

	function get_primary_key() {/*{{{*/

		$ret = [];

		if ( count( $this->primary_key ) )  
			foreach( $this->primary_key as $name => $pk ) 
				$ret[$name] = (string) $this->$name;
		return $ret;

	}/*}}}*/

	function get_primary_key_array( $full = false ) {/*{{{*/

		$ret = [];

		if ( count( $this->primary_key ) )  
			foreach( array_keys( $this->primary_key ) as $name ) 
				if ( $full )
					$ret[] = $this->get_table_name(). '.'. $name;
				else 
					$ret[] = (string) $name;
		return $ret;

	}/*}}}*/

	function is_primary_key( $attr_name ) {/*{{{*/

		if ( $attr = $this->get_attr( $attr_name ) )
			return $attr->is_primary_key();
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

		$keys = [];

		$i = 0;

		foreach ( $this->primary_key as $key => $data )
			$keys[$key] = $values[$i++];

		M()->debug( 'devulevo claves: '. json_encode( $keys ) );

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

	/* foreign */

	function config_foreign_key() {/*{{{*/ 

		/* carga la configuracion para la clave foranea desde el modelo */

		if ( ! count( $xp = $this->model->xpath( "foreign_key" ) ) )
			return;

		foreach( $xp as $xfk ) {
	
			if ( is_object( $xfk ) ) {

				$this->foreign_key = new Key( $this, $xfk );

				return $this; /* solo la primera */
			}
		}

	}/*}}}*/

	function is_foreign_key( $attr_name ) {/*{{{*/

		if ( $attr = $this->get_attr( $attr_name ) )
			return $attr->is_foreign_key();
		else
			return null;

	}/*}}}*/

	/* metadata */
	/* funciones virtuales */

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

	   	$xom = new \SimpleXMLElement( '<obj/>' );

	   	/* aca se genera la metadata final para la transformacion
		 * cualquier nuevo elemento agregarlo aca */

		foreach( $this->metadata->attributes() as $key => $value )
			$xom[$key] = $value;

		if ( count( $this->metadata->primary_key ) )
			simplexml_append( $xom, $this->metadata->primary_key );

		if ( $this->attr !== null ) {

			if ( count( $this->attr ) )
				foreach( $this->attr as $key => $attr ) 
					simplexml_append( $xom, $attr->metadata() );

		}

		if ( $xof = $this->feat->get_xml() )
			simplexml_append( $xom, $xof );

		if ( count( $this->metadata->index ) )
			foreach( $this->metadata->index as $index ) 
				simplexml_append( $xom, $index );

		/* DEBUG: aca meto tags de todo tipo de templates */

		if ( count( $t = $this->metadata->xpath("button") ) )
			foreach( $t as $item ) 
			   simplexml_append( $xom, $item );

		if ( count( $t = $this->metadata->xpath("storeCbk") ) )
			foreach( $t as $item ) 
			   simplexml_append( $xom, $item );

		if ( count( $t = $this->metadata->xpath("files") ) )
			foreach( $t as $item ) 
			   simplexml_append( $xom, $item );

		simplexml_append( $xom, array2xml( 'acl', $this->acl ) );

		simplexml_append( $xom, $this->processes );

		/* var_dump( $this->metadata->attributes() ); */

		/* $this->debug_backtrace(); */

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

		$this->invalid_attrs = [];

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

		$flag = $valid ? 'OK' : 'ERR';

		M()->info( "status: $flag" );

		/* ningun metodo ha seteado el status, entonces lo seteo aqui */

		( M()->status() == '' ) and M()->status( $flag );

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

	/* transaccion */

	function start_db_transaction( $param = null ) {/*{{{*/

		if ( ! $this->db() ) {
			$this->total_records = -2;
			return null;
		}

		M()->mem_stats();
		M()->info( 'iniciando transacciones de base de datos' );
		$this->db->StartTrans();
	
	}/*}}}*/

	function complete_db_transaction( $param = null ) {/*{{{*/
	
		M()->mem_stats();
		M()->info( 'completando transacciones de base de datos' );
		$this->db->CompleteTrans();

	}/*}}}*/

	function commit_db_transaction( $param = null ) {/*{{{*/
	
		M()->mem_stats();
		M()->info( 'completando transacciones de base de datos' );
		$this->db->CommitTrans( true );

	}/*}}}*/

	function ErrorNo() {/*{{{*/
		return $this->db->ErrorNo();
	}/*}}}*/

	function ErrorMsg() {/*{{{*/
		return $this->db->ErrorMsg();
	}/*}}}*/

	function is_new( $val = null ) {/*{{{*/

		if ( $val !== null ) {

			M()->debug( "set new == " . ($val ? 'true': 'false') );
			$this->__new = $val;

		} else 

			M()->debug( "new == " . ($val ? 'true': 'false') );
		
		return $this->__new;

	}/*}}}*/

	function loaded( $val = null ) {/*{{{*/

		if ( $val !== null ) {

			M()->debug( "set new == " . ($val ? 'true': 'false') );
			$this->__loaded = $val;

		} else 

			M()->debug( "new == " . ($val ? 'true': 'false') );
		
		return $this->__loaded;

	}/*}}}*/

	function translate() {/*{{{*/

		return $this->metadata['translate'] ? $this->metadata['translate'] : $this->class_name;

	}/*}}}*/

	function last_op() {/*{{{*/

		return $this->transac_status;

	}/*}}}*/

	function xpdoc() {/*{{{*/

		global $xpdoc;
		return $xpdoc;

	}/*}}}*/

	/* debug */

	function debug_object() {/*{{{*/


		$primary_key = json_encode( $this->primary_key );
		$foreign_key = null;

		// $foreign_key = json_encode( $this->foreign_key );

		$new = $this->is_new() ? 'true' : 'false';
		$loaded = $this->loaded() ? 'true' : 'false';
		$modified = $this->modified ? 'true' : 'false';

		$table_name = $this->get_table_name();

		print "<h1>Object: {$this->name}</h1>";
		print "<hr/>";
		print "<table border=\"1\">
				<tr><td>name:</td><td>{$this->name}</td></tr>
				<tr><td>type:</td><td>{$this->type}</td></tr>
				<tr><td>class_name:</td><td>{$this->class_name}</td></tr>
				<tr><td>table_name:</td><td>$table_name</td></tr>
				<tr><td>autonumeric_field:</td><td>{$this->autonumeric_field}</td></tr>
				<tr><td>is_new():</td><td>{$new}</td></tr>
				<tr><td>loaded:</td><td>{$loaded}</td></tr>
				<tr><td>modified:</td><td>{$modified}</td></tr>
				<tr><td>transac_status:</td><td>{$this->transac_status}</td></tr>
				<tr><td>record_count:</td><td>{$this->record_count}</td></tr>
				<tr><td>current_page:</td><td>{$this->pager['cp']}</td></tr>
				<tr><td>total_records:</td><td>{$this->total_records}</td></tr>
				<tr><td>primary_key:</td><td>{$primary_key}</td></tr>
				<tr><td>foreign_key:</td><td>{@$foreign_key}</td></tr>
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

	function filter_audit_transact_data( $transact_data ) {/*{{{*/

		return $transact_data;
	
	}/*}}}*/

} /* xpDataObject */

?>
