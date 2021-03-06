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
				<xsl:apply-templates select="table"/>
	</xsl:template>
	<xsl:template match="table">
		<xsl:element name="table">
			<xsl:apply-templates select="field"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="field">
		<xsl:if test="@type='date'">update <xsl:value-of select="../@name"/> set <xsl:value-of select="@name"/> = NULL where <xsl:value-of select="@name"/> = '0000/00/00';
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
