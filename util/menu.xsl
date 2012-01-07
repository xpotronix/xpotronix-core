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
	<xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/database">
		<xsl:element name="menu">
			<xsl:attribute name="n" select="@name"/>
			<xsl:element name="menu">
				<xsl:attribute name="n" select="'modulos'"/>
               			<xsl:apply-templates/>
			</xsl:element>
		</xsl:element>
	</xsl:template>

	<xsl:template match="table">
		<xsl:element name="item">
			<xsl:attribute name="n" select="@name"/>
			<xsl:attribute name="h" select="concat('?m=', @name )"/>	
		</xsl:element>
	</xsl:template>
</xsl:stylesheet>
<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
