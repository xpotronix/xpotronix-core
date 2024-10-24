<?php
/*
	Archivo: /var/www/sites/xpotronix//cassaba/modules/notificacion.php

*/

namespace App;
use \Xpotronix\DataObject;


global $xpdoc;


class notificacion extends DataObject {

	/* var def */

	/* class functions */


	function genera_notificacion( DataObject $obj, array $params ) {/*{{{*/

		global $xpdoc;

		$params['serial_type'] or $params['serial_type'] = \Xpotronix\Serialize::DS_ANY;

		$params['base_url'] = 
			"{$xpdoc->http->request_scheme}://{$xpdoc->http->http_host}{$xpdoc->http->context_prefix}";

		$params['remitente'] or $params['remitente'] = 'indefinido'; 
		$params['template'] or $params['template'] = 'indefinido'; 


			if ( $this->debug ) {

				foreach( $params as $key => $value ) {

					M()->info( "$key: $value" );

			}

		}
		$n = $this;

		$n->reset();
		$n->module 	= $obj->class_name;

		$n->remitente = $params['remitente'];

		$xdoc = $obj->serialize_row( $params['serial_type'] );
		$n->xml_data	= $xdoc->asXML();

		$n->contenido 	= $xpdoc->transform( $params['template'], $xdoc, $params, $params['transform_type'], false );
		$n->key 	= $obj->pack_primary_key();
		$n->titulo 	= $params['titulo'];
		$n->flow_ID = $params['flow_ID'];

		$n->email 	= $params['email'];
		$n->seccion	= $params['seccion'];

		/* implmenta si recibe o no */
		$n->enviar_email = true;

		$n->get_attr('fecha_hora')->now();
		$n->fill_primary_key();
		$n->push_privileges( [ 'add' => 1 ] );
		$n->store();

		return $n;

 	}	/*}}}*/

	function enviar_notificaciones() {/*{{{*/

		set_time_limit( 0 );

		foreach( $this->load_set( [ 'enviar_email' => true, 'enviada' => '@@' ] ) as $n ) 
			$n->enviar();

	}/*}}}*/

	function enviar( $xml = null ) {/*{{{*/

		global $xpdoc;

		if ( is_object( $xml ) and ( $ID = $xml['ID'] ) ) {

			M()->info( "cargando notificacion con ID [$ID]" );
			$this->load( $ID );
		}

		if ( ! $this->loaded() ) {

			M()->error( "no se ha definido una notificacion para enviar" );
			return false;
		}

		$mailer = $xpdoc->instance('mailer');

		$from = $this->remitente;

		strstr( $from, '@' ) or
			$from .= "@{$xpdoc->feat->default_domain}";

		$to = ( $t = $xpdoc->config->test_email ) ? $t : $this->email;

		strstr( $to, '@' ) or
			$to .= "@{$xpdoc->feat->default_domain}";

		M()->user( "remitente: $this->remitente, destino: $this->email, from: $from, to: $to" );

		$from_name = ( $t = $xpdoc->feat->mailer_from_name ) ? $t: $from;

		$mailer->from( $from , $from_name )
			->to( $to )
			->subject( $this->titulo )
			->html( $this->contenido );

			/* DEGUB: falta agregar imagenes inline */
			/* ->img_inline( 'images/header-email.png', 'header-email' ); */

		if ( $mailer->send()->ErrorInfo ) {

			M()->user( "No se pudo enviar: {$mailer->ErrorInfo}" );
			$this->estatus = $mailer->ErrorInfo;
			$this->reintento++;

		} else {

			$this->get_attr('enviada')->now(); // Citacion con Exito
		}

		$this->store();

	}/*}}}*/

	function regenera_contenido( $xml ) {

		global $xpdoc;

		if ( $ID = (string) $xml['ID'] ) {

			$this->load( $ID );

			if ( ! $this->loaded() ) {

				M()->user( "No encuentro la notificacion con el ID [$ID]" );
				return false;
			}

		} else {

			M()->user( "Debe seleccionar una notificacion para regenerar" );
			return false;
		}


		M()->user( "Regenerando contenido para la notificacion con ID [$ID]" );

		$obj = $xpdoc->get_instance( $this->module );
		$obj->load( $this->key );

		if ( ! $obj->loaded() ) {
		
			M()->user( "No encuentro el objeto [$this->module] con el ID [$this->key]" );
			return false;
		}

		M()->user( "regenera notificacion para el objeto [{$obj->class_name}] con ID [{$this->key}]" );

		$legajo = $this->legajo;
		$email = $this->email;

		if ( !$email ) {

			if ( $legajo ) {

				$email = $xpdoc->get_instance('v_empleado_min')->load($legajo)->usuario;

			} else {

				M()->error( "no hay ni email ni legajos definidos!" );
				return false;
			}
		}


		$params = [

		'legajo' => $legajo,
		'email' => $email,
		'seccion' => $this->seccion,
		'remitente' => $this->remitente,
		'default_domain' => $xpdoc->feat->default_domain,
		'base_url' => "{$xpdoc->http->request_scheme}://{$xpdoc->http->http_host}{$xpdoc->http->context_prefix}" 
		];

		/* aca es otro el link para redireccionar */
		$params['base_url']="{$xpdoc->http->request_scheme}://miportal.{$xpdoc->feat->default_domain}";

		$xdoc = $obj->serialize_row( \Xpotronix\Serialize::DS_ANY );

		$this->contenido = $xpdoc->transform( 'miportal/licencia/email', $xdoc, $params, 'bridge', false );

		$this->update();
	}

}

?>
