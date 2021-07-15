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
			<xsl:value-of select="concat($application_path,'/modules/')"/>
		</xsl:variable>

		<xsl:variable name="class_name">
			<xsl:value-of select="@name"/>
		</xsl:variable>

		<!-- <xsl:variable name="class_file_name" select="concat($path_prefix,$class_name,'.class.php')"/> -->
		<xsl:variable name="class_file_name" select="concat($path_prefix,$class_name,'.php')"/>

		<xsl:variable name="table_name" select="@name"/>

		<xsl:variable name="source">
			<xsl:choose>
				<xsl:when test="@source!=''"><xsl:value-of select="unparsed-text(concat($project_path,'/',@source))"/></xsl:when>
				<xsl:otherwise>
					<xsl:apply-templates select="." mode="generate_source">
						<xsl:with-param name="class_name" select="$class_name"/>
						<xsl:with-param name="table_name" select="$table_name"/>
						<xsl:with-param name="class_file_name" select="$class_file_name"/>
					</xsl:apply-templates>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

			<!--<xsl:message terminate="yes"><xsl:value-of select="$class_file_name"/></xsl:message> -->

		<xsl:if test="not(@source) or @source!=''">
			<xsl:result-document method="text" encoding="UTF-8" href="{$class_file_name}">
				<xsl:value-of select="$source"/>
			</xsl:result-document>
		</xsl:if>

	</xsl:template>

	<xsl:template match="table" mode="generate_source">
		<xsl:param name="class_name"/>
		<xsl:param name="table_name"/>
		<xsl:param name="class_file_name"/>
		<xsl:variable name="license" select="$all_documents/license"/><![CDATA[<?php
/*
	Archivo: ]]><xsl:value-of select="$class_file_name"/><![CDATA[

]]><xsl:value-of select="$license"/>

*/

namespace App;
use \Xpotronix\DataObject;
<xsl:for-each select="$code_collection//table[@name=$table_name]/code[@mode='use_decl' and @type='php']">
	<xsl:value-of select="."/>
</xsl:for-each>

global $xpdoc;
<xsl:for-each select="$code_collection//table[@name=$table_name]/code[@mode='include' and @type='php']"><xsl:value-of select="."/></xsl:for-each><![CDATA[

class ]]><xsl:value-of select="$class_name"/><![CDATA[ extends ]]><xsl:choose>
	<xsl:when test="@extends">
		<xsl:value-of select="@extends"/>
	</xsl:when>
	<xsl:otherwise>
		<xsl:value-of select="'DataObject'"/>
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

?>]]></xsl:template>
</xsl:stylesheet>
<!--  vim600: fdm=marker sw=3 ts=8 ai:
-->
