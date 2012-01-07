<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>
	<xsl:template match="@*|node()">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()"/>
		</xsl:copy>
	</xsl:template>
	<xsl:template match="field">
		<xsl:variable name="field_name" select="@name"/>
		<xsl:variable name="table_name" select="../@name"/>
		<xsl:variable name="entry_help" select="document('juscabadev.entry_helpers.xml')//table[@name=$table_name]/field[@name=$field_name]"/>
		<xsl:element name="field">
			<xsl:copy-of select="@*"/>
			<xsl:copy-of select="document('juscabadev.ui.xml')//table[@name=$table_name]/field[@name=$field_name]/@*"/>
			<xsl:if test="count($entry_help)">
				<xsl:attribute name="entry_help"><xsl:value-of select="@name"/></xsl:attribute>
				<xsl:copy-of select="$entry_help/@*"/>
			</xsl:if>
		</xsl:element>
		<xsl:copy-of select="document('juscabadev.database.xml')//table[@name=$table_name]/field[@before=$field_name]"/>
		<xsl:copy-of select="document('juscabadev.database.xml')//table[@name=$table_name]/field[@after=$field_name]"/>
	</xsl:template>
</xsl:stylesheet>
