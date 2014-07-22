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

/* 

exif orientation

  1        2       3      4         5            6           7          8

888888  888888      88  88      8888888888  88                  88  8888888888
88          88      88  88      88  88      88  88          88  88      88  88
8888      8888    8888  8888    88          8888888888  8888888888          88
88          88      88  88
88          88  888888  888888


examples:

$imagick->readImage('castle.jpg'); 
$imagick->rotateImage(new ImagickPixel(), 90)


*/



require_once 'includes/misc_functions.php';
require_once 'xphttp.class.php';
require_once 'xpmessages.class.php';
require_once 'xpcache.class.php';

class xpthumb {

	var $http;
	var $image;
	var $doc_root;
	var $cache_root;
	var $props;
	var $cache_dir = 'cache';
	var $file_pathinfo;
	var $cache;
	var $cache_options;
	var $cache_pathinfo;
	var $cached_image;

	function __construct( $doc_root = null, $cache_root = null ) {/*{{{*/

		$this->http = new xphttp();

		if ( $doc_root )
			$this->doc_root = $doc_root;
		else
			$this->doc_root = '/var/www/sites/fotoshow-fotos/';

		if ( $cache_root )
			$this->cache_root = $cache_root;
		else
			$this->cache_root = $doc_root;

		$this->file_pathinfo = pathinfo("{$this->doc_root}/{$this->http->src}");

		$this->cache_pathinfo = pathinfo($this->http->src);

		M()->debug( 'request url '. $this->http->request_uri );

	}/*}}}*/

	function get_cache_key() {/*{{{*/


		return md5($this->http->request_uri);
	}/*}}}*/

	function get_cache() {/*{{{*/

		$this->set_cache();

		if ( $this->cached_image = $this->cache->get( $this->get_cache_key() ) ) {

			M()->info( 'pagina de cache encontrada: '. $this->get_cache_key() );
                        return $this->cached_image;

                } else 

			M()->info( 'pagina de cache NO encontrada: '. $this->get_cache_key() );
	}/*}}}*/

	function cache() {/*{{{*/

		M()->info( 'guardando cache en '. $this->get_cache_key(). ' el path: '. $this->cache_filename() );

		$this->set_cache();	
		$this->cache->save( $this->image, $this->get_cache_key() );

	}/*}}}*/

	function cache_filename() {/*{{{*/

		$cache_filename = implode('/', array( $this->cache_root, $this->cache_pathinfo['dirname'], $this->cache_dir, $this->get_cache_key() ));

		M()->info( $cache_filename );

		return $cache_filename;

	}/*}}}*/

	function cache_pathname() {/*{{{*/

		$cache_pathname = implode('/', array( $this->cache_root, $this->cache_pathinfo['dirname'], $this->cache_dir ));

		// M()->info( $cache_pathname );

		return $cache_pathname;


	}/*}}}*/

	function set_cache() {/*{{{*/

		if ( is_object( $this->cache ) ) return;

		$cache_pathname = $this->cache_pathname();

		M()->info( 'cache_pathname: '. $this->cache_pathname() );

		file_exists( $cache_pathname ) or mkdir( $cache_pathname, 0777, true ) or M()->warn( "no se pudo crear el directorio de cache $cache_pathname" );

                $this->cache_options = array(
                        'caching' => true,
                        'cacheDir' => $cache_pathname. '/',
                        'lifeTime' => 157680000,
                        'fileLocking' => TRUE,
                        'writeControl' => FALSE,
                        'readControl' => FALSE,
                        'memoryCaching' => TRUE,
                        'automaticSerialization' => FALSE
                );

		$this->cache = new xpcache($this->cache_options);
		

	}/*}}}*/

	function get_abs_filename() {/*{{{*/

		return implode('/', array( $this->file_pathinfo['dirname'], $this->file_pathinfo['basename'] ));

	}/*}}}*/

