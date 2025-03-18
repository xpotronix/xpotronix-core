<?php
/*
	Archivo: /var/www/sites/xpotronix//xpay_electoralcaba/modules/users.php

*/

namespace App;
use \Xpotronix\DataObject;


global $xpdoc;


class users extends DataObject {

	/* var def */


	const DECL_GUARDADA_OK = "La declaración ha sido guardada";
	const NOTIFICACION_REGISTRO= "%s: Código de autentificación";
	const NOTIFICACION_ACCESO= "%s: Acceso cedido";
	const NOTIFICACION_RESETEO_CLAVE= "%s: Solicitud de reseteo de clave de acceso";
	const NOTIFICACION_RENOVACION_CLAVE= "%s: Solicitud de renovación de clave de acceso";

	private $ldap_host;
	private $ldap_port;
	private $ldap_version;
	private $ldap_base_dn;
	private $ldap_search_user;
	private $ldap_search_pass;	
	private $ldap_default_domain;	
	private $ldap_bind_format;	
	private $ldap_user_filter;
	private $username;
	private $fallback;

	var $state;
	var $user_prefs;

	
	/* class functions */


	function __construct() {/*{{{*/
		
		global $xpdoc;
		$this->state = array();
		// $this->setUserLocale($this->base_locale);
		$this->user_prefs = array();

		/* fallback */

		$this->fallback = isset( $xpdoc->config->ldap_allow_login ) ? 
			$xpdoc->config->ldap_allow_login : 
			false;

		parent::__construct();

	}/*}}}*/

		function main_sql() {/*{{{*/

			global $xpdoc;

			if ( ! $this->get_flag( 'main_sql' ) ) return;

			if ( ! $this->has_role( 'admin' ) )
				$this->sql->addWhere( "user_id = '{$xpdoc->session->user_id}'" );

		}/*}}}*/

	function check() {/*{{{*/

		M()->info();

		$this->get_attr( 'user_username' )->modified 
			and $this->user_username = strtolower(trim($this->user_username)); // DEBUG: falta sanitize

		$this->get_attr( 'user_password' )->modified 
			and strlen( $this->user_password ) != 32 and $this->user_password = $this->crypt( $this->user_password ) ;

		global $xpdoc;

		$u = $xpdoc->instance('users');

		if ( $this->is_new() and $u->load( array( 'user_username' => $this->user_username ) ) ) {

			M()->user( "Usuario [$this->user_username] existente" );
			return false;
		}

		return parent::check();

	}/*}}}*/

	function post_check() {/*{{{*/

                global $xpdoc;

		M()->info();

                $function = $this->is_new() ? 'addLogin' : 'updateLogin' ;
                if ( ! $xpdoc->perms->$function($this->user_id, $this->user_username) )
			M()->error( 'no pude agregar permisos para el usuario '. $this->user_username );
                
                return $this;

	}/*}}}*/

	function pre_delete() {/*{{{*/

		global $xpdoc;

		M()->info();

		$as = $xpdoc->instance('gacl_aro');

		foreach ( $as->load_set( array( 'value' => $this->user_id ) ) as $a )
			$a->delete();

		return true;
	}/*}}}*/

        function default_prefs() {/*{{{*/

                $this->load_prefs( 0 );

        }/*}}}*/ 

	/* notificacion */

	function proc_genera_notificacion( $xml ) {/*{{{*/

		global $xpdoc;

		/* ID via URL o $xml */

		$ID = $xpdoc->http->ID or $ID = $xml['ID'];
		$this->feat->blob_load = true;

		if ( !$this->load( $ID ) ) {

			M()->error( "No encuentro la declaracion con el ID [$ID]" );
			M()->status( 'ERR' );

			return null;

		}

		$legajo = $xpdoc->user->legajo; 
	
		M()->status('OK');

		/* genera_notificacion si el empleado existe */

		$empleado = $xpdoc->get_instance('v_empleado_min');

		if ( $empleado->load( $this->legajo )->loaded() ) {

			$titulo = $this->compose( sprintf( self::NOTIFICACION_REGISTRO, $this->feat->page_title ) );

			$this->genera_notificacion( [
				'titulo' => $titulo,
				'legajo' => $this->legajo,
				'flow_ID' => $this->ID,
				'email' => $empleado->usuario,
				'seccion' => 'pase_a_borrador' ] );
		}

	}/*}}}*/

