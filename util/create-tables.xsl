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
	<xsl:template match="/">
		<xsl:apply-templates select="database"/>
	</xsl:template>

	<xsl:template match="database">
		<xsl:apply-templates select="table"/>
	</xsl:template>

	<xsl:template match="table">

CREATE TABLE `<xsl:value-of select="@name"/>` (
	<xsl:apply-templates select="field"/>
	<xsl:apply-templates select="index"/>
	<xsl:if test="primary">
		<xsl:apply-templates select="." mode="primary"/>
	</xsl:if>
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	</xsl:template>

	<xsl:template match="table" mode="primary">
		PRIMARY KEY (<xsl:for-each select="primary">`<xsl:value-of select="@name"/>`</xsl:for-each>)
	</xsl:template>

	<xsl:template match="index">
		KEY `<xsl:value-of select="@name"/>` (`<xsl:value-of select="."/>`)<xsl:if test="position()!=last() or count(../primary)">,</xsl:if>
	</xsl:template>

	<xsl:template match="field">
		`<xsl:value-of select="@name"/>` <xsl:value-of select="@type"/><xsl:if test="position()!=last() or count(../primary) or count(../index)">,</xsl:if></xsl:template>

</xsl:stylesheet>
<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
