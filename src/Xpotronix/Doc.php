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

use Xpotronix\DataTypes;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;

class Doc extends Base {

	private $_xpid;

	/* data base manager */
	var $dbm;

	/* config private public */
	var $config;
	var $feat;

	/* session & user */
	var $session;
	var $user;

	/* request mgr */
	var $http;

	/* client stock */

	var $css = [];
	var $js = [];

	/* xml buffers */
	var $xmenu;
	var $xdoc;
	var $xstylesheet;

	var $output_buffer; // the output buffer
	// var $mode;

	var $application;
	var $model;
	var $metadata;
	var $datatypes;

	private $_content_type;
	private $data = []; 

	/* types matching for views */

	var $views;
	var $cache;
	var $cache_options;

	// var $module;
	var $action;
	var $query;
	private $view;
	var $search;
	var $req_object;
	var $param_schema;
	var $order;
	var $features;
	var $process;
	var $template;
	var $html;
	var $extra_param;
	var $pager = [];

	var $xml; // post buffer
	var $json;

	var $current_process;

	/* array de instancias de objetos del model */
	var $instances = [];

	/* array de instancias de todos los objetos creados */
	var $obj_collection = [];

	/* Permissions */

	var $perms;
	var $roles = [];

	/* parametros del URI */

	var $controller_vars = 'm;a;b;q;v;d;s;o;r;f;t;h;p;j;e;g;x;method;_dc';

	/* xpotronix ns */

	const NAMESPACE_URI = 'http://xpotronix.com/namespace/xpotronix/';
	const CLI = ( PHP_SAPI == 'cli' );
	const ROUTER_CONFIG_FILE = 'conf/routes.yaml';
	const TMP_DIR = '/tmp/xpotronix';

	/* symfony */

	private $router;
	private $fileLocator;

	/* construct */

	function  __construct( $config_file = null, $feat_file = null ) {/*{{{*/

		$xpid = $this->xpid( $this->get_hash() );

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

		$this->fileLocator = new FileLocator([getcwd(),__DIR__]);

		$this->http = new Http();

		$this->config->base_path = $this->http->base_path;

		$this->router = new Router(
	                new YamlFileLoader($this->fileLocator),
	                self::ROUTER_CONFIG_FILE,
                	['cache_dir' => getcwd().'/cache'],
	                $this->http );

		/* genera directorios cache */

		foreach( [ 'app', 'data', 'acl' ] as $subdir ) {

			$path = $this->get_cache_dir( $subdir );
		   	M()->debug( "cache path: $path" );
			file_exists( $path ) 
				or @mkdir( $path, 0777, true ) 
				or M()->error( "No pude crear el directorio $path: ". error_get_last()['message'] );
		}

		$this->cache_options = [ 

			'caching' => (bool) $this->config->app_cache_time,
			'cacheDir' => $this->get_cache_dir( 'app' ),
			'lifeTime' => $this->config->app_cache_time,
			'fileLocking' => true,
			'writeControl' => false,
			'readControl' => false,
			'memoryCaching' => true,
			'automaticSerialization' => false
		];

		$this->dbm = new DBm( $this->config );

		$this->feat->base_path or $this->feat->base_path = getcwd();

		M()->info("current working directory: {$this->feat->base_path}" );

		return $this;

	}/*}}}*/

	function set_module( $model = null ) {/*{{{*/

		/* el modulo del sistema tiene asociado un modelo */
		$msg = null; 

		( $this->module = $model and $msg = "via parametro" ) or 
		( $this->module and $msg = "previamente" ) or 
		( $this->module = $this->feat->default_module and $msg = "via feat->default_module" ) or 
		( $this->module = 'users' and $msg = "via fallback" );

		M()->info( "modulo $this->module asignado $msg" );

		$this->feat->module = $this->module;

	}/*}}}*/

