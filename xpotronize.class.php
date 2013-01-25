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

require_once 'constants.inc.php';
require_once 'xp.class.php';
require_once 'xpconfig.class.php';
require_once 'xpmessages.class.php';
require_once 'includes/misc_functions.php';

class xpotronize extends xp {

	var $argv;
	var $opts;
	var $ini;
	var $application;
	var $config ;
	var $feat ;

	var $transform = array();


	function __construct( $params = NULL ) {/*{{{*/

		global $argv;

		parent::__construct();

		$this->opts = parseParameters( array( 'f', 'a', 'd', 't', 'm', 'h' ) );

		if ( isset( $this->opts['h'] ) ) {

			print "xpotronize [project] [-fadtmh]\n";
			print "project: path del proyecto\n";
			print "m: <module> solo este modulo 'm'\n";
			print "d: (dry) mostrar que va a hacer\n";
			print "h: help\n";
			exit;
		}

		foreach( $this->opts as $key => $value )
			if ( is_numeric( $key ) ) 
				$this->argv[] = $value;

		$this->load_ini();

	}/*}}}*/

	function check_params_xpotronize() {/*{{{*/

		// uso:
		// xpotronize [project_path] --app_path <application_path> --tables_file <tables_file> --module <module> -d -f


		$this->transform['params']['xpotronix_path'] = $this->ini['paths']['lib'];

		$projects_dir = $this->ini['paths']['projects'];

		if ( count( $this->argv ) > 1 ) {
			if ( $tmp = realpath( $this->argv[1] ) )
				$project_path = $tmp;
			else if (( $tmp = realpath( implode( DS, array( $projects_dir, $this->argv[1] ) ) ) ) )
				$project_path = $tmp;
			else 
				M()->fatal( "la ruta de origen de la aplicacion {$this->argv[1]} es invalida" );
		} else if (( $tmp = getcwd() ))
			$project_path = $tmp;
		else
			M()->fatal( "la ruta de origen de la aplicacion {$this->argv[1]} es invalida" );


		$this->transform['params']['project_path'] = $project_path;

		$this->load_config( $project_path );

		$this->transform['params']['config_path'] = ( $this->ini['paths']['config'] ) ? 
			( $this->ini['paths']['config'] .'/' ) : 
			$project_path;

		$this->transform['params']['application_path'] = ( $tmp = @$this->opts['app_path'] ) ? 
			$tmp : 
			implode( DS, array( $this->ini['paths']['apps'], (string) $this->feat->application ) );

		foreach( array( 'module', 'tables_file', 'queries_file', 'ui_file', 'code_file', 'processes_file', 'database_file', 'menu_file', 'views_file', 'feat_file', 'config_file', 'license_file' ) as $var_name ) {

			if ( $tmp = @$this->opts[$var_name] )
				$this->transform['params'][$var_name] = $tmp;
		}

		// armamos los parametros en un array
		$this->transform['xsl'] = implode( DS, array(  $this->ini['paths']['lib'], 'generator', 'generator.xslt'));
		$this->transform['xml'] = implode( DS, array(  $project_path, 'config.xml' ));

	}/*}}}*/

	function check_params_xputil() {/*{{{*/

		// uso:
		// xputil [project_path] [xsl_file]


		if ( count ( $this->argv ) < 2 ) 
			M()->fatal( 'uso: xputil {command|xsl_file} [xml_file]' );

		$project_path = getcwd();
		$projects_dir = $this->ini['paths']['projects'];

		$this->transform['params']['project_path'] = $project_path;

		$command_path = implode( DS, array(  $this->ini['paths']['lib'], 'util', $this->argv[1] ) );

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

		$this->transform['xml'] = implode( DS, array( $project_path, $xml_file ) );

	}/*}}}*/

	function load_config( $project_path ) {/*{{{*/

		M()->info( 'project path: '. $this->transform['xml'] = $project_path );

		$config_file = implode( DS, array( $project_path, "config.xml" ) );
		$feat_file = implode( DS, array( $project_path, "feat.xml" ) );

		$this->config = new xpconfig( $config_file );
		$this->feat = new xpconfig( $feat_file );

		$this->application = (string) $this->feat->application;

		if ( ! $this->application ) 
			M()->fatal( "no encuentro el nombre de la aplicacion (directiva <application/> en config.xml o feat.xml)" );

	}/*}}}*/

	function transform( $to_file = NULL ) {/*{{{*/

		if ( isset( $this->opts['d'] ) ) {

			print "transform:\n";
			print_r( $this->transform );
			exit;
		}

		( @$this->config->self === true or @$this->config->self === null ) or M()->fatal( 'esta aplicacion es para ser incluida dentro de otra. No puedo transformar' );

		$out = $this->saxon_transform( $this->transform['xml'], $this->transform['xsl'], $this->transform['params'] );

		if ( $to_file ) {

			if ( $handle = fopen($to_file, "w") ) {

				fwrite($handle, $out );
				fclose($handle);

			} else M()->fatal( "No puedo crear el archivo $to_file" );

		} else print $out;


	}/*}}}*/

}

