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


require_once 'lib/gacl.class.php';
require_once 'lib/gacl_api.class.php';

class xpacl extends gacl_api {

	var $user_id;

	function clean_cache() {/*{{{*/

		if ( isset( $this->Cache_Lite ) and is_object( $this->Cache_Lite ) ) {

			M()->info( 'Borrando la cache del acl ...' );
			$this->Cache_Lite->clean(); 
		}

	}/*}}}*/

	function __construct( $opts = null ) {/*{{{*/

		global $xpdoc;

		if ( !is_array( $opts ) )

                        M()->fatal('Por favor defina las opciones: no ecuentro la base de datos');

		parent::gacl_api($opts);
	}/*}}}*/

	function setUserId( $user_id = null ) {/*{{{*/

		global $xpdoc;

		if ( $user_id === null ) 
			$user_id = $xpdoc->user->user_id;

		return $this->user_id = $user_id;

	}/*}}}*/

	function checkLogin( $user_id ) {/*{{{*/

		return $this->acl_check("system", "login", "user", $user_id );

	}/*}}}*/

	function checkModule($module, $op, $user_id = null) {/*{{{*/

		if ( !$user_id ) $user_id = $this->user_id;

		$result = $this->acl_check("application", $op, "user", $user_id, "app", $module);
		M()->info( "checkModule( $module, $op, $user_id) returned $result");
		return $result;

	}/*}}}*/

	function get_module_permissions($module, $user_id = null) {/*{{{*/

		global $xpdoc;

		if ( $user_id === null ) 
			$user_id = $this->user_id;

		$result = array();

		foreach ( $this->get_aco_list() as $aco )

			$result[$aco] = $this->acl_check( "application", $aco, "user", $user_id, "app", $module );

		return $result;

	}/*}}}*/

	function checkModuleItem($module, $op, $item = null, $userid = null) {/*{{{*/

		if ( !$user_id ) $user_id = $this->user_id;

		if ( !$item) return $this->checkModule($module, $op, $userid);

		$result = $this->acl_query("application", $op, "user", $userid, $module, $item, NULL);
		// If there is no acl_id then we default back to the parent lookup

		if (! $result || ! $result['acl_id']) {
			M()->warn("checkModuleItem($module, $op, $userid) did not return a record");
			return $this->checkModule($module, $op, $userid);
		}

		M()->info("checkModuleItem($module, $op, $userid) returned {$result['allow']}");
		return $result['allow'];

	}/*}}}*/

	function checkModuleItemDenied($module, $op, $item, $user_id = null) {/*{{{*/

		/**
		 * This gets tricky and is there mainly for the compatibility layer
		 * for getDeny functions.
		 * If we get an ACL ID, and we get allow = false, then the item is
		 * actively denied.  Any other combination is a soft-deny (i.e. not
		 * strictly allowed, but not actively denied.
		 */


		if ( !$user_id ) $user_id = $this->user_id;

		$result = $this->acl_query("application", $op, "user", $user_id, $module, $item);

		return (boolean) ( $result && $result['acl_id'] && ! $result['allow']);


	}/*}}}*/

	function addLogin($login, $username) {/*{{{*/
		M()->debug( "login: $login, username: $username" );
		$res = $this->add_object("user", $username, $login, 1, 0, "aro");
		if (! $res) M()->warn("Failed to add user permission object");
		return $res;
	}/*}}}*/

	function updateLogin($login, $username) {/*{{{*/
		M()->debug( "login: $login, username: $username" );
		$id = $this->get_object_id("user", $login, "aro");
		if (! $id)
			return $this->addLogin($login, $username);
		// Check if the details have changed.
		@list ($osec, $val, $oord, $oname, $ohid) = $this->get_object_data($id, "aro");
		if ($oname != $username) {
			$res = $this->edit_object( $id, "user", $username, $login, 1, 0, "aro");
			if (! $res)
				M()->warn("Failed to change user permission object");
		}
		return $res;
	}/*}}}*/

	function deleteLogin($login) {/*{{{*/
		$id = $this->get_object_id("user", $login, "aro");
		if ($id) {
			$id = $this->del_object($id, "aro", true);
		}
		if (! $id)
			M()->warn("Failed to remove user permission object");
		return $id;
	}/*}}}*/

