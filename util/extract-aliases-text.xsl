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
		<xsl:apply-templates select="model"/>
	</xsl:template>
	<xsl:template match="model">
		<xsl:element name="model">
			<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
			<xsl:apply-templates select="table[field/@alias!='' and substring(@name,1,2)!='v_' and sync/@group='m4']"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="table">
		<xsl:element name="table">
			<xsl:value-of select="@name"/>,<xsl:value-of select="sync/@from"/><xsl:text>
</xsl:text>
<xsl:apply-templates select="field"/><xsl:text>
</xsl:text>
		</xsl:element>
	</xsl:template>
	<xsl:template match="field">
		<xsl:element name="field">
<xsl:value-of select="@translate|@name"/>,<xsl:value-of select="@name"/>,<xsl:value-of select="@alias"/><xsl:text>
</xsl:text>
		</xsl:element>
	</xsl:template>
</xsl:stylesheet>
