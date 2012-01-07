<?

/**
 * @package xpotronix
 * @version 2.0 - Areco 
 * @copyright Copyright &copy; 2003-2011, Eduardo Spotorno
 * @author Eduardo Spotorno
 *
 * Licensed under GPL v3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

class xpblob extends xpattr {

	function serialize( $value = null ) {

		// DEBUG: devuelve una referencia http a donde se encuentra el objeto. Deberia especificar que field esta pidiendo

		return "?m={$this->obj->class_name}&amp;fi[{$this->name}]&amp;a=process&amp;p=download&amp;s[{$this->obj->class_name}][__ID__]=".$this->obj->pack_primary_key();

	}

}

?>