	function addModule($mod, $modname) {/*{{{*/

		if ( $res = $this->add_object("app", $modname, $mod, 1, 0, "axo") )
			$res = $this->addGroupItem($mod);
		else
			M()->warn("Failed to add module permission object");

		return $res;

	}/*}}}*/

	function addModuleSection($mod) {/*{{{*/
		if ( $res = $this->add_object_section(ucfirst($mod) . " Record", $mod, 0, 0, "axo") )
			return $res;
		else
			M()->warn("Failed to add module permission section");
		return $res;
	}/*}}}*/

	function addModuleItem($mod, $itemid, $itemdesc) {/*{{{*/
		$res = $this->add_object($mod, $itemdesc, $itemid, 0, 0, "axo");
		return $res;
	}/*}}}*/

	function addGroupItem($item, $group = "all", $section = "app", $type = "axo") {/*{{{*/
		if ($gid = $this->get_group_id($group, null, $type)) {
			return $this->add_group_object($gid, $section, $item, $type);
		}
		return false;
	}/*}}}*/

	function deleteModule($mod) {/*{{{*/
		$id = $this->get_object_id("app", $mod, "axo");
		if ($id) {
			$this->deleteGroupItem($mod);
			$id = $this->del_object($id, "axo", true);
		}
		if (! $id)
			M()->warn("Failed to remove module permission object");
		return $id;
	}/*}}}*/

	function deleteModuleSection($mod) {/*{{{*/
		$id = $this->get_object_section_section_id(null, $mod, "axo");
		if ($id) {
			$id = $this->del_object_section($id, "axo", true);
		}
		if (! $id)
			M()->warn("Failed to remove module permission section");
		return $id;
	}/*}}}*/

	function deleteGroupItem($item, $group = "all", $section = "app", $type = "axo") {/*{{{*/
		if ($gid = $this->get_group_id($group, null, $type)) {
			return $this->del_group_object($gid, $section, $item, $type);
		}
		return false;
	}/*}}}*/

	function isUserPermitted($userid, $module = null) {/*{{{*/
		if ($module) {
			return $this->checkModule($module, "view", $userid);
		} else {
			return $this->checkLogin($userid);
		}
	}/*}}}*/

	function getItemACLs($module, $user_id = null) {/*{{{*/

		if ( !$user_id ) $user_id = $this->user_id;

		// Grab a list of all acls that match the user/module, for which Deny permission is set.
		return $this->search_acl("application", "view", "user", $user_id, false, $module, false, false, false);
	}/*}}}*/

	function getUserACLs($user_id = null) {/*{{{*/

		if ( !$user_id ) $user_id = $this->user_id;

		return $this->search_acl("application", false, "user", $user_id, null, false, false, false, false);
	}/*}}}*/

	function getRoleACLs($role_id) {/*{{{*/
		$role = $this->getRole($role_id);
		return $this->search_acl("application", false, false, false, $role['name'], false, false, false, false);
	}/*}}}*/

	function getRole($role_id) {/*{{{*/
		$data = $this->get_group_data($role_id);
		if ($data) {
			return array('id' => $data[0],
					'parent_id' => $data[1],
					'value' => $data[2],
					'name' => $data[3],
					'lft' => $data[4],
					'rgt' => $data[5]);
		} else {
			return false;
		}
	}/*}}}*/

	function & getDeniedItems($module, $user_id = null) {/*{{{*/

		$items = array();

		if ( !$user_id ) $user_id = $this->user_id;

		$acls = $this->getItemACLs($module, $user_id);
		// If we get here we should have an array.
		if (is_array($acls)) {
			// Grab the item values
			foreach ($acls as $acl) {
				$acl_entry =& $this->get_acl($acl);
				if ($acl_entry['allow'] == false && $acl_entry['enabled'] == true && isset($acl_entry['axo'][$module]))
					foreach ($acl_entry['axo'][$module] as $id) {
						$items[] = $id;
					}
			}
		} else {
			M()->warn("getDeniedItems($module, $user_id) - no ACL's match");
		}
		M()->info("getDeniedItems($module, $user_id) returning " . count($items) . " items");
		return $items;
	}/*}}}*/

