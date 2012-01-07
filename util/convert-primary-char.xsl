<?xml version="1.0" encoding="utf-8"?>

<!--
	@package xpotronix
	@version 2.0 - Areco 
	@copyright Copyright &copy; 2003-2011, Eduardo Spotorno
	@author Eduardo Spotorno
 
	Licensed under GPL v3
	@license http://www.gnu.org/licenses/gpl-3.0.txt
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="text" version="1.0" encoding="utf-8" indent="yes"/>
	<xsl:template match="/">
		<xsl:apply-templates select="database"/>
	</xsl:template>
	<xsl:template match="database">
USE <xsl:value-of select="@name"/>;
		<xsl:apply-templates select="//field[contains(@name,'ID')]"/>
		<xsl:text>
</xsl:text>
	</xsl:template>
	<xsl:template match="field">
ALTER TABLE `<xsl:value-of select="../@name"/>` CHANGE `<xsl:value-of select="@name"/>` `<xsl:value-of select="@name"/>` CHAR( 32 ) NOT NULL ;
	</xsl:template>
</xsl:stylesheet>
