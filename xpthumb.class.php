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
	var $cache_key;
	var $props;
	var $cache_dir = 'cache';
	var $file_pathinfo;
	var $cache;
	var $cache_options;
	var $cache_pathinfo;
	var $cached_image;
	var $loaded = false;

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

		$this->cache_key = md5($this->http->request_uri);

		$this->file_pathinfo = pathinfo("{$this->doc_root}/{$this->http->src}");

		$this->cache_pathinfo = pathinfo($this->http->src);

		M()->debug( 'request url '. $this->http->request_uri );

	}/*}}}*/

	function get_cache() {/*{{{*/

		$this->set_cache();

		if ( $this->cached_image = $this->cache->get( $this->cache_key ) ) {

			M()->info( 'pagina de cache encontrada: '. $this->cache_key );
                        return $this->cached_image;

                } else 

			M()->info( 'pagina de cache NO encontrada: '. $this->cache_key );
	}/*}}}*/

	function cache() {/*{{{*/

		$cache_filename = $this->cache_filename();

		M()->info( "guardando cache en $this->cache_key con el path: $cache_filename" );

		$this->set_cache();	
		$this->cache->save( $this->image, $this->cache_key );

	}/*}}}*/

	function cache_pathname() {/*{{{*/

		$parts = array();

		$t = $this->cache_root and $parts[] = $t;
		@$t = $this->cache_pathinfo['dirname'] and $parts[] = $t;
		$t = $this->cache_dir and $parts[] = $t;

		$result = implode('/', $parts );
		M()->info($result);
		return $result;


	}/*}}}*/

	function cache_filename() {/*{{{*/

		$parts = array();

		$t = $this->cache_pathname() and $parts[] = $t;
		$t = $this->cache_key and $parts[] = $t;

		$result = implode('/', $parts );
		M()->info($result);
		return $result;

	}/*}}}*/

	function set_cache() {/*{{{*/

		if ( is_object( $this->cache ) ) return;

		$cache_pathname = $this->cache_pathname();

		M()->info( "cache_pathname: $cache_pathname");

		if ( !file_exists( $cache_pathname ) ) {

			M()->info( "cache_pathname: $cache_pathname" );

			mkdir( $cache_pathname, 0777, true );
			M()->warn( "no se pudo crear el directorio de cache $cache_pathname" );
		}

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

	function load( $file_path = null ) {/*{{{*/

		$this->image = new imagick;

		if ( ! $file_path ) {
			$file_path = "{$this->doc_root}/{$this->http->src}";
		}

		M()->debug( "file_path: $file_path" );

		try {

			$this->image->readImage($file_path);
			M()->debug( "cargada la imagen $file_path" );
			$this->get_props();
			$this->loaded = true;
			return $this;

		} catch (Exception $e) {

			M()->error( "no ecuentro la imagen $file_path" );

			if ( ! file_exists( $err_img = 'images/warning.jpg' ) ) {
				M()->error( "no encuentro $err_img en ". getcwd() );
				return null;
				}
			else {

				$this->image->readImage( $err_img );
				return $this;
			}
		}

	}/*}}}*/

	function read_blob( $image ) {/*{{{*/

		$this->image = new imagick;


		try {

			$this->image->readImageBlob( $image );
			M()->debug( "cargada la imagen desde BLOB" );
			$this->get_props();
			$this->loaded = true;
			return $this;

		} catch (Exception $e) {

			M()->error( "no se pudo cargar la imagen desde BLOB" );

			if ( ! file_exists( $err_img = 'images/warning.jpg' ) ) {
				M()->error( "no encuentro $err_img en ". getcwd() );
				return null;
				}
			else {

				$this->image->readImage( $err_img );
				return $this;
			}
		}

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

		@$orientation = $this->props['exif:Orientation'];

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

		$offset = 60 * 60 * 24 * 300;
		$ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";

		header('Pragma: public');
		header('Cache-Control: max-age=86400');
		header($ExpStr);
		header("Content-Type: image/jpg");

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
