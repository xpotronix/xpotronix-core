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


class xpdbm {

	var $db = array();
	private $db_driver;

	var $config;
	var $db_array;

	function  __construct( $config ) {/*{{{*/

		$this->db_driver = 'PDO';
		$this->config = $config;

	} /*}}}*/

	function db_driver( $driver = null ) {/*{{{*/

		$driver and $this->db_driver = $driver;
		return $this->db_driver;

	}/*}}}*/

	function init() {/*{{{*/

		$i = 0;

		$database = null;
		$host = null;
		$user = null;
		$password = null;
		$implementation = null;
		$table_prefix = null;

		foreach( $this->config->db_instance as $instance ) {

			$instance['name'] or $instance['name'] =  'default_'. str_pad($i, 3, '0', STR_PAD_LEFT);
			M()->info( "Instancia: ". $instance['name'] );

			/* por cada uno de los elementos db_instance en config.xml
			los parametros son acumulativos entre instancias de base de datos */

			if ( $instance->database )
				$database = $instance->database;
			else	$instance->database = $database;
 
			if ( $instance->host ) 
				$host = $instance->host;
			else	$instance->host = $host;

			if ( $instance->user ) 
				$user = $instance->user;
			else	$instance->user = $user;

			if ( $instance->password ) 
				$password = $instance->password;
			else	$instance->password = $password;

			if ( $instance->implementation ) 
				$implementation = $instance->implementation;
			else	$instance->implementation = $implementation;

			if ( $instance->table_prefix ) 
				$table_prefix = $instance->table_prefix;
			else	$instance->table_prefix = $table_prefix;


			/* check de parametros */

			( $database and M()->info( "database: $database" ) ) 
				or M()->warn( "debe especificar una base de datos" );

			( $host and M()->info( "host: $host" ) ) 
				or M()->warn( "debe especificar un servidor de  base de datos" );

			( $user and M()->info( "user: $user" ) ) 
				or M()->warn( "debe especificar un usuario de base de datos" );

			( $password and M()->info( "password: $password" ) ) 
				or M()->warn( "debe especificar un password de usuario de base de datos" );

			( $implementation and M()->info( "implementation: $implementation" ) ) 
				or M()->warn( 'debe especificar una implementacion de la base de datos' );

			$lazy = $instance->lazy or $lazy = false;
			M()->info( "lazy: ". ( $lazy ? 'true' : 'false' ) );

			if ( $lazy ) {

				M()->info( "sin abrir la instancia [$database] porque es 'lazy'" );

			} else {

				$this->open( $instance );
			}

			$i++;
		}

		M()->info( "Encontradas $i instancias" );
		// print_r( $this->config->get_xml()->asXML() ); exit;

	}/*}}}*/

	function open( $i ) {/*{{{*/

		M()->info( $i->asXML() );

		if ( is_string( $i ) ) {

			$arr = $this->config->get_xml()->xpath( "db_instance[@name='$i']" );

			if ( ! ( $instance = array_shift( $arr ) ) ) {
				M()->error( "no encuentro la instancia $i" );
				return null;
			} 

		} else $instance = $i;

		// shorthands
		$name = (string) $instance['name'];
		$database = (string) $instance->database;
		$host = (string) $instance->host;
		$user = (string) $instance->user;
		$password = (string) $instance->password;
		$implementation = (string) $instance->implementation;
		$table_prefix = (string) $instance->table_prefix;

		M()->info( "abriendo la instancia $name con la base de datos {$database}" );
	
		$encoding = (string) $instance->encoding or $encoding = 'utf8';

		switch( $this->db_driver ) {

			case 'ADODB':
				require_once 'adodb.inc.php'; // DEBUG: el factory tiene que estar en xpadodb
				$dbi = $this->instance( $name, NewADOConnection( $implementation ) );
				break;
			default:
				$dbi = $this->instance( $name, new xpadodb( $name, $implementation ) );
		}


		( $table_prefix ) and ( $dbi->tablePrefix = $table_prefix ) and M()->info( "table_prefix: $table_prefix" );

		M()->info( $function = $instance->persistent ? 'PConnect' : 'Connect' );

		if ( ! $dbi->$function( $host, $user, $password, $database, $encoding ) ) {

			M()->error( "No puedo conectarme con la base de datos {$database}" ) ;
			return null;

		} else 
			M()->info( "abierta la base de datos $database de la instancia $name" );

		$instance->fetch_mode and $dbi->SetFetchMode( (string) $instance->fetch_mode ) and M()->info( "fetch_mode: $instance->fetch_mode" );
		$instance->force_utf8 and $dbi->force_utf8 = true and M()->info( "force_utf8" );
		$instance->encoding and $dbi->__encoding = (string) $instance->encoding and M()->info( "encoding: $instance->encoding" );

		return $dbi;

	}/*}}}*/

	function instance( $name = null, $db_handler = null ) {/*{{{*/

		M()->info( "# instancias: ". $count = count( $this->db ) );

		if ( !$name ) {

			/* si no especifica el nombre, devuelve la primera */

			M()->info( "param name: $name" );

			if ( ! $count ) {

				M()->info( "no hay instancias, abriendo la primera" );

				if ( $this->open( $this->config->db_instance[0] ) ) {

					return reset( $this->db );

				} else {

					M()->error( "No puedo abrir la instancia por default o no hay instancias definidas" );
					return null;
				}


			} else {

				M()->info( "devolviendo instancia por default" );
				return reset( $this->db );
			}

		} else if ( is_object( $db_handler ) ) {

			M()->info( "asignada la instancia a $name" );
			return ( $this->db[$name] = $db_handler );

		} else if ( ! array_key_exists( $name, $this->db ) ) {

			return $this->open( $name );

		} else {

			return $this->db[$name];
		}

	}/*}}}*/

}

?>
