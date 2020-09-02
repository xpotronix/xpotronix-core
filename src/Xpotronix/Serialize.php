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

namespace Xpotronix;

class Serialize {

	var $obj;
	var $flags;

	/* constantes para la serializacion */

	const DS_ANY = 1;
	const DS_NORMALIZED = 1 << 1;
	const DS_RECURSIVE = 1 << 2;
	const DS_BLANK = 1 << 3;
	const DS_DEFAULTS = 1 << 4;


	function __construct( $obj, $flags = 0 ) {/*{{{*/

		$this->obj = $obj;
		$this->flags = $flags;

	}/*}}}*/

	function serialize ( $flags = 0 ) {/*{{{*/

		global $xpdoc;

		$flags or $flags = $this->flags;

		$any = $flags & self::DS_ANY;
		$normalized = $flags & self::DS_NORMALIZED;
		$recursive = $flags & self::DS_RECURSIVE;
		$blank = $flags & self::DS_BLANK;
		$defaults = $flags & self::DS_DEFAULTS;

		$e = $normalized ? 'class' : $this->obj->feat->container_tag;

		/* DEBUG: pasar el encoding como parametro */
		/* $xc = new \SimpleXMLElement( "<?xml version=\"1.0\" encoding=\"$encoding\"?><$e/>" ); */
		$xc = new \SimpleXMLElement( "<?xml version=\"1.0\" encoding=\"UTF-8\"?><$e/>" ); 
		$xc['name'] = $this->obj->class_name;

                if ( ! $this->obj->persistent() ) {

                        M()->warn( "No puedo serializar el objeto virtual {$this->obj->class_name}" );
			$xc['total_records'] = 0;
                        $xc['msg']='IS_VIRTUAL';
                        return $xc;
                }

		if ( is_object( $xpdoc->perms ) and ( ! ( $this->obj->can('view') or $this->obj->can('list') ) ) ) {

			M()->info( "Acceso denegado para el objeto {$this->obj->class_name}" );
			$xc['msg']='ACC_DENIED';
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

		} else M()->warn( "no se ha definido ni self::DS_ANY ni self::DS_NORMALIZED ni self::DS_RECURSIVE: no hay registros a serializar. Revise directiva 'include_dataset'" );

		$xc['total_records'] = $this->obj->total_records ;
		$xc['xmlns'] = null;
		$xc['page_rows'] = $this->obj->pager['pr'];
		$xc['current_page'] = $this->obj->pager['cp'];

		M()->info( "serialize con any: $any, normalized: $normalized, recursive: $recursive, blank: $blank, defaults: $defaults" );
		$objs and M()->info( "con cantidad de objetos: ". $objs->count() );
		
		if ( $blank and ( $any or $normalized or $recursive ) and ( $objs->count() ) ) { 

			M()->info( "is blank" );
			// $flags = $flags ^ self::DS_BLANK; 
		}
		
		if ( $objs and $objs->count() ) {

			M()->info( "has objs, count: ". $objs->count() );
			foreach ( $objs as $obj ) {

				simplexml_append( $xc, $this->serialize_row( $flags ) );
			} 

		} else if ( $blank ) {  

			M()->info( "recurse blank" );
			simplexml_append( $xc, $this->serialize_row( $flags ) );
		} 

		return $xc;

	}/*}}}*/

	function serialize_row( $flags = 0, $params = [] ) {/*{{{*/

		$normalized = $flags & self::DS_NORMALIZED;
		$recursive = $flags & self::DS_RECURSIVE;
		$blank = $flags & self::DS_BLANK;
		$defaults = $flags & self::DS_DEFAULTS;

		global $xpdoc;

		if( ! isset( $params['prepare_data'] ) or $params['prepare_data'] == true ) {
			M()->info( "llamando a prepare_data()" );
			$this->obj->prepare_data();
		}

		// $this->obj->debug_object();

		M()->debug( "serializando instancia objeto {$this->obj->class_name} con flags = $flags" );

		$xobj = new \SimpleXMLElement( $normalized ? '<obj/>' : "<{$this->obj->class_name}/>" );
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

				/*
				print( $iname ); print( "<br/>" );
				$xpdoc->instances[$iname]->debug_backtrace(); continue;print( "<br/>" );
				print var_dump( isset( $params['ignore'] ) and is_array( $params['ignore'] ) and ( in_array( $iname, $params['ignore'] ) ) ); exit;
				*/

				if ( ! isset( $xpdoc->instances[$iname] ) ) {
					M()->warn( "instancia $iname ignorada: no la encuentro");

				} else if ( isset( $params['ignore'] ) and is_array( $params['ignore'] ) and ( in_array( $iname, $params['ignore'] ) ) ) {
					M()->info( "instancia $iname ignorada: via parametro");

				} else {

					M()->info( "recursivo, hacia el objeto $iname" );
					simplexml_append( $xobj, $xpdoc->instances[$iname]->serialize( $flags ) );
				}
			}
		}

		return $xobj;

	}/*}}}*/ 

	function serialize_attr( $attr, $flags = 0 ) {/*{{{*/

		$normalized = $flags & self::DS_NORMALIZED;

		$attr_name = $attr->name;
		$value = $attr->serialize();

		/* value */

		if ( $attr->cdata == 'true' ) {

			// print "cdata $attr->name"; exit;
			//
			//
			// $value = $attr->serialize( $attr->value );
			$value = $attr->value;

			try {

				/* print "hola [$value]"; exit; */

				/* CDATA */
				$xattr = new SimpleXMLExtended( 
					$normalized ? 
						"<attr name=\"$attr_name\"><![CDATA[$value]]></attr>" : 
						"<$attr_name><![CDATA[$value]]></$attr_name>" );

			} catch ( \Exception $e )  { 

				M()->warn( "No puedo serializar el objeto {$this->obj->class_name} con la clave [". 
					$this->obj->pack_primary_key(). 
					"] en el atributo [$attr_name] con el valor [$value]. Motivo: ". 
					$e->getMessage() );
			}

		} else {

			try {

				/* normalizado o no */
				/* TRY 1 */
				$xattr = new \SimpleXMLElement( 
					$normalized ? 
					"<attr name=\"$attr_name\">$value</attr>" : 
					"<$attr_name>$value</$attr_name>" 
				);

			} catch ( \Exception $e ) { 

				M()->warn( 'problemas con la codificacion en la serializacion, verificar la codificacion de caracteres de la base de datos y la aplicacion. Forzando la codificacion a utf8' );
				$value = utf8_encode( $value );

				try {

					/* TRY 2 */
					$xattr = new \SimpleXMLElement( 
						$normalized ? 
							"<attr name=\"$attr_name\">$value</attr>" : 
							"<$attr_name>$value</$attr_name>" );

				} catch ( \Exception $e )  { 

					M()->warn( "No puedo serializar el objeto {$this->obj->class_name} con la clave [". 
						$this->obj->pack_primary_key(). 
						"] en el atributo [$attr_name] con el valor [$value]. Motivo: ". 
						$e->getMessage() );
				}
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
