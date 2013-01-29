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

<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="text" encoding="UTF-8"/>
	<xsl:template match="/">
		<xsl:apply-templates select="//field[@alias and @alias!=@name]"/>
	</xsl:template>

	<xsl:template match="field">
		<xsl:variable name="name" select="@name"/>
		<xsl:variable name="alias_name" select="@alias"/>
		<xsl:variable name="table_name" select="../@name"/>
		<xsl:variable name="table_field" select="document('/usr/share/xpotronix/projects/priv-projects/xpay/tables.xml')//table[@name=$table_name]/field[@name=$alias_name]"/>
ALTER TABLE `<xsl:value-of select="../@name"/>` CHANGE COLUMN `<xsl:value-of select="@alias"/>` `<xsl:value-of select="@name"/>` <xsl:value-of select="$table_field/@type"/><xsl:if test="$table_field/@max_length!=-1">(<xsl:value-of select="$table_field/@max_length"/>)</xsl:if><xsl:if test="$table_field/@not_null=1"> NOT NULL</xsl:if><xsl:if test="$table_field/@auto_increment=1"> AUTO_INCREMENT</xsl:if><xsl:if test="$table_field/@has_default=1"><xsl:choose><xsl:when test="@type='char'"> DEFAULT ''</xsl:when><xsl:otherwise> DEFAULT 0</xsl:otherwise></xsl:choose></xsl:if>;</xsl:template>

	<xsl:template match="attr[@dbtype='date' or @dbtype='datetime']">
		`<xsl:value-of select="@name"/>` <xsl:value-of select="@dbtype"/><xsl:if test="position()!=last()">,</xsl:if></xsl:template>

	<xsl:template match="attr[@dbtype='money']">
		`<xsl:value-of select="@name"/>` double(<xsl:value-of select="@max_length"/>,2)<xsl:if test="position()!=last()">,</xsl:if></xsl:template>
</xsl:stylesheet>
<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
