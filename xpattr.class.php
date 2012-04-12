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

class xpattr extends xp {

   var $obj;
   var $sql;
   var $data;
   var $xml;
   var $metadata;


   function __construct( $xml_node = NULL ) {/*{{{*/

	   global $xpdoc;

	   if ( $xml_node ) {
		$this->metadata = clone $xml_node;
		$this->name = (string) $xml_node['name'];
		}
	   else 
		$this->metadata = new SimpleXMLElement('<attr/>');

	   if ( !$this->metadata['type'] ) $this->metadata['type'] = 'varchar';

	   $this->metadata['modified'] = false; // DEBUG: los modified no van en el metadata, van el data

	   return $this;

   }/*}}}*/

   function __get( $var_name ) {/*{{{*/


      // $this->value devuelve el valor
      if ( $var_name == 'value' ) 
      	if ( isset( $this->obj->data[$this->name] ) )
	 return $this->obj->data[$this->name];
	else return NULL;

      // $this->label queda en data, no en metadata
      if ( $var_name == 'label' ) 
      	if ( isset( $this->obj->data[$this->name.'_label'] ) )
	 return $this->obj->data[$this->name.'_label'];
	else return NULL;

      if ( !isset( $this->metadata[ $var_name ] ) ) {

	 switch( $var_name ) {

	    // tags no obligatorios

	    case 'var';
	    case 'alias';
	    case 'alias_of':
	    case 'entry_help':
	    case 'entry_help_table':
	    case 'validate':
	    case 'filters':
	    case 'display':
	    case 'label':
	    case 'with':
	    case 'primary_key':
	    case 'foreign':
	    case 'search_type':
	    case 'virtual':
	    case 'encoding':

	       return NULL;
	       break;

	    default:

		  M()->debug( "La variable $var_name no esta definida en este objeto" );
	 }
      }

      return (string) $this->metadata[ $var_name ] ;
    }/*}}}*/

   function __set( $var_name, $var_value ) {/*{{{*/

      if ( $var_name == 'value' ) { 

		// if ( $this->primary = 'yes' ) $this->obj->set_primary_key(); 

		// if ( !$this->obj ) { print $this->debug_backtrace( $this ); exit; }

		if ( $this->obj->track_modified ) {
      	 		$this->modified = true;
	 		$this->obj->modified = true;
			M()->debug("modificado $this->name");
		}
	 	return $this->obj->data[$this->name] = $var_value;

	 } else if ( $var_name == 'label' ) {

		// label no va en el metadata, va en los data con su propio nombre de campo
		$key = $this->name. '_label';
		return $this->obj->data[$key] = $var_value;
	
	}

      return (string) $this->metadata[$var_name] = $var_value ;
   }/*}}}*/

	function set( $var_name, $var_value ) {/*{{{*/

		$this->__set( $var_name, $var_value );
		return $this;

	}/*}}}*/

	// consulta

	function is_key() {/*{{{*/

		return $this->is_primary_key() or $this->is_foreign_key();
	}/*}}}*/

   function is_primary_key() {/*{{{*/
  
        return $this->primary;

   }/*}}}*/

   function is_foreign_key() {/*{{{*/
   
        return $this->foreign;

   }/*}}}*/

	function is_entry_help() { /*{{{*/

		return (bool) $this->entry_help; 

	}/*}}}*/

   function get_name( $suffix = NULL ) {/*{{{*/

	// mapeo del nombre para html como name

      $ret = "data[";
      $ret .= $this->table;
      $ret .= "][";
      $ret .= $this->name;
      $ret .= "]";
      $ret .= ($suffix) ? '_' . $suffix : ""; 

      return $ret;
   }/*}}}*/

   function get_id( $suffix = NULL ) {/*{{{*/

	// mapeo del nombre para html como id

      $ret = $this->table;
      $ret .= "_";
      $ret .= $this->name;
      $ret .= ($suffix) ? '_' . $suffix : "";

      return $ret;
   }/*}}}*/

	function get_simple_type() {/*{{{*/

		global $xpdoc;

                if ( ! is_object( $xpdoc->datatypes ) )
                        $xpdoc->load_datatypes();

		$ret = $xpdoc->datatypes->xpath( "xtype[@name='{$this->type}']/@type" );

		return ( is_array( $ret ) ) ? 
			array_shift( $ret ): 
			null;

	}/*}}}*/

