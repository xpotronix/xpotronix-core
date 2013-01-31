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

class xpadodb extends PDO {

	var $instance;
	var $database;
	var $host;

	var $user;
	private $password;

	var $implem;
	var $databaseType;

	function __construct( $instance, $implem ) {/*{{{*/

		$this->instance = $instance;

		if ( $implem == 'mysqli' ) $implem = 'mysql';
		if ( $implem == 'mssql' ) $implem = 'dblib';

		$this->databaseType = $implem;
		$this->implementation = $implem;

		return $this;
	}/*}}}*/ 

	function PConnect( $host, $user, $password, $database, $encoding = null ) {/*{{{*/

		return $this->do_connect( $host, $user, $password, $database, $encoding, true );
	}/*}}}*/

	function Connect( $host, $user, $password, $database, $encoding = null ) {/*{{{*/

		return $this->do_connect( $host, $user, $password, $database, $encoding, false );
	}/*}}}*/

	function do_connect( $host, $user, $password, $database, $encoding, $persist = false ) {/*{{{*/

		$encoding or $encoding = 'utf8';

		$conn_str = sprintf( "%s:host=%s;dbname=%s;charset=%s", $this->implementation, $host, $database, $encoding );

		M()->debug( $conn_str );

		parent::__construct( $conn_str, $user, $password );

		// produce memory leaks!!
		// $this->setAttribute( PDO::ATTR_STATEMENT_CLASS, array('xpadostatement', array( $this ) ) );
		$this->setAttribute( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
		// $this->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
		$this->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

		if ( $this->implementation == 'mysql' ) {

			$this->setAttribute( PDO::ATTR_PERSISTENT, $persist );
			$this->setAttribute( PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true );
			$this->Execute( "SET NAMES $encoding" );

		}

		return $this;
	}/*}}}*/

	function SetFetchMode( $fm ) {/*{{{*/

		$this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $fm );
		return $this;
	}/*}}}*/

	function Execute( $sql ) {/*{{{*/

		// M()->user( $sql );
		// M()->mem_stats( 'antes del query' );
		$r = $this->query( $sql );
		// M()->mem_stats( 'despues del query' );
		return $r;

	}/*}}}*/

	function PageExecute( $sql, $pr, $cp ) {/*{{{*/

		$limit = $pr;
		$offset = $pr * ( $cp -1 );

		M()->debug( "limit: $limit, offset: $offset" );

		// M()->mem_stats( 'antes del query' );
		$r = $this->query( $sql. " LIMIT $offset, $limit" );
		// M()->mem_stats( 'despues del query' );
		return $r;
	}/*}}}*/

	function ErrorNoSQL() {/*{{{*/

		$r = $this->errorInfo();
		return $r[0];

	}/*}}}*/

	function ErrorNo() {/*{{{*/

		$r = $this->errorInfo();
		return $r[1];

	}/*}}}*/

	function ErrorMsg() {/*{{{*/

		$r = $this->errorInfo();
		return $r[2];

	}/*}}}*/

	function BeginTrans() { return $this->beginTransaction(); }
	function StartTrans() { return $this->beginTransaction(); }
	function CommitTrans() { return $this->commit(); }
	function CompleteTrans() { return $this->commit(); }
	function RollbackTrans() { return $this->rollBack(); }
	function Insert_ID() { return $this->lastInsertId(); }

	function GetCol( $query = null ) {
		return $this->query( $query )->fetchColumn( PDO::FETCH_NUM );
	}
	function GetOne( $query = null ) {
		return $this->query( $query )->fetch( PDO::FETCH_NUM );
	}
	function GetRow( $query = null ) {
		return $this->query( $query )->fetch( PDO::FETCH_NUM );
	}

	function quote_name($string) {/*{{{*/

		$ldelim = null;
		$rdelim = null;

		switch( $this->databaseType ) {
	
			case 'mysql':
			case 'mysqli':

				$ldelim = '`';
				$rdelim = '`';

				break;

			case 'mssql':
			case 'sybase':
			case 'dblib':

				$ldelim = '[';
				$rdelim = ']';

				break;
		}

		if ( strstr( $string, '.' ) ) {

			$ret = array();

			foreach( explode( '.', $string ) as $token )

				$ret[] = "$ldelim$token$rdelim";

			return implode( '.', $ret );

		} else return "$ldelim$string$rdelim";

	}/*}}}*/


} 

class xpadostatement extends PDOStatement {

	public $db;
	
	private function __construct( $db ) {

		$this->db = $db;
	}

	function GetRows() {
		return $this->fetchAll( PDO::FETCH_NUM );
	}
}

?>
