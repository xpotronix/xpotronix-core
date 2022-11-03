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

	function has_role() {/*{{{*/

                global $xpdoc;
                return $xpdoc->has_role( func_get_args() );

        }/*}}}*/

	function checkLogin() {/*{{{*/

		return true;
	
	}/*}}}*/

	function acl_check( $subject, $object, $action ) {/*{{{*/

		M()->user( "args: subject: $subject, object: $object, action: $action" ); 

		if ( $this->has_role( "administrator" ) ) {
		
			return true;
		} else {
		
			return $this->enforcer->enforce( $this->username, $object, $action );	
		
		}

	}/*}}}*/

	function setUserId( $user_id = null ) {/*{{{*/

		global $xpdoc;

		if ( $user_id === null ) 
			$user_id = $xpdoc->user->user_id;

		$this->username = $xpdoc->user->user_username;

		return $this->user_id = $user_id;

	}/*}}}*/

	function getUserRoles( $user = null ) {/*{{{*/

		$ret = $this->enforcer->getRolesForUser( $this->username );

		M()->info( "getUserRoles: ". json_encode( $ret ) );

		return $ret;

	}/*}}}*/

	function get_module_permissions($module, $user_id = null) {/*{{{*/

		$res = [];


		if ( in_array( 'administrator', $this->enforcer->getRolesForUser( $this->username ) ) ) {
		
			$res = ['add'=>true,'edit'=>true,'access'=>true,'list'=>true,'delete'=>true,'view'=>true];

		
		} else {
		
			foreach( $t = $this->enforcer->getPermissionsForUser( $this->username ) as $perm ) {

				$res[$perm[2]] = true;
			}
		}


		/*

		$t = $this->enforcer->getAllRoles();
		$t = $this->enforcer->getPermissionsForUser( "administrator" );
		$t = $this->enforcer->getRolesForUser( "espotorno" );
		$t = $this->enforcer->getAllObjects();

		echo "<Pre> $this->username: "; print_r( $t ); exit;

		 */

		return $res;

	}/*}}}*/

}
// vim600: fdm=marker sw=3 ts=8 ai:
?>
