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

		$db_name = $this->db->query('select database()')->fetchColumn();

		M()->user( "database name: $db_name" );

		/* todas los campos juntos de todas las tablas */

/*
		           TABLE_CATALOG: def
            TABLE_SCHEMA: xpay
              TABLE_NAME: _t_licencia
             COLUMN_NAME: cuenta
        ORDINAL_POSITION: 24
          COLUMN_DEFAULT: NULL
             IS_NULLABLE: YES
               DATA_TYPE: int
CHARACTER_MAXIMUM_LENGTH: NULL
  CHARACTER_OCTET_LENGTH: NULL
       NUMERIC_PRECISION: 10
           NUMERIC_SCALE: 0
      DATETIME_PRECISION: NULL
      CHARACTER_SET_NAME: NULL
          COLLATION_NAME: NULL
             COLUMN_TYPE: int
              COLUMN_KEY: MUL
                   EXTRA:
              PRIVILEGES: select,insert,update,references
          COLUMN_COMMENT:
		GENERATION_EXPRESSION:
 */

		$table_sql = "SELECT COLUMN_NAME AS `name`, 
			CHARACTER_MAXIMUM_LENGTH AS max_length, 
			IS_NULLABLE as `nullable`, 
			COLUMN_TYPE as column_type, 
			DATA_TYPE as type, 
			COLUMN_KEY as `key`,
			SUBSTRING(COLUMN_TYPE,5) as `enums`,
			COLUMN_DEFAULT AS `column_default`, 
			EXTRA AS `extra`,
			NUMERIC_PRECISION AS `precision`,
			NUMERIC_SCALE AS scale,
			COLUMN_COMMENT AS comment,
			TABLE_NAME as table_name
			FROM INFORMATION_SCHEMA.COLUMNS
			WHERE TABLE_SCHEMA = '$db_name'
			ORDER BY ORDINAL_POSITION";


		$rs = $this->db->Execute( $table_sql );

		foreach( $rs as $row ) {

			// print_r ( $row );

			$data = [];
			$table_name = $row['table_name'];
			$field_name = $row['name'];
			$type = $row['type'];

			/* procesa atributos */

			foreach( $row as $key => $value ) {

				switch( $key ) {

					case 'column_type':
					case 'table_name':
						continue 2;

					case 'type': 
	
						if ( $row['column_type'] == 'bigint unsigned' ) {
							$value = 'bigint unsigned';
						}

						$data['type'] = $value;
						break;

					case 'nullable':
						if ( $value == 'NO' ) {
							$data['not_null'] = '1';
						}  
						continue 2;

					case 'key':
						if ( $value == 'PRI' )
							$data['primary_key'] = '1';
						break;

					case 'extra': 
						if( str_contains( $value, 'auto_increment' ) )
							$data['auto_increment'] = '1';
						break;

					case 'column_default':

						if ( $value !== null ) {
							$data['has_default'] = '1';
							$data['default_value'] = ( $value === '' ) ? '' : $value ;
						}
						continue 2;

					case 'enums':
						if ( $type != 'enum' )
							continue 2;
						else 
							$value = substr( $value, 1, strlen($value) -2 );
						break;

					case 'precision':
						if ( false and in_array( $type, ['int', 'tinyint', 'bigint', 'integer'] ) )
							continue 2;
						break;

					case 'scale':
						if ( $value == 0 )
							continue 2;

						break;

				}

				$value != null and $data[$key] = $value;

			}


			$this->table_info[$table_name]['fields'][$field_name] = $data;

		}

		/* primary key */

		$primary_key_sql = "SELECT 
			k.TABLE_NAME as table_name, 
			k.COLUMN_NAME as column_name 
			FROM information_schema.table_constraints t 
			JOIN information_schema.key_column_usage k 
			USING(constraint_name,table_schema,table_name) 
			WHERE t.constraint_type='PRIMARY KEY' AND t.table_schema='$db_name'
			ORDER BY k.ORDINAL_POSITION";

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

		/* index */

		$index_sql = "SELECT TABLE_NAME AS table_name, INDEX_NAME AS index_name, COLUMN_NAME AS column_name, NON_UNIQUE as non_unique, SEQ_IN_INDEX as seq_in_index, SUB_PART as sub_part
			FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = '$db_name' ORDER BY SEQ_IN_INDEX ASC";

		$rs = $this->db->Execute( $index_sql );

		foreach( $rs as $row ) {

			$table_name = $row['table_name'];
			$index_name = $row['index_name'];

			// print "table_name: $table_name, index_name: $index_name\n";
			//
			$sub_part = $row['sub_part'] ? '('. $row['sub_part']. ')' : '';

			$this->table_info[$table_name]['index'][$index_name]['columns'][] = $row['column_name']. $sub_part;
			$this->table_info[$table_name]['index'][$index_name]['unique'] = ( $row['non_unique'] ) ? '0': '1';

		}

		/* views */

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

		/* triggers */

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

		$this->db = null;

	}/*}}}*/

}

?>
