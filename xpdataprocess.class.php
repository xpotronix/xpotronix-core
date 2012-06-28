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

require_once 'xpmessages.class.php';

/*
	xpdataprocess:
	proceso recursivo sobre documentos xml
*/


class xpDataProcess extends xp {

	var $obj;
	var $instances;
	var $user;
	var $processes;
	var $xml;

	var $commands;

	var $record_count = 0;
        var $halt = false;

	function __construct( $obj, $instances, $user, $processes, $xml ) {/*{{{*/

		$this->obj = $obj 		or M()->error( 'proceso sin objeto' );
		$this->instances = $instances 	or M()->error( 'proceso sin instancias' );
		$this->user = $user 		or M()->error( 'proceso sin usuario' );
		$this->processes = $processes 	or M()->warn( 'proceso sin lote' );
		is_array( $this->processes ) 	or M()->warn( 'processes no es un array' );
		$this->xml = $xml 		or M()->info( 'ejecutando proceso sin XML' );

	}/*}}}*/

	function add_command( $process_name, $section, $command ) {/*{{{*/

		$this->commands[$process_name][$section] = $command;
		return $this;

	}/*}}}*/

	function halt() {/*{{{*/

		$this->halt = true;

		M()->info('proceso interrumpido');

	}/*}}}*/

	function process_recursive( $container, $commands, $obj ) {/*{{{*/


		if ( !$commands or !is_array( $commands ) )
			M()->error( 'no encontre comandos' );
		else
			$this->commands = $commands;

		$this->call_command( 'process_start', $container, $obj );

		if ( $this->halt ) return;	

		$container and $this->recurse( $container ) ;

		return $this->call_command( 'process_stop', $container, $obj );

 	}/*}}}*/

 	function recurse( $container = null ) {/*{{{*/

		global $xpdoc;

		$ct = $xpdoc->feat->container_tag;

		if ( !isset( $container ) ) {
			M()->warn( 'no encontre un container xml para procesar' );
			return;
		}

		M()->info( 'container name: '. $container['name'] );

		$this->call_command( 'container_start', $container ); 

		if ( $this->halt ) return;	

		foreach( $container as $obj_xml ) {

			// usamos la instancia del modelo

			$obj =& $this->instances[ $obj_xml->getName() ]; 

			if ( !is_object( $obj ) ) {

				// DEBUG: seguramente son los eh que falta incorporar al modelo
				M()->warn( 'no encuentro la instancia '. $obj_xml->getName(). ' en el modelo' );
				return;
			}


			$this->call_command( 'obj_start', $obj_xml, $obj );
			if ( $this->halt ) return;	

			// por cada atributo (!= container)
			if ( is_object( $obj ) )

				foreach ( $obj_xml->xpath("*[name()!='$ct']") as $attr_xml ) {


					$attr = $obj->get_attr( $attr_xml->getName()) ;
					if ( !$attr ) continue;

					$this->call_command( 'attr_each', $attr_xml, $attr ); 
					if ( $this->halt ) return;
				}

			$this->call_command( 'obj_stop_down', $obj_xml, $obj );
			if ( $this->halt ) return;

			foreach( $obj_xml->xpath("*[name()='$ct']") as $child )
				
				$this->recurse( $child ) ;

			$this->call_command( 'obj_stop_up', $obj_xml, $obj );
			if ( $this->halt ) return;
		}

		$this->call_command( 'container_stop', $container );

	}/*}}}*/

	function call_command( $command, $xml, $data = NULL ) {/*{{{*/
	
		// DEBUG: deberia existir la posibilidad de pasar un comando a cada objeto
		// por ahora es el mismo comando para todos
		// aplica a procesos uniformes (ej. store) o a procesos planos (sin recursividad)

		// $data puede ser un obj, attr o cualquier otro objeto que aplique

		if ( !@$method = $this->commands[$command] ) return;
		
		if ( !$data ) $data = $this;

		if ( is_object( $xml ) ) 

			M()->info( "node name: ". $xml->getName() );

		if ( method_exists( $data, $method ) ) {
			
			M()->info( "$command: ". get_class($data) . '::method '. $method );
			$data->$method( $xml );
		
		} else {
			M()->error( "no encuentro el metodo ". get_class($data) . "::$method para el comando $command" );
			// $this->debug_backtrace(); exit;
			return NULL;
		}
	}/*}}}*/

