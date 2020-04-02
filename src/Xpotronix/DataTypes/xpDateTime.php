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

class xpDateTime extends xpDate {

	function format_str() {/*{{{*/

		global $xpdoc;
		return $xpdoc->feat->date_format. ' '. $xpdoc->feat->time_format;
	}/*}}}*/

	function format_long_str() {/*{{{*/

		global $xpdoc;
		return $xpdoc->feat->date_long_format. ' '. $xpdoc->feat->time_format;
	}/*}}}*/

	function db_format_str() {/*{{{*/

		global $xpdoc;
		return $xpdoc->feat->date_db_format. ' '. $xpdoc->feat->time_db_format;

	}/*}}}*/

	function db_null_str() {/*{{{*/

		global $xpdoc;
		return $xpdoc->feat->date_db_null. ' '. $xpdoc->feat->time_db_null;

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
		} 

		if ( !$date ) {

			// echo '<pre>'; print_r( $this->errors( $value ) );

			M()->debug( "no puedo decodificar la fecha $value" );
			return null;
		} 

		return $date->format( $this->db_format_str() );
	}/*}}}*/

}
// vim600: fdm=marker sw=3 ts=8 ai:
?>
