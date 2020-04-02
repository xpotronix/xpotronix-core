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

// prefijo numerico para tags invalidos
defined( 'ARRAY2XML_NUM_PREFIX' ) or define( 'ARRAY2XML_NUM_PREFIX', 'ID__' );

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

?>
