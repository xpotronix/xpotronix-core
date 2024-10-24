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

class Sync {

	var $obj;
	var $query;
	var $where;
	var $order;
	var $sync;

	function __construct( $obj ) {/*{{{*/

		$this->obj = $obj;
		$this->sync = $obj->metadata->sync;

		return $this;
	}/*}}}*/

	function set_query( $query ) {/*{{{*/

		$this->query = $query;
		return $this;
	}/*}}}*/

	function set_where( $where ) {/*{{{*/

		$this->where = $where;
		return $this;
	}/*}}}*/

	function set_order( $order ) {/*{{{*/

		$this->order = $order;
		return $this;
	}/*}}}*/

	function sync_create() {/*{{{*/

		global $xpdoc;

		if ( ! ( $source_table = (string) $this->sync['from'] ) ) {

			M()->user( "la clase $source_table no tiene definida la directiva de <sync/> para realizar la sincronizacion." );
			return;
		}

		$target_table = $this->obj->table_name;

		if ( $this->obj->table_exists( $target_table ) === false ) {

			M()->info( "la tabla $target_table no existe" );

			$s = $xpdoc->instance( $source_table );

			if ( !$s ) {
				M()->user( "la clase $source_table no existe" );
				return;
			}

			$sm = $s->metadata();

			$this->create_from_metadata( $target_table, $sm );

			$xpdoc->set_view('xml');

		} else M()->user( "la tabla $target_table ya existe" );

	}/*}}}*/

	function create_from_metadata( $table_name, $metadata ) {/*{{{*/

		global $xpdoc;

		$metadata['name'] = $table_name;

		$sql_create = $xpdoc->transform( 'create-table-sql', $metadata, null, 'php', false );

		/*
		echo '<pre>'; 
		print $sql_create;
		exit;
		*/

		$this->obj->execute( $sql_create );

		if ( $this->obj->table_exists( $table_name ) !== false ) 
			M()->user( "la tabla $table_name fue creada con exito" );
		else
			M()->user( "la tabla $table_name no pudo ser creada, revise los logs" );

	}/*}}}*/

	function sync_data() {/*{{{*/

		global $xpdoc;

		set_time_limit(0);

		if ( ! ( $source_table = (string) $this->sync['from'] ) ) {

			M()->user( "la clase $source_table no tiene definida la directiva de <sync/> para realizar la sincronizacion." );
			return;
		}

		$s = $xpdoc->instance( $source_table );

		if ( ! count( $s->get_primary_key() ) ) {

			M()->error( "Clase $source_table sin clave primaria, no se puede sincronizar. Agregue una clave" );
			return;
		}

		M()->user( "iniciando sincronizacion desde $source_table a {$this->obj->class_name}" );

		// DEBUG: mejor guardar el estado anterior
		
		$s->feat->load_full_query = false;
		$this->obj->feat->load_full_query = false;
		$this->obj->feat->count_rows = false;

		$this->obj->set_flag( 'check', false );
		$this->obj->set_flag( 'post_check', false );
		$this->obj->set_flag( 'validate', false );

		if ( $page_rows = (int) $this->sync['page_rows'] ) {

			M()->info( "page_rows: $page_rows" );
			$s->feat->page_rows = $page_rows;
		}

		if ( $limit = (int) $this->sync['limit'] ) {

			M()->info( "limit: $limit" );
		}

		$rs = $s->load_set( $this->query, $this->where, $this->order );

		$i = 0;
		foreach( $rs as $r ) {

			// xdebug_start_trace('/tmp/xpotronix-trace.xt');
			// M()->mem_stats( "$i:$r->ID" );

			$this->sync_obj( $r );
			( $i % $page_rows ) or M()->mem_stats( "[{$this->obj->class_name}]: procesados $i registros ..." );
			$i++;

			if ( $limit and $i >= $limit ) {

				M()->user( "alcanzado el limite $limit de registros. Fin del proceso" );
				break;
			}
			// xdebug_stop_trace();
			// exit;
		}

		unset( $rs );

		$this->obj->set_flag( 'check', true );
		$this->obj->set_flag( 'validate', true );
		$this->obj->feat->load_full_query = true;
		$s->feat->load_full_query = true;

		M()->user( "procesados $i registros.fin del proceso" );
	}/*}}}*/

