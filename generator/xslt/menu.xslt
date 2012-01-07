<?xml version="1.0" encoding="UTF-8"?>
<!-- edited with XML Spy v4.4 U (http://www.xmlspy.com) by eduardo spotorno (private) -->
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<!-- -->
	<!-- por ahora solamente copia el archivo menu.xml en el destino de la aplicacion -->
	<!-- -->
	<xsl:template match="menu" mode="menu">
		<xsl:result-document 	method="xml" encoding="UTF-8" indent="yes" href="{concat($application_path,'/conf/menu.xml')}">
			<xsl:sequence select="."/>
		</xsl:result-document>
	</xsl:template>
</xsl:stylesheet>
