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
$imagick->rotateImage(new \ImagickPixel(), 90)


 */

namespace Xpotronix;


class Thumb {

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
	var $request_uri;
	var $loaded = false;

	function __construct( $doc_root = null, $cache_root = null ) {/*{{{*/

		global $xpdoc;

		if ( is_object( $xpdoc ) )
		
			$this->http = $xpdoc->http;	
		else
			$this->http = new Http();

		if ( $doc_root )
			$this->doc_root = $doc_root;
		else
			$this->doc_root = '/var/www/sites/fotoshow-fotos/';

		if ( $cache_root )
			$this->cache_root = $cache_root;
		else
			$this->cache_root = $doc_root;

		$this->request_uri = htmlspecialchars_decode( $this->http->request_uri );

		$this->cache_key = md5( $this->request_uri );

		$this->file_pathinfo = pathinfo("{$this->doc_root}/{$this->http->src}");

		$this->cache_pathinfo = pathinfo($this->http->src);

		M()->debug( "request_uri: {$this->http->request_uri}, cache_key: {$this->cache_key}" );

		// print_r( $this->build_hash( 'ID' ) ); exit;

		$this->set_cache();

	}/*}}}*/

	function show_image( $config, $obj ) {/*{{{*/

		global $xpdoc;

		extract( $config );

		/* list( $id_http_var,
			$id_key,
			$image_field,
			$mime_type_field,
			$filesize_field,
			$last_modified_field ) = $config; */

		@M()->info( "id_http_var: $id_http_var, id_key: $id_key, image_field: $image_field, mime_type_field: $mime_type_field, filesize_field: $filesize_field, last_modified_field: $last_modified_field, dirname_field: $dirname_field, basename_field: $basename_field" );

		$image_hash = $this->build_hash( $id_http_var );

		$ID = $xpdoc->http->$id_http_var;

		$obj->load( $ID );

		if ( ! $obj->loaded() ) {
		
			M()->info( "no encontre el objeto con ID $ID" );	
			return false;
		}

		header_remove("Pragma");
		header_remove("Expires");
		$xpdoc->header('Cache-Control: private, must-revalidate');

		$last_modified_time = $obj->$last_modified_field; 

		$etag = md5( $image_hash['id'].'@'.$image_hash['suffix'].'@'.$last_modified_time ); 
		$gmdate = gmdate("D, d M Y H:i:s", $last_modified_time);

		M()->info( "last_modified_time: $last_modified_time $gmdate" );

		$xpdoc->header("Last-Modified: $gmdate GMT");
		$xpdoc->header("Etag: $etag"); 

		M()->info("Etag/Last-Modified: $etag/$gmdate");

		if ( $use_etag = true ) {

			$hims = @$_SERVER['HTTP_IF_MODIFIED_SINCE'];
			$hinm = @$_SERVER['HTTP_IF_NONE_MATCH'];

			M()->info( "HTTP_IF_MODIFIED_SINCE: $hims, HTTP_IF_NONE_MATCH: $hinm" );

			$test1 = ( $hims == $last_modified_time );
			$test2 = ( $hinm == $etag );

			if ( $test1 || $test2 ) {

				M()->info("Not Modified $etag $gmdate");
				header('HTTP/1.1 304 Not Modified');
				return true;
			}
			else 	M()->info( "Modified" );
		}

		$obj->feat->blob_load = true;

		if ( false and $this->get_cache( $image_hash ) ) { 

			$this->output_cached_image();
			return true;
		} 

		/* si es blob o path */

		if ( $obj->load( $ID ) ) {

			if ( $obj->$image_field ) {

				M()->info( "encontre BLOB" );
				$this->read_blob( $obj->$image_field );
			}
			else {

				if ( isset( $dirname_field ) and isset( $basename_field ) ) {
					M()->info( "busco archivo" );
					$this->load( $this->doc_root. '/'. $obj->$dirname_field. '/'. $obj->$basename_field );
				} else {
					M()->info( "no hay imagen definida" );
					return false;
				}
			}
		}

		if ( $this->loaded ) {	

			M()->info("aplico filtros");

			if ( $this->http->q )
				$this->compress( $this->http->q );

			if ( $this->http->wp or $this->http->hl )
				$this->thumb();

			if ( $this->http->ar == 'x' )
				$this->adjust_orientation();

			if ( $this->http->filtr )
				foreach( $this->http->filtr as $f ) 
					$this->filter( $f );

			if ( isset( $force_image_format ) ) {
				$this->setImageFormat( $force_image_format );
				$xpdoc->header("Content-type: image/$force_image_format");
			} else {
			
				$mime_type = $obj->$mime_type_field 
					or $mime_type = 'image/jpeg';
				$xpdoc->header("Content-type: $mime_type");
			}

			$image_length = ( isset( $filesize_field ) ) ?
				$obj->$filesize_field:
				$this->length();

			$xpdoc->header( "Content-Length: $image_length" );

			$this->cache( $image_hash );
			$this->output( false );

		} else {

			M()->info( "no encontrada, envia warning.jpg" );
			$this->readfile( null );
			return true;
		}

		return true;

	}/*}}}*/

