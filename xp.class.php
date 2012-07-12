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

require_once 'constants.inc.php';


class xp {

	private $name;
	private $type;

	// debug
	var $check_vars = true;
	var $debug = false;

	// permisos
	var $acl;
	var $perms;
	var $roles;

	// xml buffers
	var $xml_buff;

	// ui   
	var $__xml_data;

	// flow control
	var $process; 

	// node references:

	var $model;
	var $parent;
	var $parent_name;
	var $parent_node;

	var $ini;


	function __construct() {/*{{{*/

		$this->__xml_data = new SimpleXMLElement( '<data/>' );

	}/*}}}*/

	function __get( $var_name ) {/*{{{*/

		if ( $this->check_vars and $this->debug and !isset( $this->__xml_data->$var_name ) ) {

			echo "<p>__get(): La variable $var_name no esta definida en este objeto</p>";
			$this->debug_backtrace();
		}

		if ( ! $this->__xml_data ) /* DEBUG: no esta inicializado todavia el objeto? */
			return null;

		$tmp = $this->__xml_data->$var_name;

		$tmp_type = $tmp['type'];

		settype( $tmp, ($tmp_type ? (string) $tmp_type : 'string' ) ) ;

		return $tmp;

	}/*}}}*/ 

	function __set( $var_name, $var_value ) {/*{{{*/

		if ( is_object( $var_value ) or is_array( $var_value ) ) {

			echo "no se puede asignar un objeto o array a un elemento simple, para la propiedad $var_name";
			echo $this->debug_backtrace( $var_value );
			ob_flush();
			exit;
		}

		$this->__xml_data->$var_name = (string) $var_value;

		if ( !is_string( $var_value ) ) {

			$tmp =& $this->__xml_data->$var_name;
			$tmp['type'] = gettype( $var_value );
		}

		return $var_value;
	}/*}}}*/

	function dup() {/*{{{*/

		return unserialize(serialize($this));
	}/*}}}*/

	function load_ini() {/*{{{*/

		if ( file_exists( XPOTRONIX_INI_OVERRIDE ) )
			$this->ini = parse_ini_file( XPOTRONIX_INI_OVERRIDE, true );
		else if ( file_exists( XPOTRONIX_INI ) )
			$this->ini = parse_ini_file( XPOTRONIX_INI, true );
		else M()->faltal( "No encuentro el archivo de configuracion .ini de xpotronix\n" );

		// print_r( $this->config );

		return $this->ini;
	}/*}}}*/

	function load_globals() {/*{{{*/
		// funcion de prueba

		$this->_GET = $_GET;
		$this->_POST = $_POST;
		$this->_REQUEST = $_REQUEST;

	}/*}}}*/

	function set_perms( $perms, $hasAccess = true ) {/*{{{*/
		$perms = str_replace( " ", "", $perms );
		$perms = explode( ",", $perms );
		$ret = true;

		foreach( $perms as $perm )
			$this->acl[$perm] = $hasAccess;

		return $ret;
	}/*}}}*/

	function debug_backtrace( $data = NULL ) {/*{{{*/

		$debugb = debug_backtrace();
		array_shift( $debugb );

		echo '<pre>';

		foreach( $debugb as $trace ) {



			$class = ( isset( $trace['class'] ) ) ? $trace['class'] : 'N/A';
			echo "file: {$trace['file']}, line: {$trace['line']}, class: $class, function: {$trace['function']}\r";
		}

		if ( $data ) 
			if ( is_array( $data ) or is_object( $data ) )
				print_r( $data );
			else
				echo "Value: $data";
		else
			echo "NULL";

		echo '</pre>';
	}/*}}}*/

	function debug( $var = null, $type = 'undef' ) {/*{{{*/

		$origin = __FILE__ . ":". __LINE__;
		printf( "<debug type=\"%s\" origin=\"%s\">%s</debug>", $type, $origin, $var );


	}/*}}}*/

	function remove_xml_decl( $xml ) {/*{{{*/

		return preg_replace( "/<\?xml.*\?>/", "", $xml );

	}/*}}}*/

	function debug_obj_in_messages() {/*{{{*/

		// en xp

		$var_d = null;
		foreach( $this->data as $key => $data ) $var_d .= "$key = $data;";
		M()->info( 'processing obj (climbing up)', 10 , $obj->get_name(). ': '. $var_d  );	
	}/*}}}*/

	function get_username() {/*{{{*/

		global $xpdoc;

		if ( $xpdoc ) return $xpdoc->user->user_username;
		else return NULL;

	}/*}}}*/

	function xml_elem_attrs( $array ) {/*{{{*/
		$r = " ";
		foreach( $array as $attr => $value )
			$r .= "$attr=\"$value\" ";
		return $r;
	}/*}}}*/

	function saxon_transform( $xml_file, $xsl_file, $params = null ) {/*{{{*/

		M()->info("recibi parametros: $xml_file, $xsl_file ". serialize( $params ) );

		try {

		require_once 'lib/Java.inc';

		java_require( $this->ini['java']['saxon_jar'].";" );

		} catch (java_ConnectException $e) {

			M()->fatal( "No puedo iniciar la conexion con la maquina virtual Java. Mensaje: ". $e->getMessage() );
		}

		try {

			$oXmlSource = new java("javax.xml.transform.stream.StreamSource", $xml_file);
			$oXslSource = new java("javax.xml.transform.stream.StreamSource", $xsl_file);


			$oFeatureKeys = new JavaClass("net.sf.saxon.FeatureKeys");

			$oTransformerFactory = new java("net.sf.saxon.TransformerFactoryImpl");

			// $oTransformerFactory->setAttribute($oFeatureKeys->SCHEMA_VALIDATION, 4);

			$oTransformerFactory->setAttribute($oFeatureKeys->ALLOW_EXTERNAL_FUNCTIONS, true);

			$oTransFormer = $oTransformerFactory->newTransformer($oXslSource);

			$oResultStringWriter = new java("java.io.StringWriter");
			$oResultStream = new java("javax.xml.transform.stream.StreamResult", $oResultStringWriter);


			// carga los parametros (si los hay)

			if ( is_array( $params ) )
				foreach( $params as $key => $value )
					$oTransFormer->setParameter( $key, $value );


			$oTransFormer->transform($oXmlSource, $oResultStream );

			return java_cast($oResultStringWriter->toString(), "string");

		}

		catch(JavaException $e) {
			M()->warn( "Hubo mensajes en la tranformacion del archivo $xml_file con el template $xsl_file<br/> ". java_cast($e->getCause()->toString(), "string") );
			return null;
		}
	}/*}}}*/

        function get_hash() {/*{{{*/
        
                return md5(uniqid(rand(), true));
        
        }/*}}}*/

	function pp( $xml ) {/*{{{*/

		$doc = new DOMDocument('1.0');
		$doc->formatOutput = true;
		$domnode = dom_import_simplexml($xml);
		$domnode = $doc->importNode($domnode, true);
		$domnode = $doc->appendChild($domnode);
		echo $doc->saveXML();

	}/*}}}*/

}



// vim600: fdm=marker sw=3 ts=8 ai:

?>
