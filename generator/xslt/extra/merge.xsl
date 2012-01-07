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
	<xsl:variable name="database_file" select="concat(/database/@name,'.database.xml')"/>
	<xsl:message><xsl:value-of select="$database_file"/><xsl:message/>
        <xsl:template match="@*|node()">
                <xsl:copy>
                        <xsl:apply-templates select="@*|node()"/>
                </xsl:copy>
        </xsl:template>
        <xsl:template match="field">
                <xsl:variable name="field_name" select="@name"/>
                <xsl:variable name="table_name" select="../@name"/>
                <xsl:copy-of select="document('juscabadev.database.xml')//table[@name=$table_name]/field[@before=$field_name]"/>
                <xsl:copy-of select="."/>
                <xsl:copy-of select="document('juscabadev.database.xml')//table[@name=$table_name]/field[@after=$field_name]"/>
        </xsl:template>
</xsl:stylesheet>

