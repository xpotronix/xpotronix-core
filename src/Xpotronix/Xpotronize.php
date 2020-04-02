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

class Xpotronize extends Base {

	var $argv;
	var $opts;
	var $ini;
	var $application;
	var $config ;
	var $feat ;
	var $transform = array();

	const DS = '/';

	function __construct( $params = NULL ) {/*{{{*/

		global $argv;

		parent::__construct();

		$this->opts = parseParameters( array( 'f', 'a', 'd', 't', 'm', 'h' ) );

		if ( isset( $this->opts['h'] ) ) {

			print "xpotronize [project <project_path>] [config_file <config_file>] [feat_file <feat_file>][-fadtmh]\n";
			print "project: path del proyecto\n";
			print "m: <module> solo este modulo 'm'\n";
			print "d: (dry) mostrar que va a hacer\n";
			print "h: help\n";
			exit;
		}

		foreach( $this->opts as $key => $value )
			if ( is_numeric( $key ) ) 
				$this->argv[] = $value;

		M()->info( "opts: " . serialize( $this->opts ) );

		$this->load_ini();

	}/*}}}*/

	function get_absolute_path( $file_name ) {/*{{{*/


		$path_info = pathinfo( $file_name );

		if ( ! $path_info['dirname'] )
			$file_name = implode( self::DS, getcwd(), $filename );

		return realpath( $file_name );
	}/*}}}*/

	function check_params_xpotronize() {/*{{{*/

		// uso:
		// xpotronize [project_path] --app_path <application_path> --tables_file <tables_file> --module <module> -d -f


		M()->info( "xpotronix_path: ". $this->transform['params']['xpotronix_path'] = $this->ini['paths']['lib'] );
		
		M()->info( "projects_dir: ". $projects_dir = $this->ini['paths']['projects'] );

		if ( count( $this->argv ) > 1 ) {

			if ( $tmp = realpath( $this->argv[1] ) )
				$project_path = $tmp;
			else if (( $tmp = realpath( implode( self::DS, array( $projects_dir, $this->argv[1] ) ) ) ) )
				$project_path = $tmp;
			else 
				M()->fatal( "la ruta de origen de la aplicacion {$this->argv[1]} es invalida" );

		} else if (( $tmp = getcwd() ))
			$project_path = $tmp;
		else
			M()->fatal( "la ruta de origen de la aplicacion {$this->argv[1]} es invalida" );

		M()->info( "project_path: ". $this->transform['params']['project_path'] = $project_path );

		@$config_file = $this->opts['config_file'] or $config_file = 'config.xml';
		$config_file = $this->get_absolute_path( $config_file );
		$this->transform['params']['config_file'] = $config_file;
		M()->info( "config_file: $config_file" );

		@$feat_file = $this->opts['feat_file'] or $feat_file = 'feat.xml';
		$feat_file = $this->get_absolute_path( $feat_file );
		M()->info( "feat_file: $feat_file" );
		$this->transform['params']['feat_file'] = $feat_file;

		$this->load_config_feat( $config_file, $feat_file );

		M()->info( "config_path: ".
			$this->transform['params']['config_path'] = realpath( ( $this->ini['paths']['config'] ) ? 
				( $this->ini['paths']['config'] .'/' ) : 
				$project_path )
		);


		M()->info( "ini.paths.apps: ". $this->ini['paths']['apps'] );
		M()->info( "opts.app_path: ". @$this->opts['app_path'] );
		M()->info( "feat.application: ". $this->feat->application );

		$p =& $this->transform['params']['application_path'];
		$app_path = ( isset( $this->opts['app_path'] ) ) ? $this->opts['app_path'] : '';

		$ini_paths_app = $this->ini['paths']['apps'];

		$p = ( $app_path ) ? 
			implode( self::DS, array( $ini_paths_app, $app_path ) ) : 
			implode( self::DS, array( $ini_paths_app, (string) $this->feat->application ) );

		M()->info( "application_path: $p" );

		isset( $this->opts['module'] ) and $this->transform['params']['module'] = $this->opts['module'];

		/* armo los parametros en un array */

		M()->info( "xsl: ". $this->transform['xsl'] = realpath( implode( self::DS, array(  $this->ini['paths']['lib'], 'generator', 'generator.xslt'))));
		M()->info( "xml: ". $this->transform['xml'] = $config_file );

	}/*}}}*/

	function check_params_xputil() {/*{{{*/

		// uso:
		// xputil [project_path] [xsl_file]


		if ( count ( $this->argv ) < 2 ) 
			M()->fatal( 'uso: xputil {command|xsl_file} [xml_file]' );

		$project_path = getcwd();
		$projects_dir = $this->ini['paths']['projects'];

		$this->transform['params']['project_path'] = $project_path;

		$command_path = implode( self::DS, array(  $this->ini['paths']['lib'], 'util', $this->argv[1] ) );

		if ( file_exists( $tmp = $this->argv[1] ) )
			$xsl_file = $tmp;
		else if ( file_exists( $tmp = $command_path. '.xsl' ) )
			$xsl_file = $tmp;
		else if ( file_exists( $tmp = $command_path. '.xslt' ) )
			$xsl_file = $tmp;
		else	M()->fatal( "no es un archivo ni un comando valido ". $this->argv[1] );

		$this->transform['xsl'] = $xsl_file;

		$xml_file = ( count( $this->argv ) > 2 ) ? 
			$this->argv[2] : 
			'tables.xml';

		$this->transform['xml'] = implode( self::DS, array( $project_path, $xml_file ) );

	}/*}}}*/

	function load_config_feat( $config_file, $feat_file ) {/*{{{*/

		M()->info( 'config_file: '. $config_file );
		M()->info( 'feat_file: '  . $feat_file );

		$this->config = new Config( $config_file );
		$this->feat   = new Config( $feat_file );

		( $this->application = (string) $this->feat->application ) or
			M()->fatal( "no encuentro el nombre de la aplicacion (directiva <application/> en feat.xml)" );

	}/*}}}*/

	function transform( $to_file = NULL ) {/*{{{*/

		if ( isset( $this->opts['d'] ) ) {

			print "transform:\n";
			print_r( $this->transform );
			exit;
		}

		( @$this->config->self === true or @$this->config->self === null ) or M()->fatal( 'esta aplicacion es para ser incluida dentro de otra. No puedo transformar' );

		/* $out = $this->saxon_transform( $this->transform['xml'], $this->transform['xsl'], $this->transform['params'] ); */
		$out = $this->saxon_bridge_transform( $this->transform['xml'], $this->transform['xsl'], $this->transform['params'] );

		if ( $to_file ) {

			if ( $handle = fopen($to_file, "w") ) {

				fwrite($handle, $out );
				fclose($handle);

			} else M()->fatal( "No puedo crear el archivo $to_file" );

		} else print $out;


	}/*}}}*/

}

?>
