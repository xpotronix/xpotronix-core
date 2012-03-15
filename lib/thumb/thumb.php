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


/*
global $xpdoc;

$x = new xpthumb( (string) $xpdoc->feat->path_imagenes );
*/


// esto esta harcodeado, tiene que haber uno por aplicacion, DEBUG
$x = new xpthumb( (string) '/var/www/ADD/Legajos', '/tmp/xpotronix/xpay/image' );

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


// DEBUG: esto tiene que estar en combinacion con el output final y parametrizable
$x->setImageFormat('jpg');

$x->cache();

$x->output();


?>
