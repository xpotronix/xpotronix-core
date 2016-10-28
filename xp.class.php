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

		$debugb = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
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

	function debug_console( $recursive = false ) {/*{{{*/
		print_r( object_to_array( $this, $recursive ) );
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

	function saxon_transform( $xml_file, $xsl_file, $params = null, $validation = false ) {/*{{{*/

		M()->info("recibi parametros: $xml_file, $xsl_file ". serialize( $params ) );

		/* procesador */

		$saxonProc = new Saxon\SaxonProcessor();
		$proc = $saxonProc->newXsltProcessor();
		$version = $saxonProc->version();

		/* config Properties */

		$validation and $proc->setConfigurationProperty( 'http://saxon.sf.net/feature/schema-validation', 'preserve' ); // preserve: 4

		// $proc->setConfigurationProperty( 'http://saxon.sf.net/feature/allow-external-functions', true );

		$proc->setSourceFromFile( $xml_file );
		M()->debug( "xml_file: $xml_file" );

		$proc->compileFromFile( $xsl_file );
		M()->debug( "xsl_file: $xsl_file" );

		if ( is_array( $params ) ) {

			foreach( $params as $name => $value ) {

				M()->debug( "setParameter $name: $value" );
				$proc->setParameter( $name, $saxonProc->createAtomicValue($value) );
			}
		}

		M()->debug( "transformacion con Saxon/C" );

                $result = $proc->transformToString();
                
                if( $result == NULL ) {

			$errCount = $proc->getExceptionCount();

			if( $errCount > 0 ) { 

				for( $i = 0; $i < $errCount; $i++ ) {

					$errCode = $proc->getErrorCode(intval($i));
					$errMessage = $proc->getErrorMessage(intval($i));
					M()->warn( "Hubo mensajes en la tranformacion del archivo $xml_file con el template $xsl_file<br/> Cod: $errCode, Mensaje: $errMessage" );

   				}

				$proc->exceptionClear();	

			} else {

				M()->error( 'Hubo mensajes en la transformacion pero no han sido reportados por el procesador' );
			}
		}

            	$proc->clearParameters();
		$proc->clearProperties();

                return $result;

	}/*}}}*/

	function saxon_bridge_transform( $xml_file, $xsl_file, $params = null ) {/*{{{*/

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

	function fop_transform( $xml_file, $xsl_file, $params = null ) {/*{{{*/

		M()->user("recibi parametros: $xml_file, $xsl_file ". serialize( $params ) );

		try {
			require_once "/usr/share/xpotronix/lib/Java.inc";

			$jars = array( 
				"/usr/share/java/fop.jar",
				"/usr/share/java/xmlgraphics-commons.jar",
				"/usr/share/java/avalon-framework.jar",
				"/usr/share/java/commons-logging.jar",
				"/usr/share/java/commons-io.jar" );

			java_require( implode( ';', $jars ) );

			// java_require( "/usr/share/java/" );

			$oXmlSource = new java("javax.xml.transform.stream.StreamSource", $xml_file);
			$oXslSource = new java("javax.xml.transform.stream.StreamSource", $xsl_file);

			$oResultStringWriter = new java("java.io.StringWriter");
			$oResultStream = new java("javax.xml.transform.stream.StreamResult", $oResultStringWriter);


	            	// configure fopFactory as desired

			// org.apache.fop.configuration.Configuration.put("baseDir",appPath);

			$cfopFactory = new JavaClass("org.apache.fop.apps.FopFactory");
			$fopFactory = $cfopFactory->newInstance();

			M()->info( $t = getcwd() );

			$fopFactory->setBaseURL( $t );

			$foUserAgent = $fopFactory->newFOUserAgent();

			// Setup output

			$tmpDir = new Java( "java.io.File", '/tmp' );
			$outDir = new Java( "java.io.File", $tmpDir, 'fop-out' );

			$outDir->mkdirs();

			$pdffile = new Java( "java.io.File", $outDir, 'test.pdf' );

			$tmp = new Java( "java.io.FileOutputStream", $pdffile );
			$out = new Java( 'java.io.BufferedOutputStream', $tmp );


		} catch (java_ConnectException $e) {

			M()->fatal( "No puedo iniciar la conexion con la maquina virtual Java. Mensaje: ". $e->getMessage() );
		}

		try {


			java_require( $this->ini['java']['saxon_jar'].";" );

			// Fop fop = fopFactory.newFop(MimeConstants.MIME_PDF, foUserAgent, out);	

			$mimeConstants = new JavaClass( "org.apache.fop.apps.MimeConstants" );

			// $fop = $fopFactory->newFop( $mimeConstants->MIME_PDF, $foUserAgent, $oResultStream );
			$fop = $fopFactory->newFop( $mimeConstants->MIME_PDF, $foUserAgent, $out );

			$oFeatureKeys = new JavaClass("net.sf.saxon.FeatureKeys");

			// $cfactory = new JavaClass( 'javax.xml.transform.TransformerFactory' );
			$factory = new java( "net.sf.saxon.TransformerFactoryImpl" );

			// $cfactory->setAttribute($oFeatureKeys->ALLOW_EXTERNAL_FUNCTIONS, true);

			// $factory = $cfactory->newInstance();

			$factory->setAttribute($oFeatureKeys->ALLOW_EXTERNAL_FUNCTIONS, true);

			$transformer = $factory->newTransformer( $oXslSource );

			$transformer->setParameter("versionParam", "2.0");

			$res = new Java( 'javax.xml.transform.sax.SAXResult', $fop->getDefaultHandler() );

			$transformer->transform( $oXmlSource, $res );

			$out->close();

			return java_cast($out->toString(), "string");

		}

			catch(JavaException $e) {
			M()->warn( "Hubo mensajes en la tranformacion del archivo $xml_file con el template $xsl_file<br/> ". java_cast($e->getCause()->toString(), "string") );
			return null;
		}
	


	}/*}}}*/

	function get_hash( $length = 32 ) {/*{{{*/

		return getToken( $length );
	}/*}}}*/

        function get_hash_md5( $seed = null ) {/*{{{*/

		// return sha1(microtime(true).mt_rand(10000,90000).$seed);
                return md5(uniqid(microtime(true).mt_rand(10000,90000).$seed, true));
        
        }/*}}}*/

	function pp( $xml ) {/*{{{*/

		$doc = new DOMDocument('1.0');
		$doc->formatOutput = true;
		$domnode = dom_import_simplexml($xml);
		$domnode = $doc->importNode($domnode, true);
		$domnode = $doc->appendChild($domnode);
		echo $doc->saveXML();

	}/*}}}*/

	function tidy ( $options = null ) {/*{{{*/

		global $xpdoc;

		$tidy = new Tidy();

		is_array( $options ) or 
		$options = array( 

		  "indent" =>  "true",
		  "indent-spaces" =>  2,
		  "wrap" =>  0,
		  "markup" =>  true,
		  "output-xhtml" =>  true,
		  "numeric-entities" =>  true,
		  "quote-marks" =>  true,
		  "quote-nbsp" =>  false,
		  "show-body-only" =>  false,
		  "quote-ampersand" =>  false,
		  "break-before-br" =>  false,
		  "uppercase-tags" =>  false,
		  "uppercase-attributes" =>  false,
		  "drop-font-tags" =>  true,
		  "tidy-mark" =>  false );
		 
		  $tidy->parseString($xpdoc->output_buffer, $options);
		  $tidy->cleanRepair();
		 
		  $xpdoc->output_buffer = (string) $tidy;
	}/*}}}*/

}



// vim600: fdm=marker sw=3 ts=8 ai:

?>
