<?php


namespace Xpotronix;

class Cache extends \Cache_Lite {
	
	function raiseError($msg, $code) {/*{{{*/

		M()->debug( "$msg ($code)" );
	}/*}}}*/

	function clean( $group = null, $mode = null ) {/*{{{*/
	
		M()->info( "borando el cache para el grupo $group" );
		return parent::clean( $group );
	}/*}}}*/

}

?>