	function load( $file = null ) {/*{{{*/

		$this->image = new imagick;

		if ( ! $file ) 
			$file = "{$this->doc_root}/{$this->http->src}";

		try {

			$this->image->readImage($file);
			M()->debug( "cargada la imagen $file" );

		} catch (Exception $e) {

			M()->error( "no ecuentro la imagen $file" );

			if ( ! file_exists( $err_img = 'images/warning.jpg' ) ) 
				M()->error( "no encuentro $err_img en ". getcwd() );
			else
				$this->image->readImage( $err_img );
		}

		$this->get_props();
	}/*}}}*/

	function thumb() {/*{{{*/

		if ( $this->http->wp and $this->http->hl )
			$this->image->thumbnailImage( $this->http->wp, $this->http->hl, true );

		else if ( $this->http->wp ) 
			$this->image->thumbnailImage( $this->http->wp, true );

	}/*}}}*/

	function filter( $filter ) {/*{{{*/

		if ( ((int) $filter ) > 0 ) {

			$this->image->setImageColorSpace( (int) $filter );
			return;
		}

		switch ( $filter ) {

			case 'color':
			break;

			case 'b/n':
				$this->image->setImageColorSpace(Imagick::COLORSPACE_GRAY);
			break;

			case 'sepia':
				$this->image->sepiaToneImage(80);
			break;

			default:
				M()->info("filtro $filter ignorado.");


		}

	}/*}}}*/

	function get_props() {/*{{{*/

		return $this->props = $this->image->getImageProperties();

	}/*}}}*/

	function get_props_txt() {/*{{{*/

		$response = array();

		foreach( $this->get_props() as $key => $data )
			$response[] = "$key: $data";

		return implode( "\n", $response );
	}/*}}}*/

	function __toString() {/*{{{*/

		return $this->get_props_txt();
	}/*}}}*/

	function compress( $index ) {/*{{{*/

		$this->image->setImageCompression(Imagick::COMPRESSION_JPEG);
		$this->image->setImageCompressionQuality($index);
		$this->image->stripImage();

	}/*}}}*/

	function rotate( $angle ) {/*{{{*/

		M()->user( "angle $angle" );


		switch( (int) $angle ) {

			case 90:
				$this->image->rotateImage(new ImagickPixel(), 90);
			break;

			case 180:
				$this->image->rotateImage(new ImagickPixel(), 180);
			break;

			case 270:
				$this->image->rotateImage(new ImagickPixel(), -90);
			break;

			default:
				$this->image->rotateImage(new ImagickPixel(), (int) $angle );

		}

	}/*}}}*/

	function adjust_orientation() {/*{{{*/

		$orientation = $this->props['exif:Orientation'];

		switch ( $orientation ) {

			case 1:

			break;

			case 8:
				$this->image->rotateImage(new ImagickPixel(), -90);
			break;

			case 3:
				$this->image->rotateImage(new ImagickPixel(), 180);
			break;

			case 6:
				$this->image->rotateImage(new ImagickPixel(), 90);
			break;

		} 
	}/*}}}*/

	function output() {/*{{{*/

		$this->headers();
		echo $this->image;
	}/*}}}*/

	function output_cached_image() {/*{{{*/

		$this->headers();
		echo $this->cached_image;
	}/*}}}*/

	function headers() {/*{{{*/

		header("Content-Type: image/jpeg");
		$offset = 60 * 60 * 24 * 300;
		$ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
		Header($ExpStr);
	}/*}}}*/

	function setImageFormat( $format ) {/*{{{*/

		$this->image->setImageFormat( $format );

	}/*}}}*/

	function setImageDepth( $depth ) {/*{{{*/

		$this->image->setImageDepth( $depth );

	}/*}}}*/

	function write( $file ) {/*{{{*/

		try {
			$this->image->writeImage( $file );

		} catch( Exception $e ) {

			M()->error("no puedo guardar la imagen en $file" );
		}
	}/*}}}*/

}

?>
