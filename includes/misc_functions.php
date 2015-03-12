<?

function crypto_rand_secure($min, $max) {

	$range = $max - $min;
	if ($range < 0) return $min; // not so random...
	$log = log($range, 2);
	$bytes = (int) ($log / 8) + 1; // length in bytes
	$bits = (int) $log + 1; // length in bits
	$filter = (int) (1 << $bits) - 1; // set all lower bits to 1

	do {
		$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
		$rnd = $rnd & $filter; // discard irrelevant bits
	} while ($rnd >= $range);

	return $min + $rnd;
}

function getToken($length){

	$token = "";
	$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
	$codeAlphabet.= "0123456789";
	for($i=0;$i<$length;$i++){
		$token .= $codeAlphabet[crypto_rand_secure(0,strlen($codeAlphabet))];
	}
	return $token;
}

function ProcStats() {    /*{{{*/

       $fp=fopen("/proc/stat","r"); 
       if(false===$fp) 
               return false; 
       $a=explode(' ',fgets($fp)); 
       array_shift($a); //get rid of 'cpu' 
       while(!$a[0]) 
           array_shift($a); //get rid of ' ' 
       var_dump($a); 
       fclose($fp); 
       return $a; 
   }/*}}}*/

   function crypto($source) {/*{{{*/
        $salt[0] = "something  here!";   // Limit this to 16 characters
        $salt[1] = "abcd";             // Limit this to 8 characters
        $crypt[0] = crc32($source);
        $crypt[1] = crypt($source, $salt[0]);
        $crypt[2] = md5($source);
        $crypt = implode($salt[1], $crypt);
        return sha1($source.$crypt);
    }/*}}}*/

	function do_nothing() {/*{{{*/
		return true;
	}/*}}}*/

	function recursivemakehash($tab) { /*{{{*/
		if(!is_array($tab)) 
			return $tab; 
		$p = ''; 
		foreach($tab as $a => $b) 
			$p .= sprintf('%08X%08X', crc32($a), crc32(recursivemakehash($b))); 
		return $p; 
	} /*}}}*/

function array_unique2($input) {/*{{{*/

	if (!is_array( $input ))
		return $input;
	$dumdum = array(); 
	foreach($input as $a => $b) 
		$dumdum[$a] = recursivemakehash($b); 
	$newinput = array(); 
	foreach(array_unique($dumdum) as $a => $b) 
		$newinput[$a] = $input[$a]; 
	return $newinput; 
} /*}}}*/

function strtime_to_secs( $s ) {/*{{{*/

	$t = array ( 'h' => 3600, 'd' => 86400, 'w' => (86400 * 7), 'm' => (86400 * 30), 'y' => (86400 * 365) );

	$n = (int) $s;
	$n = $n ? $n : 1;

	@$m = $t[substr($s, -1)];
	// $m = $m ? $m : $t['d'];
	$m = $m ? $m : 1;

	return $n * $m; 

}/*}}}*/

function microtime_float() {/*{{{*/

	list($useg, $seg) = explode(" ", microtime());
	return ((float)$useg + (float)$seg);
}/*}}}*/

   function simplexml_append(SimpleXMLElement $parent, SimpleXMLElement $child = null ){/*{{{*/

	if ( $child == NULL ) return;
        // puse estos nombres largos para entender un poco mas el DOMDocument ...

      $dom_parent_node = dom_import_simplexml($parent);
      $dom_child_node = dom_import_simplexml($child);
      $dom_child_node_in_parent_dom = $dom_parent_node->ownerDocument->importNode($dom_child_node, true);
      $dom_parent_node->appendChild($dom_child_node_in_parent_dom);

}/*}}}*/

function __autoload_disabled( $class_name ) {/*{{{*/

	$class_path = "$class_name.class.php";

	if ( ! file_exists( $class_path ) ) {

		$class_name = str_replace( "C", NULL, $class_name );
		$class_path = "modules/$class_name/$class_name.class.php";

		if ( ! file_exists( $class_path ) ) die ( "no puedo incluir el archivo $class_path" );
	} 

	xpmessage::_( '__autoload', 10, 'Incluyendo el archivo '. $class_path );

	require_once $class_path;


}/*}}}*/

