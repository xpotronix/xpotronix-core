<?php
// $Id: gacl.class.php 422 2006-09-03 22:52:20Z ipso $

/**
 * phpGACL - Generic Access Control List
 * Copyright (C) 2002,2003 Mike Benoit
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please join the
 * phpGACL mailing list. http://sourceforge.net/mail/?group_id=57103
 *
 * You may contact the author of phpGACL by e-mail at:
 * ipso@snappymail.ca
 *
 * The latest version of phpGACL can be obtained from:
 * http://phpgacl.sourceforge.net/
 *
 * @package phpGACL
 */

namespace Xpotronix;

/**
* phpGACL main class
*
* Class gacl should be used in applications where only querying the phpGACL
* database is required.
*
* @package phpGACL
* @author Mike Benoit <ipso@snappymail.ca>
*/
class Gacl {
	/*
	--- phpGACL Configuration path/file ---
	*/
	var $config_file = './gacl.ini.php';

	/*
	--- Private properties ---
	*/
	/** @var boolean Enables Debug output if true */
	var $_debug = FALSE;

	/*
	--- Database configuration. ---
	*/
	/** @var string Prefix for all the phpgacl tables in the database */
	var $_db_table_prefix = '';

	/** @var string The database type, based on available ADODB connectors - mysql, postgres7, sybase, oci8po See here for more: http://php.weblogs.com/adodb_manual#driverguide */
	var $_db_type = 'mysql';

	/** @var string The database server */
	var $_db_host = 'localhost';

	/** @var string The database user name */
	var $_db_user = 'root';

	/** @var string The database user password */
	var $_db_password = '';

	/** @var string The database name */
	var $_db_name = 'gacl';

	/** @var object An ADODB database connector object */
	var $_db = '';

	/*
	 * NOTE: 	This cache must be manually cleaned each time ACL's are modified.
	 * 		Alternatively you could wait for the cache to expire.
	 */

	/** @var boolean Caches queries if true */
	var $_caching = FALSE;

	/** @var boolean Force cache to expire */
	var $_force_cache_expire = TRUE;

	/** @var string The directory for cache file to eb written (ensure write permission are set) */
	var $_cache_dir = '/tmp/phpgacl_cache'; // NO trailing slash

	/** @var int The time for the cache to expire in seconds - 600 == Ten Minutes */
	var $_cache_expire_time=600;

	/** @var string A switch to put acl_check into '_group_' mode */
	var $_group_switch = '_group_';

	/**
	 * Constructor
	 * @param array An arry of options to oeverride the class defaults
	 */

	function __construct($options = NULL) {/*{{{*/

		$available_options = array('db','debug','items_per_page','max_select_box_items','max_search_return_items','db_table_prefix','db_type','db_host','db_user','db_password','db_name','caching','force_cache_expire','cache_dir','cache_expire_time');

		//Values supplied in $options array overwrite those in the config file.
		if ( file_exists($this->config_file) ) {
		        $config = parse_ini_file($this->config_file);

		        if ( is_array($config) ) {
		                $gacl_options = array_merge($config, $options);
		        }

	        	unset($config);
		}

		if (is_array($options)) {
			foreach ($options as $key => $value) {
				M()->debug("Option: $key");

				if (in_array($key, $available_options) ) {
					M()->debug("Valid Config options: $key");
					$property = '_'.$key;
					$this->$property = $value;
				} else {
					M()->debug("ERROR: Config option: $key is not a valid option");
				}
			}
		}

		if (is_object($this->_db)) {
			$this->db = &$this->_db;
		} else {

			// DEBUG: esto por ahora no se usa por que comparte la sesion con xpotronix

			$this->db = ADONewConnection($this->_db_type);
			//Use NUM for slight performance/memory reasons.
			$this->db->SetFetchMode(ADODB_FETCH_NUM);
			$this->db->PConnect($this->_db_host, $this->_db_user, $this->_db_password, $this->_db_name);
		}
		$this->db->debug = $this->_debug;

		if ( $this->_caching == TRUE ) {

			/*
			 * Cache options. We default to the highest performance. If you run in to cache corruption problems,
			 * Change all the 'false' to 'true', this will slow things down slightly however.
			 */

			$cache_options = array(
				'caching' => $this->_caching,
				'cacheDir' => $this->_cache_dir.'/',
				'lifeTime' => $this->_cache_expire_time,
				'fileLocking' => TRUE,
				'writeControl' => FALSE,
				'readControl' => FALSE,
				'memoryCaching' => TRUE,
				'automaticSerialization' => FALSE				
			);
			$this->Cache_Lite = new \Xpotronix\Cache($cache_options);
		}

		return true;
	}/*}}}*/

