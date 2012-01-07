<?xml version="1.0" encoding="UTF-8"?>

<!--
	@package xpotronix
	@version 2.0 - Areco 
	@copyright Copyright &copy; 2003-2011, Eduardo Spotorno
	@author Eduardo Spotorno
 
	Licensed under GPL v3
	@license http://www.gnu.org/licenses/gpl-3.0.txt
-->

<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html" indent="no" encoding="utf-8"/>
	<xsl:template match="/"><html><head><title>xpotronix Links</title></head><body><ul>
		<xsl:apply-templates select="//alias"/></ul></body></html>
	</xsl:template>

	<xsl:template match="alias">

<xsl:value-of select="alias"/>
	<li>
	<xsl:element name="a">
		<xsl:attribute name="href">/<xsl:value-of select="."/>/?</xsl:attribute>
		<xsl:value-of select="."/>
	</xsl:element>
	</li>
	</xsl:template>

	</xsl:stylesheet>
<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
