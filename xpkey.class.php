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

class xpkey {

	var $parent;
	var $remote;
	var $set;

	function __construct( $parent, $remote, $set = null ) {

		$this->parent = $parent;
		$this->remote = $remote;
		$this->set    = $set;

		return $this;

	}
}

?>
