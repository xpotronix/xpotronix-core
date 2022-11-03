<?php
/**

Codigo original de 

 * Copyright 2005, the dotProject Team.
 *
 * This file is part of dotProject and is released under the same license.
 * Check the file index.php in the top level dotproject directory for license
 * details.  If you cannot find this file, or a LICENSE or COPYING file,
 * please email the author for details.

Adaptado para xpotronix, Eduardo Spotorno

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

	function __construct() {

		$this->enforcer = new Enforcer($this->modelAndPolicyPath . '/rbac_model.conf', 
			$this->modelAndPolicyPath . '/rbac_policy.csv');

		return $this;
	}

	function checkLogin() {/*{{{*/

		return true;
	
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
