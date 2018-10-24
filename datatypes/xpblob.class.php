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

class xpblob extends xpattr {

	function serialize( $value = null ) {/*{{{*/

		/* devuelve valor si se ha asignado con prepare_data() */

		if ( $this->modified )
			return $this->value;
		else
			return null;

	}/*}}}*/

	function unserialize( $value = null ) {/*{{{*/

		$value or $value = $this->value;
		return $value;

	}/*}}}*/

	function encode( $value = null ) {/*{{{*/

		$value or $value = $this->value;
		return addslashes( $value );

	}/*}}}*/

	function decode( $value = null ) {/*{{{*/

		$value or $value = $this->value;
		return stripslashes( $value );

	}/*}}}*/

}

?>
