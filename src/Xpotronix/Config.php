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

class Config {

	private $data;
	private $fallback;

	const PREG_TEST_XML_ENTITY = "%(<.*?/>)%is";

	function __construct( $param = null ) {/*{{{*/

		// si es un simplexml, hara fallback sobre ese 
		// si es un string, probar si es una entidad, si no, un archivo
		//
		if ( is_null( $param ) ) return $this;

		try {

			/* si es un SimpleXMLElement, va a usarlo de fallback,
			 * esto sirve para cuando uno testea localmente los feat del modulo */

			if ( is_object( $param ) and ( $entity = $param->get_xml()->getName() ) ){

				$this->fallback = $param;
				$this->data = new \SimpleXMLElement( "<$entity/>" );

			/* si es un XML en un string lo convierte */

			} else if ( preg_match( self::PREG_TEST_XML_ENTITY, $param ) ) {

				$this->data = new \SimpleXMLElement( $param );

			} else {

				/* sino busca un archivo */

				if ( ! file_exists( $param ) ) {

					$ret = pathinfo( $param );

					$file_name = $ret['basename'];

					if ( ! file_exists( $file_name ) )
						M()->fatal( "no encuentro el archivo de configuracion $param" );
					else
						$param = $file_name;

				} 

				$this->data = simplexml_load_file( $param );
				M()->info("cargando el config desde $param" );
			}

		} catch (\Exception $e) {

			$msg = $e->getMessage();

			M()->fatal( "No puedo inicializar una configuracion: o no es un obejto valido o hubo errores al abrir el archivo $param. Causa: $msg" );

		}

		return $this;

	}/*}}}*/

	function __get( $var_name ) {/*{{{*/

		if ( !isset( $this->data->$var_name ) ) {

			if ( $this->fallback ) {

				return $this->fallback->$var_name;

			} else {

				M()->debug('accediendo a una directiva de configuracion inexistente: '. $var_name );
				return NULL;
			}

		}

		$var_value = $this->data->$var_name;

		/* si tiene children es un XML debe devolverlo como tal */

		if ( count( $var_value->children() ) ) {

			// M()->info( "var_name: $var_name, type: XML" );
			return $var_value;

		}

		/* si tiene el atributo @type lo convierte a ese */

		$var_type = (string) ( $var_value['type'] ? $var_value['type'] : null );

		if ( $var_type ) {
		
			// M()->info( "var_name: $var_name, type: $var_type, value: $var_value" );

			if ( $var_type == 'bool' or $var_type == 'boolean' )
		
				return strtolower( $var_value ) == 'true';

			settype( $var_value, $var_type ) ;

			return $var_value;
		
		}

		/* es un xml son todos strings */
		$var_value = (string) $var_value;

		/* sino hace deteccion automatica */

		if ( in_array( strtolower( $var_value ), ['true','false'] ) ) {

			return strtolower( $var_value ) == 'true';
		
		}

		if ( is_numeric( $var_value ) ) {

			/* lo convierte automaticamente el PHP */
			settype( $var_value, 'int' );
			return $var_value;
		
		}

		$var_value and settype( $var_value, 'string' );
		return $var_value;

	}/*}}}*/

	function __set( $var_name, $var_value ) {/*{{{*/

		/* solo se pueden asignar datos simples */

		if ( is_object( $var_value ) or is_array( $var_value ) ) {

			M()->error( "no se puede asignar un objeto o array a un elemento simple en la variable $var_name" );
			return null;
		}

		// M()->info( "var_name: $var_name, value: $var_value" );

		$this->data->$var_name = (string) $var_value;
		$var =& $this->data->$var_name;

		if ( is_bool( $var_value ) ) {

			$var['type'] = 'bool';
			$this->data->$var_name = $var_value ? 'true' : 'false';

		} else if ( !is_string( $var_value ) ) {

			$tmp =& $this->data->$var_name;
			$tmp['type'] = gettype( $var_value );
		}

		return $var_value;

	}/*}}}*/

	function set_xml( $xml ) {/*{{{*/
		
		$this->data = $xml;

		return $this;

	}/*}}}*/

	function set_fallback( $config ) {/*{{{*/
		
		$this->fallback = $config;

		return $this;

	}/*}}}*/

	function get_fallback() {/*{{{*/

		return $this->fallback;

	}/*}}}*/

	function serialize() {/*{{{*/

		return $this->data->asXML();

	}/*}}}*/

	function debug() {/*{{{*/

		header( "Content-type: text/xml" );

		print $this->serialize();

		ob_flush();

		exit;

	}/*}}}*/

	function get_xml() {/*{{{*/

		return $this->data;

	}/*}}}*/

	function override( $xml ) {/*{{{*/

		foreach( $xml->children() as $child ) {

			$var_name = $child->getName();
			$value = (string) $child;
			$type = (string) $child['type'];
			$type = $type ? $type : 'string';

			if ( $type == 'bool' or $type == 'boolean' )
				$value = (bool) ($value == 'true');
			else
				settype( $value, $type );

			M()->info("variable: $var_name = ($type) $value" );
			$this->$var_name = $value;
		}

		return $this;

	}/*}}}*/

	static function transform( \SimpleXMLElement $base, \SimpleXMLElement $xslt, ?array $params ) {/*{{{*/

		/* abro el documento XSL */
	
		$xsl = simplexml_to_dom( $xslt );
		$xsl->resolveExternals = true;
		$xsl->substituteEntities = true;

		/* el procesador */

		$proc = new \XSLTProcessor;
		$proc->importStyleSheet($xsl);

		/* los parametros */

		if ( is_array( $params ) ) 
			foreach( $params as $name => $value )
				$proc->setParameter( '', $name, $value );

		/* el XML a transformar */

		$dom = simplexml_to_dom($base);

		/* el resultado */

		$ret = $proc->transformToXML( $dom );

		return $ret;
	
	}/*}}}*/

}

?>
