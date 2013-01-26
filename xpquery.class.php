<?

/*Copyright 2003,2004 Adam Donnison <adam@saki.com.au>
*/

/*

  Adaptado para xpotronix
  Eduardo Spotorno

*/

/** class DBQuery
 * Container for creating prefix-safe queries.  Allows build up of
 * a select statement by adding components one at a time.
 *
 * @version	$Id: query.class.php,v 1.23.2.5 2007/01/31 09:36:52 ajdonnison Exp $
 * @package	dotProject
 * @access	public
 * @author	Adam Donnison <adam@saki.com.au>
 * @license	GPL version 2 or later.
 * @copyright	(c) 2003 Adam Donnison
 */

class DBQuery {

	var $sql; // literal

	var $db;
	var $query;
	var $aliases; // fix para counts

	private $modifiers;
	var $table_list;
	var $where;
	var $having;
	var $order_by;
	var $group_by;

	var $limit;
	var $offset;

	var $join;
	var $type;

	var $update_list;
	var $value_list;

	var $create_table;
	var $create_definition;
	var $_table_prefix = null;
	var $stmt = null;

	var $databaseType;

	function DBQuery( $db_handle, $prefix = null) {/*{{{*/

		global $xpdoc;

		$db_handle and $this->db = $db_handle or M()->error( 'No hay una base de datos asociada a la consulta' );

		if ( isset($prefix) )
			$this->_table_prefix = $prefix;
		else if (isset($xpdoc->config->table_prefix))
			$this->_table_prefix = $xpdoc->config->prefix;
		else
			$this->_table_prefix = null;

		@$this->databaseType = $this->db->databaseType;

		$this->clear();

		return $this;
	}/*}}}*/

	function clear() {/*{{{*/

		$this->type = 'select';
		$this->query = null;
		$this->table_list = null;
		$this->where = null;
		$this->order_by = null;
		$this->group_by = null;
		$this->limit = null;
		$this->offset = -1;
		$this->join = null;
		$this->value_list = null;
		$this->update_list = null;
		$this->create_table = null;
		$this->create_definition = null;
		$this->stmt = null;

		return $this->clearQuery();

	}/*}}}*/

	function clearQuery() {/*{{{*/

		if ( $this->stmt ) unset( $this->stmt );
		return $this;

	}/*}}}*/

	/**
	 * Add a hash item to an array.
	 *
	 * @access	private
	 * @param	string	$varname	Name of variable to add/create
	 * @param	mixed	$name	Data to add
	 * @param	string 	$id	Index to use in array.
	 */

	function addMap($varname, $name, $id) {/*{{{*/

		if ( !isset($this->$varname) ) 
			$this->$varname = array();

		if ( isset($id) ) 
			$this->{$varname}[$id] = $name;
		else 
			$this->{$varname}[] = $name;

		return $this;

	}/*}}}*/

	/**
	 * Adds a table to the query.  A table is normally addressed by an
	 * alias.  If you don't supply the alias chances are your code will
	 * break.  You can add as many tables as are needed for the query.
	 * E.g. addTable('something', 'a') will result in an SQL statement
	 * of {PREFIX}table as a.
	 * Where {PREFIX} is the system defined table prefix.
	 *
	 * @param	string	$name	Name of table, without prefix.
	 * @parem	string	$id	Alias for use in query/where/group clauses.
	 */

	function addTable($name, $id = null) {/*{{{*/

		$this->addMap('table_list', $name, $id);
		return $this;

	}/*}}}*/


	/**
	 * Add a clause to an array.  Checks to see variable exists first.
	 * then pushes the new data onto the end of the array.
	 */

	function addClause( $clause, $value, $check_array = true ) {/*{{{*/

		// M()->debug("Adding [". serialize( $value ) ."] to $clause clause");

		isset( $this->$clause ) or $this->$clause = array();

		if ( $check_array and is_array( $value ) )
			foreach ( $value as $v )
				array_push( $this->$clause, $v );
		else 
			array_push( $this->$clause, $value );

		return $this;

	}/*}}}*/

	/**
	 * Add the actual select part of the query.  E.g. '*', or 'a.*'
	 * or 'a.field, b.field', etc.  You can call this multiple times
	 * and it will correctly format a combined query.
	 *
	 * @param	string	$query	Query string to use.
	 */

	function addQuery( $query, $alias = null ) {/*{{{*/

		$alias and $query .= " AS $alias";
		$this->addClause('query', $query );
		return $this;

	}/*}}}*/

