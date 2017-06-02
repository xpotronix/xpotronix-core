<?xml version="1.0" encoding="utf-8"?>
<!-- 

	xpotronix 2 (areco)
	eduardo spotorno (c) 2003-2007 
-->

<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 

	xmlns:fn="http://www.w3.org/2005/04/xpath-functions" 
	xmlns:saxon="http://saxon.sf.net/" 
	extension-element-prefixes="saxon" 
	exclude-result-prefixes="saxon">


	<xsl:template match="table" mode="model"><!--{{{-->

		<xsl:param name="mode"/>

		<xsl:variable name="path_prefix">
			<xsl:value-of select="concat($application_path,'/modules/',@name,'/')"/>
		</xsl:variable>

		<xsl:variable name="suffix">
			<xsl:if test="$mode!=''">
				<xsl:value-of select="concat('_',$mode)"/>
			</xsl:if>
		</xsl:variable>

		<xsl:variable name="class_name">
			<xsl:value-of select="concat(@name,$suffix)"/>
		</xsl:variable>

		<xsl:variable name="file_type" select="string('.model.xml')"/>
		<xsl:variable name="class_file_name" select="concat($path_prefix,$class_name,$file_type)"/>
		<xsl:variable name="table_name" select="@name"/>

		<!-- <xsl:message>Creando archivo <xsl:value-of select="$class_file_name"/></xsl:message> -->
		<xsl:result-document method="xml" 
			omit-xml-declaration="no" 
			encoding="utf-8" 
			href="{$class_file_name}">

			<xsl:apply-templates select="." mode="get_model"/>

		</xsl:result-document>

	</xsl:template><!--}}}-->

	<xsl:template match="table" mode="get_model"><!--{{{-->
		<xsl:element name="obj">
			<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
			<xsl:sequence select="@*"/>
			<xsl:sequence select="layout"/>
			<xsl:sequence select="config"/>
			<xsl:apply-templates select="panel" mode="copy"/>
			<xsl:apply-templates select="." mode="get_primary_key"/>
			<xsl:sequence select="foreign_key"/>
			<xsl:sequence select="order_by"/>
			<xsl:apply-templates select="." mode="get_queries"/>
			<xsl:apply-templates select="table" mode="get_model"/>
		</xsl:element>
	</xsl:template><!--}}}-->

	<xsl:template match="table" mode="get_queries"><!--{{{-->
		<xsl:variable name="table_name" select="@name"/>
		<xsl:element name="queries">

			<xsl:apply-templates select="." mode="get_main_sql"/>

			<!-- para los from relativos -->
			<xsl:sequence select="$queries_collection//query[from=$table_name]"/>

			<!-- para los from absolutos (database.table) -->
			<xsl:sequence select="$queries_collection//query[substring-after(from,'.')=$table_name]"/>

		</xsl:element>
	</xsl:template><!--}}}-->

        <xsl:template match="table" mode="get_main_sql"><!--{{{-->
                <xsl:variable name="table_name" select="@name"/>
                <query name="main_sql">
		<xsl:choose>
			<xsl:when test="$database_collection//table[@name=$table_name]/sql">
				<!-- el sql estatico en su elemento -->
				<xsl:sequence select="$database_collection//table[@name=$table_name]/sql"/>
			</xsl:when>
			<xsl:otherwise>
				<!-- automatico -->
				<xsl:sequence select="$database_collection//table[@name=$table_name]/*[name()='modifiers' or name()='join' or name()='group_by']"/>

				<!-- para los queries que definen la consulta ppal (en database) -->
				<xsl:for-each select="$database_collection//table[@name=$table_name]/query">
					<xsl:variable name="query_name" select="@name"/>
					<!-- solo los elementos contenidos por query -->
					<xsl:copy-of select="$queries_collection//query[@name=$query_name]/*" copy-namespaces="no"/>
				</xsl:for-each>

		                <!-- <from><xsl:value-of select="@name"/></from> -->

	        	        <xsl:for-each select="$ui_collection//table[@name=$table_name]/field[@eh!='' or @entry_help!='']">
	                	        <xsl:variable name="query_name" select="@eh|@entry_help"/>
					<xsl:variable name="query" select="$queries_collection//query[@name=$query_name]"/>
					<xsl:choose>
						<xsl:when test="count($query/*)=0">
							<xsl:message>No encuentro el query <xsl:value-of select="$query_name"/> para el atributo <xsl:value-of select="concat(../@name,'/',@name)"/></xsl:message>
						</xsl:when>
						<xsl:otherwise>
        	                			<xsl:sequence select="$query"/> 			
						</xsl:otherwise>
					</xsl:choose>
	        	        </xsl:for-each>
			</xsl:otherwise>
		</xsl:choose>
       	        </query>
        </xsl:template><!--}}}-->

	<xsl:template match="panel" mode="copy"><!--{{{-->
		<xsl:variable name="include" select="@include"/>

		<xsl:element name="panel" namespace="">
			<xsl:sequence select="@*"/>
			<xsl:sequence select="//panel[@id=$include]/@*"/>
			<xsl:choose>
				<xsl:when test="@include">
				<xsl:message>include <xsl:value-of select="@include"/></xsl:message>
						<!-- un override aca tambien? -->
						<xsl:apply-templates select="//panel[@id=$include]/*" mode="copy"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates select="*" mode="copy"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:element>
	</xsl:template><!--}}}-->

	<xsl:template match="*" mode="copy"><!--{{{-->
		<xsl:copy>
			<xsl:copy-of select="@*"/>
			<xsl:copy-of select="./text()"/>
			<xsl:apply-templates select="*" mode="copy"/>
		</xsl:copy>
	</xsl:template>
</xsl:stylesheet><!--}}}-->

<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
