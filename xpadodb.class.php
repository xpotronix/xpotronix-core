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

class xpadodb {

	var $instance;
	var $pdo;
	var $database;
	var $host;
	var $user;
	var $password;
	var $implem;
	var $databaseType;

	function __construct( $implem ) {/*{{{*/

		if ( $implem == 'mysqli' ) $implem = 'mysql';

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

		$conn_str = sprintf( "%s:host=%s;dbname=%s", $this->implementation, $host, $database );

		M()->debug( $conn_str );

		$this->pdo = new PDO( $conn_str, $user, $password, array(PDO::ATTR_PERSISTENT => $persist ) );

		$this->pdo->exec("set names $encoding");

		// Default fetch mode
		$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

		// Error handling
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		return $this;
	}/*}}}*/

	function SetFetchMode( $fm ) {/*{{{*/

		$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, $fm );
		return $this;
	}/*}}}*/

	function Execute( $sql ) {/*{{{*/

		// M()->user( $sql );

		return $this->pdo->query( $sql );

	}/*}}}*/

	function PageExecute( $sql, $pr, $cp ) {/*{{{*/

		$limit = array();

		$limit = $pr;
		$offset = $pr * ( $cp -1 );

		M()->debug( "limit: $limit, offset: $offset" );

		return $this->pdo->query( $sql. " LIMIT $offset, $limit" );
	}/*}}}*/

	function ErrorNo() {/*{{{*/

		return $this->errorCode();

	}/*}}}*/

	function ErrorMsg() {/*{{{*/

		return $this->errorInfo();
	}/*}}}*/

	function quote( $str ) {/*{{{*/

		return $this->pdo->quote( $str );

	}/*}}}*/

	function StartTrans() { return $this->pdo->beginTransaction(); }
	function CommitTrans() { return $this->pdo->commit(); }
	function CompleteTrans() { return $this->pdo->commit(); }
	function RollbackTrans() { return $this->pdo->rollBack(); }
	function Insert_ID() { return $this->pdo->lastInsertId(); }
}

?>
