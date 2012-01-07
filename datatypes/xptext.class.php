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

class xptext extends xpattr {

	function encode( $value = NULL ) {/*{{{*/

		// codifica los valores para la base de datos

		global $xpdoc;

		if ( $value === NULL ) $value = $this->value;

		$e = @$this->obj->get_db()->__encoding;
		$this->encoding and $e = $this->encoding;

		$app_e = (string) $xpdoc->feat->encoding;

		if ( $e and $e != $app_e  ) {

			$value = mb_convert_encoding( $value, $e, $app_e ); 
			M()->info( "$this->name a $e de $app_e: $value" );
			// M()->line();
		}

		return parent::encode( $value );

	}/*}}}*/

	function decode( $value = NULL ) {/*{{{*/

		// decodifica los valores de la base de datos

		global $xpdoc;

		if ( $value === NULL ) $value = $this->value;

		$e = @$this->obj->get_db()->__encoding;
		$this->encoding and $e = $this->encoding;

		$app_e = (string) $xpdoc->feat->encoding;

		if ( $e and $e != $app_e  ) {

			$value = mb_convert_encoding( $value, $app_e, $e ); 
			M()->info( "$this->name a $app_e de $e: $value" );
			// M()->line();
		}

		return parent::decode( $value );

	}/*}}}*/

}

?>