	function & getAllowedItems($module, $user_id = null) {/*{{{*/
		// This is probably redundant.

		$items = array();

		if ( !$user_id ) $user_id = $this->user_id;

		$acls = $this->getItemACLs($module, $user_id);

		if (is_array($acls)) {
			foreach ($acls as $acl) {
				$acl_entry =& $this->get_acl($acl);
				if ($acl_entry['allow'] == true && $acl_entry['enabled'] == true && isset($acl_entry['axo'][$module])) {
					foreach ($acl_entry['axo'][$module] as $id) {
						$items[] = $id;
					}
				}
			}
		} else {
			M()->warn("getAllowedItems($module, $user_id) - no ACL's match");
		}
		M()->info("getAllowedItems($module, $user_id) returning " . count($items) . " items");
		return $items;
	}/*}}}*/

	function getChildren($group_id, $group_type = 'ARO', $recurse = 'NO_RECURSE') {/*{{{*/

		// Copied from get_group_children in the parent class, this version returns
		// all of the fields, rather than just the group ids.  This makes it a bit
		// more efficient as it doesn't need the get_group_data call for each row.

		M()->info("Group_ID: $group_id Group Type: $group_type Recurse: $recurse");

		switch (strtolower(trim($group_type))) {
			case 'axo':
				$group_type = 'axo';
				$table = $this->_db_table_prefix .'axo_groups';
				break;
			default:
				$group_type = 'aro';
				$table = $this->_db_table_prefix .'aro_groups';
		}

		if (empty($group_id)) {

			M()->warn("ID ($group_id) is empty, this is required");
			return FALSE;
		}

		$q = new DBQuery($this->db);
		$q->addTable($table, 'g1');
		$q->addQuery('g1.id, g1.name, g1.value, g1.parent_id');
		$q->addOrder('g1.value');

		//FIXME-mikeb: Why is group_id in quotes?
		switch (strtoupper($recurse)) {
			case 'RECURSE':
				$q->addJoin($table, 'g2', 'g2.lft<g1.lft AND g2.rgt>g1.rgt');
				$q->addWhere('g2.id='. $group_id);
				break;
			default:
				$q->addWhere('g1.parent_id='. $group_id);
		}

		$result = array();
		$q->exec();
		while ($row = $q->fetchRow()) {
			$result[] = array(
					'id' => $row['id'],
					'name' => $row['name'],
					'value' => $row['value'],
					'parent_id' => $row['parent_id']);
		}
		$q->clear();
		return $result;
	}/*}}}*/

	function insertRole($value, $name) {/*{{{*/
		$role_parent = $this->get_group_id("role");
		$value = str_replace(" ", "_", $value);
		return $this->add_group($value, $name, $role_parent);
	}/*}}}*/

	function updateRole($id, $value, $name) {/*{{{*/
		return $this->edit_group($id, $value, $name);
	}/*}}}*/

	function deleteRole($id) {/*{{{*/
		// Delete all of the group assignments before deleting group.
		$objs = $this->get_group_objects($id);
		foreach ($objs as $section => $value) {
			$this->del_group_object($id, $section, $value);
		}
		return $this->del_group($id, false);
	}/*}}}*/

	function insertUserRole($role, $user) {/*{{{*/

		// Check to see if the user ACL exists first.
		$id = $this->get_object_id("user", $user, "aro");
		if (! $id) {
			$q = new DBQuery($this->db);
			$q->addTable('users');
			$q->addQuery('user_username');
			$q->addWhere("user_id = $user");
			$rq = $q->exec();
			if (! $rq) {
				M()->error("Cannot add role, user $user does not exist!");
				$q->clear();
				return false;
			}
			$row = $q->fetchRow();
			if ($row) {
				$this->addLogin($user, $row['user_username']);
			}
			$q->clear();
		}
		return $this->add_group_object($role, "user", $user);
	}/*}}}*/

	function deleteUserRole($role, $user) {/*{{{*/
		return $this->del_group_object($role, "user", $user);
	}/*}}}*/

	function getUserRoles( $user = null ) {/*{{{*/

		// Returns the group ids of all groups this user is mapped to.
		// Not provided in original phpGacl, but useful.

		if ( $user === null ) $user = $this->user_id;

		$id = $this->get_object_id( "user", $user, "aro" );

		M()->info( 'encontrado el usaurio '. $user .' con el _object_id el user_id: '. $id );

		$result = $this->get_group_map( $id );

		if ( !is_array( $result ) )
			$result = array();

		return $result;

	}/*}}}*/