	/**
	* Prints debug text if debug is enabled.
	* @param string THe text to output
	* @return boolean Always returns true
	*/

	function debug_text($text) {/*{{{*/

		if ($this->_debug) {
			echo "$text<br>\n";
		}

		return true;
	}/*}}}*/

	/**
	* Prints database debug text if debug is enabled.
	* @param string The name of the function calling this method
	* @return string Returns an error message
	*/

	function get_db_error() {/*{{{*/
		return 'Database error: '. $this->db->ErrorMsg() .' ('. $this->db->ErrorNo() .')';
	}/*}}}*/

	/**
	* Wraps the actual acl_query() function.
	*
	* It is simply here to return TRUE/FALSE accordingly.
	* @param string The ACO section value
	* @param string The ACO value
	* @param string The ARO section value
	* @param string The ARO section
	* @param string The AXO section value (optional)
	* @param string The AXO section value (optional)
	* @param integer The group id of the ARO ??Mike?? (optional)
	* @param integer The group id of the AXO ??Mike?? (optional)
	* @return boolean TRUE if the check succeeds, false if not.
	*/

	function acl_check($aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value=NULL, $axo_value=NULL, $root_aro_group=NULL, $root_axo_group=NULL) {/*{{{*/
		$acl_result = $this->acl_query($aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value, $axo_value, $root_aro_group, $root_axo_group);

		return $acl_result['allow'];
	}/*}}}*/

	/**
	* Wraps the actual acl_query() function.
	*
	* Quick access to the return value of an ACL.
	* @param string The ACO section value
	* @param string The ACO value
	* @param string The ARO section value
	* @param string The ARO section
	* @param string The AXO section value (optional)
	* @param string The AXO section value (optional)
	* @param integer The group id of the ARO (optional)
	* @param integer The group id of the AXO (optional)
	* @return string The return value of the ACL
	*/

	function acl_return_value($aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value=NULL, $axo_value=NULL, $root_aro_group=NULL, $root_axo_group=NULL) {/*{{{*/
		$acl_result = $this->acl_query($aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value, $axo_value, $root_aro_group, $root_axo_group);

		return $acl_result['return_value'];
	}/*}}}*/

	/**
	* Handles ACL lookups over arrays of AROs
	* @param string The ACO section value
	* @param string The ACO value
	* @param array An named array of arrays, each element in the format aro_section_value=>array(aro_value1,aro_value1,...)
	* @return mixed The same data format as inputted.
	\*======================================================================*/

	function acl_check_array($aco_section_value, $aco_value, $aro_array) {/*{{{*/
		/*
			Input Array:
				Section => array(Value, Value, Value),
				Section => array(Value, Value, Value)

		 */

		if (!is_array($aro_array)) {
			M()->debug("acl_query_array(): ARO Array must be passed");
			return false;
		}

		foreach($aro_array as $aro_section_value => $aro_value_array) {
			foreach ($aro_value_array as $aro_value) {
				M()->debug("acl_query_array(): ARO Section Value: $aro_section_value ARO VALUE: $aro_value");

				if( $this->acl_check($aco_section_value, $aco_value, $aro_section_value, $aro_value) ) {
					M()->debug("acl_query_array(): ACL_CHECK True");
					$retarr[$aro_section_value][] = $aro_value;
				} else {
					M()->debug("acl_query_array(): ACL_CHECK False");
				}
			}
		}

		return $retarr;

	}/*}}}*/

	/**
	* The Main function that does the actual ACL lookup.
	* @param string The ACO section value
	* @param string The ACO value
	* @param string The ARO section value
	* @param string The ARO section
	* @param string The AXO section value (optional)
	* @param string The AXO section value (optional)
	* @param string The value of the ARO group (optional)
	* @param string The value of the AXO group (optional)
	* @param boolean Debug the operation if true (optional)
	* @return array Returns as much information as possible about the ACL so other functions can trim it down and omit unwanted data.
	*/

