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

class xpsearch {

	var $obj;
	var $var_type;
	var $match_type = 'exact';

	function __construct( $obj ) {

		global $xpdoc;

		$this->obj = $obj;
	}

	function process( $search, $condition = null ) { /*{{{*/

		$result = array();

		$translate_operator = array( '|' => 'OR', '&' => 'AND', '!' => 'NOT' );

		// echo "<pre>"; print_r( $search ); echo "</pre>"; ob_flush(); exit;

		M()->info( "inicio" );

		foreach ( $search as $key => $value ) {

			// Paso 1: resuelvo los alias de claves empaquetadas y _label

			if ( strstr( $key, $this->obj->feat->key_delimiter ) ) {

				M()->debug('recibi campos multiples');
				
				$fields = explode( $this->obj->feat->key_delimiter, $key );

				$key_search = array();

				foreach( $fields as $field_name ) {

					$attr = $this->obj->get_attr( $field_name );

					if ( $attr ) /*
						if ( $attr->is_entry_help() )
							$key_search[$field_name.'_label'] = $value;
						else */
							$key_search[$field_name] = $value;
					else
						M()->debug( "la variable recibida $field_name no pertenece a la clase {$this->obj->class_name}" );
				}

				// separa los fields y busca por ahi. La codicion es OR
				$result = array_merge_recursive( $result, $this->process( $key_search, 'OR' ) );

				continue;

			} else if ( $key == '_ID' or $key == '__ID' or $key == '__ID__' ) {

				// este es un caso especial, que en un campo ID viene la clave compuesta
				// aca hay que resolver aqui, no mucho mas

				$result2 = array();

				foreach( preg_split( '/(\|)/', $value ) as $sub_key ) {

					M()->debug( "recibi un clave empaquetada: $key = $sub_key" );

					$key_search = $this->obj->unpack_primary_key( $sub_key );

					$tmp = $this->process( $key_search, 'AND' );

					$result2[] = sprintf( "(%s)", implode( ' AND ', $tmp['AND'] ) );

				}

				$result = array_merge_recursive( $result, array( 'where' => array( 0 => sprintf( "(%s)", implode( ' OR ', $result2 ) ) ) ) );
				continue;
			}

			$term_array = array();

			$c = null;

			$token_array = $this->tokenize( '/(\()|(\))|(&)|(\|)|(\!)/', $value );


			// hotfix para nulos (anda)

			if ( $token_array == null  )

				$token_array = array( 0 => "''" ); 


			foreach( $token_array as $offset => $token ) {

				if ( ! is_numeric( $token ) and ! trim( $token ) ) continue;

				if ( strstr( '&|!', $token ) ) {

					$term_array[] = $translate_operator[$token]; // traduce los simbolos a palabras

				} else if ( strstr( '()', $token ) ) {

					$term_array[] = $token;

				} else {

					$token_array2 = $this->tokenize( '/((\>=)|(\<=)|(\>)|(\<)|(\<\>))/', $token );

					$operator = ( count( $token_array2 ) > 1 ) ?
						array_shift( $token_array2 ):
						'=';

					M()->debug( "operador: [$operator]" );

					$value = array_shift( $token_array2 );

					$c = $this->clasify( $key, $value, $operator );

					if ( is_object( $c ) and $c->valid ) 
						$term_array[] = $c->result_term;
				}
			}

			if ( isset( $c ) and $c->valid ) {

				// DEBUG: esto hay que redefinirlo, ahora sale como esta:
				// como variables, no va a haber otras sentenciaas que como constrains si funcionan
				// ( que la logica esta en la consulta, ej. a>b )
				// en variables solo asigna valores.
				// por ahora no se usa foreign keys con operadores, pero los puede haber

				$sql = ( @$this->obj->foreign_key->set == 'variables' ) ? "%s" : "(%s)";

				$sql_clause = sprintf( $sql, implode( ' ', $term_array ) );

				if ( $condition ) 
					$result[$condition][] = $sql_clause;
				else 
					$result[$c->clause][] = $sql_clause;

			} else M()->debug( "la consulta para la clave [$key] no es valida" );
		}

		// print "<pre>condition: $condition<br/>";print_r( $result );
		return $result;

	}/*}}}*/

