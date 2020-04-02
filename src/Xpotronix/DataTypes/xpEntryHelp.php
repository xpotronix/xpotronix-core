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

class xpEntryHelp extends xpText {

	function bind( $hash ) {/*{{{*/

		parent::bind( $hash );

		@$value = $hash[$this->name. '_label'];

		$this->label = $value ? $value : null;

		// $value and M()->debug( $this->label );
		
		return $this;
	}/*}}}*/

}

?>
