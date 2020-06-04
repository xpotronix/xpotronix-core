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

	function __construct( $param ) {/*{{{*/


		// si es un simplexml, hara fallback sobre ese 
		// si es un string, probar si es una entidad, si no, un archivo
		try {
			if ( is_object( $param ) and ( $entity = $param->get_xml()->getName() ) ){

				$this->fallback = $param;
				$this->data = new \SimpleXMLElement( "<$entity/>" );

			} else if ( preg_match( "%(<.*?/>)%is", $param ) ) {

				$this->data = new \SimpleXMLElement( $param );

			} else {

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

			M()->fatal( 'No puedo inicializar una configuracion: o no es un obejto valido o hubo errores al abrir el archivo '. $param. '. Causa: '. $e->getMessage() );

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

		$tmp = $this->data->$var_name;

		/*

		if ( $var_name == 'force_utf8' )  {
			
			print '<pre>';
			//print $var_name. ':: '. var_dump( $tmp );
			$values = $this->data->xpath( "//$var_name" );
			print count( $values );
			global $xpdoc;
			ob_clean();
			$xpdoc->feat->debug();
			exit;
		}
		*/

		if ( count( $tmp->children() ) ) {

			// M()->info( "var_name: $var_name, type: XML" );
			return $tmp;

		} else {

			$tmp_type = (string) ( $tmp['type'] ? $tmp['type'] : 'string' );

			// M()->info( "var_name: $var_name, type: $tmp_type, value: $tmp" );

			if ( $tmp_type == 'bool' or $tmp_type == 'boolean' )
			
				return $tmp == 'true';

			settype( $tmp, $tmp_type ) ;

			return $tmp;
		}

	}/*}}}*/

	function __set( $var_name, $var_value ) {/*{{{*/

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

}

?>
