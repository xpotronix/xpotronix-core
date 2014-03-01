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



class xpcsv {

	private $obj;
	private $flags;
	private $tmpf;
	private $attr_list = array();
	private $download_name;
	private $timestamp;
	private $delim = array( 'field' => ";", 'row' => "\n", 'bom' => "\xEF\xBB\xBF" );
	// private $delim = array( 'field' => "", 'row' => "<br/>" );

	function __construct( $obj, $flags = null ) {/*{{{*/

		global $xpdoc;

		$this->obj = $obj;
		$this->flags = $flags;

		foreach ( $this->obj->attr as $key => $attr ) {

			if ( $attr->display == 'protect' or $attr->display == 'ignore' or $attr->display == 'sql' or $attr->display == 'hide' ) continue;
			$this->attr_list[$key] = $attr;
		}

		$date = date_create();

		$this->timestamp = $date->format ( "Ymd-His" );

		$this->download_name = $xpdoc->feat->application. '-'. $xpdoc->module. '-'. $this->timestamp. '-'. $xpdoc->user->user_username. '.csv';

		// $this->tmpf = tmpfile();

	}/*}}}*/

	function header_do() {/*{{{*/

		/* output aca */

		header('Content-Encoding: UTF-8');
		header('Content-type: text/csv; charset=UTF-8');
		header("Content-Disposition: attachment; filename={$this->download_name}");
		echo $this->delim['bom'];			

	}/*}}}*/

	function serialize ( $flags = null ) {/*{{{*/

		global $xpdoc;

		if ( is_object( $xpdoc->perms ) and ( ! ( $this->obj->can('list') and $this->obj->can('view') ) ) ) {

			M()->info( "acceso denegado para el objeto {$this->obj->class_name}" );
			return 'ACC_DENIED';
		}

                if ( $this->obj->is_virtual() and ! $this->obj->count_views() ) {

                        M()->warn( "no puedo serializar el objeto virtual {$this->obj->class_name}, count_views: ". $this->obj->count_views() );
                        return 'IS_VIRTUAL';
                }


		$this->header_do();

		echo $this->serialize_names();

		// echo "<br/>";

		// fwrite( $this->tmpf, $t );

		/* data */

		$tt = 0;
		$pr = $this->obj->feat->page_rows;
		$rc = $this->obj->feat->row_count ? $this->obj->feat->row_count : $pr;

		foreach ( $this->obj->load_set() as $obj ) {

			$this->obj->prepare_data( $obj );
			$t = $this->serialize_row();

			echo $t;

			// fwrite( $this->tmpf, $t );

			if ( $rc and ( ++$tt >= $rc ) ) break;
		} 

		/*
		rewind( $this->tmpf );
		fpassthru( $this->tmpf );
		fclose( $this->tmpf );
		*/

	}/*}}}*/

	function serialize_names() {/*{{{*/

		global $xpdoc;

		// $this->obj->debug_object();

		$ret = array();

		M()->debug( "serializando instancia objeto {$this->obj->class_name}" );

		foreach ( $this->attr_list as $key => $attr ) 

			$ret[] = $this->serialize_name( $attr );

		return join( $this->delim['field'], $ret ). $this->delim['row'];

	}/*}}}*/ 

	function serialize_name( $attr ) {/*{{{*/

		$ret = array();

		$ret[] = $attr->name;

		$attr->entry_help and $ret[] = $attr->name. '_label';

		return join( $this->delim['field'], $ret );
	}/*}}}*/

	function serialize_row() {/*{{{*/

		global $xpdoc;

		// $this->obj->debug_object();

		$ret = array();

		M()->debug( "serializando instancia objeto {$this->obj->class_name}" );

		foreach ( $this->attr_list as $key => $attr ) 

			$ret[] = $this->serialize_attr( $attr );

		return join( $this->delim['field'], $ret ). $this->delim['row'];

	}/*}}}*/ 

	function serialize_attr( $attr ) {/*{{{*/

		$ret = array();

		$ret[] = $attr->serialize();

		$attr->entry_help and $ret[] = $attr->label;

		return join( $this->delim['field'], $ret );
	}/*}}}*/

}

?>