class dbdump extends xp {

	var $instance;
	var $implementation;
	var $db_name;
	var $db;
	var $dd; //data dictionary

	var $table_info = array();
	var $xml;


	function __construct( $ini ) {/*{{{*/

		$this->load_ini();

	}/*}}}*/

	function get_database_info() {/*{{{*/


		global $xpdoc;

		$this->instance = $xpdoc->config->db_instance[0];

		$this->db_name = $this->instance['name'];

		$this->implementation = (string) $this->instance->implementation;

		if ( ! ( $encoding = (string) $this->instance->encoding ) )
			$encoding = 'UTF-8';

		M()->info( "codificacion de la base de datos: $encoding" );

		$this->xml = new SimpleXMLElement( "<?xml version=\"1.0\" encoding=\"$encoding\"?><database/>" );

		M()->info( "iniciando dbdump para la base de datos {$this->db_name} con implementacion {$this->implementation}" );

		$xpdoc->init_db_instances();

		if ( ! ( $this->db = $xpdoc->db_instance() ) ) {
			M()->error( 'no puede abrir la base de datos' );
			return;
		}

		$this->dd = NewDataDictionary( $this->db );

		$this->process();

		/*

		$fn_name = "get_database_info_{$this->implementation}";

		$this->$fn_name();
	
		*/
	
		$this->close();

		return $this;

	}/*}}}*/

	function process() {/*{{{*/
		
		$this->table_info = array();

		M()->info();

		foreach( $this->dd->MetaTables() as $table_name )
			$this->table_info[$table_name] = $this->get_table_info( $table_name );

	}/*}}}*/

// get_table_info

	function get_table_info( $table ) {/*{{{*/

		$table_info = array();

		$tmp = (array) $this->dd->MetaColumns( $table );

		foreach( $tmp as $key => $data )
			$table_info['fields'][$key] = array_filter( (array) $data );

		if ( $tmp = (array) $this->dd->MetaPrimaryKeys($table) )
			$table_info['primary'] = $tmp;

		$table_info['index'] = (array) $this->dd->MetaIndexes($table);

		$rs = $this->db->Execute("SHOW CREATE VIEW `$table`");

		if ( is_object( $rs ) and !$rs->EOF ) { 

			$field = $rs->fields;
			$table_info['sql_view'] = $field['Create View'];
		}

		return $table_info;

	}/*}}}*/

// output

	function serialize() {/*{{{*/

		$this->db_name and $this->xml['database'] = $this->db_name;

		$xbase = $this->xml;

		foreach( $this->table_info as $table_name => $table_data ) {

			$xtable = $xbase->addChild('table');

			$xtable['name'] = $table_name;
			$this->db_name and $xtable['dbi'] = $this->db_name;

			if ( count( $table_data['fields'] ) > 0 ) {

				foreach( $table_data['fields'] as $field ) {
	
					$xfield = $xtable->addChild('field');

					foreach( $field as $key => $data )

						if ( is_array( $data ) )
							$xfield[$key] = implode( ',', $data );
						else
							$xfield[$key] = $data;
				}
			}

			if ( count( @$table_data['primary'] ) > 0 ) {

				foreach( $table_data['primary'] as $primary ) 

					if ( $primary ) { 
						$xprimary = $xtable->addChild('primary');
						$xprimary['name'] = $primary;
					}
			}

			if ( count( @$table_data['index'] ) > 0 ) {

				foreach( $table_data['index'] as $index => $keys ) {

					if ( count( $keys['columns'] ) ) {
						$xindex = $xtable->addChild('index', implode(',', $keys['columns'] ));
						$xindex['name'] = str_replace(' ', '_',$index );

						if ( $keys['unique'] )
							$xindex['unique'] = 1;

					} else M()->warn( "indice $index sin columnas en la tabla $table_name" );

				}
			}

			if ( count( @$table_data['sql_view'] ) > 0 )

				$xtable->addChild( 'sql_view', $table_data['sql_view']);

		}

		return $this->xml;
	}/*}}}*/

	function output( $file = null ) {/*{{{*/

		if ( $file ) {

			if ( is_bool( $file ) ) 
				M()->fatal( "el argumento -o debe estar precedido por un nombre de archivo valido" );

			if ( $handle = fopen($file, "w") ) {

				fwrite($handle, $this->serialize()->asXML() );
				fclose($handle);

			} else M()->fatal( "No puedo crear el archivo $file" );

		} else print $this->serialize()->asXML();
	}/*}}}*/

// close

	function close() {/*{{{*/

		$this->db->Close();
	}/*}}}*/

}

?>
