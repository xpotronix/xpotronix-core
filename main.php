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

// ini_set( 'xdebug.profiler_enable_trigger', 1 );
// ini_set( 'xdebug.profiler_enable', 1 );
// ini_set( 'xdebug.show_mem_delta', 1); 
// xdebug_start_trace('/tmp/xpotronix-trace.xt');


ini_set( 'display_errors', 0 );
ini_set( 'log_errors', 1 );
ini_set( 'error_log', 'syslog' );

global $xpdoc;

require_once 'includes/misc_functions.php';
require_once 'xpmessages.class.php';

$t_start = microtime_float();

// includes

include_once "xpdoc.class.php";

M()->stats( '*** inicio de ejecucion de xpotronix ***' ); 
M()->mem_stats();
// M()->sys_load();

$xpdoc = new xpdoc;

$xpdoc->params_process();

if ( $xpdoc->init() ) {

	if ( $xpdoc->load_model() ) {

		file_exists( $f = 'common.php' ) and
			include_once( $f );

		$xpdoc->set_view();
		$xpdoc->action_do();

	} else 
		$xpdoc->set_xdoc( $xpdoc->get_messages() );
} else 
	$xpdoc->set_xdoc( $xpdoc->get_messages() );

$xpdoc->transform();
$xpdoc->output();
$xpdoc->close();

M()->mem_stats();
M()->mem_max_stats();

$t_stop = microtime_float();
$exec_time = $t_stop - $t_start;

M()->stats( "*** proceso del URL en $exec_time segundos ***" ); 
M()->stats( '*** fin de ejecucion de xpotronix ***' ); 

// xdebug_stop_trace();

?>
