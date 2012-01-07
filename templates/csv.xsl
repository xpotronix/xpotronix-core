<?xml version="1.0" encoding="UTF-8"?>

<!--
	@package xpotronix
	@version 2.0 - Areco 
	@copyright Copyright &copy; 2003-2011, Eduardo Spotorno
	@author Eduardo Spotorno
 
	Licensed under GPL v3
	@license http://www.gnu.org/licenses/gpl-3.0.txt
-->

<xsl:stylesheet version="2.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xpotronix="http://xpotronix.com/namespace/xpotronix/">

	<xsl:output method="text" encoding="utf-8"/>

	<xsl:template match="/">
		<xsl:apply-templates select="//xpotronix:dataset/class"/>
	</xsl:template>

	<xsl:template match="class">
		<xsl:variable name="name" select="@name"/>
		<xsl:variable name="metadata" select="//xpotronix:metadata/obj[@name=$name]/attr[not(@display) or @display='' or @display='hide' or @display='disabled']"/>
		<xsl:apply-templates select="." mode="title">
			<xsl:with-param name="metadata" select="$metadata"/>
		</xsl:apply-templates>
		<xsl:apply-templates select="obj">
			<xsl:with-param name="metadata" select="$metadata"/>
		</xsl:apply-templates>
	</xsl:template>

	<xsl:template match="class" mode="title"><xsl:param name="metadata"/><xsl:text>'</xsl:text><xsl:apply-templates select="$metadata" mode="title"/><xsl:text>
</xsl:text>
	</xsl:template>

	<xsl:template match="attr" mode="title"><xsl:value-of select="@name"/>;<xsl:if test="@entry_help!=''"><xsl:value-of select="concat(@name,'_label')"/>;</xsl:if></xsl:template>

	<xsl:template match="obj">
		<xsl:param name="metadata"/>
		<xsl:param name="obj" select="."/>
		<xsl:for-each select="$metadata">
			<xsl:variable name="name" select="@name"/>
			<xsl:apply-templates select=".">
				<xsl:with-param name="data" select="$obj/attr[@name=$name]"/>
			</xsl:apply-templates>
		</xsl:for-each><xsl:text>
</xsl:text>
</xsl:template>

	<xsl:template match="attr">
		<xsl:param name="data"/>
	<xsl:variable name="delim"><xsl:if test="@type='xptext' or @type='xpstring' or @type='xpdatetime'">"</xsl:if></xsl:variable>
<xsl:value-of select="$delim"/><xsl:value-of select="$data"/><xsl:value-of select="$delim"/>;<xsl:if test="@entry_help!=''">"<xsl:value-of select="$data/@label"/>";</xsl:if></xsl:template>


</xsl:stylesheet>

<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
