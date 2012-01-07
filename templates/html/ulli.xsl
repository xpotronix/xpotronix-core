<?xml version="1.0" encoding="UTF-8"?>

<!--
	@package xpotronix
	@version 2.0 - Areco 
	@copyright Copyright &copy; 2003-2011, Eduardo Spotorno
	@author Eduardo Spotorno
 
	Licensed under GPL v3
	@license http://www.gnu.org/licenses/gpl-3.0.txt
-->

<xsl:stylesheet version="2.0"
	xmlns="http://www.w3.org/TR/REC-html40"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:xpotronix="http://xpotronix.com/namespace/xpotronix/" 
	exclude-result-prefixes="#all">

	<xsl:preserve-space elements="text"/>
	<xsl:output method="html" version="4.0" encoding="UTF-8" indent="yes"/>

	<xsl:template match="/"><!--{{{--> 
		<xsl:element name="ul"> 
			<xsl:apply-templates select="//xpotronix:dataset/class/obj"/>
		</xsl:element>
	</xsl:template><!--}}}-->

	<xsl:template match="obj">
		<xsl:element name="li">
			<xsl:attribute name="id"><xsl:value-of select="attr[1]"/></xsl:attribute>
			<xsl:for-each select="attr">
				<xsl:element name="span">
					<xsl:attribute name="class">informal</xsl:attribute>
					<xsl:value-of select="text()"/>
				</xsl:element>
			</xsl:for-each>
		</xsl:element>
	</xsl:template>

</xsl:stylesheet>

<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