	function get_processes() {/*{{{*/

		$count = null;

		// print '<pre>'; print( $this->obj->processes->asXML() ); exit;

		foreach( $this->processes as $process_name ) {

			M()->info( 'requiriendo proceso '. $process_name ) ;

			$processes_xml = $this->obj->processes->xpath( "process[@name='$process_name' or @name='*']" );

			if ( !$processes_xml ) { 
				M()->error( "no encontre el proceso $process_name para el objeto {$this->obj->class_name} en process.xml" ) ;
				continue;
			}

			// print '<pre>'; print_r( $processes_xml ); exit;

			$this->set_view( $processes_xml );

			if ( $count = count( $this->commands[$process_name] = $this->get_commands( $processes_xml ) ) )
				M()->info( "econtre $count comandos validos para ejecutar" );
			else
				M()->error( 'no econtre comandos validos para ejecutar' );

			M()->info( 'fin del proceso '. $process_name ) ;


		}

		return $count;
	}/*}}}*/ 

	function set_view( $process_xml ) {/*{{{*/

		global $xpdoc;

		if ( @$process_xml['view'] ) $xpdoc->set_view( (string) $process_xml['view'] );
		else $xpdoc->set_view( 'xml' );

	}/*}}}*/

	function get_commands( $processes_xml ) {/*{{{*/

		foreach( $processes_xml as $process_xml ) {

			M()->info( 'encontre el proceso '. (string) $process_xml['name'] ) ;

			$this->set_view( $process_xml );

			$got_perms = false;
			$commands = array();

			// permit

			$acls = $process_xml->xpath( "acl" );

			foreach( $acls as $acl ) {

				M()->debug('buscando ACL '. $acl['role'] );

				if ( ( (string) $acl['role'] == '*' ) or $this->user->has_role( (string) $acl['role'] ) ) {

					if ( $acl['action'] == 'permit' ) {

						$got_perms = true;
						break;

					} else if ( $acl['action'] == 'deny' ) {

						$got_perms = false;
						break;

					} else M()->warn( "debe especificar una accion de 'permit' o 'deny' para el proceso {$process_xml['name']}" );
				}
			}

			M()->info( ( $got_perms ? '': 'NO ' ). "tengo permisos como el rol {$acl['role']} para el proceso {$process_xml['name']}" ) ;

			if ( $got_perms ) {

				foreach( $process_xml->xpath( "command" ) as $command_xml ) {

					$method = (string) $command_xml['name'];
					$section = (string) $command_xml['for'];

					if ( !$method or !$section ) {
	
						M()->error( 'comando incompleto '. (string) $command_xml['name']. ' para la seccion '. $command_xml['for'] ) ;

					} else { 

						M()->info( 'encontre el comando '. (string) $command_xml['name']. ' para la seccion '. $command_xml['for'] ) ;
						$commands[$section] = $method;
					}
				}

			} else 
				M()->user( "el usuario {$this->user->user_username} no tiene el rol requerido para el proceso {$process_xml['name']}" ) ;
		}

		M()->info( 'encontrados ' . count( $commands ) . ' comandos para el proceso '. $process_xml['name'] ) ;
		return $commands;

	}/*}}}*/

	function process() {/*{{{*/

		isset( $this->commands ) or $this->get_processes();

		if ( is_array( $this->commands ) )

		foreach( $this->commands as $process_name => $commands ) {

			M()->info( 'iniciando el proceso '. $process_name ) ;
			$this->process_recursive( $this->xml, $commands, $this->obj );
			M()->info( 'fin del proceso '. $process_name ) ;
		}

	}/*}}}*/

}

// vim600: fdm=marker sw=3 ts=8 ai:

?>
