<?xml version="1.0" encoding="UTF-8"?>

<!--
	@package xpotronix
	@version 2.0 - Areco 
	@copyright Copyright &copy; 2003-2011, Eduardo Spotorno
	@author Eduardo Spotorno
 
	Licensed under GPL v3
	@license http://www.gnu.org/licenses/gpl-3.0.txt
-->

<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="text" version="1.0" encoding="UTF-8" indent="yes"/>

	<xsl:template match="/">
		<xsl:apply-templates select="//sql_view"/>
	</xsl:template>

	<xsl:template match="sql_view">DROP VIEW IF EXISTS `<xsl:value-of select="../@name"/>`;
<xsl:value-of select="."/>;<xsl:text>

</xsl:text>
	</xsl:template>

</xsl:stylesheet>
