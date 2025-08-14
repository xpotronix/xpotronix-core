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

use Soluble\Japha\Bridge\Adapter as BridgeAdapter;
use Soluble\Japha\Bridge\Exception as BridgeException;
use Soluble\Japha\Bridge\Exception\ConnectionException as ConnectionException;
use Soluble\Japha\Bridge\Exception\JavaException as JavaException;

class Base {

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

	const INI_FILE = '/etc/xpotronix/xpotronix.ini';
	const INI_OVERRIDE = '~/.xpotronix.ini';

	function __construct() {/*{{{*/

		$this->__xml_data = new \SimpleXMLElement( '<data/>' );

	}/*}}}*/


	/* misc */

	function dup() {/*{{{*/

		return unserialize(serialize($this));
	}/*}}}*/

	function load_ini() {/*{{{*/

		if ( file_exists( self::INI_OVERRIDE ) )
			$this->ini = parse_ini_file( self::INI_OVERRIDE, true );
		else if ( file_exists( self::INI_FILE ) )
			$this->ini = parse_ini_file( self::INI_FILE, true );
		else M()->fatal( "No encuentro el archivo de configuracion .ini de xpotronix\n" );

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

	function get_username() {/*{{{*/

		global $xpdoc;

		if ( $xpdoc ) return $xpdoc->user->user_username;
		else return NULL;

	}/*}}}*/

	/* debug */

	static function debug_backtrace( $data = NULL ) {/*{{{*/

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

	function debug_obj_in_messages() {/*{{{*/

		// en xp

		$var_d = null;
		foreach( $this->data as $key => $data ) $var_d .= "$key = $data;";
		M()->info( 'processing obj (climbing up)', 10 , $obj->get_name(). ': '. $var_d  );	
	}/*}}}*/

	/* transform */

	function saxon_bridge_transform( $xml_file, $xsl_file, $params = null ) {/*{{{*/

		M()->info("recibi parametros: $xml_file, $xsl_file ". serialize( $params ) );

		try {

			$options = [
				'servlet_address' => 'localhost:8080/JavaBridgeTemplate/servlet.phpjavabridge'
			];

			$ba = new BridgeAdapter($options);

		} catch ( ConnectionException $e) {

			M()->fatal( "No puedo iniciar la conexion con la maquina virtual Java. Mensaje: ". $e->getMessage() );
		}

		try {

			$oXmlSource = $ba->java("javax.xml.transform.stream.StreamSource", $xml_file);
			$oXslSource = $ba->java("javax.xml.transform.stream.StreamSource", $xsl_file);


			$oFeatureKeys = $ba->javaClass("net.sf.saxon.FeatureKeys");

			$oTransformerFactory = $ba->java("net.sf.saxon.TransformerFactoryImpl");

			// $oTransformerFactory->setAttribute($oFeatureKeys->SCHEMA_VALIDATION, 4);

			$oTransformerFactory->setAttribute($oFeatureKeys->ALLOW_EXTERNAL_FUNCTIONS, true);

			$oTransFormer = $oTransformerFactory->newTransformer($oXslSource);

			$oResultStringWriter = $ba->java("java.io.StringWriter");
			$oResultStream = $ba->java("javax.xml.transform.stream.StreamResult", $oResultStringWriter);


			// carga los parametros (si los hay)

			if ( is_array( $params ) )
				foreach( $params as $key => $value )
					$oTransFormer->setParameter( $key, $value );


			$oTransFormer->transform($oXmlSource, $oResultStream );

			return (string) $oResultStringWriter->toString();

		} catch( JavaException $e ) {

			$msg = $e->getCause();
			M()->warn( "Hubo mensajes en la tranformacion del archivo $xml_file con el template $xsl_file: $msg" );
			return null;
		}
	}/*}}}*/ 

	function fop_transform( $xml_file, $xsl_file, $params = null, $output_path, $output_file ) {/*{{{*/

		M()->user("recibi parametros: $xml_file, $xsl_file ". serialize( $params ) );

		try {
			$options = [
				'servlet_address' => 'localhost:8080/JavaBridgeTemplate/servlet.phpjavabridge'
			];

			$ba = new BridgeAdapter($options);

			$oXmlSource = $ba->java("javax.xml.transform.stream.StreamSource", $xml_file);
			$oXslSource = $ba->java("javax.xml.transform.stream.StreamSource", $xsl_file);

			$oResultStringWriter = $ba->java("java.io.StringWriter");
			$oResultStream = $ba->java("javax.xml.transform.stream.StreamResult", $oResultStringWriter);


	            	// configure fopFactory as desired
			// org.apache.fop.configuration.Configuration.put("baseDir",appPath);
			//

			M()->info( $base_path = getcwd(). "/conf/fop.xconf" );
			$confFile = $ba->java( "java.io.File", $base_path );

			$cfopFactory = $ba->javaClass("org.apache.fop.apps.FopFactory");

			$fopFactory = $cfopFactory->newInstance( $confFile );

			$foUserAgent = $fopFactory->newFOUserAgent();

			// $foUserAgent->setBaseURL( $t );

			// Setup output

			/* $tmpDir = $ba->java( "java.io.File", '/tmp' ); */
			$outDir = $ba->java( "java.io.File", $output_path );

			$outDir->mkdirs();

			$pdffile = $ba->java( "java.io.File", $outDir, $output_file );

			$tmp = $ba->java( "java.io.FileOutputStream", $pdffile );
			$out = $ba->java( 'java.io.BufferedOutputStream', $tmp );

			/*$out = $ba->java( 'java.io.ByteArrayOutputStream' ); */


		} catch ( BridgeException $e) {

			M()->fatal( "No puedo iniciar la conexion con la maquina virtual Java. Mensaje: ". $e->getMessage() );
		}

		try {

			/* java_require( $this->ini['java']['saxon_jar'].";" ); */

			// Fop fop = fopFactory.newFop(MimeConstants.MIME_PDF, foUserAgent, out);	

			$mimeConstants = $ba->javaClass( "org.apache.fop.apps.MimeConstants" );

			// $fop = $fopFactory->newFop( $mimeConstants->MIME_PDF, $foUserAgent, $oResultStream );
			$fop = $fopFactory->newFop( $mimeConstants->MIME_PDF, $foUserAgent, $out );

			$oFeatureKeys =  $ba->javaClass("net.sf.saxon.FeatureKeys");

			// $cfactory = $ba->javaClass( 'javax.xml.transform.TransformerFactory' );
			$factory = $ba->java( "net.sf.saxon.TransformerFactoryImpl" );

			// $cfactory->setAttribute($oFeatureKeys->ALLOW_EXTERNAL_FUNCTIONS, true);

			// $factory = $cfactory->newInstance();

			$factory->setAttribute($oFeatureKeys->ALLOW_EXTERNAL_FUNCTIONS, true);

			$transformer = $factory->newTransformer( $oXslSource );

			$transformer->setParameter("versionParam", "2.0");

			$res = $ba->java( 'javax.xml.transform.sax.SAXResult', $fop->getDefaultHandler() );

			$transformer->transform( $oXmlSource, $res );

			$out->close();

			return $out->toString();

		} catch( JavaException $e) {

			$msg = $e->getCause();

			M()->warn( "Hubo mensajes en la tranformacion del archivo $xml_file con el template $xsl_file: $msg" );
			return null;
		}
	


	}/*}}}*/

	function saxon_transform( $xml_file, $xsl_file, $params = null, $validation = false ) {/*{{{*/

		M()->user("recibi parametros: $xml_file, $xsl_file ". json_encode( $params ) );

		/* procesador */

		$return = null;

		try {

			$saxonProc = new \Saxon\SaxonProcessor(true);
			$proc = $saxonProc->newXslt30Processor();
			$version = $saxonProc->version();

			/* config Properties */

			$validation and $saxonProc->setConfigurationProperty( 'http://saxon.sf.net/feature/schema-validation', 'preserve' );
			$saxonProc->setConfigurationProperty( 'http://saxon.sf.net/feature/allow-external-functions', true );

			$inputNode = $saxonProc->parseXmlFromFile( $xml_file );
			M()->debug( "xml_file: $xml_file" );

			$executable = $proc->compileFromFile( $xsl_file );
			M()->debug( "xsl_file: $xsl_file" );

			if ( is_array( $params ) ) {

				foreach( $params as $name => $value ) {

					M()->debug( "setParameter $name: $value" );
					$executable->setParameter( $name, $saxonProc->createAtomicValue($value) );
				}
			}

			M()->debug( "transformacion con Saxon/C" );

			$result = $executable->transformToString($inputNode);

		} catch ( Exception $e ) {

			$errMessage = $e->getMessage();
		
			M()->warn( "Hubo mensajes en la tranformacion del archivo $xml_file con el template $xsl_file<br/> Cod: $errCode, Mensaje: $errMessage" );
		
		}

        $executable->clearParameters();
        $executable->clearProperties();

		return $result;

	}/*}}}*/

	/* hash */

	function get_hash( $length = 32 ) {/*{{{*/

		return getToken( $length );
	}/*}}}*/

        function get_hash_md5( $seed = null ) {/*{{{*/

		// return sha1(microtime(true).mt_rand(10000,90000).$seed);
                return md5(uniqid(microtime(true).mt_rand(10000,90000).$seed, true));
        
        }/*}}}*/

	/* pretty print */

	function pp( $xml ) {/*{{{*/

		$doc = new \DOMDocument('1.0');
		$doc->formatOutput = true;
		$domnode = dom_import_simplexml($xml);
		$domnode = $doc->importNode($domnode, true);
		$domnode = $doc->appendChild($domnode);
		echo $doc->saveXML();

	}/*}}}*/

	function tidy ( $options = null ) {/*{{{*/

		global $xpdoc;

		$tidy = new \Tidy();

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

	/* xml */

	function xml_elem_attrs( $array ) {/*{{{*/
		$r = " ";
		foreach( $array as $attr => $value )
			$r .= "$attr=\"$value\" ";
		return $r;
	}/*}}}*/

	function remove_xml_decl( $xml ) {/*{{{*/

		return preg_replace( "/<\?xml.*\?>/", "", $xml );

	}/*}}}*/



}

// vim600: fdm=marker sw=3 ts=8 ai:

?>
