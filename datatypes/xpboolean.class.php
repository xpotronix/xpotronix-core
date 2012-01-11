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

class xpboolean extends xpattr {

	function encode( $value = NULL ) {/*{{{*/
	// codifica los valores para la base de datos

		if ( $value === NULL ) $value = $this->value;

		switch( $value ) {

			case true:
				$value = 1;
				break;

			case false:
				$value = 0;
				break;

			case NULL:
			default:
				$value = NULL;

		}

		return $value;
	}/*}}}*/

	function decode( $value = NULL ) {/*{{{*/
	// decodifica los valores de la base de datos
		
		if ( $value === NULL ) $value = $this->value;

		return (boolean) $value ;
	}/*}}}*/

	function serialize( $value = NULL ) { /*{{{*/
	
		if ( $value === NULL ) $value = $this->value;

		M()->debug( 'value: '. var_export( $value, true ) );

		switch( $value ) {

			case true:
				$value = 'true';
				break;

			case false:
				$value = 'false';
				break;

			case NULL:
			default:
				$value = '';

		}

		return $value;

	}/*}}}*/

	function unserialize( $value = NULL ) { /*{{{*/

		if ( $value === NULL ) $value = $this->value;

		M()->debug( 'value: '. var_export( $value, true ) );

		switch( $value ) {

			case '1':
			case 'on':
			case 'true':
				M()->debug( 'valor true' );
				$value = true;
				break;

			case '0':
			case 'off':
			case 'false':
				M()->debug( 'valor false' );
				$value = false;
				break;

			case '':
			default:
				M()->debug( 'valor null' );
				$value = NULL;
		}

		return $value;
	}/*}}}*/

}

?>
