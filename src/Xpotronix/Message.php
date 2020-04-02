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

namespace Xpotronix;

class Message {

	var $text;
	var $type;
	var $class;
	var $function;
	var $id;
	var $tag;
	var $nest = true;
	var $timestamp;
	var $mess; // parent
	var $xpid;
	var $ip;

	const CLI = ( PHP_SAPI == 'cli' );

	function __construct( $mess, $text = null, $type = null, $id = null, $tag = null ) {/*{{{*/

		$this->mess = $mess;
		$this->text = $text ? $text : '-- MARK --';
		$this->type = $type ? $type : MSG_INFO;
		$this->id = $id;
		$this->tag = $tag;

		return $this;
	}/*}}}*/

	function tag( $tag ) { /*{{{*/

		$this->tag = $tag;
		return $this;
	}/*}}}*/

	function t( $type ) { /*{{{*/

		$this->type = $type;
		return $this;
	}/*}}}*/

	function f() { /*{{{*/

		$fl = $this->get_file_line();

		$this->file = $fl['file'];
		$this->line = $fl['line'];
		$this->class = $fl['class'];
		$this->function = $fl['function'];
		$this->nest = $fl['nest'];

		return $this;
	}/*}}}*/

	function i( $id ) {/*{{{*/

		$this->id = $id;
		return $this;
	}/*}}}*/

	function get() {/*{{{*/

		return $this->text;
	}/*}}}*/

	function serialize() {/*{{{*/

		try{

			$xml = new \SimpleXMLElement( '<message>'. htmlspecialchars($this->text). '</message>' );

		} catch( Exception $e ) {

			$xml = new \SimpleXMLElement( '<message>no pude decodificar el mensaje de error!</message>' );

		}


		$this->type and $xml['type'] = $this->type;
		$this->file and $xml['file'] = $this->file;
		$this->line and $xml['line'] = $this->line;
		$this->line and $xml['tag']  = $this->tag;

		$this->class and $xml['class'] = $this->class;
		$this->function and $xml['function'] = $this->function;

		$this->id and $xml['id'] = $this->id;

		return $xml;
	}/*}}}*/

	function text_me() {/*{{{*/

		// linea de syslog

		global $xpdoc;

		$buff = array();

		if ( is_object( $xpdoc ) ) {

			$xpdoc->config->log_xpid and $buff[] = 'XPID:'. $xpdoc->config->application. '/'. $this->xpid;
			$xpdoc->config->log_ip and $buff[] = 'IP:'. $this->ip;
		}

		// if ( $this->type != MSG_INFO ) 
		$buff[] = str_pad($this->mess->get_type_string( $this->type ). ":", 6 );

		if ( $this->nest ) 
			$buff[] = str_repeat(">", $this->nest - 4 );

		if ( $this->class or $this->function ) 
			$buff[] = "[{$this->class}::{$this->function}]";

		$buff[] = $this->text;

		if ( $this->id )
			$buff[] = "#{$this->id}";

		if ( self::CLI ) $buff[] = "\n";
		
		return implode( " ", $buff );
		
	}/*}}}*/

	function log() {/*{{{*/
		
		syslog( LOG_INFO, $this->text_me() );

	}/*}}}*/

	public static function get_file_line( $index = 0 ) {/*{{{*/

		// print '<pre>'; print_r( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) ); exit;

		$stack = 3 + $index;
		$caller = 4 + $index;

		// DEBUG: debug_backtrace copia el valor de los atributos, duplicandolos en memoria! con blobs se queda sin memoria el programa
		// return;

		$trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		$file = $trace[$stack]['file'];
		$line = $trace[$stack]['line'];

		@$class = $trace[$caller]['class'];
		@$function = $trace[$caller]['function'];

		// return array( 'file' => $file, 'line' => $line, 'class' => $class, 'function' => $function, 'nest' => count($trace) - $index );
		return array( 'file' => $file, 'line' => $line, 'class' => $class, 'function' => $function, 'nest' => 0 );
	}/*}}}*/

}

?>
