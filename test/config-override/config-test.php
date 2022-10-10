<?php

$base = simplexml_load_file( "config.xml" );
$xslt = simplexml_load_file( "config-override.xsl" );

print merge( $base, $xslt, ['override_file_path' => 'config-local.xml'] );

	function merge( SimpleXMLElement $base, SimpleXMLElement $xslt, ?array $params ) {/*{{{*/

		/* abro el documento XSL */
	
		$xsl = simplexml_to_dom( $xslt );
		$xsl->resolveExternals = true;
		$xsl->substituteEntities = true;

		/* el procesador */

		$proc = new \XSLTProcessor;
		$proc->importStyleSheet($xsl);

		/* los parametros */

		if ( is_array( $params ) ) 
			foreach( $params as $name => $value )
				$proc->setParameter( '', $name, $value );

		/* el XML a transformar */

		$dom = simplexml_to_dom($base);

		/* el resultado */

		$ret = $proc->transformToXML( $dom );

		return $ret;
	
	}/*}}}*/


	function simplexml_to_dom( SimpleXMLElement $xml ) {
	
		$domnode = dom_import_simplexml($xml);
		$dom = new \DOMDocument();
		$domnode = $dom->importNode($domnode, true);
		$dom->appendChild($domnode);

		return $dom;

	}

?>
