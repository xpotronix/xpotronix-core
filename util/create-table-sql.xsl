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
		<xsl:apply-templates/>
	</xsl:template>

	<xsl:template match="application">
		<xsl:apply-templates/>
	</xsl:template>

	<xsl:template match="obj">

CREATE TABLE `<xsl:value-of select="@name"/>` (
	<xsl:apply-templates select="attr"/>
	<xsl:if test="primary_key/primary">
		<xsl:apply-templates select="." mode="primary"/>
	</xsl:if>
	<xsl:apply-templates select="index"/>
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	</xsl:template>

	<!-- pk & index -->

	<xsl:template match="obj" mode="primary">
		,PRIMARY KEY (<xsl:for-each select="primary_key/primary">`<xsl:value-of select="@name"/>`<xsl:if test="position()!=last()">,</xsl:if></xsl:for-each>)</xsl:template>

	<xsl:template match="index">
		,<xsl:if test="@unique=1">UNIQUE </xsl:if>KEY `<xsl:value-of select="substring(@name,1,64)"/>` (<xsl:value-of select="."/>)</xsl:template>

	<!-- attr -->

	<xsl:template match="attr">
		`<xsl:value-of select="@name"/>` <xsl:value-of select="@dbtype"/><xsl:if test="@length!=-1">(<xsl:value-of select="@length"/>)</xsl:if><xsl:if test="@not_null=1"> NOT NULL</xsl:if><xsl:if test="@auto_increment=1"> AUTO_INCREMENT</xsl:if><xsl:if test="@has_default=1"><xsl:choose><xsl:when test="@type='xpstring'"> DEFAULT ''</xsl:when><xsl:otherwise> DEFAULT 0</xsl:otherwise></xsl:choose></xsl:if><xsl:if test="position()!=last()">,</xsl:if></xsl:template>

	<xsl:template match="attr[@dbtype='date' or @dbtype='datetime']">
		`<xsl:value-of select="@name"/>` <xsl:value-of select="@dbtype"/><xsl:if test="position()!=last()">,</xsl:if></xsl:template>

	<xsl:template match="attr[@dbtype='money']">
		`<xsl:value-of select="@name"/>` double(<xsl:value-of select="@length"/>,2)<xsl:if test="position()!=last()">,</xsl:if></xsl:template>



</xsl:stylesheet>
<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
