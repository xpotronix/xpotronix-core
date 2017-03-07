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

class xpserialize {

	var $obj;
	var $flags;

	function __construct( $obj, $flags = null ) {/*{{{*/

		$this->obj = $obj;
		$this->flags = $flags;

	}/*}}}*/

	function serialize ( $flags = null ) {/*{{{*/

		global $xpdoc;

		$flags or $flags = $this->flags;

		$any = $flags & DS_ANY;
		$normalized = $flags & DS_NORMALIZED;
		$recursive = $flags & DS_RECURSIVE;
		$blank = $flags & DS_BLANK;
		$defaults = $flags & DS_DEFAULTS;

		$e = $normalized ? 'class' : $this->obj->feat->container_tag;

		/* DEBUG: pasar el encoding como parametro */
		/* $xc = new SimpleXMLElement( "<?xml version=\"1.0\" encoding=\"$encoding\"?><$e/>" ); */
		$xc = new SimpleXMLElement( "<?xml version=\"1.0\" encoding=\"UTF-8\"?><$e/>" ); 
		$xc['name'] = $this->obj->class_name;


		if ( is_object( $xpdoc->perms ) and ( ! ( $this->obj->can('list') and $this->obj->can('view') ) ) ) {

			M()->info( "acceso denegado para el objeto {$this->obj->class_name}" );
			$xc['msg']='ACC_DENIED';
			return $xc;
		}

                if ( $this->obj->is_virtual() and ! $this->obj->count_views() ) {

                        M()->warn( "no puedo serializar el objeto virtual {$this->obj->class_name}, count_views: ". $this->obj->count_views() );
			$xc['total_records'] = 0;
                        $xc['msg']='IS_VIRTUAL';
                        return $xc;
                }

		$objs = null;

		if ( $any or $normalized or $recursive ) {

			if ( $recursive && is_object( $this->obj->parent ) && is_object( $this->obj->foreign_key ) ) {

				M()->debug( 'load con fk '. serialize( $this->obj->foreign_key->get_key_values() ) );

				if ( isset( $this->obj->foreign_key->refs[0]->expr ) ) 
					$objs = $this->obj->load_page( NULL, $this->obj->foreign_key->get_key_values() ); 
				else
					$objs = $this->obj->load_page( $this->obj->foreign_key->get_key_values() ); 
			}
			else {
				M()->debug( 'load sin fk' );
				$objs = $this->obj->load_page(); 
			}

		} else M()->warn( "no se ha definido ni DS_ANY ni DS_NORMALIZED ni DS_RECURSIVE: no hay registros a serializar. Revise directiva 'include_dataset'" );

		$xc['total_records'] = $this->obj->total_records ;
		$xc['xmlns'] = null;
		$xc['page_rows'] = $this->obj->pager['pr'];
		$xc['current_page'] = $this->obj->pager['cp'];

		M()->info( "serialize con any: $any, normalized: $normalized, recursive: $recursive, blank: $blank, defaults: $defaults, con cantidad de objetos: ". count( $objs ) );
		
		if ( $blank and ( $any or $normalized or $recursive ) and ( $objs ) )

			{ $flags = $flags ^ DS_BLANK; }
		
		if ( $objs ) {

			foreach ( $objs as $obj ) {

				$this->obj->prepare_data( $obj ); // DEBUG: prepare_data deberia ir directamente en el iterador?
				simplexml_append( $xc, $this->serialize_row( $flags ) );
			} 

		} else if ( $blank ) {  

			simplexml_append( $xc, $this->serialize_row( $flags ) );
		} 

		return $xc;

	}/*}}}*/