	function genera_notificacion( array $params ) {/*{{{*/

		global $xpdoc;

		@$legajo = $params['legajo'];
		@$email = $params['email'];

		/* default domain */

		isset( $params['default_domain'] ) 
			or $params['default_domain'] = $xpdoc->feat->default_domain;

		if ( !$email ) {

			if ( $legajo ) {

				$email = $xpdoc->get_instance('v_empleado_min')->load($legajo)->usuario;

			} else {

				M()->error( "no hay ni email ni legajos definidos!" );
			}
		}

		$params['base_url'] = "{$xpdoc->http->request_scheme}://{$xpdoc->http->http_host}{$xpdoc->http->context_prefix}";

		M()->info( "base_url: ". $params['base_url'] );

		/* genera la notificacion */

		$n = $xpdoc->get_instance( 'notificacion' );
		$n->reset();

		/* datos de la notificacion */

		$xdoc = $this->serialize_row( \Xpotronix\Serialize::DS_ANY );

		$n->xml_data	= $xdoc->asXML();

		// $this->debug_object(); exit;

		$n->remitente = 'miPortal';
		isset( $params['remitente'] ) or $params['remitente'] = $n->remitente;

		$n->contenido 	= $xpdoc->transform( 'miportal/licencia/email', $xdoc, $params, 'bridge', false );
		$n->module 	= $xpdoc->module;
		$n->key 	= $this->pack_primary_key();
		$n->titulo 	= $params['titulo'];
		$n->flow_ID	= $params['flow_ID'];

		$n->legajo	= $legajo;
		$n->email 	= $email;
		$n->seccion	= $params['seccion'];

		/* si recibe notificaciones via email segun configuracion perfil */
		$p = $xpdoc->get_instance( 'perfil' );
		$p->load( [ 'legajo' => $legajo ] );

		/* si tiene perfil cargado y no quiere recibir emails */
		$n->enviar_email = ! ( $p->loaded() and $p->recibe_email == false );

		$n->get_attr('fecha_hora')->now();
		$n->fill_primary_key();
		$n->push_privileges( [ 'add' => 1 ] );
		$n->store_xml_response();

		return $n;

	}	/*}}}*/

	/* actions */

	function change_password_old() {/*{{{*/

		/* oldie, renovar */

		global $xpdoc;

		$pass1 = $xpdoc->http->password;
		$pass2 = $xpdoc->http->password_repeat;

		$this->push_privileges( array( 'edit' => true ) );

		if ( !$pass1 or !$pass2 ) {

			M()->error( "Debe ingresar una clave válida" );

		} else if ( $pass1 != $pass2 ) {

			M()->error( "La claves difieren entre si, por favor, reingrese" );
	
		} else if ( ! $this->load( $this->user_id ) ) {

			M()->error( "Usuario Inexistente" );

		} else if ( $xpdoc->user->_anon )  {

			M()->error( "Debe ingresar primero para cambiar la clave" );

		} else if ( $this->user_password == $this->crypt( $pass1 ) ) {

			M()->error( "Debe ingresar una clave diferente a la anterior" );

		} else {

			$this->is_new( false );
			$this->user_password = $this->crypt( $pass1 ) ;
			$this->update();
		}

		$this->pop_privileges();

		$result = array();
		$result["errors"]["reason"] = implode( '; ', M()->get() );
		$result["success"] = ( M()->status() == 'ERR' ) ? 0 : 1;

		return $result;

	}/*}}}*/ 

	function recaptcha_validate() {/*{{{*/

		global $xpdoc;

		/* var_dump( $xpdoc->http->http_host ); exit; */

		if ( $xpdoc->config->recaptcha and
			in_array( $xpdoc->http->http_host, explode(';',$xpdoc->config->recaptcha_domains)) ){

			$ret = $xpdoc->recaptcha_verify();
		
		} else {

			/* devuelve -1 porque no esta habilitado */	
			$ret = -1;
		}

		M()->info( "ret: $ret" );

		return $ret;

	}/*}}}*/

	/* security procedure */

