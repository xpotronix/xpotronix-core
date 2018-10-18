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



class xpjson {

	private $obj;
	private $flags;
	private $tmpf;
	private $attr_list = array();
	private $download_name;
	private $timestamp;
	private $delim = ['field' => ";", 'row' => "\n", 'bom' => "\xEF\xBB\xBF", 'string' => '"' ];
	// private $delim = array( 'field' => "", 'row' => "<br/>" );

	private $ret = [];

	function __construct( $obj, $flags = null ) {/*{{{*/

		global $xpdoc;

		$this->obj = $obj;
		$this->flags = $flags;

		foreach ( $this->obj->attr as $key => $attr ) {

			if ( ( 	$attr->display == 'protect' or 
				$attr->display == 'ignore' or 
				$attr->display == 'sql' or 
				$attr->display == 'hide' ) and ! $attr->is_primary_key() ) continue;

			$this->attr_list[$key] = $attr;
		}

		$date = date_create();

		$this->timestamp = $date->format ( "Ymd-His" );

		$this->download_name = $xpdoc->config->application. '-'. $xpdoc->module. '-'. $this->timestamp. '-'. $xpdoc->user->user_username. '.json';

		// $this->tmpf = tmpfile();

		// $obj->debug_object();

	}/*}}}*/

	function serialize ( $flags = null ) {/*{{{*/

		global $xpdoc;

                if ( ! $this->obj->persistent() ) {

                        M()->warn( "no puedo serializar el objeto virtual {$this->obj->class_name}" );
                        return 'IS_VIRTUAL';
                }

		if ( is_object( $xpdoc->perms ) and ( ! ( $this->obj->can('list') and $this->obj->can('view') ) ) ) {

			M()->info( "acceso denegado para el objeto {$this->obj->class_name}" );
			return 'ACC_DENIED';
		}

		set_time_limit(0);

		/* data */

		$tt = 0;
		$pr = $this->obj->feat->page_rows;
		$rc = $this->obj->feat->row_count ? $this->obj->feat->row_count : $pr;

		foreach ( $this->obj->load_set() as $obj ) {

			$this->obj->prepare_data( $obj );
			$this->ret[] = $this->serialize_row();

			// fwrite( $this->tmpf, $t );

			if ( $rc and ( ++$tt >= $rc ) ) break;
		} 

		$last_page = ( $this->obj->feat->page_rows !== 0 ) ?
			round( $this->obj->total_records / $this->obj->feat->page_rows ) + 1:
			1;

		return ["last_page" => $last_page, 
			"page_rows" => $this->obj->feat->page_rows,
			"total_records" => (int) $this->obj->total_records, 
			"data" => $this->ret];

	}/*}}}*/

	function serialize_row() {/*{{{*/

		global $xpdoc;

		// $this->obj->debug_object();

		$ret = array();

		M()->debug( "serializando instancia objeto {$this->obj->class_name}" );

		foreach ( $this->attr_list as $key => $attr ) {

				$ret[$attr->name] = $attr->serialize();
				$attr->entry_help and $ret[$attr->name. '_label'] = $attr->label;
			}

		return $ret;

	}/*}}}*/ 

}

?>