	function readfile( $filename = null ) {/*{{{*/

		global $xpdoc;

		header_remove("Pragma");
		header_remove("Expires");
		$xpdoc->header('Cache-Control: public, max-age=86400');
		$xpdoc->header('Content-Type: image/jpeg');

		if ( ($filename == null) or (!file_exists( $filename )) ) {
			M()->info( "no encuentro el archivo $filename" );	
			return readfile( 'images/warning.jpg' ); 
		}
		else {
			return readfile( $filename );
		}

	}/*}}}*/

	function build_hash( $key_var ) {/*{{{*/

		$request_url = $this->request_uri;

		M()->info( "request_url: $request_url" );

		$param_keys = ['q','ar','wp','hl','filtr'];
		$params = parse_url( $request_url );
		$query = $params['query'];
		parse_str( $query, $result );

		$suffix = http_build_query( array_intersect_key( $result, array_flip( $param_keys )) );

		// print_r( $result ); exit;
		//

		$value_key_var = null;
		if ( isset( $result[$key_var] ) )
			$value_key_var = $result[$key_var];
		else
			M()->warn( "no ecuentro la variable del request con el nombre $key_var" );

		$id = $result['m'].'@'.$value_key_var;

		$return = [ 'id' => $id, 'suffix' => $suffix ];

		M()->info( "id: $id, suffix: $suffix" ); 

		return $return;
	}/*}}}*/

	function get_cache( $arr = null ) {/*{{{*/

		// $this->set_cache();
		//

		if ( is_array( $arr ) ) {

			$this->cached_image = $this->cache->get( $arr['suffix'], $arr['id'] );
		
		} else {
		
			$this->cached_image = $this->cache->get( $this->cache_key );
		}

		if ( $this->cached_image ) {

			M()->info( 'pagina de cache encontrada: '. $this->cache_key );
                        return $this->cached_image;

                } else 

			M()->info( 'pagina de cache NO encontrada: '. $this->cache_key );
			return null;

	}/*}}}*/

	function cache( $arr = null ) {/*{{{*/

		$cache_filename = $this->cache_filename();

		M()->info( "guardando cache en $this->cache_key con el path: $cache_filename" );

		// $this->set_cache();	
		//
		if ( is_array( $arr ) ) {

			$this->cache->save( $this->image, $arr['suffix'], $arr['id'] );
		
		} else {
			$this->cache->save( $this->image, $this->cache_key );
		}

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
			'automaticSerialization' => FALSE,
			'hashedDirectoryLevel' => 0
                );

		$this->cache = new Cache($this->cache_options);
		

	}/*}}}*/

	function get_abs_filename() {/*{{{*/

		return implode('/', array( $this->file_pathinfo['dirname'], $this->file_pathinfo['basename'] ));

	}/*}}}*/

	function load( $file_path = null ) {/*{{{*/

		$this->image = new \Imagick; //DEBUG: era imagick en minuscula

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

		$this->image = new \Imagick;


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
				$this->image->setImageColorSpace(\Imagick::COLORSPACE_GRAY);
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

		$this->image->setImageCompression(\Imagick::COMPRESSION_JPEG);
		$this->image->setImageCompressionQuality($index);
		$this->image->stripImage();

	}/*}}}*/

	function rotate( $angle ) {/*{{{*/

		M()->user( "angle $angle" );


		switch( (int) $angle ) {

			case 90:
				$this->image->rotateImage(new \ImagickPixel(), 90);
			break;

			case 180:
				$this->image->rotateImage(new \ImagickPixel(), 180);
			break;

			case 270:
				$this->image->rotateImage(new \ImagickPixel(), -90);
			break;

			default:
				$this->image->rotateImage(new \ImagickPixel(), (int) $angle );

		}

	}/*}}}*/

	function adjust_orientation() {/*{{{*/

		@$orientation = $this->props['exif:Orientation'];

		switch ( $orientation ) {

			case 1:

			break;

			case 8:
				$this->image->rotateImage(new \ImagickPixel(), -90);
			break;

			case 3:
				$this->image->rotateImage(new \ImagickPixel(), 180);
			break;

			case 6:
				$this->image->rotateImage(new \ImagickPixel(), 90);
			break;

		}
	}/*}}}*/

	function output( $headers_do = true ) {/*{{{*/

		$headers_do and $this->headers();
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

	function length() {/*{{{*/
	
		return strlen( $this->image );
	
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