	function clasify( $key, $value, $operator ) {/*{{{*/

		global $xpdoc;

		if ( ! ( $attr = $this->obj->get_attr( $key ) ) )  {

			M()->debug( "la variable recibida $key no pertenece a la clase {$this->obj->class_name}" );
			return null; 
		}

		M()->debug( "key: [{$this->obj->class_name}::$key], operator: [$operator], value: [$value]" );

		$c = new stdClass;

		$c->attr = $attr;
		$c->valid = true;
		$c->attr_type = $attr->type;
		$c->sql_var = "{$this->obj->table_name}.{$attr->name}";
		$c->search_type = 'compare';
		$c->match_type = $this->match_type;
		$c->clause = 'where';
		$c->operator = $operator ? $operator : '=';
		$c->value = $value;
		$c->result_term = null;


		// clasificacion de la busqueda:
		// por grupos de tipos de datos (ej. string, cualquier numero (exacto) y fechas, textos)
		// defino como se va a buscar, si por
		// que nombre de variable va a ser operada sobre
		// si hago un where o un having
		// puedo alterar el tipo de dato de la busqueda

		if ( $attr->alias_of ) {

			M()->debug( "es alias_of" );

			if ( $attr->get_simple_type() == 'string' )
				$c->match_type = 'anywhere';

			if ( $this->obj->db_type() == 'dblib' ) {

				$c->sql_var = $attr->alias_of;
				$c->clause = 'where';

			} else {

				$c->sql_var = $attr->name;
				$c->clause = 'having';
			}
		} 

		// es clave => la busqueda es exacta
		$attr->is_key() and $c->match_type = 'exact' and M()->debug( "$attr->name es clave" );


		// $this->obj->debug_object();

		// simboliza un nulo?
		$value = ( $value == "''" or $value == '""') ? null : $value;

		M()->debug( 'simple_type: '. $attr->get_simple_type(). ', type: '. $attr->type );

		if ( $value === null ) {

			M()->debug( "el valor es nulo" );
			$c->search_type = ( $operator == 'NOT' ? 'not_null' : 'null' );

		} else if ( $c->attr_type == 'xpdate' ) {

			$value = trim( $value );

			M()->debug( "es date" );

			if ( strstr( $value, '*' ) ) {

				M()->debug( "busqueda por comodines" );

				$c->match_type = 'anywhere';
				$c->search_type = 'like';
				$c->operator = 'LIKE';
				$value = str_replace('*','%', $value );

				$date_parts = array_reverse( preg_split( '#/#', $value , -1 ) );

				$c->value = implode( '-', $date_parts );

			} else if ( !$c->value = $attr->human( $value ) ) {

				$c->valid = false;
				return $c;
			}

		} else if ( $c->attr_type == 'xpdatetime' ) {

			$value = trim( $value );

			M()->debug( "es datetime" );
			$date_time_array = explode( ' ', $value );

			if ( strstr( $value, '*' ) ) {

				M()->debug( "busqueda por comodines" );

				$c->match_type = 'anywhere';
				$c->search_type = 'like';
				$c->operator = 'LIKE';
				$value = str_replace('*','%', $value );

				list( $date, $time ) = explode( ' ', $value );

				$date_parts = array_reverse( preg_split( '#/#', $date, -1 ) );

				$c->value = implode( '-', $date_parts ). ' '. $time;

			} else if ( count( $date_time_array ) == 1 ) {
				M()->debug( 'datetime incompleto' );
				$c->search_type = ( $this->obj->db_type() == 'dblib' ) ? 'to_date_mssql' : 'to_date';
				$attr2 = new xpdate;
				$c->value = $attr2->human( $value );

			} else {

				M()->debug( 'datetime completo' );
				$c->value = $attr->human( $value );
			}

			if ( !$c->value ) {

				$c->valid = false;
				return $c;
			}

		} else if ( $c->attr_type == 'xpboolean' ) {

			M()->debug( 'es boolean' );

			$c->value = $attr->unserialize( $value );

		} else if ( $c->attr->get_simple_type() == 'numeric' ) {

			if ( !is_numeric( $value ) ) {
				// por ahora esto
				$c->valid = false;
				return $c;
			}

			$c->search_type = 'numeric';

		} else if ( $attr->get_simple_type() == 'string' ) {

			M()->debug( 'simple_type: string' );

			if ( !strstr( $value, '*' ) ) {

				if ( $attr->match_type ) {

					M()->debug( 'match_type: '. $attr->match_type );
					$c->match_type = $attr->match_type;
				}

				$c->operator = 'LIKE';
				$c->search_type = 'like';

				switch( $c->match_type ) {

					case 'exact': 
						$c->value = "$value"; 
						$c->operator = '=';
						$c->search_type = 'compare';
						break;

					case 'begin': 
						$c->value = "$value%"; 
						break;

					case 'end': 
						$c->value = "%$value"; 
						break;

					case 'anywhere': 
						$c->value = "%$value%"; 
						break;

					case 'like': 
						$c->value = "$value"; 
						break;

					default: 
						M()->warn( "match_type $attr_match_type desconocido" );
				}

			} else {

				M()->debug( 'busqueda con comodines' );

				$c->value = str_replace('*','%', trim($value) );
				$c->operator = 'LIKE';
				$c->search_type = 'like';
			}

			$c->value = addslashes( $c->value );

		} else {

			M()->info( 'no pude identificar el tipo de busqueda: por default' );
		}



	$term_syntaxes = array( 
		'null'	=> "%s IS NULL",
		'not_null' => "%s IS NOT NULL",
		'compare' => "%s %s '%s'",
		'like' => "%s %s '%s'",
		'numeric' => "%s %s %s",
		'match' => "MATCH(%s) AGAINST ('%s' IN BOOLEAN MODE)",
		'to_date' => "DATE(%s) %s '%s'",
		'to_date_mssql' => "DATEADD(dd, 0, DATEDIFF(dd, 0, %s)) %s '%s'" );

	if ( array_key_exists( $c->search_type, $term_syntaxes ) )

		$c->term_syntax = $term_syntaxes[$c->search_type];
	else {

		M()->warn( "no hay definido un term_syntax para el tipo de busqueda $c->search_type" );
		return $c->invalid;
	}


	// MATCH AGAINST cambia el orden de los operadores

	if ( @$this->obj->foreign_key->set == 'variables' )
		$c->result_term = sprintf( $c->term_syntax, $c->sql_var, '=', $c->value);

	else if ( $c->search_type == 'match' ) 
		$c->result_term = sprintf( $c->term_syntax, $c->value, $c->sql_var, null );

	else 
		$c->result_term = sprintf( $c->term_syntax, $c->sql_var, $c->operator, $c->value);

	M()->debug( "busqueda tipo: [{$c->search_type}], match_type: [{$c->match_type}], term_syntax: [{$c->term_syntax}], operator: [{$c->operator}], value: [{$c->value}], result_term: [$c->result_term]" );

	return $c;
	}/*}}}*/

	function tokenize( $match, $value ) {/*{{{*/

		$token_array = preg_split( $match, $value , -1,  PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_DELIM_CAPTURE  | PREG_SPLIT_NO_EMPTY );

		$ret = array();

		foreach ( $token_array as $elem )
			$ret['off_'.$elem[1]] = $elem[0];

		return $ret;
	}/*}}}*/

	function quote_name( $var_name ) {/*{{{*/

		// DEBUG: hot fix para poder usar search->proces para @variables sql

		switch( (string) @$this->obj->foreign_key->set ) {

			case 'constraints':
			case null:
				$var_name = $this->obj->sql->quote_name( $var_name ); 
			break;

			case 'variables':
				$var_name = "@$var_name";
			break;

			default:
				M()->info( 'var_name desconocido' );
		}

		return $var_name;
	}/*}}}*/

}

?>
