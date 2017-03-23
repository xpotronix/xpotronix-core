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

require_once 'xp.class.php';
require_once 'xpconfig.class.php';
require_once 'xpmessages.class.php';
require_once 'xpacl.class.php';
require_once 'xpdataprocess.class.php';
require_once 'xphttp.class.php';
require_once 'xpadodb.class.php';
require_once 'xpcache.class.php';

class xpdoc extends xp {

	protected $db = array();
	private $_xpid;

	var $db_driver;

	var $config;
	var $feat;

	var $session;
	var $user;

	var $http;

	// client stock
	var $css = array();
	var $js = array();

	// xml buffers
	var $xmenu;
	var $xdoc;

	var $output_buffer; // the output buffer
	var $mode;

	var $application;
	var $model;
	var $metadata;
	var $datatypes;

	private $_content_type;

	// types matching for views

	var $views;
	var $cache;
	var $cache_options;

	// parametros del URI

	var $controller_vars = 'm;a;b;q;v;d;s;o;r;f;t;h;p;j;e;g;x;method;_dc';
	var $module;
	var $action;
	var $query;
	var $view;
	var $data; // array con las variables del request
	var $search;
	var $req_object;
	var $param_schema;
	var $order;
	var $features;
	var $process;
	var $current_process;
	var $template;
	var $html;
	var $write;
	var $extra_param;
	var $xml; // post buffer
	var $json;
	var $pager = array();

	// array de instancias de objetos creados 
	var $instances = array();

	// Permissions

	var $perms;
	var $roles = array();

	var $obj_collection = array();

	// construct

	function  __construct( $config_file = null, $feat_file = null ) {/*{{{*/

		$this->db_driver = 'PDO';

		M()->info("current working directory: ". getcwd() );

		$this->load_ini();	
		// $this->load_datatypes();
		$this->load_features( $feat_file );

		// print $this->feat->get_xml()->asXML(); exit;

		$this->application = $this->feat->application or
			M()->fatal( 'no encuentro el nombre de la aplicacion en feat.xml' );

		$this->load_config( $config_file );

		M()->info( "application: $this->application" );

		$this->feat->set_fallback( $this->config ); // si no lo encuentra entre los features, lo buscara en config

		$this->feat->set_time_limit 
			and ( set_time_limit( $this->feat->set_time_limit ) 
				and M()->info( "tiempo maximo de ejecucion en {$this->feat->set_time_limit} segundos" ) );

		$this->feat->default_timezone 
			and ( date_default_timezone_set( $this->feat->default_timezone )
				and M()->info( "default_timezone: {$this->feat->default_timezone} segundos" ) );

		M()->info( "app encoding: {$this->feat->encoding}" );

		$this->http = new xphttp();

		if ( file_exists( 'modules/file_utils/file_utils.class.php' ) ) {

			require_once 'modules/file_utils/file_utils.class.php';
			foreach( array( 'app', 'data', 'acl' ) as $dir )
				Cfile_utils::mkdir( $this->get_cache_dir( $dir ) );
		}

		$this->cache_options = array(

			'caching' => (bool) $this->config->app_cache_time,
			'cacheDir' => $this->get_cache_dir( 'app' ),
			'lifeTime' => $this->config->app_cache_time,
			'fileLocking' => TRUE,
			'writeControl' => FALSE,
			'readControl' => FALSE,
			'memoryCaching' => TRUE,
			'automaticSerialization' => FALSE
		);


		return $this;

	}/*}}}*/

	function db_driver( $driver = null ) {/*{{{*/

		$driver and $this->db_driver = $driver;
		return $this->db_driver;
	}/*}}}*/

	function set_model( $model = null ) {/*{{{*/

		/* el modulo del sistema tiene asociado un modelo */

		( $this->module = $model and M()->info( 'asignando el modulo '. $this->module ) )
		or ( $this->module and M()->info( 'iniciando proceso para el modulo '. $this->module ) ) 
		or ( $this->module = $this->feat->default_module and M()->info( 'valor por default del modulo '. $this->module )) 
		or ( $this->module = 'users' and M()->info( "Valor por omision para el modulo: ". $this->module ));

		$this->feat->module = $this->module;

		$this->feat->base_path or $this->feat->base_path = getcwd(); 

	}/*}}}*/

	function load_config( $xml = null ) {/*{{{*/

		if ( $xml )
			$this->config = $xml;
		else {
			M()->info( $file = (( $this->ini['paths']['config'] ) ? $this->ini['paths']['config'] . "conf/{$this->application}": "conf/" ). '/config.xml' );
			$this->config = new xpconfig( $file );
		}

		M()->info('OK');

	}/*}}}*/

	function load_features( $xml = null ) {/*{{{*/

		if ( $xml )
			$this->feat = $xml;
		else {
			M()->info( $file =  'conf/feat.xml' );
			$this->feat = new xpconfig( $file );
		}

		M()->info('OK');

	}/*}}}*/

	// init

	function init() {/*{{{*/

		M()->info('***** Proceso xpotronix iniciado con xpid: *****'. $this->xpid( $this->get_hash() ) );

		// DEBUG: algo tiene que devolver false?

		$this->init_db_instances();
		$this->load_session();
		$this->load_acl();

		M()->info('OK');

		is_object( $this->session ) and $this->session->configure();

		return true;

	}/*}}}*/ 

	function close() {/*{{{*/

		is_object( $this->session ) and $this->session->close();

		$this->debug_obj_collection();

		unset( $this->obj_collection );

	}/*}}}*/

