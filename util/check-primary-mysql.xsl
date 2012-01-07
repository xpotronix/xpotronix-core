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
		<xsl:apply-templates select="table"/>
		<xsl:text>
</xsl:text>
	</xsl:template>
	<xsl:template match="table">
		<xsl:if test="not(primary/@name) or primary/@name=''">
ALTER TABLE `<xsl:value-of select="@name"/>` ADD PRIMARY KEY ( `<xsl:value-of select="field[1]/@name"/>` );
		</xsl:if>
		<xsl:if test="not(auto/@name) or auto/@name=''">
ALTER TABLE `<xsl:value-of select="@name"/>` CHANGE `<xsl:value-of select="field[1]/@name"/>` `<xsl:value-of select="field[1]/@name"/>` <xsl:value-of select="field[1]/@type"/> NOT NULL AUTO_INCREMENT;
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
