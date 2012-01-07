<?php

require_once 'xphttp.class.php';

/**
 * @package xpotronix
 * @version 2.0 - Areco 
 * @copyright Copyright &copy; 2003-2011, Eduardo Spotorno
 * @author Eduardo Spotorno
 *
 * Licensed under GPL v3
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */


class xpmenu {

	private $xmenu;

	function __construct( $file = 'menu.xml' ) {/*{{{*/

		global $xpdoc;		

		try {

	       	  	$this->xmenu = simplexml_load_file( $file );
	        	M()->info( "cargando el menu desde $file" );

		} catch (Exception $e) {

	        	M()->info( "no puedo encontrar el menu en $file" );
	       	  	$this->xmenu = new SimpleXMLElement( '<menu/>' );
		}

		$this->xmenu['username'] = $xpdoc->user->user_username;

		return $this;
	}/*}}}*/

	function asXML() {/*{{{*/

		return $this->xmenu->asXML();
	}/*}}}*/

	function get_xml() {/*{{{*/

		// print $this->menu_acl( $this->xmenu )->asXML();
		return $this->menu_acl( $this->xmenu );
	}/*}}}*/

	function menu_acl( $menu ) {/*{{{*/

		global $xpdoc;

		foreach( $menu->children() as $item ){

			$name = $item->getName();

			if ( ! ( $name == 'menu' or $name == 'item' ) ) continue;

			if ( isset( $item['acl'] ) ) {

				list( $access, $value ) = explode( ':', $item['acl'] );

				if ( $access == 'role' ) {

					if ( strstr( $value, ',' ) )
						$item['access'] = $xpdoc->has_role( explode( ',', $value ) );
					else 
						$item['access'] = $xpdoc->has_role( trim( $value ) );

				} else {

					M()->debug( "access: $access, value: $value" );

					$item['access'] = $xpdoc->perms->acl_check( 'application', $access, 'user', $xpdoc->session->user_id, 'app', $value );

					M()->debug( "ACL :: elem: $name, nombre: {$item['n']}, requiere: $access, value: $value, permite: {$item['access']}" );
				}
			}


			if ( $item->children() ) {

				$this->menu_acl( $item );

			} else {

				 // print $item->getName(). '\n';
			}
		}

		return $menu;
	} /*}}}*/

	function transform() {/*{{{*/

		global $xpdoc;

		$xml = dom_import_simplexml( $this->xmenu );

		$xsl = new DOMDocument;
		$xsl->load( $xpdoc->get_template_file( 'menu' ) );

		$proc = new XSLTProcessor;
		$proc->importStyleSheet($xsl);

		$ret = $proc->transformToXML($xml);

		return $ret;

	}/*}}}*/

}

?>
