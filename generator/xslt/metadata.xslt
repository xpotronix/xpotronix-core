<?xml version="1.0" encoding="UTF-8"?>
<!-- 
	
-->
<xsl:stylesheet version="2.0" 

	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:fn="http://www.w3.org/2005/04/xpath-functions" 
	xmlns:saxon="http://saxon.sf.net/" 
	extension-element-prefixes="saxon" 
	exclude-result-prefixes="saxon">

	<xsl:output method="text" version="1.0" encoding="UTF-8" indent="yes"/>

	<xsl:template match="table" mode="metadata"><!--{{{-->

		<xsl:param name="type"/>
		<xsl:param name="mode"/>
		
		<xsl:variable name="path_prefix">
			<xsl:value-of select="concat($application_path,'/modules/',@name,'/')"/>
		</xsl:variable>

		<xsl:variable name="class_name">
			<xsl:value-of select="@name"/>
		</xsl:variable>

		<xsl:variable name="file_type" select="string('.metadata.xml')"/>
		<xsl:variable name="class_file_name" select="concat($path_prefix,$class_name,$file_type)"/>

		<xsl:variable name="table_name" select="@name"/>
		<!-- <xsl:message>Creando archivo <xsl:value-of select="$class_file_name"/></xsl:message> -->
		<xsl:variable name="file_list" select="concat($file_list,' ',$class_file_name)"/>
		<xsl:result-document method="xml" 
					omit-xml-declaration="no" 
					encoding="UTF-8"
					cdata-section-elements="renderer"
					href="{$class_file_name}">

			<xsl:element name="application">
				<xsl:apply-templates select=".|.//table" mode="get_metadata"/>
			</xsl:element>
		</xsl:result-document>
	</xsl:template><!--}}}-->
	
	<xsl:template match="table" mode="get_metadata"><!--{{{-->


		<xsl:variable name="table_name" select="@name"/>
		<xsl:variable name="ui_attr" select="$table_collection//table[@name=$table_name]"/>

		<xsl:variable name="field_collection">
			<xsl:apply-templates select="." mode="field_collection"/>
		</xsl:variable>

		<xsl:variable name="table_data" select="$table_collection//table[@name=$table_name]"/>

		<xsl:element name="obj">
			<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
			<xsl:attribute name="database"><xsl:value-of select="../@name"/></xsl:attribute>

			<xsl:sequence select="$table_collection//table[@name=$table_name]/@*"/>
			<xsl:sequence select="$database_collection//table[@name=$table_name]/@*"/>

			<!-- atributos del obj -->
       			<xsl:for-each select="@*">
       				<xsl:variable name="attr_name" select="name()"/>
               			<xsl:if test="not($ui_attr/@*[name()=$attr_name])">
                      			<xsl:sequence select="."/>
               			</xsl:if>
               		</xsl:for-each>


			<!-- pone virtual="1" si la tabla no esta en tables.xml -->
			<xsl:if test="not($table_data)">
				<xsl:attribute name="virtual" select="1"/>
				<xsl:message>la tabla <xsl:value-of select="$table_name"/> no esta definida en tables.xml</xsl:message>
			</xsl:if>

			<xsl:sequence select="$ui_collection/table[@name=$table_name]/sync"/>

			<!-- primary_key -->
			<xsl:apply-templates select="." mode="get_primary_key"/>

			<!-- feat -->
			<xsl:choose>
				<xsl:when test="feat">
					<xsl:sequence select="feat"/>
				</xsl:when>
				<xsl:when test="../table and $ui_collection/table[@name=$table_name]/feat">
					<xsl:sequence select="$ui_collection/table[@name=$table_name]/feat"/>
				</xsl:when>
			</xsl:choose>

			<!-- fields -->
			<xsl:apply-templates select="$field_collection//field" mode="metadata">
				<xsl:with-param name="table_data" select="$table_data"/>
			</xsl:apply-templates>

			<!-- index -->
			<xsl:sequence select="$table_collection//table[@name=$table_name]/index"/>

		</xsl:element>

	</xsl:template><!--}}}-->

	<xsl:template match="table" mode="field_collection"><!--{{{-->

		<xsl:variable name="table_name" select="@name"/>
		<xsl:variable name="fields" select="field"/>
		<xsl:variable name="result">
		<xsl:element name="table">
			<xsl:sequence select="@*"/>
			<xsl:comment>Atributos propios de la vista</xsl:comment>
			<!-- attr en ui -->
			<xsl:for-each select="field">
				<xsl:variable name="field_name" select="@name"/>
				<xsl:sequence select="$database_collection//table[@name=$table_name]/field[@before=$field_name]"/>
				<xsl:sequence select="."/>
				<xsl:sequence select="$database_collection//table[@name=$table_name]/field[@after=$field_name]"/>
			</xsl:for-each>

			<!-- attr en ui parent -->
			<xsl:if test="../table">
				<xsl:comment>Atributos Heredados</xsl:comment>
				<xsl:for-each select="$ui_collection//table[@name=$table_name]/field">
					<xsl:variable name="field_name" select="@name"/>
					<xsl:if test="not($fields[@name=$field_name])">
						<xsl:sequence select="$database_collection//table[@name=$table_name]/field[@before=$field_name]"/>
						<xsl:sequence select="."/>
						<xsl:sequence select="$database_collection//table[@name=$table_name]/field[@after=$field_name]"/>
					</xsl:if>
				</xsl:for-each>
			</xsl:if>
			<xsl:comment>Atributos en tables.xml sin redefinicion en ui.xml</xsl:comment>
			<!-- attr en tables -->
			<xsl:for-each select="$table_collection//table[@name=$table_name]/field">
				<xsl:variable name="field_name" select="@name"/>
				<xsl:if test="not($fields[@name=$field_name]) and not($ui_collection//table[@name=$table_name]/field[@name=$field_name])">
					<xsl:sequence select="$database_collection//table[@name=$table_name]/field[@before=$field_name]"/>
					<xsl:sequence select="."/>
					<xsl:sequence select="$database_collection//table[@name=$table_name]/field[@after=$field_name]"/>
				</xsl:if>
			</xsl:for-each>
			<!-- attr en database sin after ni before -->
			<xsl:sequence select="$database_collection//table[@name=$table_name]/field[not(@after) and not(@before)]"/>
		</xsl:element>
		</xsl:variable>
		<!-- <xsl:if test="@name='sessions'">
			<xsl:message terminate="yes"><xsl:sequence select="$result"/></xsl:message>
		</xsl:if>-->
		<xsl:sequence select="$result"/>
	</xsl:template><!--}}}-->

	<xsl:template match="field"  mode="metadata"><!--{{{-->

		<xsl:param name="table_data"/>
		<!-- DEBUG: aca cambia el espacio de nombres de field a attr -->

		<xsl:variable name="table_name" select="../@name"/>
		<xsl:variable name="field_name" select="@name"/>
		<xsl:variable name="tables_attr" select="$table_collection//table[@name=$table_name]/field[@name=$field_name]"/>

                <xsl:variable name="type">
			<xsl:choose>
				<xsl:when test="@type">
                        		<xsl:apply-templates select="." mode="get_type"/>
				</xsl:when>

				<xsl:when test="$tables_attr">
                        		<xsl:apply-templates select="$tables_attr" mode="get_type"/>
				</xsl:when>

				<xsl:otherwise>
					<xsl:value-of select="'xpstring'"/>
				</xsl:otherwise>
			</xsl:choose>
                </xsl:variable>
		<!-- <xsl:message>@type: <xsl:value-of select="@type"/> $type <xsl:value-of select="$type"/></xsl:message> -->

                <xsl:variable name="length">
			<xsl:choose>
				<xsl:when test="@length">
					<xsl:value-of select="@length"/>
				</xsl:when>
				<xsl:when test="$tables_attr">
                        		<xsl:apply-templates select="$tables_attr" mode="get_length"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="0"/>
				</xsl:otherwise>
			</xsl:choose>
                </xsl:variable>

		<xsl:variable name="alias_of">
			<xsl:value-of select="@alias_of"/>
		</xsl:variable>

		<xsl:variable name="entry_help_name" select="@eh"/>

		<xsl:variable name="query" select="$queries_collection//query[@name=$entry_help_name]"/>

		<xsl:element name="attr">

			<xsl:variable name="this_attr" select="."/>

			<xsl:sequence select="@*"/>
			<xsl:sequence select="$tables_attr/@*"/>
			<xsl:attribute name="table" select="$table_name"/>
			<xsl:attribute name="name" select="$field_name"/>
			<xsl:attribute name="type" select="$type"/>
			<xsl:if test="$tables_attr/@type">
				<xsl:attribute name="dbtype" select="$tables_attr/@type"/>
			</xsl:if>
			<xsl:attribute name="length" select="$length"/>

			<!-- <xsl:for-each select="$tables_attr/@*">
				<xsl:variable name="attr_name" select="name()"/>
				<xsl:if test="not($this_attr/@*[name()=$attr_name])">
					<xsl:sequence select="."/>
				</xsl:if>
			</xsl:for-each> -->
			<xsl:if test="$entry_help_name!=''">
				<xsl:attribute name="entry_help"><xsl:value-of select="$entry_help_name"/></xsl:attribute>
				<xsl:attribute name="entry_help_table"><xsl:value-of select="$query/from"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="not($tables_attr)">
				<xsl:if test="$table_data">
					<xsl:message>atributo virtual: <xsl:value-of select="concat($table_name,'/',$field_name)"/></xsl:message>
				</xsl:if>
				<xsl:attribute name="virtual" select="1"/>
			</xsl:if>
			<xsl:sequence select="./*"/>
		</xsl:element>
	</xsl:template><!--}}}--> 

	<xsl:template match="table" mode="get_primary_key"><!--{{{-->
		<xsl:variable name="table_name" select="@name"/>
		<xsl:element name="primary_key">
			<xsl:choose>
				<xsl:when test="$table_collection//table[@name=$table_name]/primary">
					<xsl:sequence select="$table_collection//table[@name=$table_name]/primary"/>
				</xsl:when>
				<xsl:when test="$database_collection//table[@name=$table_name]/primary">
					<xsl:sequence select="$database_collection//table[@name=$table_name]/primary"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:if test="document($tables_file)/database/table[@name=$table_name]">
	                        		<xsl:message>La tabla <xsl:value-of select="$table_name"/> no tiene clave primaria definida</xsl:message>
					</xsl:if>
				</xsl:otherwise>
			</xsl:choose>

		</xsl:element>
	</xsl:template><!--}}}-->

</xsl:stylesheet>

<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
