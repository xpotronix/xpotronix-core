<?php 

/*
   artem at zabsoft dot co dot in
   11-May-2009 06:43
   Hey I modified script for php 5. Also I add support server auth. and fixed some little bugs on the script. 

   [EDIT BY danbrown AT php DOT net: Original was written by (unlcuky13 AT gmail DOT com) on 19-APR-09.  The following note was included: 
   Below is the my way of using through PHP 5 objecte oriented encapsulation to make thing easier.] 

   adaptado para xpotronix

 */

namespace Xpotronix;

class Curl { 

	protected $_useragent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1'; 
	protected $_url; 
	protected $_followlocation; 
	protected $_timeout; 
	protected $_maxRedirects; 
	protected $_cookieFileLocation = './cookie.txt'; 
	protected $_post; 
	protected $_postFields; 
	protected $_referer ="http://www.google.com"; 

	protected $_session; 
	protected $_webpage; 
	protected $_includeHeader; 
	protected $_noBody; 
	protected $_status; 
	protected $_error;
	protected $_errorNumber;
	protected $_binaryTransfer; 
	public    $authentication = 0; 
	public    $auth_name      = ''; 
	public    $auth_pass      = ''; 

	public function useAuth($use){ /*{{{*/
		$this->authentication = 0; 
		if($use == true) $this->authentication = 1; 
	} /*}}}*/

	public function setName($name){ /*{{{*/
		$this->auth_name = $name; 
	} /*}}}*/

	public function setPass($pass){ /*{{{*/
		$this->auth_pass = $pass; 
	} /*}}}*/

	public function __construct($url=null,$followlocation = true,$timeOut = 30,$maxRedirecs = 4,$binaryTransfer = false,$includeHeader = false,$noBody = false) { /*{{{*/
		$this->_url = $url; 
		$this->_followlocation = $followlocation; 
		$this->_timeout = $timeOut; 
		$this->_maxRedirects = $maxRedirecs; 
		$this->_noBody = $noBody; 
		$this->_includeHeader = $includeHeader; 
		$this->_binaryTransfer = $binaryTransfer; 

		// $this->_cookieFileLocation = dirname(__FILE__).'/cookie.txt'; 
		$this->_cookieFileLocation = '/tmp/cookie.txt'; 

	} /*}}}*/

	public function setReferer($referer){ /*{{{*/
		$this->_referer = $referer; 
	} /*}}}*/

	public function setCookiFileLocation($path) { /*{{{*/
		$this->_cookieFileLocation = $path; 
	} /*}}}*/

	public function setPost ($postFields) { /*{{{*/
		$this->_post = true; 
		$this->_postFields = $postFields; 
	} /*}}}*/

	public function setUserAgent($userAgent) { /*{{{*/
		$this->_useragent = $userAgent; 
	} /*}}}*/

	public function request( $url = null ) {/*{{{*/

		$url and $this->_url = $url;

		if ( ! $this->_url ) {

			M()->error('debe especificar una URL' );
			return;
		}

		$s = curl_init(); 

		curl_setopt($s,CURLOPT_URL,$this->_url); 
		curl_setopt($s,CURLOPT_HTTPHEADER,array('Expect:')); 
		curl_setopt($s,CURLOPT_TIMEOUT,$this->_timeout); 
		curl_setopt($s,CURLOPT_MAXREDIRS,$this->_maxRedirects); 
		curl_setopt($s,CURLOPT_RETURNTRANSFER,true); 
		curl_setopt($s,CURLOPT_FOLLOWLOCATION,$this->_followlocation); 
		curl_setopt($s,CURLOPT_COOKIEJAR,$this->_cookieFileLocation); 
		curl_setopt($s,CURLOPT_COOKIEFILE,$this->_cookieFileLocation); 

		if($this->authentication == 1){ 
			curl_setopt($s, CURLOPT_USERPWD, $this->auth_name.':'.$this->auth_pass); 
		} 
		if($this->_post) 
		{ 
			curl_setopt($s,CURLOPT_POST,true); 
			curl_setopt($s,CURLOPT_POSTFIELDS,$this->_postFields); 

		} 

		if($this->_includeHeader) 
		{ 
			curl_setopt($s,CURLOPT_HEADER,true); 
		} 

		if($this->_noBody) 
		{ 
			curl_setopt($s,CURLOPT_NOBODY,true); 
		} 
		/* 
		   if($this->_binary) 
		   { 
		   curl_setopt($s,CURLOPT_BINARYTRANSFER,true); 
		   } 
		 */ 
		curl_setopt($s,CURLOPT_USERAGENT,$this->_useragent); 
		curl_setopt($s,CURLOPT_REFERER,$this->_referer); 

		$this->_webpage = curl_exec($s); 
		$this->_status = curl_getinfo($s,CURLINFO_HTTP_CODE); 
		$this->_error = curl_error($s);
		$this->_errorNumber = curl_errno($s);

		curl_close($s); 

	} /*}}}*/

	public function getHttpStatus() { /*{{{*/
		return $this->_status; 
	} /*}}}*/

	public function error() {/*{{{*/

		return $this->_error;
	}/*}}}*/

	public function errorNumber() {/*{{{*/

		return $this->_errorNumber;
	}/*}}}*/

	public function __toString() { /*{{{*/
		return (string) $this->_webpage; 
	} /*}}}*/

} 
?>