	function load_config( $xml ) {/*{{{*/

		if ( $xml instanceof Config ) {

			$this->config = $xml;
		
		} else {

			$path = (( $t = $this->ini['paths']['config'] ) ? "$t/conf/{$this->application}": "conf/" );

			$file_path = "$path/config.xml";
			$file_path_local = "$path/config-local.xml";
			$file_path_final = "$path/config-final.xml";

			if ( file_exists( $file_path_local ) ) {

				M()->info( "$path, $file_path, $file_path_local, $file_path_final" );

				/* si tiene un archivo local de configuracion */

				$file_mtime = filemtime( $file_path );
				$file_mtime_local = filemtime( $file_path_local );
				$file_mtime_final = filemtime( $file_path_final );

				M()->info( "mtimes: $file_mtime, $file_mtime_local, $file_mtime_final" );

				if ( $file_mtime_final > max( $file_mtime, $file_mtime_local ) ) {

					/* si el archivo final es mas nuevo que el resto */

					M()->info( "cargando archivo $file_path_final" );
				
					$this->config = new Config( $file_path_final );
				
				} else {

					/* si el archivo final esta desactualizado (alguno de los dos es mas nuevo) regenera el final */

					M()->info( "regenerando archivo" );
				
					$base = simplexml_load_file( $file_path );
					$xslt = simplexml_load_file( "conf/config-override.xsl" );

					$ret = Config::transform( $base, $xslt, ['override_file_path' => $file_path_local ] );

					$final = simplexml_load_string( $ret );

					$this->config = new Config;
					$this->config->set_xml( $final );

					/* trata de grabarlo para no regenerarlo la proxima vez */

					if ( ! $final->asXML( $file_path_final ) ) {

						// var_dump( $this->config ); 
						
						M()->warn( "No pude generar el archivo final de configuracion en $file_path_final" );
					
					}


				}

			} else {
			
				$this->config = new Config( $file_path );

			}

		}

		M()->info('OK');

	}/*}}}*/

	function load_features( $xml = null ) {/*{{{*/

		if ( $xml )
			$this->feat = $xml;
		else {
			M()->info( $file =  'conf/feat.xml' );
			$this->feat = new Config( $file );
		}

		M()->info('OK');

	}/*}}}*/

	// init