	function getModuleList() {/*{{{*/

		// Return a list of module groups and modules that a user can
		// be permitted access to.

		$result = array();
		// First grab all the module groups.
		$parent_id = $this->get_group_id("mod", null, "axo");
		if (! $parent_id)
			M()->warn("failed to get parent for module groups");
		$groups = $this->getChildren($parent_id, "axo");
		if (is_array($groups)) {
			foreach ($groups as $group) {
				$result[] = array('id' => $group['id'], 'type' => 'grp', 'name' => $group['name'], 'value' => $group['value']);
			}
		} else {
			M()->warn("No groups available for $parent_id");
		}
		// Now the individual modules.
		$modlist = $this->get_objects_full("app", 0, "axo");
		if (is_array($modlist)) {
			foreach ($modlist as $mod) {
				$result[] = array('id' => $mod['id'], 'type' => 'mod', 'name' => $mod['name'], 'value' => $mod['value']);
			}
		}
		return $result;
	}/*}}}*/

	function getAssignableModules() {/*{{{*/

		// An assignable module is one where there is a module sub-group
		// Effectivly we just list those module in the section "modname"

		return $this->get_object_sections(null, 0, 'axo', "value not in ('sys', 'app')");
	}/*}}}*/

	function getPermissionList() {/*{{{*/
		$list = $this->get_objects_full("application", 0, "aco");
		// We only need the id and the name
		$result = array();
		if (! is_array($list))
			return $result;
		foreach ($list as $perm)
			$result[$perm['id']] = $perm['name'];
		return $result;
	}/*}}}*/

	function get_aco_list() {/*{{{*/
		$list = $this->get_objects_full("application", 0, "aco");
		// We only need the id and the value 
		$result = array();
		if (! is_array($list))
			return $result;
		foreach ($list as $perm)
			$result[$perm['id']] = $perm['value'];
		return $result;
	}/*}}}*/

	function get_group_map($id, $group_type = "ARO") {/*{{{*/

		M()->info("Assigned ID: $id Group Type: $group_type");

		switch (strtolower(trim($group_type))) {
			case 'axo':
				$group_type = 'axo';
				$table = $this->_db_table_prefix .'axo_groups';
				$map_table = $this->_db_table_prefix . 'groups_axo_map';
				$map_field = "axo_id";
				break;
			default:
				$group_type = 'aro';
				$table = $this->_db_table_prefix .'aro_groups';
				$map_table = $this->_db_table_prefix . 'groups_aro_map';
				$map_field = "aro_id";
		}

		if (empty($id)) {
			M()->warn("ID ($id) is empty, this is required");
			return FALSE;
		}

		$q = new DBQuery($this->db);
		$q->addTable($table, 'g1');
		$q->addTable( $map_table, 'g2');
		$q->addQuery('g1.id, g1.name, g1.value, g1.parent_id');
		$q->addWhere("g1.id = g2.group_id AND g2.$map_field = '$id'");
		$q->addOrder('g1.value');

		$result = array();

		M()->info( "SQL query: ". $q->prepare() );

		$q->exec();
		while ($row = $q->fetchRow()) {
			$result[] = array(
					'id' => $row['id'],
					'name' => $row['name'],
					'value' => $row['value'],
					'parent_id' => $row['parent_id']);
		}
		$q->clear();
		return $result;

	}/*}}}*/

	function get_object_full($value = null , $section_value = null, $return_hidden=1, $object_type=NULL) {/*{{{*/

		switch(strtolower(trim($object_type))) {
			case 'aco':
				$object_type = 'aco';
				$table = $this->_db_table_prefix .'aco';
				break;
			case 'aro':
				$object_type = 'aro';
				$table = $this->_db_table_prefix .'aro';
				break;
			case 'axo':
				$object_type = 'axo';
				$table = $this->_db_table_prefix .'axo';
				break;
			case 'acl':
				$object_type = 'acl';
				$table = $this->_db_table_prefix .'acl';
				break;
			default:
				M()->warn('Invalid Object Type: '. $object_type);
				return FALSE;
		}

		M()->info("Section Value: $section_value Object Type: $object_type");

		$q = new DBQuery($this->db);
		$q->addTable($table);
		$q->addQuery('id, section_value, name, value, order_value, hidden');

		if (!empty($value)) {
			$q->addWhere('value=' . $this->db->quote($value));

		}

		if (!empty($section_value)) {
			$q->addWhere('section_value='. $this->db->quote($section_value));

		}

		if ($return_hidden==0 AND $object_type != 'acl') {
			$q->addWhere('hidden=0');

		}


		$q->exec();
		$row = $q->fetchRow();
		$q->clear();

		if (!is_array($row)) {
			$this->debug_db('get_object');
			return false;
		}

		// Return Object info.
		return array(
				'id' => $row['id'],
				'section_value' => $row['section_value'],
				'name' => $row['name'],
				'value' => $row['value'],
				'order_value' => $row['order_value'],
				'hidden' => $row['hidden']
			    );
	}/*}}}*/

