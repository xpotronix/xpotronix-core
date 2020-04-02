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

class xpBlob extends \Xpotronix\Attr {

	 function serialize( $value = null ) {/*{{{*/

		$value or $value = $this->value;

		if ( is_binary( $value ) and $this->blob_serialize !== true )
			return null;
		else
			return $value;
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