	function register( $user = null, $pass = null ) {/*{{{*/

		global $xpdoc;

		$xpdoc->set_view( 'json' );

		$result = [];

		if ( ! $this->recaptcha_validate() ) {

			   $result["errors"]["reason"] = "No se pudo validar el CAPTCHA";
			   $result["success"] = false;
			   M()->user("fallo ingreso usuario $user" );

		} else {


			extract( $xpdoc->http->var );

			$empleado = $xpdoc->instance('_empleado');

			$result['success'] = false;
			$errors = [];

			if ( ! $empleado->bind_post_vars() ) {

				$xpdoc->json = [ 'status' => 'ERR', 'msg' => M()->get() ];
				return;
			}

			$email = trim($email);

			if ( ! filter_var($email, FILTER_VALIDATE_EMAIL) ) {
				$errors[] = "Debe especificar un correo electrónico válido";
			}

			if ( strlen($RUT) < 11 ) {
				$errors[] = "Debe especificar un RUT válido";
			}

			$errors = [...$errors, ...$this->check_password_pair( $password, $passwordConfirm ) ];
			
			if( ! ( $nombre and $apellido and $genero and $RUT and $f_nac and $tel_celular ) ) {

				$errors[] = 'debe ingresar todos los campos';
			}

			if ( count( $errors ) ) {
			
				$result["errors"]["reason"] = $errors;
				$result["success"] = false;

				return $result;

			
			} else {

				/* datos validos, defaults */			

				$empleado->usuario = $empleado->email;
				$empleado->fh_ingreso = $empleado->agregado;
				$empleado->nac = 'ar';
				$empleado->legajo = $empleado->RUT;
				$empleado->canal = 'web';
				$empleado->estado = 'B';


				// $empleado->debug_object(); exit;

				$empleado->push_privileges(['add'=>1,'edit'=>1]);

				$r = $empleado->store();

				/* si efectivamente inserta */
				if ( $r == INSERT_OP or $r == UPDATE_OP ) {

					$titulo = $empleado->compose( sprintf( self::NOTIFICACION_REGISTRO, $this->feat->page_title ) );

					/* Crea el usuario, asignacion temporal de permisos */

					$user = $xpdoc->instance('users');

					$user->push_privileges(['edit'=>1,'add'=>1]);

					M()->info( "creando usuario $email ..." );

					if ( (bool) ( $user->create( $email, $password, false ) ) ) { /* sin roles */


						$user->validation_code = $test = sprintf('%06d', rand(1, 1000000));
						$user->get_attr( 'validation_code_start_dt' )->now();
						$user->validation_code_action = 'register';

						$user->update();

						$user->genera_notificacion( [
						'titulo' => $titulo,
						'legajo' => $empleado->legajo,
						'flow_ID' => $empleado->legajo,
						'email' => $empleado->usuario,
						'seccion' => 'register' ] );
					
						$result["success"] = true;


					} else {

						$user->genera_notificacion( [
						'titulo' => 'ERROR '. $titulo,
						'legajo' => $empleado->legajo,
						'flow_ID' => $empleado->legajo,
						'email' => $this->feat->test_email, /* para test por si falla */
						'seccion' => 'register' ] );
					
						$result["errors"]["reason"] = M()->get();
						$result["success"] = false;
						
					}

				}

				return $result;
			}


		}

   }/*}}}*/ 

