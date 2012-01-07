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
	<xsl:template match="database">
		use <xsl:value-of select="@name"/>;
		<xsl:apply-templates select="//table/field[contains(@type,'char')]"/>
	</xsl:template>

	<xsl:template match="field">ALTER TABLE `<xsl:value-of select="../@name"/>` CHANGE `<xsl:value-of select="@name"/>` `<xsl:value-of select="@name"/>` <xsl:value-of select="@type"/> CHARACTER SET utf8 COLLATE utf8_general_ci;
	</xsl:template>

</xsl:stylesheet>
<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
