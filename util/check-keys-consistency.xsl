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
		<xsl:message>check-keys-consistency:</xsl:message>
		<xsl:message>chequea las claves primarias y los campos que las referencia</xsl:message>
		<xsl:message>pasan todas al siguiente esquema:</xsl:message>
		<xsl:message>int -&gt; unsigned int de 13</xsl:message>
		<xsl:message>char -&gt; char de 32</xsl:message>
		<xsl:message>Eduardo Spotorno - 2008</xsl:message>
		<xsl:message></xsl:message>
		<xsl:element name="database">
			<xsl:message>Chequeando las Tablas</xsl:message>
			<xsl:apply-templates select="table"/>
			<xsl:message>Chequeando las Claves Foraneas</xsl:message>
			<xsl:message>Chequeando los Entry Helpers</xsl:message>
		</xsl:element>
	</xsl:template>
	<xsl:template match="table">
		<xsl:message> * Chequeando la tabla <xsl:value-of select="@name"/></xsl:message>
		<xsl:choose>
			<xsl:when test="primary">
				<xsl:variable name="primary_name" select="primary/@name"/>
				<xsl:variable name="primary" select="field[@name=$primary_name]"/>
				<xsl:message>Clave Primaria: <xsl:value-of select="$primary/@name"/> con <xsl:value-of select="$primary/@type"/></xsl:message>
			</xsl:when>
			<xsl:otherwise>
				<xsl:message>La tabla <xsl:value-of select="@name"/>no tiene clave primaria</xsl:message>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