	function validate_code() {/*{{{*/

		global $xpdoc;

		$xpdoc->set_view( 'json' );

		$result = [];

		if ( ! $this->recaptcha_validate() ) {

			   $result["errors"]["reason"] = "No se pudo validar el CAPTCHA";
			   $result["success"] = false;
			   M()->user("fallo ingreso usuario $user" );

		} else {

			// echo "<pre>"; print_r( $xpdoc->http->var ); 

			extract( $xpdoc->http->var );


			$result['success'] = false;
			$errors = [];

			$user = $xpdoc->instance('users');

			$user->set_flag('main_sql',false);

			$validation_code = trim($validation_code);

			if ( ( $len = strlen($validation_code) ) != 6 ) {

				$errors[] = "El código de autentificación debe tener 6 dígitos y tiene $len";

			} else if( ! $user->load(['validation_code'=>$validation_code]) ) {

				$errors[] = "Código de autentificación $validation_code inexistente";

			} else if( ! $user->validation_code_start_dt ) {

				$errors[] = "El código de autentificación ha caducado: Solicite un cambio de clave para renovar el acceso";
			}

			if ( count( $errors ) ) {
			
				$result["errors"]["reason"] = $errors;
				$result["success"] = false;

				return $result;
			
			} else {


				/* si efectivamente obtiene el rol */

				if ( $user->add_role($user->user_username) ) {

					$empleado = $xpdoc->instance('_empleado');
					$empleado->set_flag('main_query', false );

					$empleado->load( ['email'=>$user->user_username] );

					$titulo = $empleado->compose( sprintf( self::NOTIFICACION_ACCESO, $this->feat->page_title ) );

					M()->info( "rol cedido a $user->username" );

					$user->get_attr( 'validation_code_end_dt' )->now();

					$user->update();

					$user->genera_notificacion( [
					'titulo' => $titulo,
					'legajo' => $empleado->legajo,
					'flow_ID' => $empleado->legajo,
					'email' => $empleado->usuario,
					'seccion' => 'validate-code' ] );
				
					$result["success"] = true;

				}

				return $result;
			}
		}

   }/*}}}*/ 

	function reset_password() {/*{{{*/

		global $xpdoc;

		$result = [];

		if ( ! $this->recaptcha_validate() ) {

			   $result["errors"]["reason"] = "No se pudo validar el CAPTCHA";
			   $result["success"] = false;
			   M()->user("fallo ingreso usuario $user" );

		} else {

			// echo "<pre>"; print_r( $xpdoc->http->var ); 

			extract( $xpdoc->http->var );

			$result['success'] = false;
			$errors = [];

			$user = $xpdoc->instance('users');

			if ( ! filter_var($email, FILTER_VALIDATE_EMAIL) ) {
				$errors[] = "Debe especificar un correo electrónico válido";
			}

			$user->set_flag('main_sql',false);

			if ( ! $user->load( ['user_username'=>$email] ) ) {
			
				$errors[] = "El email $email no esta registrado, utilice la opción de registro";
			
			}

			if ( count( $errors ) ) {
			
				$result["errors"]["reason"] = $errors;
				$result["success"] = false;

				return $result;
			
			} else {


				$titulo = $user->compose( sprintf( self::NOTIFICACION_RESETEO_CLAVE, $this->feat->page_title ) );

				/* Crea el usuario, asignacion temporal de permisos */

				$user->push_privileges(['edit'=>1,'add'=>1]);

				$user->validation_code = $test = sprintf('%06d', rand(1, 1000000));
				$user->get_attr( 'validation_code_start_dt' )->now();
				$user->validation_code_action = 'change-password';

				$user->update();

				$empleado = $xpdoc->instance('_empleado');
				$empleado->set_flag('main_query', false );

				$empleado->load( ['email'=>$user->user_username] );

				$user->genera_notificacion( [
				'titulo' => $titulo,
				'legajo' => $empleado->legajo,
				'flow_ID' => $empleado->legajo,
				'email' => $empleado->usuario,
				'seccion' => 'reset-password' ] );
			
				$result["success"] = true;

				return $result;
			}
		}

   }/*}}}*/ 

	function change_password() {/*{{{*/

		global $xpdoc;

		$xpdoc->set_view( 'json' );

		$result = [];

		if ( ! $this->recaptcha_validate() ) {

			   $result["errors"]["reason"] = "No se pudo validar el CAPTCHA";
			   $result["success"] = false;
			   M()->user("fallo ingreso usuario $user" );

		} else {

			// echo "<pre>"; print_r( $xpdoc->http->var ); 

			extract( $xpdoc->http->var );


			$result['success'] = false;

			$user = $xpdoc->instance('users');

			$user->set_flag('main_sql',false);

			$errors = $user->check_password_pair( $password, $passwordConfirm );

			$validation_code = trim($validation_code);

			if ( ( $len = strlen($validation_code) ) != 6 ) {

				$errors[] = "El código de autentificación debe tener 6 dígitos y tiene $len";

			} else if( ! $user->load(['validation_code'=>$validation_code]) ) {

				$errors[] = "Código de autentificación $validation_code inexistente";

			} else if( ! $user->validation_code_start_dt ) {

				$errors[] = "El código de autentificación ha caducado: Solicite un cambio de clave para renovar el acceso";
			}

			if ( count( $errors ) ) {
			
				$result["errors"]["reason"] = $errors;
				$result["success"] = false;

				return $result;
			
			} else {

				$user->add_role($user->user_username); /* agrega el rol por default */

				$empleado = $xpdoc->instance('_empleado');
				$empleado->set_flag('main_query', false );

				$empleado->load( ['email'=>$user->user_username] );

				$titulo = $empleado->compose( sprintf( self::NOTIFICACION_RENOVACION_CLAVE, $this->feat->page_title ) );

				$user->user_password = $this->crypt( $password ) ;
				$user->push_privileges(['edit'=>1,'add'=>1]);
				$user->update();

				$user->get_attr( 'validation_code_end_dt' )->now();

				$user->update();

				$user->genera_notificacion( [
				'titulo' => $titulo,
				'legajo' => $empleado->legajo,
				'flow_ID' => $empleado->legajo,
				'email' => $empleado->usuario,
				'seccion' => 'change-password' ] );
			
				$result["success"] = true;

				return $result;
			}
		}

   }/*}}}*/ 


