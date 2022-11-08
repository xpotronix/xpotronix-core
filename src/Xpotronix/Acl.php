<?php
/**

Conector de casbin para xpotronix

 */

namespace Xpotronix;

use Casbin\EnforceContext;
use Casbin\Enforcer;
use Casbin\Model\Model;
use Casbin\Persist\Adapters\FileAdapter;

class Acl {

	private $user_id;
	private $username;
	private $model;
	private $enforcer;
	private $modelAndPolicyPath = "conf";

	function __construct() {/*{{{*/

		$this->enforcer = new Enforcer($this->modelAndPolicyPath . '/rbac_model.conf', 
			$this->modelAndPolicyPath . '/rbac_policy.csv');

		return $this;
	}/*}}}*/

        public function has_role() {/*{{{*/

                $arr_role = array_flatten( func_get_args() );

                return (bool) count( array_intersect( $this->enforcer->getRolesForUser( $this->username ), $arr_role ) );

        }/*}}}*/

	public function checkLogin() {/*{{{*/

		return true;
	
	}/*}}}*/

	function acl_check( $sub, $obj, $eft ) {/*{{{*/

		$ret = $this->enforcer->enforce( $this->username, $obj, $eft );

		M()->user( "args: sub: $sub, obj: $obj, eft: $eft >> result: $ret" ); 

		return $ret;

	}/*}}}*/

	function setUserId( $user_id = null ) {/*{{{*/

		global $xpdoc;

		if ( $user_id === null or $user_id === $xpdoc->user->user_id ) {

			$user_id = $xpdoc->user->user_id;
			$this->username = $xpdoc->user->user_username;
		
		} else {

			/* DEBUG: hay que resolver esto */
			M()->warn( "cambiando el id de usuario a $user_id" );
		}


		M()->info( "setUserId: $user_id" );
		return $this->user_id = $user_id;

	}/*}}}*/

	function getUserRoles( $user = null ) {/*{{{*/

		$ret = $this->enforcer->getRolesForUser( $this->username );

		M()->info( "getUserRoles: ". json_encode( $ret ) );

		return $ret;

	}/*}}}*/

	function get_module_permissions($module, $username = null) {/*{{{*/

		$res = [];

		$username or $username = $this->username;

		foreach( ['add','edit','access','list','delete','view'] as $eft ) {
		
			$res[$eft] = $this->enforcer->enforce( $username, $module, $eft );
		}

		M()->info( "user: $this->username, id: $this->user_id, module: $module, perm: ". json_encode( $res ) );

		/*
		 *
		 *
		$t = $this->enforcer->enforce('espotorno', '_empleado', 'lisst');
		echo "<Pre> $this->username: "; print_r( $t ); exit;

		$t = $this->enforcer->getAllRoles();
		$t = $this->enforcer->getRolesForUser( "espotorno" );
		$t = $this->enforcer->getAllObjects();
		*/

		return $res;

	}/*}}}*/

}
// vim600: fdm=marker sw=3 ts=8 ai:
?>
