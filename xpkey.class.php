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


class ref {

	var $local;
	var $remote;
	var $expr;
}

class xpkey {

	var $obj;
	var $parent;
	var $remote;
	var $set;
	var $expr;
	var $refs = array();

	function __construct( $obj, SimpleXMLElement $xk ) {/*{{{*/

		$this->obj = $obj;
		$this->set = (string) $xk['set'];
		$this->parent = $obj->parent;

		foreach( $xk->children() as $xref ) {

			$ref = new ref;

			( $t = (string) $xref['local'] ) and $ref->local = $t;
			( $t = (string) $xref['remote'] ) and $ref->remote = $t;
			( $t = (string) $xref['expr'] ) and $ref->expr = $t;
		
			$this->refs[] = $ref;
		} 
		
		return $this;
	}/*}}}*/

	function get_key_values() {/*{{{*/

		if ( ! $this->parent ) return null;

		$ret = array();

		foreach ( $this->refs as $ref ) {

			if ( isset( $ref->expr ) ) {

				M()->debug( "parseo $ en expresion: $ref->expr " );
				return $this->parse( $ref->expr );
			}

			else if ( strstr( $ref->remote, '$' ) ) {

				M()->debug( "parseo $ en expresion: [$ref->remote]" );
				$ret[$ref->local] = $this->parse( $ref->remote );

			} else {

				$value = $this->parent->{$ref->remote};
				$ret[$ref->local] = $value;
				M()->debug( "valor del foreign_key: {$this->obj->table_name}::{$ref->local} = $value" ); 
			}
		}

		M()->debug( serialize( $ret ) );

		return $ret;
	}/*}}}*/

	function parse( $expr, $as_sql = false ) {/*{{{*/

		preg_match_all( "/[\$][A-Za-z0-9_\.]+/", $expr , $match );

		// var_dump( $match );
		// echo '<pre>';

		$pairs = array();

		foreach( $match[0] as $token ) {

			// print $token."\n";

			$var = str_replace( '$', '', $token );

			if ( count( $parts = explode( '.', $var ) ) == 1 ) {

				$value = $this->parent->{$parts[0]};

			} else {

				global $xpdoc;

				if ( $obj = $xpdoc->get_instance( $parts[0] ) ) {

					$value = $obj->{$parts[1]};

				} else {

					M()->error( "no encuentro el objeto [{$parts[0]}] para la variable [$token]" );
					continue;
				}
			}

			$pairs[$var] = $value;
			M()->debug( "variable: $var, value: [$value]" );
		}

		foreach( $pairs as $var => $value ) 
			$expr = str_replace( '$'.$var, $value, $expr );

		M()->debug( "expr: $expr" );

		return $expr;

	}/*}}}*/

	function assign() {/*{{{*/

		if ( $this->refs[0]->expr )
			M()->warn( "no puedo asignar una clave foranea basada en una expresion" );
		else
			$this->obj->bind( $this->get_key_values(), true );

		return $this->obj;

	}/*}}}*/

}

?>
