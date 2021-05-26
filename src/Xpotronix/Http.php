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

use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

use \samejack\PHP\ArgvParser;

class Http extends RequestContext {
	
	var $var = [];
	var $server_var;
	var $http_host;
	var $request_scheme;
	var $request_uri;
	var $context_prefix;
	var $remote_addr;
	var $remote_host;

	/** nombre de la variable que contiene el url encriptado, que se usa en GET */
	
	var $new_query; /* ultimo query armado con build */
	var $key;

	const CLI = ( PHP_SAPI == 'cli' );

	function __construct( $params = null ) {/*{{{*/

		global $xpdoc;

		if ( self::CLI ) {

			global $argv;
			
			M()->info("ejecucion via shell de comandos");

			$argvParser = new ArgvParser();
			$params = $argvParser->parseConfigs($argv);

			array_shift( $params );
	
			if ( isset( $params['path'] ) ) {

				@$xpdoc->config->base_path = $params['path'];
				unset( $params['path'] );
			}

			$arr_params = [];

			foreach( $params as $key => $value )

				$arr_params[] = "$key=$value";

			parse_str( implode( "&", $arr_params ), $this->var );

		} else if ( is_array( $params ) ) {

			M()->info("Recibidos los parametros en la creacion de la clase");
			$this->var = $params;

		} else {


			M()->info("Recibidos los parametros via la web");

			$this->fromRequest(Request::createFromGlobals());

			foreach ( $_REQUEST as $key => $value ) {

				$key = str_replace( '@', '', $key );	
				$key = str_replace( 'amp;', '', $key );

				$this->var[$key] = $value;
			}

			$this->server_var 	= $_SERVER;
			$this->method 		= $_SERVER['REQUEST_METHOD'];
			$this->request_uri 	= $_SERVER['REQUEST_URI'];
			isset( $_SERVER['HTTP_HOST'] ) and 
				$this->http_host	= $_SERVER['HTTP_HOST'];
			$this->context_prefix 	= $_SERVER['CONTEXT_PREFIX'];
			$this->request_scheme	= $_SERVER['REQUEST_SCHEME'];

			if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
				$this->remote_addr = $_SERVER['HTTP_X_FORWARDED_FOR'];
			else
				$this->remote_addr = $_SERVER['REMOTE_ADDR'];


			if (strstr($this->remote_addr, ', ')) {
				$ips = explode(', ', $this->remote_addr);
				$ip = $ips[0];

			} else	$ip = $this->remote_addr;

			// $this->remote_host = gethostbyaddr( $ip );
		}

		return $this;

	}/*}}}*/

	function get_vars() {/*{{{*/

		return array_keys( $this->var );


	}/*}}}*/

	function get_post_vars() {/*{{{*/

		return array_keys( $_POST );

	}/*}}}*/

	function get_get_vars() {/*{{{*/

		return array_keys( $_GET );

	}/*}}}*/

	function local_host_name() {/*{{{*/

		return gethostname();

	}/*}}}*/

	function remote_host_name( $ip = null ) {/*{{{*/

		if ( ! function_exists( 'gethostbyaddr' ) ) 
			return null;
		else if ( $ip ) 
			return gethostbyaddr( $ip );
		else 
			return gethostbyaddr( $this->remote_addr );

	}/*}}}*/

	function __get( $var_name ) {/*{{{*/

		if ( isset( $this->var[ $var_name ] ) )  
			return $this->var[ $var_name ] ;
		else
			return NULL;
	}/*}}}*/

	function __set( $var_name, $var_value ) {/*{{{*/

		return $this->var[ $var_name ] = $var_value ;

	}/*}}}*/

	function set_array( $var_name, $var_value ) {/*{{{*/

	/* get/set no funcionan con arrays */

		return $this->var[ $var_name ] = $var_value;

	}/*}}}*/

	function get_xml() {/*{{{*/
		
		return array2xml( 'var', $this->var );

	}/*}}}*/

	function get_SERVER_xml() {/*{{{*/

		return array2xml( 'server', $this->server_var );

	}/*}}}*/

}

// vim600: fdm=marker sw=3 ts=8 ai:

?>
