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
	<xsl:output method="text"/>

	<xsl:variable name="pattern" select="'desde'"/>

	<xsl:template match="/">
		<xsl:apply-templates select="//field[@type='datetime']"/>
	</xsl:template>

	<xsl:template match="field">ALTER TABLE `<xsl:value-of select="../@name"/>` change column `<xsl:value-of select="@name"/>`<xsl:text> </xsl:text>`<xsl:value-of select="@name"/>` date;<xsl:text>
</xsl:text>
	</xsl:template>


</xsl:stylesheet>
<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
