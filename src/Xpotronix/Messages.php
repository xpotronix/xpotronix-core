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

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;


class Messages {

	/* messages */

	const MSG_INFO = 1;
	const MSG_WARN = 1 << 1;
	const MSG_ERROR = 1 << 2;
	const MSG_FATAL = 1 << 3;
	const MSG_USER = 1 << 4;
	const MSG_DEBUG = 1 << 5;
	const MSG_STATS = 1 << 6;

	const MSG_CONFIG_FILE = 'conf/messages.yaml';

	var $config_data;

	/* control de mensajes al syslog y al cliente web */

	var $syslog_flags; 
	var $messages_flags;

	var $log_function = '';
	var $log_class = '';

	var $buffer = [];
	var $buffer_length = 500;
	var $trace = true;

	var $messages = [];

	var $status;
	var $xml_changes;

	function __construct() {/*{{{*/
		
		/* const DEFAULT_SYSLOG_FLAGS = MSG_DEBUG | MSG_INFO | MSG_WARN | MSG_ERROR | MSG_FATAL | MSG_STATS; */

		/* defaults */

		$this->syslog_flags = self::MSG_USER | self::MSG_ERROR | self::MSG_WARN | self::MSG_FATAL;
		$this->messages_flags = self::MSG_USER | self::MSG_ERROR | self::MSG_WARN;

		$this->status = new \SimpleXMLElement( '<status/>' );
		$this->xml_changes = new \SimpleXMLElement( '<changes/>' );	

		/* Yaml */
		try {

			$this->config_data = Yaml::parseFile( self::MSG_CONFIG_FILE, Yaml::PARSE_CONSTANT );

			if ( @$t = $this->config_data['syslog_flags'] ) {
				$this->syslog_flags = eval( 'return '. str_replace( 'MSG_', 'self::MSG_', $t ). ';' );
				/* syslog( LOG_INFO, "syslog_flags: $this->syslog_flags" ); */
			}

			if ( @$t = $this->config_data['messages_flags'] ) {
				$this->messages_flags = eval( 'return '. str_replace( 'MSG_', 'self::MSG_', $t ). ';' );
				/* syslog( LOG_INFO, "messages_flags: $this->messages_flags" ); */
			}

			if ( @$t = $this->config_data['log_class'] ) {
				$this->log_class = $t;
				/* syslog( LOG_INFO, "log_class: $this->log_class" ); */
			}

			if ( @$t = $this->config_data['log_function'] ) {
				$this->log_function = $t;
				/* syslog( LOG_INFO, "log_function: $this->log_function" ); */
			}

		} catch (ParseException $exception) {

			syslog( LOG_CRIT, "No puedo leer el archivo ". self::MSG_CONFIG_FILE );
		}

		return $this;

	}/*}}}*/

	function set_syslog_flags( $flags ) {/*{{{*/

		$this->syslog_flags = $flags;
	}/*}}}*/

	function set_messages_flags( $flags ) {/*{{{*/

		$this->messages_flags = $flags;

	}/*}}}*/

	function m( $text = null, $type =self::MSG_INFO, $id = null, $tag = null ) {/*{{{*/

		if ( ( $type & $this->syslog_flags ) or ( $type & $this->messages_flags ) or $this->log_function or $this->log_class ) {

			$m = new Message( $this, $text, $type, $id, $tag );

			global $xpdoc;

			if ( is_object( $xpdoc ) and get_class( $xpdoc ) == 'Doc' ) {

				// echo '<pre>'; var_dump( $xpdoc ); print_r( debug_backtrace( false ) ); exit;
				$m->xpid = $xpdoc->xpid();
				$m->ip = $xpdoc->http->remote_addr;
			}

			$m->f();

			if ( ( $type & $this->syslog_flags ) 
				or ( $this->log_function and preg_match( $this->log_function, $m->function ) ) 
				or ( $this->log_class and preg_match( $this->log_class, $m->class ) ) ) {
			
					$m->log();
			}

			if ( ( $type & $this->syslog_flags ) or ( $type & $this->messages_flags ) ) {

				$this->messages[] = $m;

				if ( count( $this->messages ) > $this->buffer_length ) 
					array_shift( $this->messages );
			}

			if ( $type ==self::MSG_FATAL ) {

				print $m->text_me();
				// trigger_error($m->text_me(), E_USER_ERROR);
				exit(1);
			}

			return $m;
		} else 
			return true;
 	}/*}}}*/  

	function get_type_string( $type ) {/*{{{*/

		$type_str = [self::MSG_INFO => 'INFO',
			self::MSG_WARN => 'WARN',
			self::MSG_ERROR => 'ERROR',
			self::MSG_FATAL => 'FATAL',
			self::MSG_USER => 'USER',
			self::MSG_DEBUG => 'DEBUG',
			self::MSG_STATS => 'STATS' ];

		return ( array_key_exists( $type, $type_str ) ? $type_str[$type] : 'UNDEF' );

	}/*}}}*/

	function ok() {/*{{{*/

		return true;
	}/*}}}*/

	function line( $index = 0, $text = null ) {/*{{{*/
		return $this->m( vsprintf( "file: %s, line: %d, class:, %s, function:, %s, nest: %d $text", $this->get_file_line( $index ) ),self::MSG_INFO );
	}/*}}}*/

	function info( $text = null ) {/*{{{*/
		return $this->m( $text,self::MSG_INFO );
	}/*}}}*/

	function warn( $text ) {/*{{{*/
		return $this->m( $text,self::MSG_WARN );
	}/*}}}*/

