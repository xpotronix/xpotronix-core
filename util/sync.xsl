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

	<xsl:output method="html" version="4.0" encoding="UTF-8" indent="yes"/>



	<xsl:template match="/">
<html>
<head>
<title>Sync Mapping</title>
</head>
<body>
		<xsl:apply-templates select="database"/>
</body>
<html>

	</xsl:template>


	<xsl:template match="database">
<h1>Sync Mapping for Application</h1>
		<xsl:apply-templates select="table[field/@alias]"/>
	</xsl:template>

	<xsl:template match="table">
		<xsl:element name="table">
			<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
			<xsl:apply-templates select="field[@alias]"/>
		</xsl:element>
	</xsl:template>

	<xsl:template match="field">
		<xsl:element name="field">
			<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
		</xsl:element>
	</xsl:template>
</xsl:stylesheet>