	function addSql( $query ) {/*{{{*/

		$this->addClause('sql', $query );
		return $this;

	}/*}}}*/



	function addModifiers( $query ) {/*{{{*/

		$this->addClause('modifiers', $query );
		return $this;

	}/*}}}*/



	function addInsert($field, $value, $set = false, $func = false) {/*{{{*/

		if ($set) {

			if (is_array($field))
				$fields = $field;
			else
				$fields = explode(',', $field);

			if (is_array($value))
				$values = $value;
			else
				$values = explode(',', $value);

			for($i = 0; $i < count($fields); $i++)
				$this->addMap('value_list', $this->quote($values[$i]), $fields[$i]);
		}
		else if (!$func)
			$this->addMap('value_list', $this->quote($value), $field);
		else
			$this->addMap('value_list', $value, $field);
		$this->type = 'insert';

		return $this;

	}/*}}}*/

	function addReplace($field, $value, $set = false, $func = false) {/*{{{*/

		/* implemented addReplace() on top of addInsert() */

		$this->addInsert($field, $value, $set, $func);
		$this->type = 'replace';

		return $this;

	}/*}}}*/

	function addUpdate($field, $value, $set = false) {/*{{{*/

		if ($set)
		{
			if (is_array($field))
				$fields = $field;
			else
				$fields = explode(',', $field);

			if (is_array($value))
				$values = $value;
			else
				$values = explode(',', $value);

			for($i = 0; $i < count($fields); $i++)
				$this->addMap('update_list', $values[$i], $fields[$i]);
		}
		else
			$this->addMap('update_list', $value, $field);
		$this->type = 'update';

		return $this;
	}/*}}}*/

	function createTable($table) {/*{{{*/

		$this->type = 'createPermanent';
		$this->create_table = $table;

		return $this;

	}/*}}}*/

	function createTemp($table) {/*{{{*/

		$this->type = 'create';
		$this->create_table = $table;

		return $this;

	}/*}}}*/

	function dropTable($table) {/*{{{*/

		$this->type = 'drop';
		$this->create_table = $table;

		return $this;

	}/*}}}*/

	function dropTemp($table) {/*{{{*/

		$this->type = 'drop';
		$this->create_table = $table;

		return $this;
	
	}/*}}}*/

	function alterTable($table) {/*{{{*/

		$this->create_table = $table;
		$this->type = 'alter';

		return $this;

	}/*}}}*/

	function addField($name, $type) {/*{{{*/

		if (! is_array($this->create_definition))
			$this->create_definition = array();
		$this->create_definition[] = array('action' => 'ADD',
				'type' => null,
				'spec' => $name . ' ' . $type);

		return $this;

	}/*}}}*/

	function dropField($name) {/*{{{*/

		if (! is_array($this->create_definition))
			$this->create_definition = array();
		$this->create_definition[] = array('action' => 'DROP',
				'type' => null,
				'spec' => $name);

		return $this;

	}/*}}}*/

	function addIndex($name, $type) {/*{{{*/

		if (! is_array($this->create_definition))
			$this->create_definition = array();
		$this->create_definition[] = array('action' => 'ADD',
				'type' => 'INDEX',
				'spec' => $name . ' ' . $type);

		return $this;

	}/*}}}*/

	function dropIndex($name) {/*{{{*/

		if (! is_array($this->create_definition))
			$this->create_definition = array();
		$this->create_definition[] = array('action' => 'DROP',
				'type' => 'INDEX',
				'spec' => $name);

		return $this;

	}/*}}}*/

	function dropPrimary() {/*{{{*/

		if (! is_array($this->create_definition))
			$this->create_definition = array();
		$this->create_definition[] = array('action' => 'DROP',
				'type' => 'PRIMARY KEY',
				'spec' => null);

		return $this;

	}/*}}}*/

	function createDefinition($def) {/*{{{*/

		$this->create_definition = $def;

		return $this;

	}/*}}}*/

	function setDelete($table) {/*{{{*/

		$this->type = 'delete';
		$this->addMap('table_list', $table, null);

		return $this;

	}/*}}}*/

	/** 
	 * Add where sub-clauses.  The where clause can be built up one
	 * part at a time and the resultant query will put in the 'and'
	 * between each component.
	 *
	 * Make sure you use table aliases.
	 *
	 * @param	string 	$query	Where subclause to use
	 */

	function addWhere( $query ) {/*{{{*/

		if (isset($query)) 
			$this->addClause('where', $query);

		return $this;
	}/*}}}*/