	function init_db_instances() {/*{{{*/

		$database = null;
		$host = null;
		$user = null;
		$password = null;
		$implementation = null;

		foreach( $this->config->db_instance as $instance ) {

			// por cara uno de los elementos db_instance en config.xml

			// parametros acumulativos entre instancias de base de datos

			if ( $instance->database )
				$database = $instance->database;
			else	$instance->database = $database;
 
			if ( $instance->host ) 
				$host = $instance->host;
			else	$instance->host = $host;

			if ( $instance->user ) 
				$user = $instance->user;
			else	$instance->user = $user;

			if ( $instance->password ) 
				$password = $instance->password;
			else	$instance->password = $password;

			if ( $instance->implementation ) 
				$implementation = $instance->implementation;
			else	$instance->implementation = $implementation;

			// check de parametros;

			( $database and M()->info( "database: $database" ) ) 
				or M()->warn( "debe especificar una base de datos" );
			( $host and M()->info( "host: $host" ) ) 
				or M()->warn( "debe especificar un servidor de  base de datos" );
			( $user and M()->info( "user: $user" ) ) 
				or M()->warn( "debe especificar un usuario de base de datos" );
			( $password and M()->info( "password: $password" ) ) 
				or M()->warn( "debe especificar un password de usuario de base de datos" );
			( $implementation and M()->info( "implementation: $implementation" ) ) 
				or M()->warn( 'debe especificar una implementacion de la base de datos' );

			$lazy = $instance->lazy or $lazy = false;

			$lazy or $this->open_db_instance( $instance );
		}
	}/*}}}*/

	function open_db_instance( $i ) {/*{{{*/

		if ( is_string( $i ) ) {

			$arr = $this->config->get_xml()->xpath( "db_instance[@name='$i']" );

			if ( ! ( $instance = array_shift( $arr ) ) ) {
				M()->error( "no encuentro la instancia $i" );
				return null;
			} 

		} else $instance = $i;

		// shorthands
		$in = (string) $instance['name'];

		$database = (string) $instance->database;
		$host = (string) $instance->host;
		$user = (string) $instance->user;
		$password = (string) $instance->password;
		$implementation = (string) $instance->implementation;

		M()->info( "abriendo la instancia $in con la base de datos {$instance->database}" );
	
		$encoding = (string) $instance->encoding or $encoding = 'utf8';

		switch( $this->db_driver ) {

			case 'ADODB':
				require_once 'adodb.inc.php'; // DEBUG: el factory tiene que estar en xpadodb
				$dbi = $this->db_instance( $in, NewADOConnection( $implementation ) );
				break;
			default:
				$dbi = $this->db_instance( $in, new xpadodb( $in, $implementation ) );
		}
	

		( $instance->table_prefix ) and ( $dbi->tablePrefix = (string) $instance->table_prefix ) and M()->info( "table_prefix: $instance->table_prefix" );

		M()->info( $function = $instance->persistent ? 'PConnect' : 'Connect' );

		if ( ! $dbi->$function( $host, $user, $password, $database, $encoding ) ) {

			M()->error( "No puedo conectarme con la base de datos {$database}" ) ;
			return null;

		}

		$instance->fetch_mode and $dbi->SetFetchMode( (string) $instance->fetch_mode ) and M()->info( "fetch_mode: $instance->fetch_mode" );

		$instance->force_utf8 and $dbi->force_utf8 = true and M()->info( "force_utf8" );

		$instance->encoding and $dbi->__encoding = (string) $instance->encoding and M()->info( "encoding: $instance->encoding" );

		return $dbi;

	}/*}}}*/

	function init_acl_db() {/*{{{*/

		if ( $gacl_class = $this->config->gacl_class ) {

			if ( ! $acl_db = $this->db_instance( 'default-acl' ) )
				$acl_db = $this->db_instance(); // default 

			$params = array();

			$params['db'] = $acl_db;
			$params['caching'] = (bool) $this->config->gacl_cache_time;
			$params['cache_expire_time'] = $this->config->gacl_cache_time;
			$params['cache_dir'] = $this->get_cache_dir( 'acl' );
			( @$acl_db->tablePrefix ) and $params['db_table_prefix'] = $acl_db->tablePrefix;

			$this->perms = new $gacl_class( $params ); 

		} else M()->warn( 'no hay definida una clase de permisos del sistema, no hay seguridad alguna' );

	}/*}}}*/

	function db_instance( $name = null, $db_handler = null ) {/*{{{*/

		if ( !$name )
			foreach( $this->db as $ret )
				return $ret;

		if ( is_object( $db_handler ) )
			$this->db[$name] = $db_handler;

		if ( $name and ( ! array_key_exists( $name, $this->db ) ) ) 
			return null;
		else
			return $this->db[$name];

	}/*}}}*/