	// serializacion

 	function get_element() {/*{{{*/

		$e = new SimpleXMLElement( sprintf( "<attr>%s</attr>", $this->serialize() ) );
		$e['name'] = $this->name;
		$e['obj'] = $this->obj->class_name;
		$e['ID'] = $this->obj->pack_primary_key();
		return $e;

	}/*}}}*/

	function encode( $value = NULL ) {/*{{{*/
		// codifica los valores para la base de datos

		if ( $value === NULL ) $value = $this->value;

		return addslashes( $value );
	}/*}}}*/

	function decode( $value = NULL ) {/*{{{*/

		// decodifica los valores de la base de datos
		if ( $value === NULL ) $value = $this->value;

		return stripslashes( $value );
	}/*}}}*/

	function serialize( $value = NULL ) {/*{{{*/
	
		if ( $value === NULL ) $value = $this->value;
			return htmlspecialchars( $value ); 

	}/*}}}*/

	function unserialize( $value = NULL ) {/*{{{*/

		if ( $value === NULL) 
			$value = $this->value;

		return html_entity_decode( $value ); 
	}/*}}}*/

function metadata() {/*{{{*/

	$this->is_primary_key() and  $this->obj->feat->hide_pk and $this->display = 'hide';
	$this->is_foreign_key() and  $this->obj->feat->hide_fk and $this->display = 'hide';

	return $this->metadata;

}/*}}}*/

	function assign_attr_xml( $xml ) {/*{{{*/

		$value = $this->unserialize( (string) $xml );

		M()->info( "prev value: $value" );

		if ( $this->value != $value ) {

			$this->value = $value;
			$this->obj->modified = true;
			M()->info($this->obj->class_name.'::'.$this->name.' = '.$this->value );
		}

		// $this->debug_object(); exit;
	
	}/*}}}*/

   function bind( $hash ) {/*{{{*/

	if ( isset( $hash[ $this->name ] ) ) 
		$this->value = $this->decode( $hash[ $this->name ] );

	if ( $this->is_entry_help() ) {

		@$value = $hash[$this->name. '_label'];

		$this->label = $value ? $value : null;

		$value and M()->debug( $this->label );
	}

   }/*}}}*/

	// funciones virtuales

	function not_empty() {/*{{{*/

		$valid = true;


		if ( (bool) ($this->value == '' or $this->value === NULL ) ) {

			M()->user( 'Debe especificar un(a) '. $this->obj->translate() .'/'. $this->translate(), $this->id(), 'empty' ) ; 

			$valid = false;
		}

		return $valid;

	}/*}}}*/

	function validate() {/*{{{*/

		M()->info("validando el attr {$this->obj->class_name}::{$this->name} con el esquema [{$this->validate}]");

		$valid = true;

		// validaciones

		$validations = explode( ',', strtolower( str_replace( ' ','', $this->validate ) ) );

		// print $this->validate . "<br/>";

		// $this->obj->debug_object(); exit;

		foreach( $validations as $validation ) {

			if ( trim( $validation ) == '' ) continue;

			$validation == 'empty' and $validation = 'not_empty';

			if ( !method_exists( $this, $validation ) ) {

				M()->error("el metodo para la directiva de validacion (validate) con el nombre $validation no existe" );
				continue;
			}

			if ( !$this->$validation() ) 
				$valid = false;
		}

		return $valid;

	}/*}}}*/

	function filter() { /*{{{*/

		M()->info("filtrando el attr {$this->obj->class_name}::{$this->name} con el esquema [{$this->validate}]");

		$valid = true;

		if ( $this->filters ) {

			$filters = explode( ',', strtolower( str_replace( ' ','', $this->filters ) ) );

			foreach( $filters as $filter ) {

				if ( !method_exists( $this, $filter ) ) {

					M()->error( "el metodo para la directiva de filtrado (filters) con el nombre $filter no existe" );
					continue;
				}

				if ( !$this->$filter() ) 
					$valid = false;
			}
		}

		return $valid;

	}/*}}}*/

	function translate() {/*{{{*/

		return $this->translate ? $this->translate : $this->name;

	}/*}}}*/

	function id() {/*{{{*/

		return $this->obj->class_name. ':'. $this->name;


	}/*}}}*/

}

// vim600: fdm=marker sw=3 ts=8 ai:
?>