	/** 
	 * Add having sub-clauses.  The having clause can be built up one
	 * part at a time and the resultant query will put in the 'and'
	 * between each component.
	 *
	 * Make sure you use table aliases.
	 *
	 * @param	string 	$query	Where subclause to use
	 */

	function addHaving($query) {/*{{{*/

		if ( isset( $query ) ) 
			$this->addClause('having', $query);

		return $this;
	}/*}}}*/

	/**
	 * Add a join condition to the query.  This only implements
	 * left join, however most other joins are either synonymns or
	 * can be emulated with where clauses.
	 *
	 * @param	string	$table	Name of table (without prefix)
	 * @param	string	$alias	Alias to use instead of table name (required).
	 * @param	mixed	$join	Join condition (e.g. 'a.id = b.other_id')
	 *				or array of join fieldnames, e.g. array('id', 'name);
	 *				Both are correctly converted into a join clause.
	 */

	function addJoin($table, $alias, $join, $type = 'left') {/*{{{*/

		$this->addClause( 'join', 
				array ( 'table' => $table,
					'alias' => $alias,
					'condition' => $join,
					'type' => $type ), 
				false);

		return $this;

	}/*}}}*/

	function leftJoin($table, $alias, $join) {/*{{{*/

		$this->addJoin($table, $alias, $join, 'left');

		return $this;

	}/*}}}*/

	function rightJoin($table, $alias, $join) {/*{{{*/

		$this->addJoin($table, $alias, $join, 'right');

		return $this;

	}/*}}}*/

	function innerJoin($table, $alias, $join) {/*{{{*/

		$this->addJoin($table, $alias, $join, 'inner');

		return $this;

	}/*}}}*/

	/**
	 * Add an order by clause.  Again, only the fieldname is required, and
	 * it should include an alias if a table has been added.
	 * May be called multiple times.
	 *
	 * @param	string	$order	Order by field.
	 */

	function addOrder($order) {/*{{{*/

		if (isset($order)) 
			$this->addClause('order_by', $order);

		return $this;

	}/*}}}*/

	/**
	 * Add a group by clause.  Only the fieldname is required.
	 * May be called multiple times.  Use table aliases as required.
	 *
	 * @param	string	$group	Field name to group by.
	 */

	function addGroup($group) {/*{{{*/

		$this->addClause('group_by', $group);
		return $this;

	}/*}}}*/

	function setLimit($limit, $start = -1) {/*{{{*/

		$this->limit = $limit;
		$this->offset = $start;

		return $this;
	}/*}}}*/

	/* prepare */

	function prepareCount() {

		return $this->prepare( false, true );
	}


	function prepare( $clear = false, $count = false ) {/*{{{*/

		// ES: esto es para que no repita los joins que siempre
		// van a dar un error en el mysql;

		$this->join = array_unique2( $this->join );

		switch ($this->type) {
			case 'select':
				$q = $this->prepareSelect( $count );
				break;
			case 'update':
				$q = $this->prepareUpdate();
				break;
			case 'insert':
				$q = $this->prepareInsert();
				break;
			case 'replace':
				$q = $this->prepareReplace();
				break;
			case 'delete':
				$q = $this->prepareDelete();
				break;
			case 'create':	// Create a temporary table
				$s = $this->prepareSelect();
				$q = 'CREATE TEMPORARY TABLE ' . $this->_table_prefix . $this->create_table;
				if (!empty($this->create_definition))
					$q .= ' ' . $this->create_definition;
				$q .= ' ' . $s;
				break;
			case 'alter':
				$q = $this->prepareAlter();
				break;
			case 'createPermanent':	// Create a temporary table
				$s = $this->prepareSelect();
				$q = 'CREATE TABLE ' . $this->_table_prefix . $this->create_table;
				if (!empty($this->create_definition))
					$q .= ' ' . $this->create_definition;
				$q .= ' ' . $s;
				break;
			case 'drop':
				$q = 'DROP TABLE IF EXISTS ' . $this->_table_prefix . $this->create_table;
				break;
		}

		$clear and $this->clear();

		// M()->debug( 'query: '. $q );

		return $q;

	}/*}}}*/

	function prepareSelectCommand() {/*{{{*/

		$q = array();

		$q[] = 'SELECT';
		// inclusion de modifiers (all, distinct, etc)

		if( isset( $this->modifiers ) )
			if ( is_array( $this->modifiers ) )
				$q[] = implode( ' ', $this->modifiers );
			else
				$q[] = $this->modifiers;

		return implode( ' ', $q );

	}/*}}}*/