function array2xml( $root_tag, $array ) {/*{{{*/

	global $xpdoc;

	try {


		$x = new array2xml( $root_tag, $array ); 
		return simplexml_import_dom( $x->data );

	} catch(Exception $e) {

		M()->warn('No puedo realizar la conversion: Error: '. $e->getMessage(). ', tag: '. $root_tag. ', data: '. $x->data );
		// print '<pre>';
		// var_dump( $e );
		// print 'root_tag: '. $root_tag;
	}
}/*}}}*/

function a2o($array) {/*{{{*/
	if(!is_array($array)) {
		return $array;
	}
	$object = new stdClass();
	if (is_array($array) && count($array) > 0) {
	  foreach ($array as $name=>$value) {
	     $name = trim($name);
	     if (!empty($name)) {
	        $object->$name = a2o($value);
	     }
	  }
      return $object;
	}
    else {
      return FALSE;
    }
}/*}}}*/

function object_to_array( $obj, $recursive = false ) {/*{{{*/

        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;

        foreach ($_arr as $key => $val) {

                $val = (is_array($val) || is_object($val)) ? 
			( ( $recursive ) ? $object_to_array($val) : 'Array' ) :  
			$val;

		$val && $arr[$key] = $val;
        }
        return $arr;
}/*}}}*/
   
function remove_accents($string) {/*{{{*/

	return str_replace( array('à','á','â','ã','ä', 'ç', 'è','é','ê','ë', 'ì','í','î','ï', 'ñ', 'ò','ó','ô','õ','ö', 'ù','ú','û','ü', 'ý','ÿ', 'À','Á','Â','Ã','Ä', 'Ç', 'È','É','Ê','Ë', 'Ì','Í','Î','Ï', 'Ñ', 'Ò','Ó','Ô','Õ','Ö', 'Ù','Ú','Û','Ü', 'Ý'), array('a','a','a','a','a', 'c', 'e','e','e','e', 'i','i','i','i', 'n', 'o','o','o','o','o', 'u','u','u','u', 'y','y', 'A','A','A','A','A', 'C', 'E','E','E','E', 'I','I','I','I', 'N', 'O','O','O','O','O', 'U','U','U','U', 'Y'), $string);

} /*}}}*/

/**
 * basic class for converting an array to xml.
 * @author Matt Wiseman (trollboy at shoggoth.net)
 *
 */

class array2xml {/*{{{*/

	public $data;
	public $dom_tree;

	/**
	 * basic constructor
	 *
	 * @param array $array
	 */
	public  function __construct($root_node, $array){

		$this->data = new DOMDocument('1.0');


		if ( is_array( $array ) or is_object( $array ) ) {

			$this->dom_tree = $this->data->createElement($root_node);
			$this->data->appendChild($this->dom_tree);
			$this->recurse_node($array, $this->dom_tree);

		}
		else {

			$this->dom_tree = $this->data->createElement($root_node, $array);
			$this->data->appendChild($this->dom_tree);
		}
	}

	/**
	 * recurse a nested array and return dom back
	 *
	 * @param array $data
	 * @param dom element $obj
	 */
	private function recurse_node($data, $obj){
		$i = 0;
		foreach($data as $key=>$value){

			if ( is_numeric( substr( $key, 0, 1 ) ) ) 
				$key = ARRAY2XML_NUM_PREFIX. $key;

			if(is_array($value)){
				//recurse if neccisary
				$sub_obj[$i] = $this->data->createElement($key);
				$obj->appendChild($sub_obj[$i]);
				$this->recurse_node($value, $sub_obj[$i]);
			} elseif(is_object($value)) {
				//no object support so just say what it is
				$sub_obj[$i] = $this->data->createElement($key, 'Object: "' . $key . '" type: "'  . get_class($value) . '"');
				$obj->appendChild($sub_obj[$i]);
			} else {
				//straight up data, no weirdness
				$sub_obj[$i] = $this->data->createElement($key, htmlspecialchars( $value ) );
				$obj->appendChild($sub_obj[$i]);
			}
			$i++;
		}
	}

