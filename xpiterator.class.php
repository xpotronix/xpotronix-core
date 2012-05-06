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

class xpIterator implements Iterator { 

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

		M()->info( "obj: {$obj->class_name}, key: $key, where: $where, order: $order, can_jump: " . ( $can_jump ? 'true' : 'false' ) );

		$this->obj = $obj;
		$this->key = $key;
		$this->where = $where;
		$this->order = $order;

		$this->data = $this->obj->loadc( $this->key, $this->where, $this->order );
		$this->can_jump = $can_jump;
	} /*}}}*/

	function count() {/*{{{*/

		M()->info();
		return count( $this->data );

	}/*}}}*/

	function can_jump( $flag ) {/*{{{*/

		M()->info();
		return $this->can_jump = $flag;

	}/*}}}*/

	function rewind(){ /*{{{*/

		if ( ( is_array( $this->data ) ) and ( $new_key = reset( $this->data ) ) ) {

			M()->info( 'valid: true' );
			$this->bind( $new_key );
			$this->valid = true;
		}

		else {
			M()->info( 'valid: false' );
			$this->valid = false;
			$this->obj->reset();
		}
		
	} /*}}}*/

	function bind( $data ) {/*{{{*/

		M()->info();
		// $this->obj->reset();
		$this->obj->set_data( $data );
		$this->obj->set_primary_key();
		$this->obj->loaded = true;
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

	function next() {/*{{{*/

		M()->info();

		$new_key = next( $this->data );

		if ( $this->valid = ( $new_key !== false ) ) {

			M()->info( "valid: {$this->valid}" );

			$this->bind( $new_key );

		} else if ( $this->can_jump and $this->obj->recordset->AtLastPage() == false ) {

			$this->valid = true;

			M()->info( "jumping" );

			unset( $this->data );

			// $this->data = $this->obj->page( ++ $this->page );
			$this->data = $this->obj->loadc( $this->key, $this->where, $this->order, ++ $this->page );

			M()->info( count( $this->data ). " registros cargdos." );

			$this->rewind();

		} else {

			M()->info( "not jumping" );
			$this->reset();
		}
	} /*}}}*/

	function valid() {/*{{{*/

		M()->info( $this->valid ? 'true' : 'false' );
		return $this->valid; 

	} /*}}}*/
}

?>