	function init() {/*{{{*/

		$xpid = $this->xpid();

		M()->info( "***** Proceso xpotronix iniciado con xpid [$xpid] *****" );

		// DEBUG: algo tiene que devolver false?

		$this->dbm->init();

		if ( ! $this->load_session() )
			return false;

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

	function init_acl_db() {/*{{{*/

		if ( $gacl_class = $this->config->gacl_class ) {

			if ( ! $acl_db = $this->dbm->instance( 'default-acl' ) ) {

				M()->info( "no hay definido un default-acl, selecciono el default de la aplicacion" );
				$acl_db = $this->dbm->instance(); /* usa el default */
			
			}

			$params = [];

			$params['db'] = $acl_db;
			$params['caching'] = (bool) $this->config->gacl_cache_time;
			$params['cache_expire_time'] = $this->config->gacl_cache_time;
			$params['cache_dir'] = $this->get_cache_dir( 'acl' );
			( @$acl_db->tablePrefix ) and $params['db_table_prefix'] = $acl_db->tablePrefix;

			$this->perms = new $gacl_class( $params ); 

		} else M()->warn( 'no hay definida una clase de permisos del sistema, no hay seguridad alguna' );

	}/*}}}*/

	function load_session() {/*{{{*/

		M()->info('START');

		if ( ! $this->feat->class_session ) {

			M()->warn('no hay una clase definida para el manejo de sesiones');
			return true;
		}


		$t = $this->config->trusted_host and M()->warn( "trusted_host (deprecated): $t" );

		/* trusted_host_ip */

		$trusted_host_ip = [];

		if ( $this->config->trusted_host_ip ) {

			$trusted_host_ip = explode( ';', $this->config->trusted_host_ip );
			M()->info( "trusted_host_ip: ". json_encode( $trusted_host_ip ) );
		}


		/* trusted_host_name */

		$trusted_host_name = [];

		if ( $this->config->trusted_host_name ) {

			$trusted_host_name = explode( ';', $this->config->trusted_host_name );
			M()->info( "trusted_host_name: $trusted_host_name" );
		}

		

		$this->session = $this->instance( $this->feat->class_session );
		$this->session->set_flag('set_global_search', false);
		$this->user = $this->instance( $this->feat->class_user );
		$this->user->set_flag('set_global_search', false);

		$sid = $this->session->start( $this->application );

		try {

		$this->session->read( $sid ); 

		} catch (Throwable $t) {

			M()->error( "No puedo leer la sesion" );
			return false;
		}

		// print var_dump( $this->session->user_id ); exit;

		if ( self::CLI ) {

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
				M()->info( "la sesion existe [$sid]" );
		}

		$this->user->set_flag( 'main_sql', false );	
		$this->user->load( $this->session->user_id );
		$this->user->set_flag( 'main_sql', true );

		$this->user->attr( '_anon' )->set( 'virtual', true )->set( 'type' , 'int' )->set( 'value', $this->user->user_id == $this->config->anonymous_user_id );

		$this->user->set_flag('set_global_search',true);
		$this->session->set_flag('set_global_search',true);

		M()->info( 'Cargada Sesion con Usuario ID '. $this->user->user_id );
		M()->info('OK');

		return true;

	}/*}}}*/

	function current_session() {/*{{{*/

		return $this->session->session_id;

	}/*}}}*/

	function load_acl() {/*{{{*/

		$this->init_acl_db();

		if ( is_object( $this->perms ) ) {

			$this->perms->setUserId( $this->user->user_id );

			$this->roles = $this->perms->getUserRoles();

			M()->info('roles para el usuario '. json_encode( $this->roles ) );	
		}

		M()->info( 'OK' );

	}/*}}}*/

	function params_process() {/*{{{*/

		if ( ! $this->http ) {

			M()->warn( 'no esta inicializado el Http para este documento, no puedo procesar los parametros' );
			return;
		}


		if ( ! self::CLI ) {

			$route_param = null;

			try {
				/* Find the current route */
				$route_param = $this->router->match( $this->http->getPathInfo() );

			   	// print_r( $this->http->getPathInfo() );
			   	M()->info( $this->http->getPathInfo() );
				M()->info( "route_param: ". json_encode( $route_param ) );

			} catch ( FileLocatorFileNotFoundException $e ) {
			
				M()->error( $e->getMessage() );

			} catch ( ResourceNotFoundException $e ) {

				/* DEBUG: por ahora no hace nada hasta implementar rutas */
				M()->info( $e->getMessage() );
			}

			/* ejecuta la ruta */

			// echo '<pre>'; print_r( $route_param );

			if ( is_array( $route_param ) ) {

				list( $class, $method ) = explode( '::', $route_param['controller'] );
				$ret = $class::$method( $route_param );
				// print_r( $ret ); exit;
			
			} 

		}

		$this->http->m and $this->set_module( $this->http->m );

		$this->action         = $this->http->a;

		if ( $this->http->p ) {

			$this->action  = 'process';
			$this->process = explode( ';', $this->http->p );
		}

		$this->query          = $this->http->q;
		// $this->view           = $this->http->v;
		// $this->data	      = $this->http->d;

		$this->search = ( is_array( $this->http->s ) ) ?
			$this->http->s:
			$this->search = [];

		$this->order          = $this->http->o;
		( $this->req_object     = $this->http->r ) or $this->req_object = $this->http->m;
		$this->features       = $this->http->f;
		$this->template       = $this->http->t;
		$this->html           = $this->http->h;
		$this->json	      = json_decode( $this->http->j );
		$this->param_schema   = $this->http->b;
		$this->extra_param    = $this->http->e;
		$this->pager	      = $this->http->g;

		// echo var_dump( $_REQUEST );
		// exit;

		/*
		M()->info("module:$this->module");
		M()->info("req_object:$this->req_object");
		*/

		try {
			if ( $this->http->x )
				$this->xml  = new \SimpleXMLElement( stripslashes( $this->http->x ));
		} catch ( \Exception $e ) {
			M()->error( "el fragmento XML recibido en el parametro x no es valido, no se puede procesar: ". $e->getMessage() );
		}

		if ( $this->features )
			foreach ( $this->features as $feat => $value )
				$this->feat->$feat = $value;

		if ( $this->feat->debug_xml ) 
			M()->debug( 'xml: '.$this->http->x );

		/* parametros de la consulta */

		if ( $this->param_schema == 'ext4' ) {

			$params = new Params();
			M()->info( "params: ". json_encode( $params->get() ) );
			$params->process();
			// print_r( $params->get() );

		} else {

			/* search sobre las GET vars solamente */

			foreach ( $this->http->get_get_vars() as $key ) {

				if ( strstr( $this->controller_vars, $key. ';' ) ) 
					continue; 

				$this->search[$this->req_object][$key] = $this->http->$key;
			}

			// print_r( $this->search ); 

			/* query_field: sobre que campo alias tiene que buscar (ej.: _label) */

			if ( ( $query = $this->http->query ) and ( $query_field = $this->feat->query_field ) ) {

				$this->search[$this->req_object][$query_field] = $query;
				M()->info( "parametro query buscando el valor \"$query\" sobre [$query_field]" );
			}

			/* paginado */

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

			/* version de paginado para todos los objetos del modelo */

			if ( $this->http->gp ) {

				M()->info( "gp con valor: ". json_encode( $gp ) );

				foreach( $this->http->gp as $obj_name => $param ) {

					M()->info( "obj: $obj_name" );

					/* DEBUG: chequear si el nombre del objeto existe */

					$op = [];
					
					foreach( $param as $key => $value ) {

						M()->info( "param: $key = $value" );

						if ( $key == 'page' ) {

							$value = (int) $value;
							$op['cp'] = $value;

						}  else if ( $key == 'start' ) {

							$op['start'] = (int) $value;

						} else if ( $key == 'limit' and ( (int) $value ) > 0 ) {

							$value = (int) $value;

							/* equivalencia start/limit vs page_row/current_page */

							$op['pr'] = $value;
							$op['cp'] = (int) ceil( $op['start'] / $value ) + 1;

						} else if ( $key == 'sort' ) {

							$this->order[$obj_name][$g['sort']]=$g['dir'];

						} else {

							M()->warn( "pametro desconocido $key con valor $value" );

						}
					}
				}

				$this->pager[$obj_name] = $op;

				/*
				if ( $obj_name == 'imagen' ) {
					echo '<pre>';
					print_r( $this->pager );
					exit;
				}
				*/
			}
		}

		//print_r( $this->search );
		M()->info( 'OK' );
	}/*}}}*/

	function xpid( $hash = null ) {/*{{{*/

		( $hash === null ) or $this->_xpid = $hash;

		return $this->_xpid;

	}/*}}}*/

	function get_cache_dir( $suffix = null ) {/*{{{*/

		$base_path = ( $t = (string) $this->config->cache_dir ) ? 
	   	   	$t : self::TMP_DIR;

		$suffix and $suffix = "$suffix/";

		$ret = "$base_path/{$this->config->application}/$suffix";
	   	M()->info( "path $ret" );
		return $ret;

	}/*}}}*/

	function get_log_dir( $suffix = null ) {/*{{{*/

		$suffix and $suffix = "$suffix/";

		return "{$this->config->log_dir}/{$this->config->application}/$suffix";

	}/*}}}*/

	function set_stylesheet( $uri ) {/*{{{*/

		return $this->xstylesheet = $uri;
	}/*}}}*/

	function get_stylesheet( $uri ) {/*{{{*/
	
		return $this->xstylesheet;
	}/*}}}*/

	// head

	function recaptcha_verify() {/*{{{*/

	/* DEBUG: aca hay que ser mas estricto xq asi no */

	 if ( ! $this->http->token ) {

		 M()->error('no hay token de reCapcha, no se puede validar');
		 return null;
	 }

	 $version = $this->config->recaptcha_version;
	 $url = $this->config->recaptcha_site_verify;
	 $secret = $this->config->recaptcha_private_key;

	 if ( ! ( $url and $secret ) ) {

	    M()->error( 'no esta definido reCaptcha en config.xml' );
	    return null;
	 }

	 if ( ! $this->http->token ) {

	    M()->error( 'no se recibió la variable token, no se puede verificar' );
	    return null;
	 }

	 $token = $this->http->token;

	 M()->info( "reCaptcha version: $version, secret: $secret, url: $url, token: $token" );

	 $content = http_build_query([ 'secret' => $secret, 'response' => $token ]);

	 $options = 
	    [ 'http' => [ 
		 'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		 'method'  => 'POST',
		 'content' => $content ] ];

	 if ( $t = $this->config->proxy ) {
		 $options['http']['proxy'] = "tcp://{$t}/";
		 M()->info( "usando proxy en $t" );
	}

	$context  = stream_context_create($options);
	$response = file_get_contents($url, false, $context);

	if ( $response === false ) {

		/* DEBUG: pruebo sin proxy por las dudas */

		if ( isset( $options['http']['proxy'] ) ) {

			M()->info( "falla la conexion proxy via ". $options['http']['proxy'] ." haciendo fallback con conexion directa" );
		
			unset( $options['http']['proxy'] );

			$context  = stream_context_create($options);
			$response = file_get_contents($url, false, $context);
		}
	
	}

	if ( $response === false ) {

		M()->error( 'no se pudo conectar con el servidor de reCaptcha' );
		return null;

	} else {
	
		$responseKeys = json_decode($response,true);
		$success = $responseKeys['success'];

		M()->info( "reCaptcha response: $success" );
		return $success;
	}

   }/*}}}*/

	function headers_do() {/*{{{*/

		$this->header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); // Date in the past
		$this->header( "Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" ); // always modified
		$this->header( "Cache-Control: no-cache, must-revalidate, no-store, post-check=0, pre-check=0" ); // HTTP/1.1
		$this->header( "Pragma: no-cache" ); // HTTP/1.0
		// $this->header( "Content-type: text/html;charset=UTF-8" );

	}/*}}}*/