	function serialize_row( $flags = null, $params = null ) {/*{{{*/

		$normalized = $flags & DS_NORMALIZED;
		$recursive = $flags & DS_RECURSIVE;
		$blank = $flags & DS_BLANK;
		$defaults = $flags & DS_DEFAULTS;


		global $xpdoc;

		// $this->obj->debug_object();

		M()->debug( "serializando instancia objeto {$this->obj->class_name}" );

		$xobj = new SimpleXMLElement( $normalized ? '<obj/>' : "<{$this->obj->class_name}/>" );
		if ( $normalized ) $xobj['name'] = $this->obj->class_name;


		if ( $blank ) {

			$this->obj->reset();
			$this->obj->fill_primary_key( true );
			$this->obj->set_primary_key();
			$this->obj->foreign_key and $this->obj->foreign_key->assign();
			$defaults and $this->obj->defaults();
		}

		$xobj['ID'] = $this->obj->pack_primary_key();
		$xobj['edit'] = (string) $this->obj->has_access('edit');
		$xobj['delete'] = (string) $this->obj->has_access('delete');
		$xobj['new'] = (string) (bool) $blank;


		foreach ( $this->obj->attr as $key => $attr ) {

			// print $attr->name;
			// print $attr->value;

			if ( $attr->display == 'protect' or $attr->display == 'ignore' or $attr->display == 'sql' ) continue;
			if ( !$blank and $attr->value === null and $this->obj->feat->ignore_null_fields ) continue;


			simplexml_append( $xobj, $this->serialize_attr( $attr, $flags ) );
		}

		if ( $recursive ) {

			$iname = null;

			foreach ( $this->obj->model->xpath( "obj" ) as $obj ) {

				$iname = (string) $obj['name'];

				if ( ! isset( $xpdoc->instances[$iname] ) ) 
					M()->warn( "instancia $iname ignorada: no la encuentro");
				else {

					M()->info( "recursivo, hacia el objeto $iname" );

					if ( is_array( $params ) and is_array( $params['ignore'] ) and ( in_array( $iname, $params['ignore'] ) ) )
						continue;
					else
						simplexml_append( $xobj, $xpdoc->instances[$iname]->serialize( $flags ) );

				} 
			}
		}

		return $xobj;

	}/*}}}*/ 

	function serialize_attr( $attr, $flags ) {/*{{{*/

		$normalized = $flags & DS_NORMALIZED;
		$defaults = $flags & DS_DEFAULTS;

		$attr_name = $attr->name;
		$value = $attr->serialize();

		/* value */

		try {
			$xattr = new SimpleXMLElement( $normalized ? "<attr name=\"$attr_name\">$value</attr>" : "<$attr_name>$value</$attr_name>" );

		} catch (Exception $e)  { 

			M()->warn( 'problemas con la codificacion en la serializacion, verificar la codificacion de caracteres de la base de datos y la aplicacion. Forzando la codificacion a utf8' );
			$value = utf8_encode( $value );

			try {
				$xattr = new SimpleXMLElement( $normalized ? "<attr name=\"$attr_name\">$value</attr>" : "<$attr_name>$value</$attr_name>" );

			} catch (Exception $e)  { 

				M()->warn( "No puedo serializar el objeto {$this->obj->class_name} con la clave [". $this->obj->pack_primary_key(). "] en el atributo [$attr_name] con el valor [$value]. Motivo: ". $e->getMessage() );
			}
		}

		/* DEBUG: deberia ser al reves, que en text() quede el valor real y en un @label el otro */

		if ( $attr->type == 'xpdate' or $attr->type == 'xpdatetime' ) {

			$xattr->addAttribute( 'value', $attr->value );
		}

		/* label */

		if ( $attr->label ) {

			// $attr->label and $xattr['label'] = utf8_encode( $attr->serialize( $attr->label ) );
			// $attr->label and $xattr['label'] = $attr->serialize( $attr->label );
			// $attr->label and $xattr['label'] = $attr->label;
			// $attr->label and $xattr['label'] = utf8_decode( $attr->label );

			$xattr->addAttribute( 'label', $attr->label );
			// M()->user( "label " . $attr->label );
		}


		return $xattr;
	}/*}}}*/

}

?>
