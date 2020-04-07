<?php

namespace Xpotronix;

class Params {

	private $params = array( 

		'pageParam' => 'page',
		'startParam' => 'start',
		'limitParam' => 'limit',
		'groupParam' => 'group',
		'groupDirectionParam' => 'groupDir',
		'sortParam' => 'sort',
		'filterParam' => 'filter',
		'directionParam' => 'dir',
		'idParam' => 'id',
		'cacheString' => '_dc' );

	private $result = array();

	private $obj_name;

	function __construct() {/*{{{*/

		global $xpdoc;

		$this->obj_name = $xpdoc->req_object or $this->obj_name = $xpdoc->module;

		foreach( $this->params as $param => $key ) {
			$this->result[$param] = json_decode( $xpdoc->http->$key );
		}
	}/*}}}*/

	function get() {/*{{{*/

		return $this->result;
	}/*}}}*/

	function process() {/*{{{*/

		global $xpdoc;

		M()->debug( "obj_name: $this->obj_name" );

		$obj_name = $this->obj_name;

		foreach( $this->result as $param => $value ) {

			M()->info( "$param: ". ( is_array( $value ) ? serialize( $value ) : $value ) );

			switch( $param ) {

				case 'pageParam':

					$xpdoc->pager[$obj_name]['cp'] = $value;
					break;

				case 'startParam':

					break;

				case 'limitParam':

					$xpdoc->pager[$obj_name]['pr'] = $value;
					break;

				case 'groupParam':

					M()->info( 'no implementado' );
					break;

				case 'groupDirectionParam':

					M()->info( 'no implementado' );
					break;

				case 'sortParam':

					if ( is_array( $value ) ) {

						foreach( $value as $val )
							$xpdoc->order[$obj_name][$val->property] = $val->direction;
					}

					break;

				case 'filterParam':

					if ( is_array( $value ) ) {

						foreach( $value as $val ) {

							if ( strstr( $xpdoc->controller_vars, $val->property. ';' ) ) 
								continue; 

							$xpdoc->search[$obj_name][$val->property] = $val->value;
						}
					}

					break;

				case 'directionParam':

					break;

				case 'idParam':

					M()->info( 'no implementado' );
					break;

				case 'cacheString':

					M()->info( 'no implementado' );
					break;

				default:

					M()->info( "no entiendo el parametro $param" );

			}
		}

	}/*}}}*/

}

?>
