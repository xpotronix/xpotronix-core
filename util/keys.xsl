<?xml version="1.0" encoding="utf-8"?>

<!--
	@package xpotronix
	@version 2.0 - Areco 
	@copyright Copyright &copy; 2003-2011, Eduardo Spotorno
	@author Eduardo Spotorno
 
	Licensed under GPL v3
	@license http://www.gnu.org/licenses/gpl-3.0.txt
-->

<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="text" version="1.0" encoding="utf-8" indent="yes"/>

	<xsl:template match="/">
		<xsl:apply-templates select="//table"/>
	</xsl:template>

	<xsl:template match="table">
-- <xsl:value-of select="@name"/>
		<xsl:if test="primary">
			<xsl:apply-templates select="." mode="primary"/>
		</xsl:if>
		<xsl:apply-templates select="index"/>
	</xsl:template>


	<xsl:template match="table" mode="primary">

	<xsl:variable name="fields">
		<xsl:for-each select="primary"><xsl:value-of select="@name"/><xsl:if test="position()!=last()">,</xsl:if></xsl:for-each>
	</xsl:variable>
ALTER TABLE `<xsl:value-of select="@name"/>` ADD PRIMARY KEY ( <xsl:value-of select="$fields"/> );</xsl:template>

	<xsl:template match="index">
ALTER TABLE `<xsl:value-of select="../@name"/>` ADD INDEX `<xsl:value-of select="@name"/>` ( <xsl:value-of select="."/> );</xsl:template>
</xsl:stylesheet>
