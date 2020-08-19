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

namespace Xpotronix\DataTypes;

class xpDate extends \Xpotronix\Attr {

	function errors( $value ) {/*{{{*/

		if ( function_exists( 'DateTime::getLastErrors' ) ) {

			$mess = \DateTime::getLastErrors(); 

			if ( $mess['warning_count'] ) M()->info( "warnings para la fecha $value: " . implode( '; ', $mess['warnings'] ) );
			if ( $mess['error_count'] ) M()->info( "errors para la fecha $value: " . implode( '; ', $mess['errors'] ) );
		}
	}/*}}}*/

	function create( $value = null ) {/*{{{*/

		global $xpdoc;

		$date = date_create( $value );
		$this->errors( $value );

		return $date; 
	}/*}}}*/ 

	function encode( $value = NULL ) {/*{{{*/
		// codifica los valores para la base de datos

		if ( $value === NULL ) 
			$value = $this->value;

		if ( ! $value ) return null;

		// M()->debug( 'valor previo: '. $value );

		if ( $p = strpos( $value, '(' ) ) 
			$value = substr( $value, 0, $p ); // elimina el () al final

		$value = trim( $value );

		// M()->debug( 'valor nuevo: '. $value );


		if ( $date = $this->create( $value ) ) 
			return $date->format( $this->db_format_str() );
		else
			return null;
	}/*}}}*/

	function human( $value ) {/*{{{*/

		$value = trim( $value );

		// M()->info( "value: $value" );

		$dt = new \DateTime();

		if ( !method_exists( $dt, 'createFromFormat' ) ) {

			M()->info('DateTime: version de compatibilidad' );
			$dt = new \DateClass();
		}

		
		global $xpdoc;

		$tz = new \DateTimeZone( $xpdoc->feat->default_timezone );


		$date = $dt->createFromFormat( $this->format_str(), $value, $tz );
		$this->errors( $value );

		if ( !$date ) {

			$date = $dt->createFromFormat( $this->format_long_str(), $value, $tz );
			$this->errors( $value );

			if ( !$date ) {

				$date = $dt->createFromFormat( $this->db_format_str(), $value, $tz );
				$this->errors( $value );

				if ( !$date ) {

					// echo '<pre>'; print_r( $this->errors( $value ) );
					M()->debug( "no puedo decodificar la fecha $value" );
					return null;
				} 
			} 
		} 

		return $date->format( $this->db_format_str() );
	}/*}}}*/

	function decode( $value = NULL ) {/*{{{*/
		// decodifica los valores de la base de datos
		
		if ( $value === NULL ) 
			$value = $this->value;

		// M()->debug( 'valor: '. $value );

		if ( $value === NULL or $value == $this->db_null_str() ) 
			return NULL;

		if ( $date = $this->create( $value ) ) 
			return $date->format( $this->db_format_str() );
		else
			return null;
	}/*}}}*/

	function serialize( $value = NULL ) {/*{{{*/
	
		if ( $value === NULL ) 
			$value = $this->value;

		// M()->debug( 'valor: '. $value );

		if ( $value ) {

			global $xpdoc;

			if ( $date = $this->create( $value ) )
				if ( $xpdoc->param_schema == 'ext4' )
					return $date->format( 'c' );
				else
					return $date->format( $this->format_long_str() );
			else 
				return null;

		}

	}/*}}}*/

	function unserialize( $value = NULL ) {/*{{{*/

		return $this->encode( $value );

	}/*}}}*/

	function now() {/*{{{*/

		return $this->value = date_create()->format( $this->db_format_str() );
	}/*}}}*/

	function format_str() {/*{{{*/

		global $xpdoc;
		return $xpdoc->feat->date_format;
	}/*}}}*/

	function format_long_str() {/*{{{*/

		global $xpdoc;
		return $xpdoc->feat->date_long_format;
	}/*}}}*/

	function db_format_str() {/*{{{*/

		global $xpdoc;
		return $xpdoc->feat->date_db_format;

	}/*}}}*/

	function db_null_str() {/*{{{*/

		global $xpdoc;
		return $xpdoc->feat->date_db_null;

	}/*}}}*/
}
// vim600: fdm=marker sw=3 ts=8 ai:
?>