	/* login's */

        function login( $user, $pass ) {/*{{{*/

		global $xpdoc;

		$user = strtolower(trim($user));

		$this->set_flag('main_sql', false);

                if ( ( $r = $this->$login_fn( $user, $pass ) ) ) {

			$xpdoc->session->set_user_session( $xpdoc->user->user_id );
			M()->info("ingreso usuario $user" );

		} else {

			M()->info("fallo ingreso usuario $user" );
                }

		$this->set_flag('main_sql',true);
		return $r;

        }/*}}}*/ 

	function POST_login( $user = null, $pass = null ) {/*{{{*/

		global $xpdoc;

		$result = [];

		if ( ! $this->recaptcha_validate() ) {

			   $result["errors"]["reason"] = "No se pudo validar el CAPTCHA";
			   $result["success"] = false;
			   M()->user("fallo ingreso usuario $user" );

		} else {

			$user or $user = $xpdoc->http->loginUsername;
			$pass or $pass = $xpdoc->http->loginPassword;

			if ( ! $user ) {

				$result['success'] = false;
				M()->user( $result["errors"]["reason"] = 
					"Debe especificar un usuario o las variables loginUser/loginPassword" );

				return $result;

			}

			$user = strtolower(trim($user));

			$login_fn = $xpdoc->config->login_fn;

			$this->set_flag('main_sql', false);

			if ( $this->$login_fn( $user, $pass ) ) {

				$xpdoc->session->set_user_session( $xpdoc->user->user_id );
				$result["success"] = true;
				M()->info("ingreso usuario $user" );

			} else {

				$result["errors"]["reason"] = "Ingreso fallido: intente nuevamente";
				$result["success"] = false;
				M()->user("fallo ingreso usuario $user" );

			}

			$this->set_flag('main_sql',true);
		}

		return $result;

        }/*}}}*/ 

	function POST_email_login( $user = null, $pass = null ) {/*{{{*/

		global $xpdoc;

		$result = [];

		if ( ! $this->recaptcha_validate() ) {

			   $result["errors"]["reason"] = "No se pudo validar el CAPTCHA";
			   $result["success"] = false;
			   M()->user("fallo ingreso usuario $user" );

		} else {

			$user or $user = $xpdoc->http->email;
			$pass or $pass = $xpdoc->http->password;

			if ( ! $user ) {

				$result['success'] = false;
				M()->user( $result["errors"]["reason"] = 
					"Debe especificar un usuario o las variables email/clave" );

				return $result;

			}

			$user = strtolower(trim($user));

			$login_fn = $xpdoc->config->login_fn;

			$this->set_flag('main_sql', false);

			if ( $this->$login_fn( $user, $pass ) ) {

				$xpdoc->session->set_user_session( $xpdoc->user->user_id );
				$result["success"] = true;
				M()->info("ingreso usuario $user" );

			} else {

				$result["errors"]["reason"] = "Ingreso fallido: intente nuevamente";
				$result["success"] = false;
				M()->user("fallo ingreso usuario $user" );

			}

			$this->set_flag('main_sql',true);
		}

		return $result;

        }/*}}}*/ 

