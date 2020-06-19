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

class Soap {

	private $Username;
	private $Password;

	function __construct() {
	
		ini_set( 'soap.wsdl_cache_enabled', 0 );
	}

	public function LoginHeader( $params ){/*{{{*/

		global $xpdoc;

		$this->Username = $params->Username;
		$this->Password = $params->Password;

	}/*}}}*/

	function check_auth_status() {/*{{{*/

		global $xpdoc;

		if ( $this->Username === null ) {

			// no se ejecuto el metodo "LoginHeader": busco el Username y Password parseando el XML

			$post_data = file_get_contents("php://input");
			$header_xml = new \SimpleXMLElement( $post_data );

			if ( $ut = array_shift( $header_xml->xpath("//ns1:LoginHeader") ) ) {

				$this->Username = $ut->Username;
				$this->Password = $ut->Password;	
			}
		} 

		if ( ! $this->Username ) {
			throw new \SoapFault("Server","Por favor, especifique Username y Password como variables en el header del mensaje SOAP"); 
		}
	}/*}}}*/

	function login() {/*{{{*/

		global $xpdoc;

		if ( $xpdoc->user->_login( $this->Username, $this->Password ) )
			$xpdoc->session->set_user_session( $xpdoc->user->user_id );
		else 
			throw new \SoapFault("Server", "Acceso Denegado");

		M()->info( "user_id: " . $xpdoc->user->user_id ); 

		$xpdoc->roles = $xpdoc->perms->getUserRoles( $xpdoc->user->user_id );

		M()->info('roles para el usuario '. serialize( $xpdoc->roles ) );
		// M()->user('sesion '. $xpdoc->get_session()->asXML() );

	}/*}}}*/

	function process( $model, $action, $process, $data, $transform = null ) {/*{{{*/

		M()->set_messages_flags( MSG_ERROR | MSG_USER );

		global $xpdoc;
		$xpdoc = new Doc;

		$this->check_auth_status();

		$xpdoc->init();

		$this->login();


		if ( ! $data ) throw new \SoapFault("Server","No he recibido un XML para procesar"); 

		try {
			$xml = new \SimpleXMLElement( stripslashes( $data ) );

		} catch ( \Exception $e ) {

			throw new \SoapFault("Server", "el documento recibido no es un XML" );
			return;
		}

		$xpdoc->params_process();


		if ( $transform ) {

			M()->info( "transformando con planilla: $transform" );
			$xml = new \SimpleXMLElement( $xpdoc->transform( $transform, $xml, null, 'bridge', false ) );

		} else {
		
			M()->info( "sin plantilla de transformacion" );
		}

		M()->info( $xml->asXML() );

		$xpdoc->xml = $xml;

		// M()->info( $xpdoc->transform( $transform, $xml, null, 'php', false ) );

		$xpdoc->set_model( $model );
		$xpdoc->action = $action;
		$xpdoc->process = array( $process );

		if ( file_exists( 'common.php' ) )
		        include_once( 'common.php' );

		$xpdoc->load_model();
		$xpdoc->action_do();

		if ( M()->status() == 'ERR' ) {

			$m = array();

			// M()->debug( $xpdoc->get_messages()->asXML() );

			$messages = $xpdoc->get_messages();

			foreach ( $messages->messages->message as $message )

				$m[] = $message;

			throw new \SoapFault("Server", "\n". implode( ";\n", $m ). "\n" );

		}

		$xpdoc->close();

		// print $xml->asXML(); exit;


	}/*}}}*/

}

?>
