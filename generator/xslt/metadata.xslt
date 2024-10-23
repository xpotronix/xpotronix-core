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

		<xsl:variable name="path_prefix" select="concat($application_path,'/modules/',@name,'/')"/>
		<xsl:variable name="class_name" select="@name"/>
		<xsl:variable name="file_type" select="'.metadata.xml'"/>
		<xsl:variable name="class_file_name" select="concat($path_prefix,$class_name,$file_type)"/>

		<!-- <xsl:message>Creando archivo <xsl:value-of select="$class_file_name"/></xsl:message> -->
		<xsl:result-document method="xml" omit-xml-declaration="no" encoding="UTF-8" cdata-section-elements="renderer" href="file:///{$class_file_name}">
			<xsl:element name="application">
				<xsl:apply-templates select=".|.//table" mode="get_metadata"/>
			</xsl:element>
		</xsl:result-document>

	</xsl:template><!--}}}-->
	
	<xsl:template match="table" mode="get_metadata"><!--{{{-->

		<xsl:variable name="table_name" select="@name"/>
		<xsl:variable name="ui_table" select="$model_collection/table[@name=$table_name]"/>
		<xsl:variable name="tb_table" select="$table_collection/table[@name=$table_name]"/>
		<xsl:variable name="db_table" select="$database_collection/table[@name=$table_name]"/>
		<xsl:variable name="cd_table" select="$code_collection/table[@name=$table_name]"/>


		<xsl:element name="obj">

			<xsl:attribute name="name" select="@name"/>
			<xsl:attribute name="database" select="../@name"/>

			<xsl:sequence select="@*"/>
			<xsl:sequence select="$tb_table/@*"/>
			<xsl:sequence select="$db_table/@*"/>
			<xsl:sequence select="$model_collection/table[@name=$table_name]/@*"/>

			<xsl:choose>
				<xsl:when test="not($tb_table)">
				<xsl:attribute name="virtual" select="1"/>
				<xsl:message>la tabla <xsl:value-of select="$table_name"/> no esta definida en tables.xml</xsl:message>
				</xsl:when>
			     	<xsl:otherwise>
					<xsl:attribute name="persistent" select="1"/>
				</xsl:otherwise>
			</xsl:choose>

			<xsl:sequence select="$ui_table/sync"/>
			<xsl:sequence select="$ui_table/dbi"/>

			<!-- primary_key -->
			<xsl:apply-templates select="." mode="get_primary_key"/>

			<!-- feat -->
			<xsl:choose>
				<xsl:when test="feat">
					<xsl:sequence select="feat"/>
				</xsl:when>
				<xsl:when test="../table and $ui_table/feat">
					<xsl:sequence select="$ui_table/feat"/>
				</xsl:when>
			</xsl:choose>

			<!-- fields -->
			<xsl:variable name="field_collection">
				<xsl:apply-templates select="." mode="field_collection"/>
			</xsl:variable>

			<xsl:apply-templates select="$field_collection//field" mode="metadata">
				<xsl:with-param name="tb_table" select="$tb_table"/>
			</xsl:apply-templates>


			<!-- index -->
			<xsl:sequence select="$tb_table/index"/>

			<!-- aca se copian los elementos para la transformacion -->
			<xsl:sequence select="$ui_table/button"/>
			<xsl:sequence select="$ui_table/storeCbk"/>


			<!-- js-files -->
			<xsl:if test="$cd_table">
				<files>
					<xsl:for-each select="$cd_table/code">
						<file name="{concat('modules/',$table_name,'/',@mode,'.js')}" type="{@type}" mode="{@mode}"/>
					</xsl:for-each>
				</files>


			</xsl:if>

		</xsl:element>

	</xsl:template><!--}}}-->

	<xsl:template match="table" mode="field_collection"><!--{{{-->
		<!-- recorre $model_collection -->

		<xsl:variable name="table_name" select="@name"/>

		<!-- attr sets -->
		<xsl:variable name="ui_fields">
			<xsl:sequence select="field"/>
		</xsl:variable>

		<xsl:variable name="in_fields">
 			<xsl:sequence select="$model_collection/table[@name=$table_name]/field"/>
		</xsl:variable>

		<xsl:variable name="tb_fields">
			<xsl:sequence select="$table_collection/table[@name=$table_name]/field"/>
		</xsl:variable>

		<xsl:variable name="db_fields">
			<xsl:sequence select="$database_collection/table[@name=$table_name]/field"/>
		</xsl:variable>

		<!-- 
		<xsl:message>CLASS NAME: <xsl:value-of select="$table_name"/></xsl:message>
		<xsl:message>UI_FIELDS: <xsl:copy-of select="$ui_fields"/></xsl:message>
		<xsl:message>IN_FIELDS: <xsl:copy-of select="$in_fields"/></xsl:message>
		<xsl:message>TB_FIELDS: <xsl:copy-of select="$tb_fields"/></xsl:message>
		<xsl:message>DB_FIELDS: <xsl:copy-of select="$db_fields"/></xsl:message>
		-->

		<!-- override ui/in -->
		<xsl:variable name="p1_fields">
			<xsl:call-template name="fields_override">
				<xsl:with-param name="base_fields" select="$ui_fields"/>
				<xsl:with-param name="over_fields" select="$in_fields"/>
			</xsl:call-template>
		</xsl:variable>

		<!-- override p1/tb -->
		<xsl:variable name="p2_fields">
			<xsl:call-template name="fields_override">
				<xsl:with-param name="base_fields" select="$p1_fields"/>
				<xsl:with-param name="over_fields" select="$tb_fields"/>
			</xsl:call-template>
		</xsl:variable>

		<!-- overide p2/db -->
		<xsl:variable name="p3_fields">
			<xsl:call-template name="fields_override">
				<xsl:with-param name="base_fields" select="$p2_fields"/>
				<xsl:with-param name="over_fields" select="$db_fields"/>
			</xsl:call-template>
		</xsl:variable>


		<!-- metadata final -->
		<xsl:element name="table">
			<xsl:sequence select="@*"/>
			<xsl:sequence select="$p3_fields/*"/>
		</xsl:element>

	</xsl:template><!--}}}-->

	<xsl:template name="fields_override"><!--{{{-->

		<xsl:param name="base_fields"/>
		<xsl:param name="over_fields"/>

		<!--
		<xsl:message>**** fields_override ****</xsl:message>
		<xsl:message>BASE_FIELDS: <xsl:copy-of select="$base_fields/*"/></xsl:message>
		<xsl:message>count: <xsl:copy-of select="count($base_fields/*)"/></xsl:message>
		<xsl:message>OVER_FIELDS: <xsl:copy-of select="$over_fields/*"/></xsl:message>
		<xsl:message>count: <xsl:copy-of select="count($over_fields/*)"/></xsl:message>
		-->

		<!-- <xsl:message><xsl:value-of select="concat('fields_override: ', count($base_fields), '/', count($over_fields))"/></xsl:message> -->

		<xsl:variable name="result">
			<xsl:for-each select="$base_fields/*[@name=$over_fields/*/@name]">
				<xsl:copy>
					<xsl:copy-of select="$over_fields/*[@name=current()/@name]/@*"/>
					<xsl:copy-of select="@*"/>
					<!-- DEBUG: por ahora no hay override de los elementos -->
					<xsl:copy-of select="*"/>
				</xsl:copy>
			</xsl:for-each>
			<xsl:copy-of select="$base_fields/*[not(@name=$over_fields/*/@name)]"/>
			<xsl:copy-of select="$over_fields/*[not(@name=$base_fields/*/@name)]"/>
		</xsl:variable>

		<!-- <xsl:message>RESULT: <xsl:copy-of select="$result"/></xsl:message> -->

		<xsl:sequence select="$result"/>

	</xsl:template><!--}}}-->

	<xsl:template match="field" mode="metadata"><!--{{{-->
		<xsl:param name="tb_table"/>

		<!-- DEBUG: aca cambia el espacio de nombres de field a attr -->

		<xsl:variable name="table_name" select="../@name"/>
		<xsl:variable name="field_name" select="@name"/>
		<xsl:variable name="tb_field" 
			select="$table_collection//table[@name=$table_name]/field[@name=$field_name]"/>

		<xsl:variable name="type">
			<xsl:choose>
				<xsl:when test="@type">
                  		<xsl:apply-templates select="." mode="get_type"/>
				</xsl:when>
				<xsl:when test="$tb_field">
                   		<xsl:apply-templates select="$tb_field" mode="get_type"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="'xpstring'"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<xsl:variable name="doctrineType">
			<xsl:choose>
				<xsl:when test="@type">
                  		<xsl:apply-templates select="." mode="get_doctrineType"/>
				</xsl:when>
				<xsl:when test="$tb_field">
                   		<xsl:apply-templates select="$tb_field" mode="get_doctrineType"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="'string'"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>



		<!-- <xsl:message>@type: <xsl:value-of select="@type"/> $type <xsl:value-of select="$type"/></xsl:message> -->

		<xsl:variable name="length">
			<xsl:choose>
				<xsl:when test="@length">
					<xsl:value-of select="@length"/>
				</xsl:when>
				<xsl:when test="$tb_field">
					<xsl:apply-templates select="$tb_field" mode="get_length"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="0"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<xsl:variable name="eh_name" select="@eh"/>

		<!-- toma el primer query de la transformacion, el resto son ignorados -->
		<xsl:variable name="query" select="$queries_collection//query[@name=$eh_name][1]"/>

		<xsl:element name="attr">
			<xsl:sequence select="@*"/>

			<xsl:attribute name="table" select="$table_name"/>
			<xsl:attribute name="name" select="$field_name"/>
			<xsl:attribute name="type" select="$type"/>

			<xsl:if test="not(current()/@enums) and $enums_collection/enums/enum[@table=current()/../@name and @field=current()/@name]/@values">
				<xsl:attribute name="enums" select="$enums_collection/enums/enum[@table=current()/../@name and @field=current()/@name]/@values"/>
			</xsl:if>

			<xsl:message><xsl:value-of select="$tb_field"/></xsl:message>

			<xsl:if test="$tb_field/@type">
				<xsl:attribute name="dbtype" select="$tb_field/@type"/>
			</xsl:if>

			<xsl:choose>
				<xsl:when test="$datatypes//type[@name=$tb_field/@type]/@doctrineType!=''">
					<xsl:attribute name="doctrineType" select="$datatypes//type[@name=$tb_field/@type]/@doctrineType"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:attribute name="doctrineType" select="$datatypes//xtype[@name=$type]/@type"/>
				</xsl:otherwise>
			</xsl:choose>


			<xsl:attribute name="length" select="$length"/>

			<xsl:if test="$eh_name!=''">
				<xsl:attribute name="entry_help"><xsl:value-of select="$eh_name"/></xsl:attribute>
				<!-- DEBUG: fix para que tome el nombre relativo de la tabla, no el absoluto cuando tiene el nombre de la base de datos delante -->

				<xsl:choose>
					<xsl:when test="contains($query/from,'.')">
						<xsl:attribute name="entry_help_table"><xsl:value-of select="substring-after($query/from,'.')"/></xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
						<xsl:attribute name="entry_help_table"><xsl:value-of select="$query/from"/></xsl:attribute>
					</xsl:otherwise>
				</xsl:choose>

			</xsl:if>

			<xsl:if test="not($tb_field)">
				<xsl:if test="$tb_table">
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
					<xsl:if test="$all_documents/tables/table[@name=$table_name]">
                      		<xsl:message>La tabla <xsl:value-of select="$table_name"/> no tiene clave primaria definida</xsl:message>
					</xsl:if>
				</xsl:otherwise>
			</xsl:choose>

		</xsl:element>
	</xsl:template><!--}}}-->

</xsl:stylesheet>

<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