	function _login( $username, $password ) {/*{{{*/

		global $xpdoc;

		M()->info();

		if ( ! $this->authenticate( $username, $password ) ) 
			return false;

		$user_id = $this->user_id;
		$username = $this->user_username;

		M()->info("usuario $username autenticado con id $user_id");

		if ( ! $xpdoc->perms->checkLogin($user_id) ) {

			M()->error("Sin permisos para el usuario $username ");
			return false;
		}

		/* DEBUG: migrar
		$this->load_prefs( $this->user_id );
		$this->set_locale();
		*/

		return true;

	}/*}}}*/

	function _login_fake( $username, $password ) {/*{{{*/

		global $xpdoc;

		M()->info( "login fake $username:$password" );

		$this->push_privileges( array( 'edit' => true, 'add' => true, 'list' => true, 'view' => true ) );

		if ( $this->user_exists( $username ) ) {

			M()->info( "el usuario existe. Actualizando la clave" );

			$this->user_password = $password;
			$this->update();

		} else { 

			// DEBUG: mmm le estoy dando permisos al anon para crear usuarios por un segundo, revisar

			$this->push_privileges( array( 'edit' => true, 'add' => true, 'list' => true, 'view' => true ) );

			M()->info( "el usuario no existe, creando uno" );

			$ret = (bool) $this->create( $username, $password );

		}

		$this->pop_privileges();
		return true;

	}/*}}}*/

	function _login_ldap( $username, $password ) {/*{{{*/

		global $xpdoc;

		M()->info( "autenticando con LDAP, usuario $username, clave $password" );

		$this->ldap_host = $xpdoc->config->ldap_host;
		$this->ldap_port = $xpdoc->config->ldap_port;
		$this->ldap_version = $xpdoc->config->ldap_version;
		$this->ldap_base_dn = $xpdoc->config->ldap_base_dn;

		$this->ldap_user_filter = $xpdoc->config->ldap_user_filter;

		$this->ldap_search_user = $xpdoc->config->ldap_search_user ? $xpdoc->config->ldap_search_user : $username;
		$this->ldap_search_pass = $xpdoc->config->ldap_search_pass ? $xpdoc->config->ldap_search_pass : $password;

		$this->ldap_default_domain = $xpdoc->config->ldap_default_domain;
		$this->ldap_bind_format = $xpdoc->config->ldap_bind_format;

		$this->username = $username;

		if ( strlen( $password ) == 0) {

			M()->info( "Clave nula, devuelvo FALSE" );
			return false; 
		} 

		if ( ! function_exists( 'ldap_connect' ) ) {

			M()->error( "modulo ldap no instalado" );
			return false;
		}

		if ( !$rs = ldap_connect( $this->ldap_host, $this->ldap_port ) ) {

			M()->warn( "no hubo respuesta del servidor LDAP" );
			return $this->_login_ldap_fallback( $username, password );
		} 

		ldap_set_option( $rs, LDAP_OPT_PROTOCOL_VERSION, $this->ldap_version );
		ldap_set_option( $rs, LDAP_OPT_REFERRALS, 0 );

		$ldap_bind_dn = null;

		M()->info( "ldap_bind_format: $this->ldap_bind_format" );

		$ldap_bind_dn = string_parse( $this->ldap_bind_format, get_object_vars( $this ) );

		M()->info( "ldap_bind_dn: $ldap_bind_dn, ldap_search_pass: {$this->ldap_search_pass}" );

		if ( ! ( @$bindok = ldap_bind( $rs, $ldap_bind_dn, $this->ldap_search_pass ) ) ) {

			$err = ldap_error( $rs );
			M()->warn( "No se pudo autenticar via LDAP: [$err] con [$ldap_bind_dn@{$this->ldap_host}:{$this->ldap_port}]" );
			return $this->_login_ldap_fallback( $username, $password );
		
		} else {

		M()->info( "pude conectarme al servidor LDAP" );

			/* var_dump( get_object_vars( $this )['user_username'] ); exit;	*/

			$filter_query = string_parse( $this->ldap_user_filter, get_object_vars( $this ) );

			M()->info( "filter_query: $filter_query" );

			if ( $result = @ldap_search( $rs, $this->ldap_base_dn, $filter_query ) ) {

				M()->info( "ldap_search result: ". json_encode( $result ) );

			} else {

				M()->info( "respuesta vacia del servidor LDAP" );
				return $this->_login_ldap_fallback( $username, $password );
			}

			$result_user = ldap_get_entries($rs, $result);

			M()->info( json_encode( $result_user ) );

			if ( $result_user["count"] == 0 ) {

				M()->info("el usuario no existe en el servidor LDAP" );
				return $this->_login_ldap_fallback( $username, $password );
			}

			$ldap_user_dn = $result_user[0]["dn"];

			// Bind with the dn of the user that matched our filter (only one user should match sAMAccountName or user_id etc..)

			if ( $bind_user = @ldap_bind( $rs, $ldap_user_dn, $password ) ) {

				M()->info( "login exitoso" );

	                $this->push_privileges( array( 'edit' => true, 'add' => true, 'list' => true, 'view' => true ) );

				if ( $this->user_exists( $username ) ) {

					M()->info( "el usuario existe. Actualizando la clave" );

					$this->user_password = $password;
					$this->store();

				} else { 

					// DEBUG: mmm le estoy dando permisos al anon para crear usuarios por un segundo, revisar

	                $this->push_privileges( array( 'edit' => true, 'add' => true, 'list' => true, 'view' => true ) );

					M()->info( "el usuario no existe, creando uno" );

					$ret = (bool) $this->create( $username, $password );
				}

				$this->pop_privileges();
				return true;

			} else {

				M()->warn( ldap_error( $rs ). " en $ldap_user_dn@{$this->ldap_host}:{$this->ldap_port}" );
				return $this->_login_ldap_fallback( $username, $password );
			}
		}

	}/*}}}*/