	/**
	 * get the finished xml
	 *
	 * @return string
	 */
	public function saveXML(){
		return $this->data->saveXML();
	}
}/*}}}*/

/**
 * Splits the given string into chunks with the given length.
 *
 * @param     string    $string         Input string
 * @param     string    $chunkLength    Chunk length
 * @return    array     Splitted string     
 */ 

function SplitByLength($string, $chunkLength=1){/*{{{*/
 
    $Result     = array(); 
    $Remainder  = strlen($string) % $chunkLength;
 
    $cycles = ((strlen($string) - $Remainder) / $chunkLength) + (($Remainder != 0) ? 1 : 0);
 
    for ($x=0; $x < $cycles; $x++)
        $Result[$x] = substr($string, ($x * $chunkLength), $chunkLength);
 
    return $Result;
}/*}}}*/
 
// Alternative way:

function SplitByLength2($string, $chunkLength=1){/*{{{*/
 
    $result = array();     
    $strLength = strlen($string);
    $x = 0;
 
    while($x < ($strLength / $chunkLength)){
        $result[] = substr($string, ($x * $chunkLength), $chunkLength);
        $x++;
    }
 
    return $result;
}/*}}}*/

    /**
     * Parses $GLOBALS['argv'] for parameters and assigns them to an array.
     *
     * Supports:
     * -e
     * -e <value>
     * --long-param
     * --long-param=<value>
     * --long-param <value>
     * <value>
     *
     * @param array $noopt List of parameters without values
     */

    function parseParameters( $noopt = array() ) {/*{{{*/

        $result = array();
        $params = $GLOBALS['argv'];

        // could use getopt() here (since PHP 5.3.0), but it doesn't work relyingly

	if ( ! is_array( $params ) )
		return $result;

        reset( $params );

        while ( list( $tmp, $p ) = each( $params) ) {

            if ( $p{0} == '-' ) {

                $pname = substr( $p, 1 );
                $value = true;

                if ( $pname{0} == '-' ) {

                    // long-opt (--<param>)
                    $pname = substr( $pname, 1 );

                    if ( strpos($p, '=') !== false ) {

                        // value specified inline (--<param>=<value>)
                        list($pname, $value) = explode('=', substr($p, 2), 2);

                    }
                }

                // check if next parameter is a descriptor or a value

                $nextparm = current($params);
                if (!in_array($pname, $noopt) && $value === true && $nextparm !== false && $nextparm{0} != '-') list($tmp, $value) = each($params);
                $result[$pname] = $value;

            } else {

                // param doesn't belong to any option
                $result[] = $p;
            }
        }

        return $result;
    }/*}}}*/

	/* ejemplo de run in background

		de http://nsaunders.wordpress.com/2007/01/12/running-a-background-process-in-php/

		echo("Running funky Perl. . .")
		$ps = run_in_background("perl FunkyPerl.pl '$infile'");

		while(is_process_running($ps)) {
			echo(" . ");
			ob_flush();
			flush();
			sleep(1);
		}
	*/

   function run_in_background( $Command, $Priority = 0 ) {/*{{{*/

       if( $Priority )
           $PID = shell_exec("nohup nice -n $Priority $Command 2> /dev/null & echo $!");
       else
           $PID = shell_exec("nohup $Command 2> /dev/null & echo $!");

	// Para Ubuntu con php5
  	// $PID = shell_exec(”nohup $Command > /dev/null 2> /dev/null & echo $!”);
       return( $PID );
   }/*}}}*/

   function is_process_running($PID) {/*{{{*/

       exec("ps $PID", $ProcessState);
       return(count($ProcessState) >= 2);

   }/*}}}*/

?>
