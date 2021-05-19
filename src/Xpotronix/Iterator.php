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

class Iterator implements \Iterator { 

	private $obj;
	private $data = array();

	private $page = 1;
	private $offset;

	private $key;
	private $where;
	private $order;

	private $can_jump = true;

	private $valid = false; 

	function __construct( $obj, $key, $where, $order, $can_jump = false ) {/*{{{*/

		M()->debug( "obj: {$obj->class_name}, key: ". serialize( $key ). ", where: ". serialize( $where ). ", order: ". serialize( $order ). ", can_jump: " . ( $can_jump ? 'true' : 'false' ) );

		$this->obj = $obj;
		$this->key = $key;
		$this->where = $where;
		$this->order = $order;

		$this->obj->reset();
		$this->data = $this->obj->loadc( $this->key, $this->where, $this->order );
		$this->can_jump = $can_jump;
	} /*}}}*/

	function count() {/*{{{*/

		M()->info();
		if ( isset( $this->data ) )
			return count( $this->data );
		else
			return null;

	}/*}}}*/

	function get_data() {/*{{{*/

		return $this->data;

	}/*}}}*/

	function can_jump( $flag ) {/*{{{*/

		M()->info();
		return $this->can_jump = $flag;

	}/*}}}*/

	function rewind(){ /*{{{*/

		is_array( $this->data ) and M()->debug( "count data: ". count( $this->data ) );

		if ( ( is_array( $this->data ) ) and ( $new_key = reset( $this->data ) ) ) {

			M()->debug( 'valid: true' );
			$this->bind( $new_key );
			$this->valid = true;
		}

		else {
			M()->debug( 'valid: false' );
			$this->valid = false;
			$this->obj->reset();
		}
		
	} /*}}}*/

	function bind( $data ) {/*{{{*/

		M()->info();
		// $this->obj->reset();
		$this->obj->set_data( $data );
		$this->obj->set_primary_key();
		$this->obj->loaded( true );
	}/*}}}*/

	function reset() {/*{{{*/

		M()->info();
		$this->obj->reset();
	}/*}}}*/ 

	function current() {/*{{{*/

		M()->info();
		$this->bind( current( $this->data ) );
		return $this->obj;

	} /*}}}*/ 

	function key() {/*{{{*/

		M()->info();
		return key($this->data); 
	} /*}}}*/

	function index( $pos = 0 ) {/*{{{*/

		$count = $this->count();

		if ( $pos >= $count ) 
			return null;

		$this->rewind();

		for ( $i = 0; $i <= $pos; $i++ )
			$this->next();

		return $this->obj;

	}/*}}}*/

	function rand() {/*{{{*/

		$count = $this->count();

		if ( $count == 0 ) return null;
		else if ( $count == 1 ) return $this->current();
		else return $this->index( mt_rand(0, $this->count() -1 ) );

	}/*}}}*/

	function next() {/*{{{*/

		$new_key = next( $this->data );

		if ( $this->valid = ( $new_key !== false ) ) {

			M()->debug( "valid: {$this->valid}" );

			$this->bind( $new_key );

		} else if ( $this->can_jump and $this->last_page() == false ) {

			$this->valid = true;

			M()->debug( "jumping" );

			unset( $this->data );

			// $this->data = $this->obj->page( ++ $this->page );
			$this->data = $this->obj->loadc( $this->key, $this->where, $this->order, ++ $this->page );

			M()->debug( count( $this->data ). " registros cargados." );

			$this->rewind();

		} else {

			M()->debug( "not jumping" );
			$this->reset();
		}
	} /*}}}*/

	function valid() {/*{{{*/

		M()->debug( $this->valid ? 'true' : 'false' );
		return $this->valid; 

	} /*}}}*/

	function debug() {/*{{{*/
		print_r( object_to_array( $this ) );
	}/*}}}*/

	function last_page() {

		return $this->obj->last_page;
	}
}

?>
