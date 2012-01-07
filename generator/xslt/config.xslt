<?xml version="1.0" encoding="UTF-8"?>
<!-- edited with XML Spy v4.4 U (http://www.xmlspy.com) by eduardo spotorno (private) -->
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<!-- -->
	<!-- por ahora solamente copia el archivo config.xml en el destino de la aplicacion -->
	<!-- -->
	<xsl:template match="config" mode="config">
		<xsl:variable name="application_name" select="$feat_collection//application"/>
		<xsl:variable name="output_file" select="concat($config_path,'/conf/',$application_name,'/config.xml')"/>
		<xsl:message>generando archivo de configuracion en <xsl:value-of select="$output_file"/></xsl:message>
		<xsl:result-document method="xml" encoding="UTF-8" indent="yes" href="{$output_file}">
			<xsl:sequence select="."/>
		</xsl:result-document>
	</xsl:template>
</xsl:stylesheet>
