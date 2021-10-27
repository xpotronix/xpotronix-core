<?php

use Xpotronix\Messages;

function rsearch($folder, $pattern) {

    $dir = new RecursiveDirectoryIterator($folder);
    $ite = new RecursiveIteratorIterator($dir);
    $files = new RegexIterator($ite, $pattern, RegexIterator::GET_MATCH, RecursiveIteratorIterator::CATCH_GET_CHILD);
    $fileList = array();
    foreach($files as $file) {
        $fileList = array_merge($fileList, $file);
    }
    return $fileList;
}

function M() {/*{{{*/

	static $mess;

	if ( ! is_object( $mess ) ) {
		$mess = new Messages;
		/* syslog( LOG_INFO, "nueva instancia de Messages" ); */
	}
	return $mess;
}/*}}}*/

function periodo_add( $periodo, $value ) {/*{{{*/

	/* suma un entero a un string de periodo */

	$pos = 4;

	$anio = (int) substr($periodo, 0, $pos);
	$mes = (int) substr($periodo, $pos);

	$meses = ( $anio * 12 ) + $mes + $value;

	$r_anio = (int) ($meses / 12);
	$r_mes = $meses % 12;

	if ( $r_mes === 0 ) {

		$r_mes = 12;
		$r_anio -= 1;
	}

	return str_pad($r_anio, 4, '0', STR_PAD_LEFT ).
		str_pad($r_mes, 2, '0', STR_PAD_LEFT );
}/*}}}*/

function array_flatten(array $array) {/*{{{*/

    $return = array();
    array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });
    return $return;
}/*}}}*/

function is_binary($str) {/*{{{*/
    return preg_match('~[^\x20-\x7E\t\r\n]~', $str) > 0;
}/*}}}*/

function string_parse( $data, $replace, $pattern = '/\{(\w+)}/' ) {/*{{{*/

	return preg_replace_callback( $pattern, 

		function ($match) use ( $replace ) {

			/* print_r( $match ); */

			list ($_, $name) = $match;

			if (isset($replace[$name])) 
				return $replace[$name];
		}

	, $data);

}/*}}}*/

function utf8_for_xml($string) {/*{{{*/
	return preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $string);
}/*}}}*/

function utf8_for_xml_entities($string) {/*{{{*/

	$find = array( 
		'/[^\x{0009}]+/u',
		'/[^\x{000a}]+/u',
		'/[^\x{000d}]+/u',
		'/[^\x{0020}]+/u',
		'/[^\x{D7FF}]+/u',
		'/[^\x{E000}]+/u',
		'/[^\x{FFFD}]+/u'
	);

	$replace = array( 

		'&#x0009;',
		'&#x000a;',
		'&#x000d;',
		'&#x0020;',
		'&#xD7FF;',
		'&#xE000;',
		'&#xFFFD;'
	);

	return preg_replace ( $find, $replace, $string);

}/*}}}*/

function crypto_rand_secure($min, $max) {/*{{{*/

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
}/*}}}*/

function getToken($length){/*{{{*/

	$token = "";
	$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
	$codeAlphabet.= "0123456789";
	for($i=0;$i<$length;$i++){
		$token .= $codeAlphabet[crypto_rand_secure(0,strlen($codeAlphabet))];
	}
	return $token;
}/*}}}*/

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

	function reArrayFiles($posted_vars) {/*{{{*/

		$afile = [];

		foreach( $posted_vars as $name => $file_post ) {

			if ( $file_post['name'] == null ) 
				continue;

			if ( is_string( $file_post['name'] ) ) {

				$file_post['var_name'] = $name;
				$afile[] = $file_post;
				continue;
			}

			$file_count = count( $file_post['name'] );

			$file_keys = array_keys($file_post);

			for ($i=0; $i < $file_count; $i++) {

				$afile[$i]['var_name'] = $name;

				foreach ($file_keys as $key) {
					$afile[$i][$key] = $file_post[$key][$i];
				}
			}
		}

		/* echo '<pre>'; print_r( $afile ); exit; */

	    return $afile;
	}/*}}}*/

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
		require_once 'constants.inc.php';
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

   function simplexml_append(SimpleXMLElement $parent, SimpleXMLElement $child = null ){/*{{{*/

	if ( $child == NULL ) return;
        // puse estos nombres largos para entender un poco mas el DOMDocument ...

      $dom_parent_node = dom_import_simplexml($parent);
      $dom_child_node = dom_import_simplexml($child);
      $dom_child_node_in_parent_dom = $dom_parent_node->ownerDocument->importNode($dom_child_node, true);
      $dom_parent_node->appendChild($dom_child_node_in_parent_dom);

}/*}}}*/

	function string2dom( string $html_string = null ) {/*{{{*/

			/* se queda solo con el texto del campo imagen */

			if ( $html_string === null )
				return null;

			$htmlDom = new \DOMDocument;

			//Load the HTML string into our DOMDocument object.

			@$htmlDom->loadHTML( mb_convert_encoding($html_string, 'HTML-ENTITIES', 'UTF-8') );
			// @$htmlDom->loadHTML( mb_convert_encoding($html_string, ENT_NOQUOTES, 'UTF-8') );
	
			return simplexml_import_dom($htmlDom);
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

?>
