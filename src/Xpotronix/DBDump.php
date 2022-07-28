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
	var $encoding;

	var $table_info = [];
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

		$this->encoding = (string) $this->instance->encoding or
			$this->encoding = 'UTF8';

		M()->debug( "codificacion de la base de datos: $this->encoding" );


		M()->info( "iniciando dbdump para la base de datos {$this->db_name} con implementacion {$this->implementation}" );

		$xpdoc->dbm->init();

		if ( ! ( $this->db = $xpdoc->dbm->instance() ) ) {
			M()->error( 'no puede abrir la base de datos' );
			return false;
		}

		$this->db->Execute("set names '$this->encoding'");

		$this->process();

		/*

		$fn_name = "get_database_info_{$this->implementation}";

		$this->$fn_name();
	
		*/
	
		$this->close();

		return true;

	}/*}}}*/

	function process() {/*{{{*/
		
		M()->info();

		$this->db->setFetchMode( ADODB_FETCH_ASSOC );

		$db_name = $this->db->databaseName;

		$table_sql = "SELECT COLUMN_NAME AS `name`, 
			CHARACTER_MAXIMUM_LENGTH AS max_length, 
			DATA_TYPE as type, 
			IS_NULLABLE as `null`, 
			COLUMN_KEY as `key`,
			SUBSTRING(COLUMN_TYPE,5) as `enums`,
			COLUMN_DEFAULT AS `has_default`, 
			EXTRA AS `extra`,
			NUMERIC_SCALE AS scale,
			TABLE_NAME as table_name
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = '$db_name'";


		$rs = $this->db->Execute( $table_sql );

		foreach( $rs as $row ) {

			$data = [];
			$table_name = $row['table_name'];
			$field_name = $row['name'];
			$type = $row['type'];

			foreach( $row as $key => $value ) {

				if ( $key == 'table_name' ) {
					continue;
				}

				if ( $key == 'enums' ) {
					continue;
				}

				if ( $key == 'null' ) {

					$value == 'NO' && $data['not_null'] = '1';
					continue;
				}

				if ( $key == 'scale' and $value == 0 ) continue;

				if ( $key == 'key' and $value == 'PRI' )
					$data['primary_key'] = '1';

			
				$value != '' and $data[$key] = $value;

			}

			if ( $type == 'enum' ) {

				$data['enums'] = substr( $row['enums'], 1, -1 );

			}

			$this->table_info[$table_name]['fields'][$field_name] = $data;

		}

		$primary_key_sql = "SELECT 
			k.TABLE_NAME as table_name, 
			k.COLUMN_NAME as column_name 
			FROM information_schema.table_constraints t 
			JOIN information_schema.key_column_usage k 
			USING(constraint_name,table_schema,table_name) 
			WHERE t.constraint_type='PRIMARY KEY' AND t.table_schema='$db_name'";

		$rs = $this->db->Execute( $primary_key_sql );

		foreach( $rs as $row ) {

			$data = [];
			$table_name = $row['table_name'];

			foreach( $row as $key => $value ) {

				if ( $key == 'table_name' ) {
					continue;
				}

				$value != '' and $data[$key] = $value;

			}

			$this->table_info[$table_name]['primary'][] = $data['column_name'];

		}


		$index_sql = "SELECT TABLE_NAME AS table_name, INDEX_NAME AS index_name, COLUMN_NAME AS column_name, NON_UNIQUE as non_unique 
			FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = '$db_name'";

		$rs = $this->db->Execute( $index_sql );

		foreach( $rs as $row ) {

			$table_name = $row['table_name'];
			$index_name = $row['index_name'];

			// print "table_name: $table_name, index_name: $index_name\n";

			$this->table_info[$table_name]['index'][$index_name]['columns'][] = $row['column_name'];
			$this->table_info[$table_name]['index'][$index_name]['unique'] = ( $row['non_unique'] ) ? '0': '1';

		}

		$view_data_sql = "SELECT TABLE_NAME AS table_name, VIEW_DEFINITION AS sql_view, IS_UPDATABLE AS updatable
			FROM INFORMATION_SCHEMA.VIEWS 
			WHERE TABLE_SCHEMA = '$db_name'";

		$rs = $this->db->Execute( $view_data_sql );

		foreach( $rs as $row ) {

			$table_name = $row['table_name'];

			$this->table_info[$table_name]['sql_view'] = $row['sql_view'];
			$this->table_info[$table_name]['updatable'] = ( $row['updatable'] == 'YES' ) ? '1' : '0';

			// print_r( $this->table_info[$table_name] ); exit;

		}

		/* para triggers 
		$rs = $this->db->Execute("SELECT * FROM INFORMATION_SCHEMA.TRIGGERS where EVENT_OBJECT_SCHEMA=`{$this->db_name}` AND EVENT_OBJECT_TABLE=`$table_name`");

		if ( is_object( $rs ) and !$rs->EOF ) { 

			$field = $rs->fields;
			$table_info['trigger'] = $field['ACTION_STATEMENT'];

			print_r( $table_info );exit;
		}
		 */


		return $this;

	}/*}}}*/

	/* serialize */

	function serialize() {/*{{{*/

		$this->xml = new \SimpleXMLElement( "<?xml version=\"1.0\" encoding=\"$this->encoding\"?><tables/>" );
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

			if ( isset( $table_data['updatable'] ) ) {

				$xtable->addChild( 'updatable', $table_data['updatable']);

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
