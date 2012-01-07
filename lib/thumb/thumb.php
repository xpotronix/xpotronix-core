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

include_once '../../xpthumb.class.php';

$x = new xpthumb;


if ( $x->get_cache() ) { 
	$x->output_cached_image();
	exit;
}

$x->load();

if ( $x->http->q )
	$x->compress( $x->http->q );

if ( $x->http->wp or $x->http->hl )
	$x->thumb();

if ( $x->http->ar == 'x' )
	$x->adjust_orientation();

if ( $x->http->filtr )
	foreach( $x->http->filtr as $f ) 
		$x->filter( $f );

$x->cache();

$x->output();


?>
