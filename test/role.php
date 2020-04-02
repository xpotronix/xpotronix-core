<?php


$obj = new object;

$obj->roles = ['admin','empleado'];

class object {

	function has_role2() {/*{{{*/

		$args = func_get_args();
		$arr_role = is_array( $args[0] ) ? $args[0]: $args;
		return (bool) count( array_intersect( $this->roles, $arr_role ) );

	}/*}}}*/
}

var_dump( $obj->has_role2(  ) === false );


?>
