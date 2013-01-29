<?xml version="1.0" encoding="UTF-8"?>

<!--
	@package xpotronix
	@version 2.0 - Areco 
	@copyright Copyright &copy; 2003-2011, Eduardo Spotorno
	@author Eduardo Spotorno
 
	Licensed under GPL v3
	@license http://www.gnu.org/licenses/gpl-3.0.txt
-->

<!-- incompleto para todos los tipos de datos -->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>

	<xsl:variable name="pattern" select="'desde'"/>

	<xsl:template match="/">
		<xsl:apply-templates select="//table[contains(lower-case(@name), $pattern) ] | //table[ field[contains( lower-case( @name ), $pattern) ] ]"/>
	</xsl:template>

	<xsl:template match="table">

		<xsl:copy-of select="."/>

	</xsl:template>


</xsl:stylesheet>
<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
