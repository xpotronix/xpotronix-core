<?xml version="1.0" encoding="UTF-8"?>
<!-- 
	xpotronix 0.98 - Areco
-->
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:fn="http://www.w3.org/2005/04/xpath-functions" 
	xmlns:saxon="http://saxon.sf.net/" 
	extension-element-prefixes="saxon" 
	exclude-result-prefixes="saxon">
	<xsl:output method="text" version="1.0" encoding="UTF-8" indent="yes"/>

	<!-- -->
	<!-- class -->
	<!-- -->

	<xsl:template match="table" mode="class_main">

		<xsl:param name="type"/>
		<xsl:param name="mode"/>

		<xsl:variable name="type" select="string('class')"/>

		<xsl:variable name="path_prefix">
			<xsl:value-of select="concat($application_path,'/modules/',@name,'/')"/>
		</xsl:variable>

		<xsl:variable name="class_name">
			<xsl:value-of select="@name"/>
		</xsl:variable>

		<xsl:variable name="class_file_name" select="concat($path_prefix,$class_name,'.class.php')"/>

		<xsl:variable name="table_name" select="@name"/>
		<xsl:variable name="license" select="document($license_file)/license"/>

		<!--<xsl:message terminate="yes"><xsl:value-of select="$class_file_name"/></xsl:message> -->
		<xsl:result-document method="text" encoding="UTF-8" href="{$class_file_name}"><![CDATA[<?
/*
	Archivo: ]]><xsl:value-of select="$class_file_name"/><![CDATA[

]]><xsl:value-of select="$license"/><![CDATA[

*/

global $xpdoc;

require_once ']]><xsl:choose>
	<xsl:when test="@extends">
		<xsl:value-of select="concat('modules/',@extends,'/',@extends,'.class.php')"/>
	</xsl:when>
	<xsl:otherwise>
		<xsl:value-of select="'xpdataobject.class.php'"/>
	</xsl:otherwise>
	</xsl:choose><xsl:text>';

</xsl:text>

<xsl:for-each select="$code_collection//table[@name=$table_name]/code[@mode='include' and @type='php']"><xsl:value-of select="."/></xsl:for-each><![CDATA[

class C]]><xsl:value-of select="$class_name"/><![CDATA[ extends ]]><xsl:choose>
	<xsl:when test="@extends">
		<xsl:value-of select="concat('C',@extends)"/>
	</xsl:when>
	<xsl:otherwise>
		<xsl:value-of select="'xpDataObject'"/>
	</xsl:otherwise>
	</xsl:choose><![CDATA[ {

	var $class_name		= "]]><xsl:value-of select="$class_name"/><![CDATA[";
	var $table_name 	= "]]><xsl:value-of select="$table_name"/><![CDATA[";

	]]><xsl:text>// var def
</xsl:text>
		<xsl:for-each select="$code_collection//table[@name=$table_name]/code[@mode='var_def' and @type='php']">
			<xsl:value-of select="."/>
		</xsl:for-each><xsl:text>
	// class functions
</xsl:text>
<xsl:for-each select="$code_collection//table[@name=$table_name]/code[@mode='class_functions' and @type='php']"><xsl:value-of select="."/></xsl:for-each>
<![CDATA[
}

// vim600: fdm=marker sw=3 ts=8 ai:

?>]]></xsl:result-document>

</xsl:template>
</xsl:stylesheet>
<!--  vim600: fdm=marker sw=3 ts=8 ai:
-->