	function header( $directive, $replace = true ) {/*{{{*/

		M()->debug( $directive );
		headers_sent() or header( $directive, $replace );

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

		isset( $this->module ) or $this->set_module();
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

	function instance( $class_name, $model = null ) {/*{{{*/

		$full_class_name = "\\App\\$class_name";
		$class_file = "modules/$class_name.php";

		try {

			if ( class_exists( $full_class_name ) or file_exists( $class_file ) ) {
			
				$instance = new $full_class_name( $model );
				return $instance;

			} else {

				M()->info("no existe el archivo $class_file" );

				/* hack para cerar clases on-the-fly */

				$extends = "\Xpotronix\DataObject";

				if ( isset( $model ) ) {

					if ( $t = (string) $model->obj['instance'] )

						$extends = $t;
				}

				M()->info("extiende la clase $extends" );

				eval("namespace App; class $class_name extends $extends {}; \$instance = new $class_name( $model );");

				return $instance;
			
			}

			M()->user( "creando la clase $full_class_name" );
		
		} catch ( \Throwable $e ) {

			$mess = $e->getMessage();
			$line = $e->getLine();
			$file = $e->getFile();

			M()->error( "No pude crear la clase $class_name, motivo: $mess, archivo: $file, linea: $line" );
			return null;
		}

	}	/*}}}*/

	function get_instance( $obj_name = null ) {/*{{{*/

		if ( count( $this->instances ) < 1 ) {

			M()->error( 'No hay instancias definidas' );
			return null;
		} 

		if ( ! $obj_name ) {

			M()->info( "devolviendo la primera instancia del modulo" );
			return reset( $this->instances );
		} 

		if ( array_key_exists( $obj_name, $this->obj_collection ) ) {

			M()->info( "devolviendo una instacia existente" );
			return reset( $this->obj_collection[$obj_name] );

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

		$this->xmenu = new Menu( 'conf/menu.xml' );

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
		catch ( \Exception $e ) {
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

		$this->css[] = [ 'href' => $href, 'media' => $media ];

	}/*}}}*/

	function add_js( $href, $extra = NULL ) {/*{{{*/

		$this->js[] = [ 'href' => $href, 'extra' => $extra ];

	}/*}}}*/

	// procesos

	function process() {/*{{{*/


		// default view
		$this->get_view() or $this->set_view( ( self::CLI ) ? 'text' : 'xml' );

		$obj = $this->get_instance( $this->module );
		$instances = $this->instances;
		$user = $this->user;
		$xml = $this->xml;
		$processes = $this->process;

		$this->current_process = new DataProcess( $obj, $instances, $user, $processes, $xml ) and $this->current_process->process();

		if ( ! $this->view ) 
			$this->set_view( 'error' );

		return isset($this->xdoc) ? $this->xdoc : $this->get_messages();

	}/*}}}*/

	// roles

	function has_role() {/*{{{*/

		M()->user( "has_role: ". json_encode( func_get_args() ) );

		return $this->perms->has_role( func_get_args() );

	}/*}}}*/

	function serialize_roles() {/*{{{*/

		$roles = new \SimpleXMLElement( "<roles/>" );

		foreach( $this->roles as $role ) {

			$role_xml = $roles->addChild( "role" );
			$role_xml['id'] = $role;
			$role_xml['name'] = $role;
			$role_xml['value'] = $role;
		}

		return $roles;
	}/*}}}*/

	// get_*

	function get_session() {/*{{{*/

		M()->info();

		$d = new \DOMDocument;
		$x = simplexml_import_dom( $d->createElementNs(self::NAMESPACE_URI, "xpotronix:session") );

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

		/* simplexml_append( $x, $this->get_menu() ); */

		return $x;

	}/*}}}*/

	function get_menu() {/*{{{*/


		M()->info();

		$this->xmenu or $this->load_menu();

		return $this->xmenu->get_xml();

		/* menu sin namespace 

		$d = new \DOMDocument;
		$x = simplexml_import_dom( $d->createElementNs(self::NAMESPACE_URI, "menu") );

		simplexml_append( $x, $this->xmenu->get_xml() );

		return $x;
		*/

	}/*}}}*/

	function get_model() {/*{{{*/

		M()->info();

		$d = new \DOMDocument;
		$x = simplexml_import_dom( $d->createElementNs(self::NAMESPACE_URI, "xpotronix:model") );

		simplexml_append($x, $this->model);

		return $x;

	}/*}}}*/

	function get_metadata() {/*{{{*/

		M()->info();

		$d = new \DOMDocument;
		$x = simplexml_import_dom( $d->createElementNs(self::NAMESPACE_URI, "xpotronix:metadata") );

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

			$d = new \DOMDocument;
			$x = simplexml_import_dom( $d->createElementNs(self::NAMESPACE_URI, "xpotronix:dataset") );

			simplexml_append($x, $dataset);

			return $x;
		
		}

		return $dataset;

	}/*}}}*/

	function get_messages( $all = false ) {/*{{{*/

		M()->info();

		$d = new \DOMDocument;
		$x = simplexml_import_dom( $d->createElementNs(self::NAMESPACE_URI, "xpotronix:messages") );

		simplexml_append( $x, M()->serialize( $all ) );
		simplexml_append( $x, M()->get_response() );
		simplexml_append( $x, M()->get_changes() );

		if ( $this->feat->debug_xml && $this->xml ) 
			simplexml_append( $x, $this->xml );

		return $x;

	}/*}}}*/

	function get_document() {/*{{{*/

		M()->info();

		$d = new \DOMDocument;
		$x = simplexml_import_dom( $d->createElementNs(self::NAMESPACE_URI, "xpotronix:document") );

		if ( $this->xstylesheet ) {



		   	M()->info( "xml-stylesheet href={$this->xstylesheet}" );
			$x->addProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="'. $this->xstylesheet.'"'); 
		
		}

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
			$obj->change_attr( 'display', 'ignore', $do );

		foreach( $obj->get_primary_key_array() as $key ) 
			$obj->get_attr( $key )->display = '';

		// $obj->debug_object(); exit;

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

			/* process y abm */

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

			/* serializacion de datos */

			case 'data':
				$this->set_xdoc( $this->get_dataset( Serialize::DS_ANY ) );
				break;

			case 'data_r':
				$this->set_xdoc( $this->get_dataset( Serialize::DS_RECURSIVE ) );
				break;

			case 'dataset':
				$this->set_xdoc( $this->get_dataset( Serialize::DS_NORMALIZED ) );
				break;

			case 'dataset_r':
				$this->set_xdoc( $this->get_dataset( Serialize::DS_RECURSIVE | Serialize::DS_NORMALIZED ) );
				break;

			/* blanks */

			case 'blank':
				$this->set_xdoc( $this->get_dataset( Serialize::DS_BLANK | Serialize::DS_DEFAULTS ) );
				break;

			case 'datab':
				$this->set_xdoc( $this->get_dataset( Serialize::DS_BLANK | Serialize::DS_DEFAULTS | Serialize::DS_ANY ) );
				break;

			case 'blank_r':
			case 'datab_r':
				$this->set_xdoc( $this->get_dataset( Serialize::DS_BLANK | Serialize::DS_DEFAULTS | Serialize::DS_RECURSIVE ) );
				break;

			case 'blank_dataset':
			case 'databset':
				$this->set_xdoc( $this->get_dataset( Serialize::DS_BLANK | Serialize::DS_DEFAULTS | Serialize::DS_NORMALIZED ) );
				break;

			case 'blank_dataset_r':
			case 'databset_r':
				$this->set_xdoc( $this->get_dataset( Serialize::DS_BLANK | Serialize::DS_DEFAULTS | Serialize::DS_RECURSIVE | Serialize::DS_NORMALIZED ) );
				break;

			/* json y csv */

			case 'json':
				$this->json = $this->get_json(); 
				$this->set_view( 'json' );
				break;
		
			case 'csv':
				$this->output_buffer = $this->get_csv(); 
				$this->set_view( 'csv' );
				break;


			/* menu */

			case 'menu':

				$this->set_xdoc( $this->get_menu() );
				break;

				if ( ( $audit = $this->instance('audit') ) ) $audit->record();
				break;

			/* secciones xpotronix document */

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

			/* user */

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

			/* xpotronize */

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
				$x = new Xpotronize;

				$x->init( [ 'xpotronize', $this->application ]  );
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

		return implode( ':', [ 
			$this->user->user_username,
			$this->session->session_id,
			$this->config->application,
			$this->module,
			$this->req_object,
			$this->view 
		]);

	}/*}}}*/

	function transform( string $view, \SimpleXMLElement $xdoc = null, array $params = null, $transform_type = null, bool $cache = true ) {/*{{{*/

		$tmp_file = null;

		/* null view */

		$view or $view = 'xml';

		M()->info( "Transform VIEW: $view" );

		if ( $view == 'csv' or $view == 'none' ) return;

		/* cache */

		if ( $this->config->app_cache_time and $cache ) {

			if ( ! is_object( $this->cache ) ) 

				if ( is_object( $this->cache = new Cache( $this->cache_options ) ) )
					M()->info( 'Cache habilitada par la aplicacion' );
				else
					M()->info( 'No se pudo habilitar la cache para la aplicacion' );
				
			if ( $this->output_buffer = $this->cache->get( $this->view_ID() ) ) {

				M()->info( 'pagina de cache encontrada: '. $this->view_ID() );

				return $this->output_buffer;

				}

			else M()->info( 'pagina de cache no encontrada: '. $this->view_ID() );

		} else M()->info( 'Cache deshabilitada para la aplicacion' );


		/* xdoc */

		if ( is_object( $xdoc ) ) {

			M()->info( 'recibo un xdoc via parametro' );

		} else if ( is_object( $this->xdoc ) ) {

			$xdoc = $this->xdoc;
			M()->info( 'asigno el xdoc de xpdoc' );

		} else {

			$xdoc = $this->get_document();
			M()->info( 'no hay xpdoc, hago uno' );
		}


		/* view */

		if ( $this->html ) {

			//$filename = "static/{$this->html}.xhtml";
			$filename = "static/{$this->html}.html";
			$handle = fopen( $filename, "r" );

			$this->content_type( 'text/html' );
			$this->output_buffer = fread ( $handle, filesize ($filename) );

			fclose( $handle );

		} else if ( $view == 'xml' ) {

			$this->content_type( 'text/xml' );
			$this->output_buffer = $xdoc->asXML(); 

		} else if ( $view == 'json' ) {

			$this->content_type( 'text/x-json' );

			if ( is_array( $this->json ) ) 
				$this->output_buffer = json_encode($this->json); 

			else if ( is_string( $this->json ) )
				$this->output_buffer = $this->json; 

			else M()->warn( 'json no es ni un array ni un string, devolviendo nulo' );


		} else  if ( $view == 'rss' ) {

			$this->content_type( 'text/xml' );


		} else {

			M()->info("vista XML por default");

			// DEBUG: deberia cachear el xpotronix:document

			/* filenames */

			$tmp_filename = $this->xpid();
			$tmp_basename = "$tmp_filename.xml";

			$tmp_path = $this->ini['paths']['tmp'];
			$tmp_file = "$tmp_path/$tmp_basename";

			$tmp_file_uri = null;

			if ( isset( $this->ini['uri'] ) and ( isset( $this->ini['uri']['tmp'] ) ) ) {
				$tmp_uri = $this->ini['uri']['tmp'];
				$tmp_file_uri = "$tmp_uri/$tmp_basename";
			}


			/* xml tmp file */

			if ( @$handle = fopen($tmp_file, "w") ) {

				fwrite($handle, $xdoc->asXML() );
				fclose($handle);

			} else { 
				M()->error( "No puedo crear el archivo temporal $tmp_file" ); 
			}

			$view_file = $this->get_template_file( $view. '.xsl' );
			M()->info( 'template seleccionado para la transformacion: '. $view_file );

			/* transform */

			$transform_type = $transform_type ? $transform_type : $this->feat->transform;

			M()->info( "transform_type: $transform_type" );

			switch ( $transform_type ) {

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
					$msgs = ob_get_clean();

					$msgs and M()->warn( $mgsg );

					if ( $retval == 2 ) {

						$this->content_type( 'text/html' );
						M()->error( "Hubo errores en la transformacion SYSTEM" );
						$this->output_buffer = sprintf( "<h1>Error en la transformaci&oacute;n:</h1><pre>%s</pre>", $msgs );

					} else {

						$this->content_type( 'application/xhtml+xml' );
						$this->content_type( 'text/html' );
					}

					break;

				case 'fo':
				case 'fop':

					$tmp_pdf_file = "$tmp_path/$tmp_filename.pdf";

					$xml_file = $tmp_file_uri ? $tmp_file_uri : $tmp_file;

					M()->debug( "xml_file: $xml_file" );
					$ret = $this->fop_transform( $xml_file, $view_file, $params, $tmp_path, "$tmp_filename.pdf" );


					/* 
					$this->content_type( 'application/pdf' );
					$this->output_buffer = $ret;

					 */
					if ( $handle = fopen( $tmp_pdf_file, 'r' ) ) {

						$this->content_type( 'application/pdf' );
						$this->output_buffer = fread( $handle, filesize( $tmp_pdf_file ) );
						fclose( $handle );
						unlink( $tmp_pdf_file );

					} else {

						M()->error("No pude abrir el archivo $tmp_pdf_file");	
						$this->output_buffer = $this->get_messages()->asXML(); 
					
					}


					if ( $this->output_buffer === null ) {

						$this->content_type() or $this->content_type( 'text/html' );
						$this->output_buffer = $this->get_messages()->asXML(); 
					}

					break;

				case 'system_fop':

					$tmp_pdf_file = "$tmp_path/$tmp_filename.pdf";

					$system_command = "/usr/bin/fop";

					M()->info( "tmp_pdf_file: $tmp_pdf_file" );

					if ( $params ) {
						$sparam = null;
						foreach( $params as $pr => $prv )
							$sparam += "$pr='$prv' ";
					}

					$retval = 0;
					ob_start();
					$param_template = ( $this->template ) ? "-it {$this->template}" : NULL;
					system( "$system_command -xml $tmp_file -xsl $view_file -pdf $tmp_pdf_file 2>&1", $retval );
					$msgs = ob_get_clean();

					$msgs and M()->warn( $msgs );

					if ( $retval == 2 ) {

						$this->content_type( 'text/html' );
						M()->error( "Hubo errores en la transformacion SYSTEM" );
						$this->output_buffer = sprintf( "<h1>Error en la transformaci&oacute;n:</h1><pre>%s</pre>", $msgs );

					} else {

						if ( $handle = fopen( $tmp_pdf_file, 'r' ) ) {

							$this->content_type( 'application/pdf' );
							$this->output_buffer = fread( $handle, filesize( $tmp_pdf_file ) );
							fclose( $handle);
							unlink( $tmp_pdf_file );
						}

						if ( $this->output_buffer === null ) {

							$this->content_type() or $this->content_type( 'text/html' );
							$this->output_buffer = $this->get_messages()->asXML(); 
						}
					}

					break;


				case 'php':

					$xsl = new \DOMDocument;
					$xsl->resolveExternals = true;
					$xsl->substituteEntities = true;
					if ( ! $xsl->load( $view_file ) ) {

						M()->error( "Template de transformación [$view_file] no válido en transform/PHP" );
						break;

					}

					$proc = new \XSLTProcessor;
					$proc->importStyleSheet($xsl);

					if ( is_array( $params ) ) 
						foreach( $params as $name => $value )
							$proc->setParameter( '', $name, $value );

					$domnode = dom_import_simplexml($xdoc);
					$dom = new \DOMDocument();
					$domnode = $dom->importNode($domnode, true);
					$dom->appendChild($domnode);

					$this->output_buffer = $proc->transformToXML( $dom );

					break;

				case 'browser':

					$dom = new \DOMDocument();

					// DEBUG: fijo para prueba
					$stylesheet = "http://localhost/patrocinio/templates/card.xsl";
					$pi = new \DOMProcessingInstruction( "xsl-stylesheet", "type=\"text/xsl\" href=\"$stylesheet\"");
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

	function remove_obj_collection( $class_name, $id_hash ) {/*{{{*/

		M()->info( "removing class_name: $class_name, id_hash: $id_hash" );
		@$this->obj_collection[$class_name][$id_hash] = null;

	}/*}}}*/

}

?>