	function load_session() {/*{{{*/

		M()->info('START');

		if ( ! $this->feat->class_session ) {

			M()->warn('no hay una clase definida para el manejo de sesiones');
			return;
		}


		$t = $this->config->trusted_host and M()->warn( "trusted_host (deprecated): $t" );

		/* trusted_host_ip */

		$trusted_host_ip = array();

		if ( $this->config->trusted_host_ip ) {

			$trusted_host_ip = explode( ';', $this->config->trusted_host_ip );
			M()->info( "trusted_host_ip: ". serialize( $trusted_host_ip ) );
		}


		/* trusted_host_name */

		$trusted_host_name = array();

		if ( $this->config->trusted_host_name ) {

			$trusted_host_name = explode( ';', $this->config->trusted_host_name );
			M()->info( "trusted_host_name: $trusted_host_name" );
		}

		

		$this->session = $this->instance( $this->feat->class_session );
		$this->user = $this->instance( $this->feat->class_user );

		$sid = $this->session->start( $this->application );

		$this->session->read( $sid ); 

		// print var_dump( $this->session->user_id ); exit;


		if ( CLI ) {

			// sesion CLI

			M()->debug('CLI: cargando trusted_host_user_id con id '. $this->config->trusted_host_user_id );
			$this->session->user_id = $this->config->trusted_host_user_id;

		} else {

			// sesion WEB

			M()->info('remote_addr: '. $this->http->remote_addr );

			if ( $this->session->user_id === NULL or $this->session->user_id === '' ) {

				// no hay sesion

				M()->info('no existe la sesion' );


				if ( ( $this->config->trusted_host_user_id !== NULL ) ) {

					M()->debug( "trusted_host_user_id: {$this->config->trusted_host_user_id}" );

					if ( in_array( $this->http->remote_addr, $trusted_host_ip, true ) ) {

						M()->info( "trusted_host_ip machea remote_addr: {$this->http->remote_addr}" );
						$this->session->user_id = $this->config->trusted_host_user_id;

					} else if ( $this->config->trusted_host_name !== NULL ) {

						if ( in_array( $trusted_host_name, $this->http->remote_host_name(), true ) ) {
							M()->info( "trusted_host_name machea remote_host: {$this->http->remote_host}" );
							$this->session->user_id = $this->config->trusted_host_user_id;
						} else {
							M()->debug('cargando anonymous_user_id con id '. $this->config->anonymous_user_id );
							$this->session->user_id = $this->config->anonymous_user_id;
						}
					} else {

						M()->debug('cargando anonymous_user_id con id '. $this->config->anonymous_user_id );
						$this->session->user_id = $this->config->anonymous_user_id;
					}
				} else {

					M()->debug('cargando anonymous_user_id con id '. $this->config->anonymous_user_id );
					$this->session->user_id = $this->config->anonymous_user_id;
				}
			} else 
				M()->info( "la sesion existe" );
		}
		
		$this->user->load( $this->session->user_id );
		$this->user->attr( '_anon' )->set( 'virtual', true )->set( 'type' , 'int' )->set( 'value', $this->user->user_id == $this->config->anonymous_user_id );

		M()->info( 'Cargada Sesion con Usuario ID '. $this->user->user_id );
		M()->info('OK');

	}/*}}}*/

	function current_session() {/*{{{*/

		return $this->session->session_id;
	}/*}}}*/

	function load_acl() {/*{{{*/

		$this->init_acl_db();

		if ( is_object( $this->perms ) ) {

			$this->perms->setUserId( $this->user->user_id );

			$this->roles = $this->perms->getUserRoles();

			M()->info('roles para el usuario '. serialize( $this->roles ) );	
		}

		M()->info( 'OK' );

	}/*}}}*/

	function params_process() {/*{{{*/

		if ( ! $this->http ) {

			M()->warn( 'no esta inicializado el xphttp para este documento, no puedo procesar los parametros' );
			return;
		}


		$this->http->m and $this->set_model( $this->http->m );

		$this->action         = $this->http->a;
		$this->query          = $this->http->q;
		// $this->view           = $this->http->v;
		$this->data	      = $this->http->d;
		$this->search 	      = $this->http->s;
		$this->order          = $this->http->o;
		$this->req_object     = $this->http->r or $this->http->m;
		$this->features       = $this->http->f;
		$this->template       = $this->http->t;
		$this->html           = $this->http->h;
		$this->process	      = explode( ';', $this->http->p );
		$this->json	      = json_decode( $this->http->j );
		$this->param_schema   = $this->http->b;
		$this->extra_param    = $this->http->e;
		$this->pager	      = $this->http->g;

		// echo var_dump( $_REQUEST ); exit;

		try {
			if ( $this->http->x )
				$this->xml  = new SimpleXMLElement( stripslashes( $this->http->x ));
		} catch (Exception $e) {
			M()->error( "el fragmento XML recibido en el parametro x no es valido, no se puede procesar: ". $e->getMessage() );
		}

		if ( $this->features )
			foreach ( $this->features as $feat => $value )
				$this->feat->$feat = $value;

		if ( $this->feat->debug_xml ) 
			M()->debug( 'xml: '.$this->http->x );

		/* parametros de la consulta */


		if ( $this->param_schema == 'ext4' ) {

			require_once 'xpparams.class.php';

			$params = new xpparam();
			M()->info( "params: ". serialize( $params->get() ) );
			$params->process();
			// print_r( $params->get() );

		} else {

			foreach ( $this->http->var as $key => $data ) {

				if ( strstr( $this->controller_vars, $key. ';' ) ) 
					continue; 

				$this->search[$this->req_object][$key] = $data;
			}

			// query_field: sobre que campo alias tiene que buscar (ej.: _label)

			if ( ( $query = $this->http->query ) and ( $query_field = $this->feat->query_field ) ) {

				$this->search[$this->req_object][$query_field] = $query;
				M()->info( "parametro query buscando el valor \"$query\" sobre [$query_field]" );
			}

			if ( $this->http->g ) {

				$g = $this->http->g;

				if ( isset( $g['limit'] ) and $g['limit'] > 0 ) {

					// equivalencia start/limit vs page_row/current_page

					$this->pager[$this->req_object]['pr'] = $g['limit'];
					$this->pager[$this->req_object]['cp'] = (int) ceil( $g['start'] / $g['limit'] ) + 1;
				}

				if ( isset( $g['sort'] ) ) 

					$this->order[$this->req_object][$g['sort']]=$g['dir'];
			}

		}

		//print_r( $this->search );
		M()->info( 'OK' );
	}/*}}}*/

	function xpid( $value = NULL ) {/*{{{*/

		$value and $this->_xpid = $this->get_hash();

		return $this->_xpid;
	}/*}}}*/

	function get_cache_dir( $suffix = null ) {/*{{{*/

		$suffix and $suffix = "$suffix/";

		return "{$this->config->cache_dir}/{$this->config->application}/$suffix";

	}/*}}}*/

	function get_log_dir( $suffix = null ) {/*{{{*/

		$suffix and $suffix = "$suffix/";

		return "{$this->config->log_dir}/{$this->config->application}/$suffix";

	}/*}}}*/

