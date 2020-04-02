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

namespace Xpotronix\DataTypes;

class xpTime extends xpDate {

	function format_str() {

		global $xpdoc;
		return $xpdoc->feat->time_format;
	}

	function format_long_str() {

		global $xpdoc;
		return $xpdoc->feat->time_format;
	}

	function db_format_str() {

		global $xpdoc;
		return $xpdoc->feat->time_db_format;

	}

	function db_null_str() {

		global $xpdoc;
		return $xpdoc->feat->time_db_null;

	}
}
// vim600: fdm=marker sw=3 ts=8 ai:
?>