	function prepareSelectFields() {/*{{{*/

		$q = array();

		if ( isset( $this->query ) )
			$q[] = ( is_array( $this->query )? implode(',', $this->query) : $this->query );
		else
			$q[] = '*';

		return implode( ' ', $q );

	}/*}}}*/

	function prepareSelectFrom() {/*{{{*/

		$q = array();

		$q[] = 'FROM';

		if ( isset( $this->table_list ) ) {

			if ( is_array( $this->table_list ) ) {

				$qt = array();
				foreach ( $this->table_list as $table_id => $table )
					$qt[] = $this->quote_name( $this->_table_prefix . $table ). ( !is_numeric( $table_id ) ? " AS $table_id" : null );

				$q[] = implode( ',', $qt );
				
			} else 
				$q[] = $this->_table_prefix . $this->table_list;
		}

		return implode( ' ', $q );
	}/*}}}*/

	function prepareSelect( $count = false ) {/*{{{*/

		$q = array();

		if ( $count and $this->databaseType == 'mysql' )
			$this->addModifiers( 'SQL_CALC_FOUND_ROWS' );

		if ( $this->sql ) {

			$q[] = array_shift( $this->sql );
			$q[] = $this->make_order_clause();

		} else {


			$q[] = $this->prepareSelectCommand();
			$q[] = $this->prepareSelectFields();
			$q[] = $this->prepareSelectFrom();
			$q[] = $this->make_join();
			$q[] = $this->make_where_clause();
			$q[] = $this->make_group_clause();
			$q[] = $this->make_having_clause();
			$q[] = $this->make_order_clause();
		}

		return implode( ' ', $q );
	}/*}}}*/

	function prepareUpdate() {/*{{{*/

		// You can only update one table, so we get the table detail
		$q = 'UPDATE ';
		if (isset($this->table_list)) {
			if (is_array($this->table_list)) {
				reset($this->table_list);
				// Grab the first record
				list($key, $table) = each ($this->table_list);
			} else {
				$table = $this->table_list;
			}
		} else {
			return false;
		}
		$q .= $this->quote_name( $this->_table_prefix . $table );

		$q .= ' SET ';
		$sets = null;
		foreach( $this->update_list as $field => $value) {
			if ($sets)
				$sets .= ", ";
			$sets .= $this->quote_name( $field ). "=" . $this->quote($value);
		}
		$q .= $sets;
		$q .= $this->make_where_clause($this->where);
		return $q;
	}/*}}}*/

	function prepareInsert() {/*{{{*/

		$q = 'INSERT INTO ';
		if (isset($this->table_list)) {
			if (is_array($this->table_list)) {
				reset($this->table_list);
				// Grab the first record
				list($key, $table) = each ($this->table_list);
			} else {
				$table = $this->table_list;
			}
		} else {
			return false;
		}
		$q .= $this->quote_name( $this->_table_prefix . $table );

		$fieldlist = null;
		$valuelist = null;
		foreach( $this->value_list as $field => $value) {
			if ($fieldlist)
				$fieldlist .= ",";
			if ($valuelist)
				$valuelist .= ",";
			$fieldlist .= $this->quote_name( $field );
			$valuelist .= $value;
		}
		$q .= "($fieldlist) values ($valuelist)";
		return $q;
	}/*}}}*/

	function prepareReplace() {/*{{{*/

		$q = 'REPLACE ';

		if( isset( $this->modifiers ) )
			if ( is_array( $this->modifiers ) )
				$q .= implode( ' ', $this->modifiers );
			else
				$q .= $this->modifiers;
		
		$q .= ' INTO ';
		// DEBUG: implementar
		// $q = 'REPLACE DELAYED INTO ';


		if (isset($this->table_list)) {
			if (is_array($this->table_list)) {
				reset($this->table_list);
				// Grab the first record
				list($key, $table) = each ($this->table_list);
			} else {
				$table = $this->table_list;
			}
		} else {
			return false;
		}
		$q .= $this->quote_name( $this->_table_prefix . $table );

		$fieldlist = null;
		$valuelist = null;
		foreach( $this->value_list as $field => $value) {
			if ($fieldlist)
				$fieldlist .= ",";
			if ($valuelist)
				$valuelist .= ",";
			$fieldlist .= $this->quote_name( trim($field) );
			$valuelist .= $value;
		}
		$q .= "($fieldlist) values ($valuelist)";
		return $q;
	}/*}}}*/

