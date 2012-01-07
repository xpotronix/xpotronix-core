<?xml version="1.0" encoding="UTF-8"?>

<!--
	@package xpotronix
	@version 2.0 - Areco 
	@copyright Copyright &copy; 2003-2011, Eduardo Spotorno
	@author Eduardo Spotorno
 
	Licensed under GPL v3
	@license http://www.gnu.org/licenses/gpl-3.0.txt
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="text" version="1.0" encoding="UTF-8" indent="no"/>

	<xsl:variable name="type_to" select="'DECIMAL(6,2)'"/>
	<xsl:template match="/">
		<xsl:apply-templates select="//field[contains(@type,'double')]"/>
	</xsl:template>

	<xsl:template match="field">
ALTER TABLE `<xsl:value-of select="../@name"/>` CHANGE `<xsl:value-of select="@name"/>` `<xsl:value-of select="@name"/>` <xsl:value-of select="$type_to"/> NULL DEFAULT NULL 
	</xsl:template>

</xsl:stylesheet>
<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
