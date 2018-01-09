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


	<xsl:variable name="queries" select="document('queries.xml')"/>
	<xsl:variable name="tables" select="document('tables.xml')"/>

	<xsl:output method="text" version="1.0" encoding="UTF-8" indent="no"/>

	<xsl:template match="/">

		<xsl:apply-templates select="//field[@eh!='']"/>

	</xsl:template>

	<xsl:template match="field">

		<xsl:variable name="eh_name" select="@eh"/>

		<xsl:variable name="query" select="$queries/database/queries/query[@name=$eh_name]"/>


		<!-- source -->

		<xsl:variable name="field_name" select="@name"/>
		<xsl:variable name="table_name" select="../@name"/>

		<xsl:variable name="src_type" select="$tables/database/table[@name=$table_name]/field[@name=$field_name]/@type"/>
		<xsl:variable name="src_length" select="$tables/database/table[@name=$table_name]/field[@name=$field_name]/@max_length"/>

		<!-- dest -->

		<xsl:variable name="dest_table_name" select="$queries/database/queries/query[@name=$eh_name]/from"/>
		<xsl:variable name="dest_field_name" select="substring-after($queries/database/queries/query[@name=$eh_name]/id, '.')"/>

		<xsl:variable name="dest_type" select="$tables/database/table[@name=$dest_table_name]/field[@name=$dest_field_name]/@type"/>
		<xsl:variable name="dest_length" select="$tables/database/table[@name=$dest_table_name]/field[@name=$dest_field_name]/@max_length"/>


		<xsl:if test="$src_type!=$dest_type and $src_length!=$dest_length">

			<!-- print -->

			<xsl:value-of select="concat($table_name,'/',$field_name)"/>[<xsl:value-of select="concat($src_type,'/',$src_length)"/>]===<xsl:value-of select="concat($dest_table_name,'/',$dest_field_name)"/>[<xsl:value-of select="concat($dest_type,'/',$dest_length)"/>]<xsl:text>
</xsl:text>

		</xsl:if>

		<!-- <xsl:copy-of select="$query"/>-->

	</xsl:template>


	</xsl:stylesheet>
<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
