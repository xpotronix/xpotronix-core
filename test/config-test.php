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

// ini_set( 'xdebug.profiler_enable_trigger', 1 );
// ini_set( 'xdebug.profiler_enable', 1 );
// ini_set( 'xdebug.show_mem_delta', 1); 
// xdebug_start_trace('/tmp/xpotronix-trace.xt');

require __DIR__ . '/../vendor/autoload.php';

use Xpotronix\Messages;
use Noodlehaus\Config;

// M()->user('hola');


$xconfig = new Config( "/etc/xpotronix/conf/xpay/config.xml" );

var_dump( $xconfig );

?>