	function prepareDelete() {/*{{{*/

		$q = 'DELETE FROM ';
		if (isset($this->table_list)) {
			if (is_array($this->table_list)) {
				// Grab the first record
				list($key, $table) = each ($this->table_list);
			} else {
				$table = $this->table_list;
			}
		} else {
			return false;
		}
		$q .= $this->quote_name( $this->_table_prefix . $table );
		$q .= $this->make_where_clause($this->where);
		return $q;
	}/*}}}*/

	/* TODO: add ALTER DROP/CHANGE/MODIFY/IMPORT/DISCARD/...
	definitions: http://dev.mysql.com/doc/mysql/en/alter-table.html */

	function prepareAlter() {/*{{{*/

		$q = 'ALTER TABLE '. $this->quote_name( $this->_table_prefix . $this->create_table ). ' ';
		if (isset($this->create_definition)) {
			if (is_array($this->create_definition)) {
				$first = true;
				foreach ($this->create_definition as $def) {
					if ($first)
						$first = false;
					else
						$q .= ', ';
					$q .= $def['action'] . ' ' . $def['type'] . ' ' . $def['spec'];
				}
			} else {
				$q .= 'ADD ' . $this->create_definition;
			}
		}

		return $q; 
	}/*}}}*/

	function make_join( $join_clause = null ) {/*{{{*/

		$join_clause or $join_clause = $this->join;

		if ( !$join_clause ) return;

		$result = null;

		if (is_array($join_clause)) {
			foreach ($join_clause as $join) {
				$result .= ' ' . strtoupper($join['type']) . ' JOIN '. $this->quote_name( $this->_table_prefix . $join['table'] ); 
				if ($join['alias'])
					$result .= ' AS ' . $join['alias'];
				if (is_array($join['condition'])) {
					$result .= ' USING (' . implode(',', $join['condition']) . ')';
				} else {
					$result .= ' ON ' . $join['condition'];
				}
			}
		} else {
			$result .= ' LEFT JOIN '. $this->quote_name( $this->_table_prefix . $join_clause );
		}
		return $result;
	}/*}}}*/

function make_where_clause( $where_clause = null ) {/*{{{*/

		$where_clause or $where_clause = $this->where;

		if ( !$where_clause ) return;

		$result = null;
		$clause = ' WHERE ';

		if ( is_array( $where_clause ) and count( $where_clause ) )
			$result = $clause. implode(' AND ', $where_clause);

		else if ( $where_clause )
			$result = $clause. $where_clause;

		return $result;
	}/*}}}*/

	function make_group_clause( $group_clause = null ) {/*{{{*/

		$group_clause or $group_clause = $this->group_by;

		if ( !$group_clause ) return;

		$result = null;
		$clause = ' GROUP BY ';

		if ( is_array( $group_clause ) and count( $group_clause ) )
			$result = $clause. implode( ',', $group_clause );

		else if ( $group_clause )
			$result = $clause. $group_clause;

		return $result;

	}/*}}}*/

		function make_having_clause( $having_clause = null ) {/*{{{*/

		$having_clause or $having_clause = $this->having;

		if ( !$having_clause ) return;

		$result = null;
		$clause = ' HAVING ';

		if ( is_array( $having_clause ) and count( $having_clause ) )
			$result = $clause. implode( ' AND ', $having_clause );

		else if ( $having_clause )
			$result = $clause. $having_clause;

		return $result;

	}/*}}}*/

	function make_order_clause( $order_clause = null ) {/*{{{*/

		$order_clause or $order_clause = $this->order_by;

		if ( !$order_clause ) return;

		is_array( $order_clause ) or $order_clause = array( $order_clause ); 

		$ra = array();

		foreach( $order_clause as $oc )

			$ra[] = $oc;

		return ' ORDER BY '. implode( ',', $ra );

	}/*}}}*/

	/* Execute the query and return a handle.  Supplants the db_exec query */

	function explain( $q ) {/*{{{*/

		// Before running the query, explain the query and return the details.
		$qid = $this->db->Execute('EXPLAIN ' . $q);

		if ($qid) {
			$res = array();
			while ($row = $this->fetchRow( $style )) {
				$res[] = $row;
			}
			M()->debug( "QUERY DEBUG: " . var_export($res, true));
			$qid->Close();
		}
	}/*}}}*/

