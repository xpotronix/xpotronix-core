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

class DBDump extends Base {

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

		/* instancia en XML del config */

		$this->instance = $xpdoc->config->db_instance[0];

		M()->debug( "instancia: ". $this->db_name = (string) $this->instance['name'] );
		M()->debug( "implementation: ". $this->implementation = (string) $this->instance->implementation );

		$encoding = (string) $this->instance->encoding or
			$encoding = 'UTF8';

		M()->debug( "codificacion de la base de datos: $encoding" );

		$this->xml = new \SimpleXMLElement( "<?xml version=\"1.0\" encoding=\"$encoding\"?><database/>" );

		M()->info( "iniciando dbdump para la base de datos {$this->db_name} con implementacion {$this->implementation}" );

		$xpdoc->dbm->init();

		if ( ! ( $this->db = $xpdoc->dbm->instance() ) ) {
			M()->error( 'no puede abrir la base de datos' );
			return false;
		}

		$this->db->Execute("set names '$encoding'");

		$this->dd = NewDataDictionary( $this->db );

		$this->process();

		/*

		$fn_name = "get_database_info_{$this->implementation}";

		$this->$fn_name();
	
		*/
	
		$this->close();

		return true;

	}/*}}}*/

	function process() {/*{{{*/
		
		$this->table_info = array();

		M()->info();

		foreach( $this->dd->MetaTables() as $table_name )
			$this->table_info[$table_name] = $this->get_table_info( $table_name );

	}/*}}}*/

	/* get_table_info */

	function get_table_info( $table_name ) {/*{{{*/

		$table_info = array();

		$tmp = (array) $this->dd->MetaColumns( $table_name );

		foreach( $tmp as $field_name => $field_data )
			$table_info['fields'][$field_name] = array_filter( (array) $field_data );

		if ( $tmp = (array) $this->dd->MetaPrimaryKeys($table_name) )
			$table_info['primary'] = $tmp;

		$table_info['index'] = (array) $this->dd->MetaIndexes($table_name);

		/*
		if ( $table_name == 'audit' ) {
			print_r( $table_info ); exit;
		}
		*/

		$rs = $this->db->Execute("SHOW CREATE VIEW `$table_name`");

		if ( is_object( $rs ) and !$rs->EOF ) { 

			$field = $rs->fields;
			$table_info['sql_view'] = $field['Create View'];
		}


		/* para triggers 
		$rs = $this->db->Execute("SELECT * FROM INFORMATION_SCHEMA.TRIGGERS where EVENT_OBJECT_SCHEMA=`{$this->db_name}` AND EVENT_OBJECT_TABLE=`$table_name`");

		if ( is_object( $rs ) and !$rs->EOF ) { 

			$field = $rs->fields;
			$table_info['trigger'] = $field['ACTION_STATEMENT'];

			print_r( $table_info );exit;
		}
		*/

		return $table_info;

	}/*}}}*/

	/* serialize */

	function serialize() {/*{{{*/

		$this->db_name and $this->xml['database'] = $this->db_name;

		$xbase = $this->xml;

		foreach( $this->table_info as $table_name => $table_data ) {

			$xtable = $xbase->addChild('table');

			/*
			if ( $table_name == 'audit' ) {
				print_r( $table_data ); exit;
			}
			*/

			$xtable['name'] = $table_name;
			$this->db_name and $xtable['dbi'] = $this->db_name;

			if ( isset( $table_data['fields'] ) ) {

				foreach( $table_data['fields'] as $field ) {
	
					$xfield = $xtable->addChild('field');

					foreach( $field as $key => $data ) {

						// M()->user( serialize( $data ) );

						if ( is_array( $data ) )
							$xfield[$key] = implode( ',', $data );
						else
							$xfield[$key] = $data;
					}
				}
			}

			if ( isset( $table_data['primary'] ) ) {

				foreach( $table_data['primary'] as $primary ) 

					if ( $primary ) { 
						$xprimary = $xtable->addChild('primary');
						$xprimary['name'] = $primary;
					}
			}

			if ( isset( $table_data['index'] ) ) {

				foreach( $table_data['index'] as $index => $keys ) {

					if ( isset( $keys['columns'] ) ) {
						$xindex = $xtable->addChild('index', implode(',', $keys['columns'] ));
						$xindex['name'] = str_replace(' ', '_',$index );

						if ( $keys['unique'] )
							$xindex['unique'] = 1;

					} else M()->warn( "indice $index sin columnas en la tabla $table_name" );

				}
			}

			if ( isset( $table_data['sql_view'] ) ) {

				$xtable->addChild( 'sql_view', $table_data['sql_view']);

			}

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

	/* close */

	function close() {/*{{{*/

		$this->db->Close();
	}/*}}}*/

}

?>
