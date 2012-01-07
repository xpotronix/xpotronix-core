<?xml version="1.0" encoding="UTF-8"?>
<!-- 

	xpotronix 0.96 b (miramar)
	eduardo spotorno (c) 2005-2006 

-->
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 

	xmlns:fn="http://www.w3.org/2005/04/xpath-functions" 
	xmlns:saxon="http://saxon.sf.net/" 
	extension-element-prefixes="saxon" 
	exclude-result-prefixes="saxon">

	<xsl:output method="text" version="1.0" encoding="UTF-8" indent="yes"/>

	<xsl:template match="table" mode="queries">

		<xsl:param name="type"/>
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

		<xsl:variable name="file_type" select="string('.queries.xml')"/>
		<xsl:variable name="class_file_name" select="concat($path_prefix,$class_name,$file_type)"/>
		<xsl:variable name="table_name" select="@name"/>
		<xsl:variable name="license" select="document($license_file)/license"/>

		<!-- si el archivo existe, se corta la recursividad y el tiempo de proceso -->
		<!-- <xsl:message>Creando archivo <xsl:value-of select="$class_file_name"/></xsl:message> -->
		<xsl:choose>
			<xsl:when test="contains($file_list,$class_file_name)"/>
			<!-- si el archivo existe, no hace nada -->
			<xsl:otherwise>
				<xsl:variable name="file_list" select="concat($file_list,' ',$class_file_name)"/>
				<xsl:result-document method="xml" 
							omit-xml-declaration="no" 
							encoding="UTF-8" 
							href="{$class_file_name}"
							indent="yes">

					<xsl:element name="queries">
						<xsl:apply-templates select="." mode="get_main_sql"/>
						<xsl:sequence select="$queries_collection//query[from=$table_name]"/>
					</xsl:element>
				</xsl:result-document>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>
