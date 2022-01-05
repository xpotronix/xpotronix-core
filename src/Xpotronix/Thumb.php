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


namespace Xpotronix;

use Xpotronix\Glade\ServerFactory;
use League\Flysystem\Local\LocalFilesystemAdapter as LocalFilesystemAdapter;
use League\Flysystem\Filesystem as Filesystem;
use League\Flysystem\Memory\MemoryAdapter as MemoryAdapter;
use League\Flysystem\UnableToCreateDirectory as UnableToCreateDirectory;


class Thumb {

	var $http;
	var $image;
	var $doc_root;
	var $cache_root;
	var $props;
	var $file_pathinfo;
	var $request_uri;
	var $loaded = false;
	var $server;

	function __construct( $doc_root = null, $cache_root = null ) {/*{{{*/

		global $xpdoc;

		/* Setup Glide server */


		if ( is_object( $xpdoc ) )
		
			$this->http = $xpdoc->http;	
		else
			$this->http = new Http();

		if ( $doc_root )
			$this->doc_root = $doc_root;
		else
			$this->doc_root = '/var/www/sites/fotoshow-fotos';

		if ( $cache_root )
			$this->cache_root = $cache_root;
		else
			$this->cache_root = $doc_root.'/cache';


		/* prueba si puede crear el fs del cache */

		try {

			$cache_fs = new LocalFilesystemAdapter( $this->cache_root );

		} catch ( UnableToCreateDirectory $e ) {
		
			M()->error( "No puedo crear el directorio. Mensaje: ". $e->getMessage() );
			return null;
		
		}

		$this->server = ServerFactory::create([

				'source' => new Filesystem(new LocalFilesystemAdapter( $this->doc_root )),
				'cache' => new Filesystem( $cache_fs ),
				'driver' => 'imagick'
			]);

		$this->request_uri = htmlspecialchars_decode( $this->http->request_uri );

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

		/* array con el ID y el hash de la URL */

		$image_hash = $this->build_hash( $id_http_var );

		$ID = $xpdoc->http->$id_http_var;

		$obj->load( $ID );

		if ( ! $obj->loaded() ) {
		
			M()->info( "No encontre el objeto con ID $ID" );
			return false;
		}


		if ( isset( $last_modified_time ) and $last_modified_time ) {

			/* si esta definido el last_modified_time field
			 * utiliza el mecanismo de ETAG
			 * para sincronizar el cacheo */

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
		}


		$obj->feat->blob_load = true;

		/* si es blob o path */

		if ( $obj->load( $ID ) ) {

			if ( $obj->$image_field ) {

				M()->info( "encontre BLOB" );
				$this->read_blob( $config, $obj );

			} else {

				if ( isset( $dirname_field ) and isset( $basename_field ) ) {
					M()->info( "busco archivo" );
					$this->load( $obj->$dirname_field. '/'. $obj->$basename_field );
					/* se va porque se aplican todos los filtros en la funcion de league/glide en load */
					return;
				} else {
					M()->info( "no hay imagen definida" );
					return false;
				}
			}
		}


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

		global $xpdoc;

		$request_url = $this->request_uri;

		M()->info( "request_url: $request_url" );

		$param_keys = ['q','or','w','h','filtr'];
		$params = parse_url( $request_url );
		@$query = $params['query'];


		parse_str( $query, $result );

		$suffix = http_build_query( array_intersect_key( $result, array_flip( $param_keys )) );

		// print_r( $result ); exit;
		//

		$value_key_var = null;
		if ( isset( $result[$key_var] ) )
			$value_key_var = $result[$key_var];
		else
			M()->warn( "no ecuentro la variable del request con el nombre $key_var" );

		$id = "{xpdoc->module}@$value_key_var";

		$return = [ 'id' => $id, 'suffix' => $suffix ];

		M()->info( "id: $id, suffix: $suffix" ); 

		return $return;
	}/*}}}*/

	function get_abs_filename() {/*{{{*/

		return implode('/', array( $this->file_pathinfo['dirname'], $this->file_pathinfo['basename'] ));

	}/*}}}*/

	function hotfix_image_params( $params ) {/*{{{*/

		unset( $params['p'] );
		isset( $params['pre'] ) and $params['p']=$params['pre'];

		return $params;
	}/*}}}*/

	function load( $file_path = null ) {/*{{{*/


		$file_path or $file_path = "{$this->http->src}";

		M()->debug( "file_path: $file_path" );

		try {

			$this->server->outputImage( $file_path, $this->hotfix_image_params( $_GET ) );

			M()->debug( "cargada la imagen $file_path" );
			$this->loaded = true;
			return $this;

		} catch ( \Exception $e ) {

			M()->error( "no ecuentro la imagen $file_path" );

			if ( ! file_exists( $err_img = 'images/warning.jpg' ) ) {
				M()->error( "no encuentro $err_img en ". getcwd() );
				return null;
				}
			else {

				$image = new \Imagick;

				$image->readImage( $err_img );
				return $this;
			}
		}

	}/*}}}*/

	function read_blob( $config, $obj ) {/*{{{*/

		extract( $config );

		/* $file_path or $file_path = "{$this->http->src}";
		M()->debug( "file_path: $file_path" ); */

		try {

			$this->server = ServerFactory::create([

				'source' => new Glade\Imageblob( $config, $obj ),
				'cache' => new Filesystem(new LocalFilesystemAdapter( $this->cache_root )),
				'driver' => 'imagick'
			]);

		} catch ( UnableToCreateDirectory $e ) {

			M()->error( "No puedo crear el directorio. Mensaje: ". $e->getMessage() );
			return null;
		}

		try {

			$this->server->outputImage( $obj->$id_key, $this->hotfix_image_params( $_GET ) );

			M()->debug( "Cargada la imagen con clave: {$obj->$id_key}" );
			$this->loaded = true;
			return $this;

		} catch ( \Exception $e ) {

			M()->error( "No ecuentro la imagen {$obj->$id_key}" );

			if ( ! file_exists( $err_img = 'images/warning.jpg' ) ) {
				M()->error( "no encuentro $err_img en ". getcwd() );
				return null;
				}
			else {

				$image = new \Imagick;

				$image->readImage( $err_img );
				return $this;
			}
		}



	}/*}}}*/

	function compress( $index ) {/*{{{*/

		$this->image->setImageCompression(\Imagick::COMPRESSION_JPEG);
		$this->image->setImageCompressionQuality($index);
		$this->image->stripImage();

	}/*}}}*/

	function output( $headers_do = true ) {/*{{{*/

		$headers_do and $this->headers();
		echo $this->image;
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

	/* compatibilidad fotoshow */

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

    function write( $file ) {/*{{{*/

        try {
            $this->image->writeImage( $file );

        } catch( Exception $e ) {

            M()->error("no puedo guardar la imagen en $file" );
        }
    }/*}}}*/

}

?>