	function _login_ldap_fallback( $username, $password ) {/*{{{*/

		global $xpdoc;

		$this->fallback = isset( $xpdoc->config->ldap_allow_login ) ? 
			$xpdoc->config->ldap_allow_login : 
			false;

		$this->fallback = true;

		if ( $this->fallback ) {

			M()->info( "haciendo fallback en base de datos local via SQL" );
			return $this->_login( $username, $password );


		} else { 

			M()->warn( "No hay fallback permitido sobre SQL" );
			return false;

		}

	}/*}}}*/

        function logout() {/*{{{*/

		global $xpdoc;

		$xpdoc->session->set_user_session( $xpdoc->config->anonymous_user_id ) ;
		$xpdoc->session->close();
		$xpdoc->session->destroy();
		M()->info("Egreso del usuario {$this->user_username}" );
		$result["success"] = true;

		return $result;

        }/*}}}*/

 	function authenticate( $username, $password ) {/*{{{*/

		$username = $this->sanitize( $username );
		$password = $this->crypt( $password );

		$this->set_flag('main_sql',false);
		$ret = false;

		if ( $this->load( array( 'user_username' => $username ) ) )
			if ( $password == $this->user_password )
				$ret = true and M()->info("Usuario $username autenticado" );
			else
				M()->user("Clave incorrecta para el usuario $username" );
		else
			M()->user("usuario $username inexistente" );

		$this->set_flag('main_sql',true);
		return $ret; 

	}/*}}}*/


	/* user / role mgmt */

	 function create( $username, $password, $roles = null) {/*{{{*/

		// recibe el id del grupo (aro_group.id)

		global $xpdoc;

		M()->info();

		$this->set_flag('main_sql', false );

		if ( $this->load( array( 'user_username' => $username ) ) ) {

			M()->user( "El usuario $username ya existe, recupere su clave" );
			$this->set_flag('main_sql', true );
			return null;
		}

		$this->user_username = $username;
		$this->user_password = $password;

		$this->fill_primary_key();
		$this->defaults();

		if ( $this->store() ) {

			if ( $roles !== false ) {
			
				$this->add_role( $username, $roles );
			} else {
			
				M()->info( "usuario $username creado sin roles" );
			
			}

		} else {

			M()->user( "No pude crear el usaurio $username." );
			$this->set_flag('main_sql', true );
			return null;
		}

		$this->set_flag('main_sql', true );
		return $this;

	}/*}}}*/

