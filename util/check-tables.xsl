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

	<xsl:output method="text" version="1.0" encoding="UTF-8" indent="no"/>

	<xsl:param name="base_dir"/>
	<xsl:param name="base_url"/>
	<xsl:param name="dry" select="'no'"/>

	<xsl:variable name="app">
		<xsl:value-of select="document(concat($base_dir,'/feat.xml'))//feat/application"/>
	</xsl:variable>

	<xsl:variable name="help">
check-tables.xsl 
recorre los flujos XML de datos de todos los objetos y sus entry_helpers
uso: saxonb-xslt tables.xml ../../util/check-tables.xsl base_dir=`pwd` base_url=http://localhost/app dry=yes
	</xsl:variable>

	<xsl:template match="/">
		<xsl:if test="$base_dir='' or $base_url=''">
			<xsl:message terminate="yes"><xsl:value-of select="$help"/></xsl:message>
		</xsl:if>
		<xsl:apply-templates select="database"/>
	</xsl:template>

	<xsl:template match="database">

		<xsl:result-document method="xml" omit-xml-declaration="no" encoding="UTF-8" href="check-tables.xml" indent="yes">
		<database>
		<xsl:apply-templates select="table"/>
		</database>
		</xsl:result-document>

	</xsl:template>

	<xsl:template match="table">
		<xsl:variable name="table_name" select="@name"/>
		<xsl:variable name="href" select="concat($base_url,'?a=data&amp;v=xml&amp;m=',@name,'&amp;r=',@name)"/>

		<xsl:element name="table">
			<xsl:attribute name="name" select="@name"/>
			<xsl:attribute name="href" select="$href"/>
			<xsl:if test="$dry!='yes' or not($dry)">
				<xsl:variable name="container" select="document($href)/c_"/>
				<xsl:attribute name="total_records" select="$container/@total_records"/>
				<xsl:copy-of select="$container"/>
				<xsl:for-each select="document(concat($base_dir,'/queries.xml'))/queries/query[from=$table_name]">

					<xsl:variable name="href_eh" select="concat($href,'&amp;q=',@name)"/>
					<xsl:element name="entry_help">
						<xsl:attribute name="name" select="@name"/>
						<xsl:attribute name="href" select="$href_eh"/>
						<xsl:attribute name="total_records" select="document($href_eh)/c_/@total_records"/>
						<xsl:copy-of select="$container"/>
					</xsl:element>
				</xsl:for-each>
			</xsl:if>
		</xsl:element>
	</xsl:template>

	</xsl:stylesheet>
<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
