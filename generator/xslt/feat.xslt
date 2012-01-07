<?xml version="1.0" encoding="UTF-8"?>
<!-- edited with XML Spy v4.4 U (http://www.xmlspy.com) by eduardo spotorno (private) -->
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<!-- -->
	<!-- por ahora solamente copia el archivo feat.xml en el destino de la aplicacion -->
	<!-- -->
	<xsl:template match="feat" mode="feat">
		<xsl:variable name="feats" select="."/>

		<xsl:result-document 	method="xml" encoding="UTF-8" indent="yes" href="{concat($application_path,'/conf/feat.xml')}">
			<feat>
			<xsl:for-each-group select="./*" group-by="name()">
				<!-- <xsl:sort select="name()"/> -->
				<xsl:variable name="cgk" select="current-grouping-key()"/>
				<xsl:element name="{$cgk}">
					<xsl:if test="//*[name()=$cgk][1]/@type">
						<xsl:attribute name="type" select="//*[name()=$cgk][1]/@type"/>
					</xsl:if>
					<xsl:value-of select="//*[name()=$cgk][last()]"/>
				</xsl:element>
	    		</xsl:for-each-group>
			</feat>
		</xsl:result-document>
	</xsl:template>
</xsl:stylesheet>
