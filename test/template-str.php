<?

        function tokenize( $match, $value ) {/*{{{*/

                $token_array = preg_match_all( $match, $value , -1,  PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_DELIM_CAPTURE   );

                $ret = array();

		// print_r( $token_array );

                foreach ( $token_array as $elem )
			if ( trim( $elem[0] ) )
	                        $ret['off_'.$elem[1]] = $elem[0];

                return $ret;
        }/*}}}*/


$template = "Hola {1} buen dia como estas? Hoy es {2} {3} de {4}";

print_r( tokenize("/(\{.*?\})/", $template ) );

?>