	function acl_query($aco_section_value, $aco_value, $aro_section_value, $aro_value, $axo_section_value=NULL, $axo_value=NULL, $root_aro_group=NULL, $root_axo_group=NULL, $debug=NULL) {/*{{{*/
				
		$cache_id = 'acl_query_'.$aco_section_value.'-'.$aco_value.'-'.$aro_section_value.'-'.$aro_value.'-'.$axo_section_value.'-'.$axo_value.'-'.$root_aro_group.'-'.$root_axo_group.'-'.$debug;

		$retarr = $this->get_cache($cache_id);

		if (!$retarr) {

			M()->debug( "no hay un ACL cacheado para esta sesion" );
			/*
			 * Grab all groups mapped to this ARO/AXO
			 */
			$aro_group_ids = $this->acl_get_groups($aro_section_value, $aro_value, $root_aro_group, 'ARO');

			M()->debug( 'aro_group_ids: '. serialize( $aro_group_ids ) );

			// DEBUG: 

			// print_r( $aro_group_ids ); exit;

			if (is_array($aro_group_ids) AND !empty($aro_group_ids)) {
				$sql_aro_group_ids = implode(',', $aro_group_ids);
			}

			if ($axo_section_value != '' AND $axo_value != '') {
				$axo_group_ids = $this->acl_get_groups($axo_section_value, $axo_value, $root_axo_group, 'AXO');

				
				M()->debug( 'axo_group_ids: '. serialize( $axo_group_ids ) );

				if (is_array($axo_group_ids) AND !empty($axo_group_ids)) {
					$sql_axo_group_ids = implode(',', $axo_group_ids);
				}
			}

			/*
			 * This query is where all the magic happens.
			 * The ordering is very important here, as well very tricky to get correct.
			 * Currently there can be  duplicate ACLs, or ones that step on each other toes. In this case, the ACL that was last updated/created
			 * is used.
			 *
			 * This is probably where the most optimizations can be made.
			 */

			$order_by = array();

			$query = '
					SELECT		a.id,a.allow,a.return_value
					FROM		'. $this->table_name( 'acl' ) .' as a
					LEFT JOIN 	'. $this->table_name( 'aco_map' ).' as ac ON ac.acl_id=a.id';

			if ($aro_section_value != $this->_group_switch) {
				$query .= '
					LEFT JOIN	'. $this->table_name( 'aro_map' ). ' as ar ON ar.acl_id=a.id';
			}

			if ($axo_section_value != $this->_group_switch) {
				$query .= '
					LEFT JOIN	'. $this->table_name( 'axo_map' ). ' as ax ON ax.acl_id=a.id';
			}

			/*
			 * if there are no aro groups, don't bother doing the join.
			 */
			if (isset($sql_aro_group_ids)) {
				$query .= '
					LEFT JOIN	'. $this->table_name( 'aro_groups_map' ). ' as arg ON arg.acl_id=a.id
					LEFT JOIN	'. $this->table_name( 'aro_groups' ). ' as rg ON rg.id=arg.group_id';
			}

			// this join is necessary to weed out rules associated with axo groups
			$query .= '
					LEFT JOIN	'. $this->table_name( 'axo_groups_map' ). ' as axg ON axg.acl_id=a.id';

			/*
			 * if there are no axo groups, don't bother doing the join.
			 * it is only used to rank by the level of the group.
			 */
			if (isset($sql_axo_group_ids)) {
				$query .= '
					LEFT JOIN	'. $this->table_name( 'axo_groups' ). ' as xg ON xg.id=axg.group_id';
			}

			//Move the below line to the LEFT JOIN above for PostgreSQL's sake.
			//AND	ac.acl_id=a.id
			$query .= '
					WHERE		a.enabled=1
						AND		(ac.section_value='. $this->db->quote($aco_section_value) .' AND ac.value='. $this->db->quote($aco_value) .')';

			// if we are querying an aro group
			if ($aro_section_value == $this->_group_switch) {
				// if acl_get_groups did not return an array
				if ( !isset ($sql_aro_group_ids) ) {
					M()->debug ('acl_query(): Invalid ARO Group: '. $aro_value);
					return FALSE;
				}

				$query .= '
						AND		rg.id IN ('. $sql_aro_group_ids .')';

				$order_by[] = '(rg.rgt-rg.lft) ASC';
			} else {
				$query .= '
						AND		((ar.section_value='. $this->db->quote($aro_section_value) .' AND ar.value='. $this->db->quote($aro_value) .')';

				if ( isset ($sql_aro_group_ids) ) {
					$query .= ' OR rg.id IN ('. $sql_aro_group_ids .')';

					$order_by[] = '(CASE WHEN ar.value IS NULL THEN 0 ELSE 1 END) DESC';
					$order_by[] = '(rg.rgt-rg.lft) ASC';
				}

				$query .= ')';
			}


			// if we are querying an axo group
			if ($axo_section_value == $this->_group_switch) {
				// if acl_get_groups did not return an array
				if ( !isset ($sql_axo_group_ids) ) {
					M()->debug ('acl_query(): Invalid AXO Group: '. $axo_value);
					return FALSE;
				}

				$query .= '
						AND		xg.id IN ('. $sql_axo_group_ids .')';

				$order_by[] = '(xg.rgt-xg.lft) ASC';
			} else {
				$query .= '
						AND		(';

				if ($axo_section_value == '' AND $axo_value == '') {
					$query .= '(ax.section_value IS NULL AND ax.value IS NULL)';
				} else {
					$query .= '(ax.section_value='. $this->db->quote($axo_section_value) .' AND ax.value='. $this->db->quote($axo_value) .')';
				}

				if (isset($sql_axo_group_ids)) {
					$query .= ' OR xg.id IN ('. $sql_axo_group_ids .')';

					$order_by[] = '(CASE WHEN ax.value IS NULL THEN 0 ELSE 1 END) DESC';
					$order_by[] = '(xg.rgt-xg.lft) ASC';
				} else {
					$query .= ' AND axg.group_id IS NULL';
				}

				$query .= ')';
			}

			/*
			 * The ordering is always very tricky and makes all the difference in the world.
			 * Order (ar.value IS NOT NULL) DESC should put ACLs given to specific AROs
			 * ahead of any ACLs given to groups. This works well for exceptions to groups.
			 */

			$order_by[] = 'a.updated_date DESC';

			$query .= '
					ORDER BY	'. implode (',', $order_by) . '
					';

			$rs = $this->db->Execute( $query. ' LIMIT 1' );

			/*
			// we are only interested in the first row
			$rs = $this->db->SelectLimit($query, 1);
			*/

			if (!is_object($rs)) {
				M()->error("error al ejecutar la consulta $query" );
				return FALSE;
			}

			$row = $rs->fetch( \PDO::FETCH_NUM );

			/*
			 * Return ACL ID. This is the key to "hooking" extras like pricing assigned to ACLs etc... Very useful.
			 */
			if (is_array($row)) {
				// Permission granted?
				// This below oneliner is very confusing.
				//$allow = (isset($row[1]) AND $row[1] == 1);

				//Prefer this.
				if ( isset($row[1]) AND $row[1] == 1 ) {
					$allow = TRUE;
				} else {
					$allow = FALSE;
				}

				$retarr = array('acl_id' => &$row[0], 'return_value' => &$row[2], 'allow' => $allow);
			} else {
				// Permission denied.
				$retarr = array('acl_id' => NULL, 'return_value' => NULL, 'allow' => FALSE);
			}

			/*
			 * Return the query that we ran if in debug mode.
			 */
			if ($debug == TRUE) {
				$retarr['query'] = &$query;
			}

			//Cache data.
			$this->put_cache($retarr, $cache_id);
		}

		M()->debug("<b>acl_query():</b> ACO Section: $aco_section_value ACO Value: $aco_value ARO Section: $aro_section_value ARO Value $aro_value ACL ID: ". $retarr['acl_id'] .' Result: '. $retarr['allow']);
		return $retarr;
	}/*}}}*/

	/**
	* Grabs all groups mapped to an ARO. You can also specify a root_group for subtree'ing.
	* @param string The section value or the ARO or ACO
	* @param string The value of the ARO or ACO
	* @param integer The group id of the group to start at (optional)
	* @param string The type of group, either ARO or AXO (optional)
	*/

	function acl_get_groups($section_value, $value, $root_group=NULL, $group_type='ARO') {/*{{{*/

		switch(strtolower($group_type)) {
			case 'axo':
				$group_type = 'axo';
				$object_table = $this->table_name( 'axo' );
				$group_table = $this->table_name( 'axo_groups' );
				$group_map_table = $this->table_name( 'groups_axo_map' );
				break;
			default:
				$group_type = 'aro';
				$object_table = $this->table_name( 'aro' );
				$group_table = $this->table_name( 'aro_groups' );
				$group_map_table = $this->table_name( 'groups_aro_map' );
				break;
		}

		//$profiler->startTimer( "acl_get_groups()");

		//Generate unique cache id.
		$cache_id = 'acl_get_groups_'.$section_value.'-'.$value.'-'.$root_group.'-'.$group_type;

		$retarr = $this->get_cache($cache_id);

		if ( true or !$retarr) {

			// Make sure we get the groups
			$query = '
					SELECT 		DISTINCT g2.id';

			if ($section_value == $this->_group_switch) {
				$query .= '
					FROM		' . $group_table . ' g1,' . $group_table . ' g2';

				$where = '
					WHERE		g1.value=' . $this->db->quote( $value );
			} else {
				$query .= '
					FROM		'. $object_table .' o,'. $group_map_table .' gm,'. $group_table .' g1,'. $group_table .' g2';

				$where = '
					WHERE		(o.section_value='. $this->db->quote($section_value) .' AND o.value='. $this->db->quote($value) .')
						AND		gm.'. $group_type .'_id=o.id
						AND		g1.id=gm.group_id';
			}

			/*
			 * If root_group_id is specified, we have to narrow this query down
			 * to just groups deeper in the tree then what is specified.
			 * This essentially creates a virtual "subtree" and ignores all outside groups.
			 * Useful for sites like sourceforge where you may seperate groups by "project".
			 */
			if ( $root_group != '') {
				//It is important to note the below line modifies the tables being selected.
				//This is the reason for the WHERE variable.
				$query .= ','. $group_table .' g3';

				$where .= '
						AND		g3.value='. $this->db->quote( $root_group ) .'
						AND		((g2.lft BETWEEN g3.lft AND g1.lft) AND (g2.rgt BETWEEN g1.rgt AND g3.rgt))';
			} else {
				$where .= '
						AND		(g2.lft <= g1.lft AND g2.rgt >= g1.rgt)';
			}

			$query .= $where;

			// M()->debug($query);
			$rs = $this->db->Execute($query);

			if (!is_object($rs)) {
				M()->error("error al ejecutar la consulta $query" );
				return FALSE;
			}

			$retarr = array();

			// DEBUG: devuelve dos arrays 
			//  Unbuffered query?

			while ( $row = $rs->fetch( \PDO::FETCH_NUM ) )
				$retarr[] = $row[0];

			//Cache data.
			$this->put_cache($retarr, $cache_id);
		}

		return $retarr;
	}/*}}}*/

	/**
	* Uses PEAR's Cache_Lite package to grab cached arrays, objects, variables etc...
	* using unserialize() so it can handle more then just text string.
	* @param string The id of the cached object
	* @return mixed The cached object, otherwise FALSE if the object identifier was not found
	*/

	function get_cache($cache_id) {/*{{{*/

		if ( $this->_caching == TRUE ) {
			M()->debug("get_cache(): on ID: $cache_id");

			if ( is_string($this->Cache_Lite->get($cache_id) ) ) {
				return unserialize($this->Cache_Lite->get($cache_id) );
			}
		}

		return false;
	}/*}}}*/

	/**
	* Uses PEAR's Cache_Lite package to write cached arrays, objects, variables etc...
	* using serialize() so it can handle more then just text string.
	* @param mixed A variable to cache
	* @param string The id of the cached variable
	*/

	function put_cache($data, $cache_id) {/*{{{*/

		if ( $this->_caching == TRUE ) {
			M()->debug("put_cache(): Cache MISS on ID: $cache_id");

			return $this->Cache_Lite->save(serialize($data), $cache_id);
		}

		return false;
	}/*}}}*/

	function table_name( $table ) {/*{{{*/

		return $this->_db_table_prefix.$table;
	}/*}}}*/

}
?>
