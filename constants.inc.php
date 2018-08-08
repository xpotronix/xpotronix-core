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

// Constantes para xpotronix

defined( 'CLI' ) or define( 'CLI', PHP_SAPI == 'cli' );

// messages

defined( 'MSG_INFO') or define( 'MSG_INFO', 1 );
defined( 'MSG_WARN' ) or define( 'MSG_WARN', MSG_INFO << 1 );
defined( 'MSG_ERROR' ) or define( 'MSG_ERROR', MSG_INFO << 2 );
defined( 'MSG_FATAL' ) or define( 'MSG_FATAL', MSG_INFO << 3 );
defined( 'MSG_USER' ) or define( 'MSG_USER', MSG_INFO << 4 );
defined( 'MSG_DEBUG' ) or define( 'MSG_DEBUG', MSG_INFO << 5 );
defined( 'MSG_STATS' ) or define( 'MSG_STATS', MSG_INFO << 6 );

// control de mensajes al syslog y al cliente web

// defined( 'DEFAULT_SYSLOG_FLAGS' ) or define( 'DEFAULT_SYSLOG_FLAGS', MSG_DEBUG | MSG_INFO | MSG_WARN | MSG_ERROR | MSG_FATAL | MSG_STATS );
defined( 'DEFAULT_SYSLOG_FLAGS' ) or define( 'DEFAULT_SYSLOG_FLAGS', MSG_USER | MSG_ERROR | MSG_WARN | MSG_FATAL  );
defined( 'DEFAULT_MESSAGES_FLAGS' ) or define( 'DEFAULT_MESSAGES_FLAGS', MSG_USER | MSG_ERROR | MSG_WARN );
//defined( 'LOG_FUNCTION' ) or define( 'LOG_FUNCTION', '/(page)/si');
defined( 'LOG_CLASS' ) or define( 'LOG_CLASS', '/(xpdoc)/si');

// class prefix namespace (php)

defined( 'XP_CLASS_NAMESPACE' ) or define( 'XP_CLASS_NAMESPACE', 'C' );

// config

defined( 'DS' ) or define( 'DS', '/' );
defined( 'XPOTRONIX_INI' ) or define( 'XPOTRONIX_INI', '/etc/xpotronix/xpotronix.ini' );
defined( 'XPOTRONIX_INI_OVERRIDE' ) or define( 'XPOTRONIX_INI_OVERRIDE', '~/.xpotronix.ini' );

// xpotronix ns

defined( 'XPOTRONIX_NAMESPACE_URI' ) or define ( 'XPOTRONIX_NAMESPACE_URI', 'http://xpotronix.com/namespace/xpotronix/' );

//  Constantes para php-java-bridge 

// defined( 'JAVA_HOSTS' ) or define('JAVA_HOSTS', 9267 ); 
// defined( 'JAVA_HOSTS' ) or define('JAVA_HOSTS', '127.0.0.1:8787' );
// defined( 'JAVA_HOSTS' ) or define('JAVA_HOSTS', 'java.presencia.net:8787' );
defined('JAVA_HOSTS' ) or define ('JAVA_HOSTS', 'localhost:8787' );
// defined( 'JAVA_HOSTS' ) or define ( 'JAVA_HOSTS', 'justamente.net:8787' );

// defined( 'JAVA_SERVLET' ) or define ( 'JAVA_SERVLET', '/MyWebApp/JavaBridge.phpjavabridge' );
// defined( 'JAVA_SERVLET' ) or define ( 'JAVA_SERVLET', false);

defined( 'JAVA_PERSISTENT_SERVLET_CONNECTIONS' ) or define ( 'JAVA_PERSISTENT_SERVLET_CONNECTIONS', false );
// defined( 'JAVA_PREFER_VALUES' ) or define ( 'JAVA_PREFER_VALUES', 1 );
// defined( 'JAVA_DEBUG' ) or define ( 'JAVA_DEBUG', true );
// defined( 'JAVA_PIPE_DIR' ) or define ( 'JAVA_PIPE_DIR', '/dev/shm' );

// flags para el dataset

defined( 'DS_ANY' ) or define( 'DS_ANY', 1 );
defined( 'DS_NORMALIZED' ) or define( 'DS_NORMALIZED', DS_ANY << 1 );
defined( 'DS_RECURSIVE' ) or define( 'DS_RECURSIVE', DS_ANY << 2 );
defined( 'DS_BLANK' ) or define( 'DS_BLANK', DS_ANY << 3 );
defined( 'DS_DEFAULTS' ) or define( 'DS_DEFAULTS', DS_ANY << 4 );

// prefijo numerico para tags invalidos

defined( 'ARRAY2XML_NUM_PREFIX' ) or define( 'ARRAY2XML_NUM_PREFIX', 'ID__' );

?>
