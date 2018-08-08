<?php

require_once 'Cache/Lite.php';

class xpcache extends Cache_Lite {
	
	function raiseError($msg, $code) {
	M()->debug( "$msg ($code)" );
    }

}

?>
