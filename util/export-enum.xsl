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
	<xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes"/>
	<xsl:template match="/">
		<enums>
		<xsl:apply-templates select="//field[@type='enum']"/>
		</enums>
	</xsl:template>

	<xsl:template match="field">
		<xsl:element name="enum">
			<xsl:attribute name="table" select="../@name"/>
			<xsl:attribute name="field" select="@name"/>
			<xsl:attribute name="values" select="@enums"/>
		</xsl:element>
	</xsl:template>

</xsl:stylesheet>