	// head

	function headers_do() {/*{{{*/

		$this->header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); // Date in the past
		$this->header( "Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" ); // always modified
		$this->header( "Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0" ); // HTTP/1.1
		$this->header( "Pragma: no-cache" ); // HTTP/1.0
		// $this->header( "Content-type: text/html;charset=UTF-8" );

	}/*}}}*/


	function header( $directive, $replace = true ) {/*{{{*/

		M()->debug( $directive );
		header( $directive, $replace );

	}/*}}}*/

	// metadata

	function load_metadata() {/*{{{*/

		$file = "modules/{$this->module}/{$this->module}.metadata.xml";

		( $this->metadata = simplexml_load_file( $file ) 
			and M()->info( "cargando el modelo para el modulo {$this->module} desde $file" ) ) 
		or M()->fatal( "no puedo encontrar la definicion de la metadata en $file:". $e->getMessage() ); 

		return $this;
	}/*}}}*/

	// model

	function load_model() {/*{{{*/

		isset( $this->module ) or $this->set_model();
		$file = "modules/{$this->module}/{$this->module}.model.xml";

		( @$this->model = simplexml_load_file( $file )
			and M()->info( "cargando el modelo para el modulo {$this->module} desde $file" ) )
		or M()->error( "no puedo encontrar la descripcion del modelo en $file" );

		if ( ! $this->model ) 
			return false;

		$this->load_metadata();

		$objs = $this->model->xpath( "//obj" );

		foreach ( $objs as $model )
			if ( !$this->add_class( $model ) ) 
				return false;

		// chequeos varios del modelo y los objetos

		foreach( $this->instances as $name => $obj ) {

			if ( ! is_object( $obj ) ) {

				unset( $this->instances[$name] );
				M()->error( "objeto $name eliminado del modelo" );
				continue;
			}

			if ( !$obj->constructed ) 
				M()->error( "el objeto $name no fue inicializado correctamente. Incluya el llamado ::__construct() para este objeto" );

		}

		M()->info( "OK" );

		return true;

	}/*}}}*/

	function instance( $object, $model = null ) {/*{{{*/

		$class_name = XP_CLASS_NAMESPACE . $object;
		$class_file_name = "modules/$object/$object.class.php";

		if ( file_exists( $class_file_name ) ) {

			require_once $class_file_name;
			M()->info( "nueva clase $class_name para la instancia $object" );
			return new $class_name( $model );

		} else {

			M()->error( "no existe el archivo $class_file_name para la clase $class_name" );
			return null;

		}

	}	/*}}}*/

	function get_instance( $obj_name = null ) {/*{{{*/

		if ( count( $this->instances ) < 1 ) {

			M()->error( 'No hay instancias definidas' );
			return null;
		} 

		if ( !$obj_name ) {

			M()->info( "devolviendo la primera instancia del modulo" );
			reset( $this->instances );
			return current( $this->instances );
		} 

		if ( array_key_exists( $obj_name, $this->obj_collection ) ) {

			M()->info( "devolviendo una instacia existente" );
			return $this->obj_collection[$obj_name][0];

		} else {

			if (  $instance = $this->instance( $obj_name ) ) {

				M()->info( "creando una nueva instancia para $obj_name" );
				return $instance;

			} else
				M()->error( 'No encuentro la instancia '. $obj_name );

		}

	}/*}}}*/

	function add_class( $model ) {/*{{{*/

		if ( !$model ) M()->error( "No hay modelo para esta definicion del objeto" );

		$class_name = (string) $model['name'];

		$this->instances[$class_name] = $this->instance( $class_name, $model );

		return true;

	}/*}}}*/

	function load_menu() {/*{{{*/

		require_once 'xpmenu.class.php';
		$this->xmenu = new xpmenu( 'conf/menu.xml' );

	}/*}}}*/

        function load_datatypes() {/*{{{*/

                // DEBUG: podria haber eventualmente tipos de datos por objeto (ej. mysql, mssql, oracle) a la vez

                $file = "conf/datatypes.xml";

                if ( ! $this->datatypes = simplexml_load_file( $file ) )
                        M()->fatal( "No puedo cargar los tipos de datos de $file: " );
                return $this->datatypes;
                
        }/*}}}*/

	function load_views( $vf ) {/*{{{*/

		try { $this->views = simplexml_load_file( $this->get_template_file( $vf ) ) ; } 
		catch (Exception $e) {
			M()->fatal( "No encuentro el archivo $vf" );
		}

	}/*}}}*/

	function content_type( $type = null, $encoding = null ) {/*{{{*/

		if ( ! $type ) return $this->_content_type;

		if ( $encoding or ( $encoding = (string) $this->feat->encoding ) ) $type .= ";$encoding";

		M()->info( "content_type: $type" );

		return $this->_content_type = $type;
	}/*}}}*/

	function get_view() {/*{{{*/

		return $this->view;
	}/*}}}*/

	function set_view( $view = null ) {/*{{{*/

		if ( !$this->views ) 
			$this->load_views( 'views.xml' );

		// default view

		if ( $view ) {

			M()->info( "via parametro" );
			$this->view = $view;

		} else if ( (string) $this->http->v ) {

			M()->info( "via &v={$this->http->v}" );
			$this->view = $this->http->v;

		} else if ( $v = $this->get_instance()->metadata['layout'] ) {

			M()->info( "vista por default del modulo" );
			$this->view = $v;

		} else {

			M()->info( "via default view" );
			$this->view = $this->feat->default_view;
		}

		foreach ( $this->views->xpath( "//view[@name='*']" ) as $view ) 
			$ct = $this->content_type( (string) $view['type'] );

		foreach ( $this->views->xpath( "//view[@name='{$this->view}']" ) as $view ) 
			$ct = $this->content_type( (string) $view['type'] );

		M()->info( "vista {$this->view}, content_type: $ct, OK" );
		M()->line( 0 );

	}/*}}}*/

	// extra files

	function get_template_file( $template_file ) {/*{{{*/

		$file = getcwd(). "/templates/". $template_file;

		if ( file_exists( $file ) ) return $file;

		M()->debug( 'no existe '. $file );

		$file1 = $this->ini['paths']['lib']."/templates/" . $template_file;

		if ( file_exists( $file1 ) ) return $file1;

		M()->error( "no encontre el archivo $template_file ni en $file ni en $file1" );

		return null;

	}/*}}}*/

	function get_config_file( $config_file ) {/*{{{*/

		$file = "config/". $config_file. ".xml";

		if ( file_exists( $file ) ) return $file;

		$file1 = $this->ini['paths']['lib']."/config/" . $config_file . ".xml";

		if ( file_exists( $file1 ) ) return $file1;

		M()->error( "no encontre el archivo $config_file ni en $file ni en $file1" );

		return null;

	}/*}}}*/

	function add_css( $href, $media = "all" ) {/*{{{*/

		$this->css[] = array( 'href' => $href, 'media' => $media ) ;

	}/*}}}*/

	function add_js( $href, $extra = NULL ) {/*{{{*/

		$this->js[] = array( 'href' => $href, 'extra' => $extra );

	}/*}}}*/

	// procesos

	function process() {/*{{{*/


		// default view
		$this->get_view() or $this->set_view( ( CLI ) ? 'text' : 'xml' );

		$obj = $this->get_instance( $this->module );
		$instances = $this->instances;
		$user = $this->user;
		$xml = $this->xml;
		$processes = $this->process;

		$this->current_process = new xpdataprocess( $obj, $instances, $user, $processes, $xml ) and $this->current_process->process();

		if ( ! $this->view ) 
			$this->set_view( 'error' );

		return isset($this->xdoc) ? $this->xdoc : $this->get_messages();

	}/*}}}*/

	// roles

	function has_role() {/*{{{*/

		$arr_role = func_get_args();

		$arr_role = is_array( $arr_role[0] ) ? $arr_role[0]: $arr_role;

		M()->debug( serialize( $arr_role ) );

		if ( is_array( $this->roles ) ) 

			foreach( $this->roles as $user_role )

				foreach( $arr_role as $role )

					if ( $user_role['value'] == trim( $role ) )

					return true;

		return false;

	}/*}}}*/

	function serialize_roles() {/*{{{*/

		$roles = new SimpleXMLElement( "<roles/>" );

		foreach( $this->roles as $role ) {

			$role_xml = $roles->addChild( "role" );
			$role_xml['id'] = $role['id'];
			$role_xml['name'] = $role['name'];
			$role_xml['value'] = $role['value'];
			$role_xml['parent_id'] = $role['parent_id'];
		}

		return $roles;
	}/*}}}*/

	// get_*

	function get_session() {/*{{{*/

		M()->info();

		$d = new DOMDocument;
		$x = simplexml_import_dom( $d->createElementNs(XPOTRONIX_NAMESPACE_URI, "xpotronix:session") );

		if ( $this->feat->expose_server_vars )
			simplexml_append( $x, $this->http->get_SERVER_xml() );

		simplexml_append( $x, $this->http->get_xml() );

		if ( $this->session ) {

			simplexml_append( $x, $this->session->serialize_row() );
			$this->user->get_attr( 'user_password' )->display = 'protect';
			simplexml_append( $x, $this->user->serialize_row() );
			simplexml_append( $x, $this->serialize_roles() );
			simplexml_append( $x, $this->feat->get_xml() );

		} 

		simplexml_append( $x, $this->get_menu() );

		return $x;

	}/*}}}*/

	function get_menu() {/*{{{*/


		M()->info();

		$this->xmenu or $this->load_menu();

		return $this->xmenu->get_xml();

		/* menu sin namespace 

		$d = new DOMDocument;
		$x = simplexml_import_dom( $d->createElementNs(XPOTRONIX_NAMESPACE_URI, "menu") );

		simplexml_append( $x, $this->xmenu->get_xml() );

		return $x;
		*/

	}/*}}}*/

	function get_model() {/*{{{*/

		M()->info();

		$d = new DOMDocument;
		$x = simplexml_import_dom( $d->createElementNs(XPOTRONIX_NAMESPACE_URI, "xpotronix:model") );

		simplexml_append($x, $this->model);

		return $x;

	}/*}}}*/

	function get_metadata() {/*{{{*/

		M()->info();

		$d = new DOMDocument;
		$x = simplexml_import_dom( $d->createElementNs(XPOTRONIX_NAMESPACE_URI, "xpotronix:metadata") );

		foreach( $this->instances as $name => $obj ) {

			if ( $this->req_object == $name and ( $do = $this->feat->display_only ) )
				$obj->hide_all( $do );

			simplexml_append( $x, $obj->metadata() );
		}

		return $x;

	}/*}}}*/

	function get_dataset( $flags = false, $ns = false ) {/*{{{*/

		M()->info();

		$instance_fn = ( $this->req_object == 'users' or $this->req_object == 'sessions' ) ? 'instance' : 'get_instance';

		if ( ! ( $obj = $this->$instance_fn( $this->req_object ) ) ) return null;

		if ( $this->query ) $obj->add_query( $this->query );

		if ( $do = $this->feat->display_only ) 
			$obj->hide_all( $do );

		$dataset = $obj->serialize( $flags );

		if ( $ns ) { 

			$d = new DOMDocument;
			$x = simplexml_import_dom( $d->createElementNs(XPOTRONIX_NAMESPACE_URI, "xpotronix:dataset") );

			simplexml_append($x, $dataset);

			return $x;
		
		}

		return $dataset;

	}/*}}}*/

	function get_messages( $all = false ) {/*{{{*/

		M()->info();

		$d = new DOMDocument;
		$x = simplexml_import_dom( $d->createElementNs(XPOTRONIX_NAMESPACE_URI, "xpotronix:messages") );

		simplexml_append( $x, M()->serialize( $all ) );
		simplexml_append( $x, M()->get_response() );
		simplexml_append( $x, M()->get_changes() );

		if ( $this->feat->debug_xml && $this->xml ) 
			simplexml_append( $x, $this->xml );

		return $x;

	}/*}}}*/

	function get_document() {/*{{{*/

		M()->info();

		$d = new DOMDocument;
		$x = simplexml_import_dom( $d->createElementNs(XPOTRONIX_NAMESPACE_URI, "xpotronix:document") );


		simplexml_append( $x, $this->get_session() );
		simplexml_append( $x, $this->get_model() );
		simplexml_append( $x, $this->get_metadata() );

		if ( $obj = $this->get_instance( $this->req_object ) )
			if ( $obj->feat->include_dataset )
				simplexml_append( $x, $this->get_dataset( $obj->feat->include_dataset, true ) );

		simplexml_append( $x, $this->get_messages() );

		return $x;

	}/*}}}*/

	function set_xdoc( $xdoc ) {/*{{{*/

		M()->info('configurando el xdoc');

		return $this->xdoc = $xdoc;

	}/*}}}*/

	function get_json() {/*{{{*/

		M()->info();

		$instance_fn = ( $this->req_object == 'users' or $this->req_object == 'sessions' ) ? 'instance' : 'get_instance';

		if ( ! ( $obj = $this->$instance_fn( $this->req_object ) ) ) return null;

		if ( $this->query ) $obj->add_query( $this->query );

		if ( $do = $this->feat->display_only ) 
			$obj->hide_all( $do );

		return $obj->json();

	}/*}}}*/

	function get_csv() {/*{{{*/

		M()->info();

		$instance_fn = ( $this->req_object == 'users' or $this->req_object == 'sessions' ) ? 'instance' : 'get_instance';

		if ( ! ( $obj = $this->$instance_fn( $this->req_object ) ) ) return null;

		if ( $this->query ) $obj->add_query( $this->query );

		if ( $do = $this->feat->display_only ) 
			$obj->change_attr( 'display', 'ignore', $do );

		foreach( $obj->get_primary_key_array() as $key ) 
			$obj->get_attr( $key )->display = '';

		// $obj->debug_object(); exit;

		return $obj->csv();

	}/*}}}*/

	// action_do

	function action_do() {/*{{{*/

		M()->info( "solicitando action [{$this->action}]" );

		switch ( $this->action ) {

			case 'process': 
				$this->set_xdoc( $this->process() );
				if ( ( $audit = $this->instance('audit') ) ) $audit->record();
				break;

			case 'store': 
				$this->set_xdoc( $this->store() );
				if ( ( $audit = $this->instance('audit') ) ) $audit->record();
				break;

			case 'delete': 
				$this->set_xdoc( $this->delete() );
				if ( ( $audit = $this->instance('audit') ) ) $audit->record();
				break;

			// serializacion de la base de datos

			case 'blank':
				$this->set_xdoc( $this->get_dataset( DS_BLANK | DS_DEFAULTS ) );
				break;

			case 'blank_r':
				$this->set_xdoc( $this->get_dataset( DS_BLANK | DS_DEFAULTS | DS_RECURSIVE ) );
				break;

			case 'blank_dataset':
				$this->set_xdoc( $this->get_dataset( DS_BLANK | DS_DEFAULTS | DS_NORMALIZED ) );
				break;

			case 'blank_dataset_r':
				$this->set_xdoc( $this->get_dataset( DS_BLANK | DS_DEFAULTS | DS_RECURSIVE | DS_NORMALIZED ) );
				break;

			case 'data':
				$this->set_xdoc( $this->get_dataset( DS_ANY ) );
				break;

			case 'data_r':
				$this->set_xdoc( $this->get_dataset( DS_RECURSIVE ) );
				break;

			case 'dataset':
				$this->set_xdoc( $this->get_dataset( DS_NORMALIZED ) );
				break;

			case 'dataset_r':
				$this->set_xdoc( $this->get_dataset( DS_RECURSIVE | DS_NORMALIZED ) );
				break;

			case 'datab':
				$this->set_xdoc( $this->get_dataset( DS_ANY | DS_BLANK | DS_DEFAULTS ) );
				break;

			case 'datab_r':
				$this->set_xdoc( $this->get_dataset( DS_RECURSIVE | DS_BLANK | DS_DEFAULTS ) );
				break;

			case 'databset':
				$this->set_xdoc( $this->get_dataset( DS_NORMALIZED | DS_BLANK | DS_DEFAULTS ) );
				break;

			case 'databset_r':
				$this->set_xdoc( $this->get_dataset( DS_RECURSIVE | DS_NORMALIZED | DS_BLANK | DS_DEFAULTS ) );
				break;

			case 'json':
				$this->json = $this->get_json(); 
				$this->set_view( 'json' );
				break;
		
			case 'csv':
				$this->output_buffer = $this->get_csv(); 
				$this->set_view( 'csv' );
				break;


			// menu

			case 'menu':

				$this->set_xdoc( $this->get_menu() );
				break;

				if ( ( $audit = $this->instance('audit') ) ) $audit->record();
				break;

			// config 

			case 'session':

				if ( ! $this->get_instance()->can('access') ) {

					M()->user('acceso denegado');
					M()->status('ERR');
					$this->set_xdoc( $this->get_messages() );
					$this->set_view( 'error' );

				} else {

					$this->set_view('xml');
					$this->set_xdoc( $this->get_session() );
				}

				if ( ( $audit = $this->instance('audit') ) ) $audit->record();
				break;

			case 'metadata':

				if ( ! $this->get_instance()->can('access') ) {

					M()->user('acceso denegado');
					M()->status('ERR');
					$this->set_xdoc( $this->get_messages() );
					$this->set_view( 'error' );

				} else {

					$this->set_view('xml');
					$this->set_xdoc( $this->get_metadata() );

				}
		
				if ( ( $audit = $this->instance('audit') ) ) $audit->record();
				break;

			case 'model':

				if ( ! $this->get_instance()->can('access') ) {

					M()->user('acceso denegado');
					M()->status('ERR');
					$this->set_xdoc( $this->get_messages() );
					$this->set_view( 'error' );

				} else {

					$this->set_view('xml');
					$this->set_xdoc( $this->get_model() );

				}

				if ( ( $audit = $this->instance('audit') ) ) $audit->record();

				break;

			// user

			case 'login':

				// $this->set_view( 'json' );
				$this->json = $this->user->POST_login();
				if ( ( $audit = $this->instance('audit') ) ) $audit->record();
				break;

			case 'logout':

				// $this->set_view( 'json' );
				$this->json = $this->user->logout();
				if ( ( $audit = $this->instance('audit') ) ) $audit->record();
				break;

			case 'change_password':

				// $this->set_view( 'json' );
				$this->json = $this->user->change_password();
				if ( ( $audit = $this->instance('audit') ) ) $audit->record();
				break;

			case 'xpotronize':

				if ( ! $this->get_instance()->can('xpotronize') ) {

					M()->user('no puede realizar la trasformacion');
					M()->status('ERR');
					$this->set_xdoc( $this->get_messages() );
					$this->set_view( 'error' );

					if ( ( $audit = $this->instance('audit') ) ) 
						$audit->record();

					break;

				}

				$this->set_view( 'xml' );
				require_once 'xpotronize.class.php';
				$x = new xpotronize;

				$x->init( array( 'xpotronize', $this->application ));
				$x->check_params_xpotronize();
				$x->transform();

				M()->status('OK');
				$this->set_xdoc( $this->get_messages() );
				if ( ( $audit = $this->instance('audit') ) ) $audit->record();

				break;

			default:


				if ( ! $this->get_instance()->can('access') ) {

					M()->user('acceso denegado para el modulo '. $this->get_instance()->class_name );
					M()->status('ERR');
					// $this->set_xdoc( $this->get_messages() );
					// $this->set_view( 'error' );
					if ( ( $audit = $this->instance('audit') ) ) $audit->record();
				}

				// TODO?: si hay definida una accion y no se encuentra en la lista, probar con procesos (xpdataprocess)
				// en los objetos del modulo primero
				// en los plugins del sistema

		}

		M()->info('OK');	

 	}/*}}}*/

	// transform

	function output() {/*{{{*/

		/* view == 'csv' genera el output directamente en el modulo xpcsv */

		if ( $this->view == 'csv' or $this->view == 'none' ) return;

		$this->headers_do();

		M()->info( $tmp = "Content-type: ". $this->content_type() );

		$this->header( $tmp );

		/* output final */

		print( $this->output_buffer );

	}/*}}}*/

	function view_ID () {/*{{{*/

		$ret[] = $this->user->user_username;
		$ret[] = $this->session->session_id;
		$ret[] = $this->config->application;
		$ret[] = $this->module;
		$ret[] = $this->req_object;
		$ret[] = $this->view;

		return implode( ':', $ret );
	}/*}}}*/

	function transform( $view = null, $xdoc = null, $params = null, $transform_type = null, $cache = true ) {/*{{{*/

		$tmp_file = null;

		$view and $this->set_view( $view );

		$this->get_view() or $this->set_view( 'xml' );

		M()->info( 'transform view: ' . $this->get_view() );

		if ( $this->view == 'csv' or $this->view == 'none' ) return;

		if ( $this->config->app_cache_time and $cache ) {

			if ( ! is_object( $this->cache ) ) 

				if ( is_object( $this->cache = new xpcache( $this->cache_options ) ) )
					M()->info( 'Cache habilitada par la aplicacion' );
				else
					M()->info( 'No se pudo habilitar la cache para la aplicacion' );
				
			if ( $this->output_buffer = $this->cache->get( $this->view_ID() ) ) {

				M()->info( 'pagina de cache encontrada: '. $this->view_ID() );

				return $this->output_buffer;

				}

			else M()->info( 'pagina de cache no encontrada: '. $this->view_ID() );

		} else M()->info( 'Cache deshabilitada para la aplicacion' );


		if ( is_object( $xdoc ) ) {

			M()->info( 'recibo un xdoc via parametro' );

		} else if ( is_object( $this->xdoc ) ) {

			$xdoc = $this->xdoc;
			M()->info( 'asigno el xdoc de xpdoc' );

		} else {

			$xdoc = $this->get_document();
			M()->info( 'no hay xpdoc, hago uno' );
		}


		if ( $this->html ) {

			//$filename = "static/{$this->html}.xhtml";
			$filename = "static/{$this->html}.html";
			$handle = fopen( $filename, "r" );

			$this->content_type( 'text/html' );
			$this->output_buffer = fread ( $handle, filesize ($filename) );

			fclose( $handle );

		} else if ( $this->view == 'xml' ) {

			$this->content_type( 'text/xml' );
			$this->output_buffer = $xdoc->asXML(); 

		} else if ( $this->view == 'json' ) {

			$this->content_type( 'text/x-json' );

			if ( is_array( $this->json ) ) 
				$this->output_buffer = json_encode($this->json); 

			else if ( is_string( $this->json ) )
				$this->output_buffer = $this->json; 

			else M()->warn( 'json no es ni un array ni un string, devolviendo nulo' );


		} else  if ( $this->view == 'rss' ) {

			$this->content_type( 'text/xml' );


		} else {

			M()->info("vista XML por default");

			// DEBUG: deberia cachear el xpotronix:document

			$tmp_basename = $this->get_hash().'.xml';

			$tmp_path = $this->ini['paths']['tmp'];
			$tmp_file = "$tmp_path/$tmp_basename";

			$tmp_file_uri = null;

			if ( isset( $this->ini['uri'] ) and ( isset( $this->ini['uri']['tmp'] ) ) ) {
				$tmp_uri = $this->ini['uri']['tmp'];
				$tmp_file_uri = "$tmp_uri/$tmp_basename";
			}

			if ( @$handle = fopen($tmp_file, "w") ) {

				fwrite($handle, $xdoc->asXML() );
				fclose($handle);

			} else { 
				M()->error( "No puedo crear el archivo temporal $tmp_file" ); 
			}

			$view_file = $this->get_template_file( $this->view. '.xsl' );
			M()->info( 'template seleccionado para la transformacion: '. $view_file );

			$transform_type = $transform_type ? $transform_type : $this->feat->transform;

			M()->info( "transform_type: $transform_type" );

			switch ( $transform_type ) {

				case 'fo':

					$xml_file = $tmp_file_uri ? $tmp_file_uri : $tmp_file;
					$this->fop_transform( $xml_file, $view_file, $params );
					$this->content_type( 'application/pdf' );

					if ( $handle = fopen( '/tmp/fop-out/test.pdf', 'r' ) ) {

						$this->output_buffer = fread( $handle, filesize(  '/tmp/fop-out/test.pdf' ) );
						fclose( $handle);
					}

					if ( $this->output_buffer === null ) {

						$this->content_type() or $this->content_type( 'text/html' );
						$this->output_buffer = $this->get_messages()->asXML(); 
					}

					break;

				case 'saxon':

					$xml_file = $tmp_file_uri ? $tmp_file_uri : $tmp_file;
					$this->output_buffer = $this->saxon_transform( $xml_file, $view_file, $params );

					if ( $this->output_buffer === null ) {

						$this->content_type() or $this->content_type( 'text/html' );
						$this->output_buffer = $this->get_messages()->asXML(); 

					}
					break;

				case 'bridge':

					$xml_file = $tmp_file_uri ? $tmp_file_uri : $tmp_file;
					$this->output_buffer = $this->saxon_bridge_transform( $xml_file, $view_file, $params );

					if ( $this->output_buffer === null ) {

						$this->content_type() or $this->content_type( 'text/html' );
						$this->output_buffer = $this->get_messages()->asXML(); 

					}
					break;

				case 'system':

					$saxon_command = "exec java -classpath ".$this->ini['java']['saxon_jar']." net.sf.saxon.Transform -novw ";


					if ( $params ) {
						$sparam = null;
						foreach( $params as $pr => $prv )
							$sparam += "$pr='$prv' ";
					}

					$retval = 0;
					ob_start();
					$param_template = ( $this->template ) ? "-it {$this->template}" : NULL;
					system( "$saxon_command $param_template $tmp_file $view_file 2>&1", $retval );
					$this->output_buffer = ob_get_clean();

					if ( $retval == 2 ) {

						$this->content_type( 'text/html' );
						M()->error( "Hubo errores en la transformacion SYSTEM" );
						$this->output_buffer = sprintf( "<h1>Error en la transformaci&oacute;n:</h1><pre>%s</pre>", $this->output_buffer );

					} else {

						$this->content_type( 'application/xhtml+xml' );
						$this->content_type( 'text/html' );
					}

					break;

				case 'php':

					$xsl = new DOMDocument;
					if ( ! $xsl->load( $view_file ) ) {

						M()->error( "Template de transformación no válido en transform/PHP" );
						break;

					}

					$proc = new XSLTProcessor;
					$proc->importStyleSheet($xsl);

					if ( is_array( $params ) ) 
						foreach( $params as $name => $value )
							$proc->setParameter( '', $name, $value );

					$domnode = dom_import_simplexml($xdoc);
					$dom = new DOMDocument();
					$domnode = $dom->importNode($domnode, true);
					$dom->appendChild($domnode);

					$this->output_buffer = $proc->transformToXML( $dom );

					break;

				case 'browser':

					$dom = new DOMDocument();

					// DEBUG: fijo para prueba
					$stylesheet = "http://localhost/patrocinio/templates/card.xsl";
					$pi = new DOMProcessingInstruction( "xsl-stylesheet", "type=\"text/xsl\" href=\"$stylesheet\"");
					$dom->appendChild($pi);

					$domnode = dom_import_simplexml($xdoc);
					$domnode = $dom->importNode($domnode, true);
					$dom->appendChild($domnode);
					
					$this->output_buffer = "<?xsl-stylesheet type=\"text/xsl\" href=\"$stylesheet\"?>\n".  $dom->saveXML($dom->documentElement);

					$this->content_type( 'text/xml' );

					break;


				default:
	
					M()->warn("tipo de transformacion $transform_type desconocida." );
			}

			if ( $this->config->app_cache_time and $cache )
				
				$this->cache->save( $this->output_buffer, $this->view_ID() );

		}

		if ( $tmp_file and $this->config->clean_xpdoc ) 
			@unlink( $tmp_file );

		M()->info('OK');	

		return $this->output_buffer;

	}/*}}}*/

	// debug

	function debug_obj_collection( $mesg_fn = 'info' ) {/*{{{*/

		foreach( $this->obj_collection as $class_name => $obj_collection ) {

			M()->$mesg_fn( "class_name $class_name count: ". count( $obj_collection ) );
		}
	}/*}}}*/

}

// vim600: fdm=marker sw=3 ts=8 ai:
?>
