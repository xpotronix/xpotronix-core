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

class xpString extends xpText {

// filtros de validacion especificas para xpstring

    function obfuscate( $value = NULL ) {/*{{{*/

		if ( $value === NULL ) $value = $this->value;

        if ( in_array( $this->name, ['nombre', 'usuario', 'ap_nom', 'email_alternativo', 'email_laboral', 'apellido'] ) ) {
            $value = preg_replace('/[A-Za-z]/xms', '▓', $value);
        }
	
		return ( $this->escape ) ? htmlspecialchars( $value ): $value;

	}/*}}}*/

	function unique() {/*{{{*/

		global $xpdoc;

		$valid = true;

		if ( $this->value == NULL ) return $valid;

		$obj = $xpdoc->instance($this->obj->class_name);

		$flag = $obj->get_flag('main_sql');
		$obj->set_flag('main_sql', false);

		$obj->load( array( $this->name => $this->value ) );

		$obj->set_flag('main_sql', $flag);

		if ( $obj->loaded ) {

			M()->user( 'el atributo ' . $this->translate. ' ya se encuentra guardado con el valor '. $this->value .': no se puede repetir' , $this->id(), 'uniq' );
			$valid = false;
		}

		return $valid;

	}/*}}}*/

	function email() {/*{{{*/ 


		// $pattern = '^([0-9a-zA-Z]([-\.\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\w]*[0-9a-zA-Z]\.)+[a-zA-Z]{2,9})$';
		$pattern = '/^(?:[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+\.)*[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+@(?:(?:(?:[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!\.)){0,61}[a-zA-Z0-9_-]?\.)+[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!$)){0,61}[a-zA-Z0-9_]?)|(?:\[(?:(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\]))$/';

		$valid = true;

		if ( ! preg_match( $pattern, $this->value ) ) {

			// M()->user('valid', $this->id()); DEBUG: para oceanglobal
			M()->user( "El email {$this->value} no es válido", $this->id(), 'notvalid' );
			$valid = false;
		}

		return $valid;

	}/*}}}*/

	function user() {/*{{{*/

		$pattern = '/[a-zA-Z0-9_]+/';

		$valid = true;

		if ( ! preg_match( $pattern, $this->value ) ) {

			M()->user( "Usuario {$this->value} no válido", $this->id(), 'notvalid' );
			$valid = false;
		}

		return $valid;

	}/*}}}*/

	function password() {/*{{{*/

		$pattern = '/(?!^[0-9]*$)(?!^[a-zA-Z]*$)^([a-zA-Z0-9]{6,10})$/';

		$valid = true;

		if ( ! preg_match( $pattern, $this->value ) ) {

			M()->user('La clave es inválida', $this->id(), 'notvalid' );
			$valid = false;
		}

		return $valid;


	}/*}}}*/

	function captcha() {/*{{{*/

		$valid = false;

		if ( isset($_SESSION['securimage_code_value']) && !empty($_SESSION['securimage_code_value']) )
                        if ( strtolower($_SESSION['securimage_code_value']) == strtolower(trim($this->value)) ) {
                                $valid = true;
                                $_SESSION['securimage_code_value'] = '';  // clear code to prevent session re-use
                        } 

		if ( ! $valid ) {

			M()->user('captcha', $this->id());
			$valid = false;
		}

		return $valid;

	}/*}}}*/

function now() {/*{{{*/

	return null;
}/*}}}*/

}

?>