	function get_objects_full($section_value = NULL, $return_hidden = 1, $object_type = NULL, $limit_clause = NULL) {/*{{{*/

		/*
Purpose:	Grabs all Objects in the database, or specific to a section_value
returns format suitable for add_acl and is_conflicting_acl
		 */

		switch (strtolower(trim($object_type))) {
			case 'aco':
				$object_type = 'aco';
				$table = $this->_db_table_prefix .'aco';
				break;
			case 'aro':
				$object_type = 'aro';
				$table = $this->_db_table_prefix .'aro';
				break;
			case 'axo':
				$object_type = 'axo';
				$table = $this->_db_table_prefix .'axo';
				break;
			default:
				M()->info('Invalid Object Type: '. $object_type);
				return FALSE;
		}

		M()->info("Section Value: $section_value Object Type: $object_type");

		$q = new DBQuery($this->db);
		$q->addTable($table);
		$q->addQuery('id, section_value, name, value, order_value, hidden');

		if (!empty($section_value)) {
			$q->addWhere('section_value='. $this->db->quote($section_value));
		}

		if ($return_hidden==0) {
			$q->addWhere('hidden=0');
		}

		if (!empty($limit_clause)) {
			$q->addWhere($limit_clause);
		}

		$q->addOrder('order_value');

		$q->exec();

		$retarr = array();

		while ($row = $q->fetchRow()) {

			$retarr[] = array(
					'id' => $row['id'],
					'section_value' => $row['section_value'],
					'name' => $row['name'],
					'value' => $row['value'],
					'order_value' => $row['order_value'],
					'hidden' => $row['hidden']
					);
		}
		$q->clear();

		// Return objects
		return $retarr;
	}/*}}}*/

	function get_object_sections($section_value = NULL, $return_hidden = 1, $object_type = NULL, $limit_clause = NULL) {/*{{{*/
		switch (strtolower(trim($object_type))) {
			case 'aco':
				$object_type = 'aco';
				$table = $this->_db_table_prefix .'aco_sections';
				break;
			case 'aro':
				$object_type = 'aro';
				$table = $this->_db_table_prefix .'aro_sections';
				break;
			case 'axo':
				$object_type = 'axo';
				$table = $this->_db_table_prefix .'axo_sections';
				break;
			default:
				M()->warn('Invalid Object Type: '. $object_type);
				return FALSE;
		}

		M()->info("Section Value: $section_value Object Type: $object_type");

		// $query = 'SELECT id, value, name, order_value, hidden FROM '. $table;
		$q = new DBQuery($this->db);
		$q->addTable($table);
		$q->addQuery('id, value, name, order_value, hidden');


		if (!empty($section_value)) {
			$q->addWhere('value='. $this->db->quote($section_value));

		}

		if ($return_hidden==0) {
			$q->addWhere('hidden=0');

		}

		if (!empty($limit_clause)) {
			$q->addWhere($limit_clause);

		}

		$q->addOrder('order_value');

		$rs = $q->exec();

		/*
		   if (!is_object($rs)) {
		   $this->debug_db('get_object_sections');
		   return FALSE;
		   }
		 */

		$retarr = array();

		while ($row = $q->fetchRow()) {
			$retarr[] = array(
					'id' => $row['id'],
					'value' => $row['value'],
					'name' => $row['name'],
					'order_value' => $row['order_value'],
					'hidden' => $row['hidden']
					);
		}
		$q->clear();

		// Return objects
		return $retarr;
	}/*}}}*/

}
// vim600: fdm=marker sw=3 ts=8 ai:
?>
