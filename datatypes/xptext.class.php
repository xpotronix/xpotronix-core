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

class xptext extends xpattr {

	/*

	function encode( $value = NULL ) {

		// codifica los valores para la base de datos

		global $xpdoc;

		if ( $value === NULL ) $value = $this->value;

		$db_enc = @$this->obj->get_db()->__encoding;
		$this->encoding and $db_enc = $this->encoding;

		$app_enc = (string) $xpdoc->feat->encoding;

		if ( $db_enc and $db_enc != $app_enc  ) {

			// DEBUG: esto no va o hay que formularlo de otra forma

			// $new_value = mb_convert_encoding( $value, $db_enc, $app_enc ); 
			// M()->info( "$this->name: ($app_enc) [$value] ==>> ($db_enc) [$new_value]" );

			$new_value = $value;

			// M()->line();

			return parent::encode( $new_value );

		} else

			return parent::encode( $value );

	}

	function decode( $value = NULL ) {

		// decodifica los valores de la base de datos

		global $xpdoc;

		if ( $value === NULL ) $value = $this->value;

		$db_enc = @$this->obj->get_db()->__encoding;
		$this->encoding and $db_enc = $this->encoding;

		$app_enc = (string) $xpdoc->feat->encoding;

		if ( $db_enc and $db_enc != $app_enc  ) {

			// DEBUG: idem anterior
			// $new_value = mb_convert_encoding( $value, $app_enc, $db_enc ); 
			// M()->info( "$this->name: ($db_enc) [$value] ==>> ($app_enc) [$new_value]" );

			$new_value = $value;
			// M()->line();

			return parent::decode( $new_value );

		} else

			return parent::decode( $value );

	}

	*/

}

?>