	function add_role( $username, $roles = null ) {/*{{{*/

		global $xpdoc;

		M()->info( "usuario: $username" );

		if ( !$roles ) {

			if ( $dr = $xpdoc->config->create_user_default_role ) {

				M()->info( "create_user_default_role: $dr" );
				$roles = array( $dr );

			} else {

				M()->warn('no se ha especificado el grupo, asignandolo al grupo "anon"');
				$roles = array( 'anon' );
			}

		}

		if ( !is_array( $roles ) ) 
			$roles = array( $roles );

		// que gacl_aro_id tiene el usuario

		$ga = $xpdoc->instance( 'gacl_aro' );

		if ( ! $ga->load( array( 'name' => $username ) ) ) {

			M()->user( "no encuentro el usuario $username en el sistema" );
			return;
		}

		$gacl_aro_id = $ga->id; 

		if ( !$gacl_aro_id ) {

			M()->error( "el usuario {$username} no tiene permisos en el sistema" );
			return;
		}

		$ggam = $xpdoc->instance( 'gacl_groups_aro_map' );
		$gag = $xpdoc->instance( 'gacl_aro_groups' );

		$ggam->push_privileges( array( 'edit' => true, 'add' => true, 'list' => true, 'view' => true ) );

		foreach( $roles as $role ) {

			if ( ! is_numeric( $role ) ) {

            	if ( ! $gag->load( array( 'value' => $role ) ) ) {

                    M()->user( "Rol $role inexistente, ignorado. Revise la configuracion" );
                      continue;

                } else $role_id = $gag->id;

			} else $role_id = $role;

			$io = $ggam->bind_store( array( 'group_id' => $role_id, 'aro_id' => $gacl_aro_id ) );

			if ( $io == INSERT_OP or $io == UPDATE_OP ) {

				M()->info( "El usuario {$username} obtuvo el rol $role" );
				$xpdoc->perms->clean_cache();	
			}
			else
				M()->user( "El usuario {$username} no pudo obtener el rol $role" );

		}

		$ggam->pop_privileges();

		return $this;

	}/*}}}*/


	/* misc */

	function set_locale() {/*{{{*/

		// DEBUG: cargar los locales del usuario
	}/*}}}*/

	function is_logged() {/*{{{*/

		return ($this->user_id < 0) ? true : false;
	}/*}}}*/

	function get_pref( $name ) {/*{{{*/

		return @$this->user_prefs[$name];
	}/*}}}*/

	function set_pref( $name, $val ) {/*{{{*/

		$this->user_prefs[$name] = $val;
	}/*}}}*/

	function load_prefs( $user_id=0 ) {/*{{{*/

		$this->user_prefs = new \App\user_preferences;
		$this->user_prefs->load( $user_id );

	}/*}}}*/

	function user_id( $username ) {/*{{{*/

                if ( $user = $this->load( array( 'user_username' => $username ) ) ) 
                        return $user->user_id;
                return NULL;

	}/*}}}*/

	function user_exists( $username ) {/*{{{*/

		return (bool) $this->user_id( $username ); 

	}/*}}}*/

	function crypt( $string ) {/*{{{*/

		// M()->debug( 'llamo a crypt con el valor: '. $string );
		return md5( $this->sanitize( $string ) );

	}/*}}}*/

	function sanitize( $string ) {/*{{{*/

		// DEBUG: no anda esto por ahora
		// return trim( filter_var( $string, FILTER_SANITIZE_EMAIL) );
		return trim( $string );

	}/*}}}*/

	function check_password_pair( $password, $passwordConfirm, $weak = false ) {/*{{{*/

		$errors = [];
	
		if ( $password !== $passwordConfirm ) {
			$errors[] = 'Las claves deben coincidir';
		}

		if ( $weak ) {

			// sin restriccion
			$uppercase = 1;
			$lowercase =1;
			$number =1;
			$specialChars=1;
	
		} else {

			preg_match('@[A-Z]@', $password) or $errors[] = "debe incluir al menos una mayúscula";
			preg_match('@[a-z]@', $password) or $errors[] = "debe incluir al menos una minúscula";
			preg_match('@[0-9]@', $password) or $errors[] = "debe incluir al menos un número";
			#preg_match('@[^\w]@', $password) or $errors[] = "debe incluir al menos una caracter especial";
		}

		count( $errors ) and $errors[] = "Clave inválida";

		return $errors;

	}/*}}}*/

}

?>