	function exec( $style = PDO::FETCH_ASSOC, $debug = false ) {/*{{{*/

		if ( $q = $this->prepare() ) {
			$debug and M()->debug( "executing query($q)" );
			$this->stmt = ( $this->limit ) ? 
				$this->db->PageExecute($q, $this->offset, $this->limit ):
				$this->db->Execute( $q );

			return $this->stmt;
		} else return null;
	}/*}}}*/

	function fetchRow() {/*{{{*/

		if (! $this->stmt) {
			return false;
		}
		return $this->stmt->fetch();
	}/*}}}*/

        function loadList($maxrows = null) {/*{{{*/

                if (! $this->exec(PDO::FETCH_ASSOC)) {
			M()->error( "Error en la consulta: ".$this->db->ErrorMsg());
			M()->error( $this->prepare() );
                        $this->clear();
                        return null;
                }

                $list = array();
                $cnt = 0;
                while ($hash = $this->fetchRow()) {
                        $list[] = $hash;
                        if ($maxrows and $maxrows == $cnt++)
                                break;
                }
                $this->clear();
                return $list;
        }/*}}}*/

	function loadHashList($index = null) {/*{{{*/

		if (! $this->exec(PDO::FETCH_ASSOC)) {
			M()->error( "Error en la consulta: ".$this->db->ErrorMsg());
			M()->error( $this->prepare() );
                        $this->clear();
                        return null;
		}
		$hashlist = array();
		$keys = null;
		while ($hash = $this->fetchRow()) {
			if ($index) {
				$hashlist[$hash[$index]] = $hash;
			} else {
				// If we are using fetch mode of ASSOC, then we don't
				// have an array index we can use, so we need to get one
				if (! $keys)
					$keys = array_keys($hash);
				$hashlist[$hash[$keys[0]]] = $hash[$keys[1]];
			}
		}
		$this->clear();
		return $hashlist;
	}/*}}}*/

	function loadHash() {/*{{{*/

		if (! $this->exec(PDO::FETCH_ASSOC)) {
			M()->error( "Error en la consulta: ".$this->db->ErrorMsg());
			M()->error( $this->prepare() );
                        $this->clear();
                        return null;
		}
		$hash = $this->fetchRow();
		$this->clear();
		return $hash;
	}/*}}}*/

	function loadArrayList($index = 0) {/*{{{*/

		if (! $this->exec(PDO::FETCH_NUM)) {
			M()->error( "Error en la consulta: ".$this->db->ErrorMsg());
			M()->error( $this->prepare() );
                        $this->clear();
                        return null;
		}
		$hashlist = array();
		$keys = null;
		while ($hash = $this->fetchRow()) {
			$hashlist[$hash[$index]] = $hash;
		}
		$this->clear();
		return $hashlist;
	}/*}}}*/

	function loadColumn() {/*{{{*/

		if (! $this->exec(PDO::FETCH_NUM)) {

			M()->error( "Error en la consulta: ".$this->db->ErrorMsg() );
			M()->error( $this->prepare() );
			return null;
		}
		
		$result = array();

		while ($row = $this->fetchRow()) {
			$result[] = $row[0];
		}
		$this->clear();
		return $result;
	}/*}}}*/

	function loadObject( &$object, $bindAll=false , $strip = true) {/*{{{*/

		if (! $this->exec(PDO::FETCH_NUM)) {

			M()->error( "Error en la consulta: ".$this->db->ErrorMsg() );
			M()->error( $this->prepare() );
			return;
		}

		if ($object != null) {
			$hash = $this->fetchRow();
			$this->clear();
			if( !$hash ) {
				return false;
			}
			$this->bindHashToObject( $hash, $object, null, $strip, $bindAll );
			return true;
		} else {
			if ($object = $this->stmt->FetchNextObject(false)) {
				$this->clear();
				return true;
			} else {
				$object = null;
				return false;
			}
		}
	}/*}}}*/

	/** function loadResult
	 * Load a single column result from a single row
	 */

	function loadResult() {/*{{{*/

		$result = false;

		if (! $this->exec(PDO::FETCH_NUM)) {

			M()->error( $this->db->ErrorMsg());
			M()->error( $this->prepare() );

		} else if ($data = $this->fetchRow()) {

			$result =  $data[0];

		}

		$this->clear();
		return $result;
	}/*}}}*/

	/* quoting */

	function quote($string) {/*{{{*/

		return $this->db->quote($string);
	}/*}}}*/

	function quote_name( $string ) {/*{{{*/

		return $this->db->quote_name( $string );

	}/*}}}*/
}

// vim600: fdm=marker sw=2 ts=8 ai:

?>
