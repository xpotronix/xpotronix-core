<?

/**
 * @package xpotronix
 * @version 2.0 - Areco 
 * @copyright Copyright &copy; 2003-2011, Eduardo Spotorno
 * @author Eduardo Spotorno
 *
 * Licensed under GPL v3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

require_once 'constants.inc.php';

if ( ! defined( 'LOG_FUNCTION' ) ) define( 'LOG_FUNCTION', NULL );
if ( ! defined( 'LOG_CLASS' ) ) define( 'LOG_CLASS', NULL );

   function M() {/*{{{*/
	static $mess;
	$mess or $mess = new xpmessages;
	return $mess;
}/*}}}*/

class xpmessage {

	var $text;
	var $type;
	var $class;
	var $function;
	var $id;
	var $tag;
	var $nest;
	var $timestamp;
	var $mess; // parent
	var $xpid;
	var $ip;

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

			$xml = new SimpleXMLElement( '<message>'. htmlspecialchars($this->text). '</message>' );

		} catch( Exception $e ) {

			$xml = new SimpleXMLElement( '<message>no pude decodificar el mensaje de error!</message>' );

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

			$xpdoc->config->log_xpid and $buff[] = 'XPID:'. $xpdoc->feat->application. '/'. $this->xpid;
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

		if ( CLI ) $buff[] = "\n";
		
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

class xpmessages {

	var $buffer = array();
	var $buffer_length = 50;
	var $trace = true;

	var $syslog_flags = DEFAULT_SYSLOG_FLAGS;
	var $messages_flags = DEFAULT_MESSAGES_FLAGS;

	var $messages = array();
	var $type_str = array( MSG_INFO => 'INFO', MSG_WARN => 'WARN', MSG_ERROR => 'ERROR', MSG_FATAL => 'FATAL', MSG_USER => 'USER', MSG_DEBUG => 'DEBUG', MSG_STATS => 'STATS' );

	var $status;
	var $xml_changes;


	function __construct() {/*{{{*/

		$this->status = new SimpleXMLElement( '<status/>' );
		$this->xml_changes = new SimpleXMLElement( '<changes/>' );

	}/*}}}*/

	function set_syslog_flags( $flags ) {/*{{{*/

		$this->syslog_flags = $flags;
	}/*}}}*/

	function set_messages_flags( $flags ) {/*{{{*/

		$this->messages_flags = $flags;

	}/*}}}*/

	function m( $text = null, $type = MSG_INFO, $id = null, $tag = null ) {/*{{{*/

		if ( ( $type & $this->syslog_flags ) or ( $type & $this->messages_flags ) or LOG_FUNCTION or LOG_CLASS ) {

			$m = new xpmessage( $this, $text, $type, $id, $tag );

			global $xpdoc;

			if ( is_object( $xpdoc )  ) {

				// echo '<pre>'; var_dump( $xpdoc ); print_r( debug_backtrace( false ) ); exit;
				$m->xpid = $xpdoc->xpid();
				$m->ip = $xpdoc->http->remote_addr;
			}

			$m->f();

			if ( ( $type & $this->syslog_flags ) or ( LOG_FUNCTION and preg_match( LOG_FUNCTION, $m->function ) ) or ( LOG_CLASS and preg_match( LOG_CLASS, $m->class ) ) ) 
				$m->log();

			if ( ( $type & $this->syslog_flags ) or ( $type & $this->messages_flags ) ) {

				$this->messages[] = $m;

				if ( count( $this->messages ) > $this->buffer_length ) 
					array_shift( $this->messages );
			}

			if ( $type == MSG_FATAL ) {

				print $m->text_me();
				// trigger_error($m->text_me(), E_USER_ERROR);
				exit(1);
			}

			return $m;
		} else 
			return true;
 	}/*}}}*/  

	function get_type_string( $type ) {/*{{{*/

		return ( array_key_exists( $type, $this->type_str ) ? $this->type_str[$type] : 'UNDEF' );
	}/*}}}*/

	function ok() {/*{{{*/

		return true;
	}/*}}}*/

	function line( $index = 0, $text = null ) {/*{{{*/
		return $this->m( vsprintf( "file: %s, line: %d, class:, %s, function:, %s, nest: %d $text", $this->get_file_line( $index ) ), MSG_INFO );
	}/*}}}*/

	function info( $text = null ) {/*{{{*/
		return $this->m( $text, MSG_INFO );
	}/*}}}*/

	function warn( $text ) {/*{{{*/
		return $this->m( $text, MSG_WARN );
	}/*}}}*/

	function error( $text ) {/*{{{*/
		$this->status('ERR');
		return $this->m( $text, MSG_ERROR );
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

		$msg[] = "Error al $op [$errno]: $errmsg";
		$sql and $msg[] = "en $sql";

		return $this->m( implode( ' ', $msg ), MSG_ERROR );

	}/*}}}*/

	function fatal( $text ) {/*{{{*/
		return $this->m( $text, MSG_FATAL );
	}/*}}}*/

	function user( $text = null, $id = null, $tag = null ) {/*{{{*/
		return $this->m( $text, MSG_USER, $id, $tag );
	}/*}}}*/

	function get_file_line( $index = 0 ) {/*{{{*/

		return xpmessage::get_file_line( $index );

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

		$xo = new SimpleXMLElement( "<$obj->class_name/>" );

		switch( $obj->transac_status ) {


			case INSERT_OP:
			case UPDATE_OP:
			case REPLACE_OP:
				// DEBUG: por ahora devuelve todos los atributos, no hay diferencia entre modificados o no.
				// agregar DS_MODIFIED para poder traer solo los modificados

				if ( $full ) $obj->load();

				$xo = $obj->serialize_row( DS_ANY );
			break;

			case DELETE_OP:
			case NO_OP:
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
		return $this->m( $text, MSG_DEBUG );
	}/*}}}*/

	function stats( $text ) {/*{{{*/
		return $this->m( $text, MSG_STATS );
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

		$xm = new SimpleXMLElement('<messages/>');

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


	function write_log( $content, $extension = 'log' ) {

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

		
	}

}

?>
