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
	private $attr_list = [];
	private $download_name;
	private $timestamp;
	private $delim = ['field' => ";", 'row' => "\n", 'bom' => "\xEF\xBB\xBF", 'string' => '"' ];
	// private $delim = ['field' => "", 'row' => "<br/>" ];

	private $ret = [];

	function __construct( $obj, $flags = null ) {/*{{{*/

		global $xpdoc;

		$this->obj = $obj;
		$this->flags = $flags;

		foreach ( $this->obj->attr as $key => $attr ) {

			if ( ( 	$attr->display == 'protect' or 
				$attr->display == 'ignore' or 
				$attr->display == 'sql' ) and ! $attr->is_primary_key() ) continue;

			$this->attr_list[$key] = $attr;
		}

		$date = date_create();
		$this->timestamp = $date->format ( "Ymd-His" );
		$this->download_name = $xpdoc->config->application. '-'. $xpdoc->module. '-'. $this->timestamp. '-'. $xpdoc->user->user_username. '.json';

		/* $this->tmpf = tmpfile();
		$obj->debug_object(); */

	}/*}}}*/

	function serialize ( $flags = null ) {/*{{{*/

		global $xpdoc;

                if ( ! $this->obj->persistent() ) {

                        M()->warn( "no puedo serializar el objeto virtual {$this->obj->class_name}" );
                        return 'IS_VIRTUAL';
                }

		if ( is_object( $xpdoc->perms ) and ( ! ( $this->obj->can('view') or $this->obj->can('list') ) ) ) {

			M()->info( "acceso denegado para el objeto {$this->obj->class_name}" );
			return 'ACC_DENIED';
		}

		set_time_limit(0);

		/* data */

		$tt = 0;
		$pr = $this->obj->feat->page_rows;
		$rc = $this->obj->feat->row_count ? $this->obj->feat->row_count : $pr;

		foreach ( $this->obj->load_set() as $obj ) {

			$this->ret[] = $this->serialize_row( $obj );

			// fwrite( $this->tmpf, $t );

			if ( $rc and ( ++$tt >= $rc ) ) break;
		} 


		/* para tabulator.info */

		/* last_page == 1 cuando page_rows == 0 O BIEN $last_page == 0 */
		if  ( ( $page_rows = $this->obj->feat->page_rows ) == 0 ) {
			$last_page = 1;
		}
		else {

			$last_page = floor( $this->obj->total_records / $page_rows ) + 1;
		}
		
		return ["last_page" => $last_page, 
			"page_rows" => $this->obj->feat->page_rows,
			"total_records" => (int) $this->obj->total_records, 
			"data" => $this->ret];

	}/*}}}*/

	function serialize_row( $obj = null ) {/*{{{*/

		global $xpdoc;

		/* DEBUG: fix para que las fechas no salgan en UTC
		$prev = $xpdoc->param_schema;
		$xpdoc->param_schema = null; */

		$obj or $obj = $this->obj;
		// $this->obj->debug_object();

		$ret = [];

		M()->debug( "serializando instancia objeto {$obj->class_name}" );

		$obj->prepare_data();

		foreach ( $this->attr_list as $key => $attr ) {

				$ret[$attr->name] = $attr->serialize();
				$attr->entry_help and $ret[$attr->name. '_label'] = $attr->label;
			}

		/* $xpdoc->param_schema = $prev; */

		return $ret;

	}/*}}}*/ 

}

?>