	function error( $text ) {/*{{{*/
		$this->status('ERR');
		return $this->m( $text,self::MSG_ERROR );
	}/*}}}*/

	function db_error( $db, $op, $sql = null ) {/*{{{*/

		if ( ! $db ) {
			M()->error( 'no hay base de datos activa!' );
			return null;
		}

		if ( ! ( $errno = $db->ErrorNo() ) )
			return null;

		$errmsg = $db->ErrorMsg();

		$this->status('ERR');

		$msg = array();

		$msg[] = "Error en la operación $op [$errno]: $errmsg";
		$sql and $msg[] = "en $sql";

		return $this->m( implode( ' ', $msg ),self::MSG_ERROR );

	}/*}}}*/

	function fatal( $text ) {/*{{{*/
		return $this->m( $text,self::MSG_FATAL );
	}/*}}}*/

	function user( $text = null, $id = null, $tag = null ) {/*{{{*/
		return $this->m( $text,self::MSG_USER, $id, $tag );
	}/*}}}*/

	function get_file_line( $index = 0 ) {/*{{{*/

		return Message::get_file_line( $index );

	}/*}}}*/

	function get_response() {/*{{{*/

		return $this->status;
	}/*}}}*/

	function get_changes() {/*{{{*/

		return $this->xml_changes;

	}/*}}}*/

	function response( $obj, $node = NULL ) {/*{{{*/

		global $xpdoc;
		// if ( $obj->track_modified ) 
		$this->changes( $obj, $node );
	}/*}}}*/

	function changes( $obj, $node = NULL, $full = true ) {/*{{{*/

		// if ( ! $obj->modified ) return;

		$xo = new \SimpleXMLElement( "<$obj->class_name/>" );

		switch( $obj->transac_status ) {


			case INSERT_OP:
			case UPDATE_OP:
			case REPLACE_OP:
			case NO_OP:

				/* DEBUG: por ahora devuelve todos los atributos, no hay diferencia entre modificados o no.
					agregar Serialize::DS_MODIFIED para poder traer solo los modificados */

				if ( $full ) {
					$t = $obj->get_flag('set_global_search');
					$obj->set_flag('set_global_search', false);
					$obj->loadc( $obj->primary_key );
					$obj->set_flag('set_global_search', $t);
				}

				$xo = $obj->serialize_row( Serialize::DS_ANY );
			break;

			case DELETE_OP:
			case NOT_VALID:
			case NOT_FOUND:
			case NO_PERMS:
			case DB_ERROR:

			break;

			default:
				 M()->warn( 'transac_status vacio' );
		}

		$xo['action'] = $obj->transac_status;
		$node and $xo['uiid'] = $node['uiid'];
		$xo['obj'] = $obj->class_name;

		M()->debug( "obj: {$xo['obj']}, transac_status: {$xo['action']}, uiid: {$xo['uiid']}" );
		
		simplexml_append( $this->xml_changes, $xo );

		return $xo;
	}/*}}}*/

	function status( $value = null ) {/*{{{*/

		$value and M()->info( "status value: $value" );

		if ( !$value ) return $this->status['value'];
		else return $this->status['value'] = $value;	
	}/*}}}*/

	function debug( $text ) {/*{{{*/
		return $this->m( $text,self::MSG_DEBUG );
	}/*}}}*/

	function stats( $text ) {/*{{{*/
		return $this->m( $text, self::MSG_STATS );
	}/*}}}*/ 

	function sys_load() {/*{{{*/

		if ( ! function_exists( 'sys_getloadavg' ) ) 
			return null;

		$load = ProcStats();

		return $this->m( "Carga: $load[0], $load[1], $load[2]" );
	}/*}}}*/

	function mem_max_stats( $text = null ) {/*{{{*/

		return $this->stats( sprintf( "Uso maximo de memoria: %01.2f MB %s", memory_get_peak_usage() / (1024 * 1024), $text ) );
	}/*}}}*/

	function mem_stats( $text = null ) {/*{{{*/

		return $this->stats( sprintf( "Uso de memoria: %01.2f MB %s", memory_get_usage() / (1024 * 1024), $text ) );
	}/*}}}*/

	function serialize( $all = false ) {/*{{{*/

		$xm = new \SimpleXMLElement('<messages/>');

		$filter = $this->messages_flags;

		$all and $filter |= $this->syslog_flags;

		foreach( $this->messages as $message )

		 	if ( $message->type & $filter ) 	

				simplexml_append( $xm, $message->serialize() );

		return $xm;

	}/*}}}*/

	function get( $all = false ) {/*{{{*/

		$ret = array();

		$filter = $this->messages_flags;

		$all and $filter |= $this->syslog_flags;

		foreach( $this->messages as $message )

		 	if ( $message->type & $filter ) 	

				$ret[] = $message->get();

		return $ret;

	}/*}}}*/

	function write_log( $content, $extension = 'log' ) {/*{{{*/

		global $xpdoc;

		if ( !$xpdoc ) return;

		if ( ! file_exists( $path_name = $xpdoc->get_log_dir( 'log' ) ) )
			mkdir( $path_name );

		$file_name = $path_name. $xpdoc->xpid(). '.'. $extension;

		if ( $xpdoc ) {

			file_put_contents( $file_name, '/* '. microtime(). " */\n" );
			file_put_contents( $file_name, $content."\n", FILE_APPEND | LOCK_EX );
		} 

		return $file_name;

		
	}/*}}}*/

}

?>
