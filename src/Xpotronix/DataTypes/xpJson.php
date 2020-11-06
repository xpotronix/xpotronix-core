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

class xpJson extends \Xpotronix\Attr {

	function encode( $value = NULL ) {

		global $xpdoc;

		if ( $value === NULL ) 
			$value = $this->value;

		return $value;

	}

	function decode( $value = NULL ) {

		// decodifica los valores de la base de datos

		global $xpdoc;

		if ( $value === NULL ) $value = $this->value;

		return $value;

	}

}

?>