	function sync_info() {/*{{{*/

		global $xpdoc;

		set_time_limit(0);

		if ( ! ( $source_table = (string) $this->sync['from'] ) ) {

			M()->user( "la clase $source_table no tiene definida la directiva de <sync/> para realizar la sincronizacion." );
			return;
		}

		$s = $xpdoc->instance( $source_table );

		if ( ! count( $s->get_primary_key() ) ) {

			M()->error( "Clase $source_table sin clave primaria, no se puede sincronizar. Agregue una clave" );
			return;
		}


		if ( $page_rows = (int) $this->sync['page_rows'] ) {

			$s->feat->page_rows = $page_rows;
		}

		if ( $limit = (int) $this->sync['limit'] ) {

			M()->info( "limit: $limit" );
		}


		$info = array();

		$info['source'] = $source_table;
		$info['target'] = $this->obj->class_name;
		$info['page_rows'] = $page_rows;
		$info['limit'] = $limit;

		$start = microtime_float();
		$rs = $s->load_set( $this->query, $this->where, $this->order );
		$stop = microtime_float();

		$info['secs_page'] = $stop - $start;
		$info['total_records'] = $s->total_records;

		$info['not_found_fields'] = array();

		foreach( $s->attr as $key => $attr ) {

			if ( $this->obj->get_attr( $key ) ) {
				// $info['fields'][$key] = true;
			} else {
				$info['fields'][$key] = false;
			}
		}

		print_r( $info ); exit;

	}/*}}}*/

 	function sync_obj( $so ) {/*{{{*/

		$this->obj->load( $key = $so->get_primary_key() ) or $this->obj->fill_primary_key();

		M()->info( "objeto: {$this->obj->class_name} clave: ". serialize( $key ) );

		if ( ! count( $so->get_primary_key() ) ) {

			M()->error( "Clase $so->class_name sin clave primaria, no se puede sincronizar. Agregue una clave" );
			return;
		}

		// var_dump( $this->obj->track_modified );
		// print_r ( $so->data );
		// print_r ( $this->obj->data );

		// foreach( $this->obj->attr as $key => $attr ) { print "$key: ". ( $attr->modified ? 'SI': 'NO' ). "\n"; }

		foreach( $so->attr as $key => $attr ) {


			if ( ! $this->obj->get_attr( $key ) ) {
				M()->debug( "no encuentro al key {$this->obj->class_name}::$key existente en $so->class_name" );
				continue;
			}

			if ( trim( $this->obj->$key ) != trim( $so->$key ) ) {

				// fechas con a;o en 3000 de PayRoll. Esto tiene que ir en una funcion aparte, por ahora lo pone nulo
				if ( ( $attr->type == 'xpdate' or $attr->type == 'xpdatetime' )
					and ( substr( $so->$key, 0, 4 ) == '3000' or substr( $so->$key, 0, 4 ) == '1900' ) ) {

						$this->obj->$key = null;

				} else if ( $attr->type == 'xpdatetime' ) {

					if ( str_replace( ' 00:00:00', '', $this->obj->$key ) != $so->$key )

						$this->obj->$key = $so->$key;
				} else {

					$this->obj->$key = $so->$key;
				}
			}

			/*

			$dattr = $this->obj->get_attr( $key );

			$sv = $so->$key;
			@$dv = $this->obj->get_attr( $key )->value;

			print "$key: $sv >> $dv ". ( $dattr->modified ? 'SI': 'NO' ). "\n"; 

			*/
		}

		// print "modified::: ". ( $this->obj->modified ? 'SI': 'NO' ). "\n";

		// $this->obj->debug_object(); exit;

		// $this->obj->replace( 'DELAYED' );
		// $this->obj->replace();

		$this->obj->store();

		// M()->fatal( "yendome" );
	}/*}}}*/

}
?>
