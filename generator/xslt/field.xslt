<?xml version="1.0" encoding="utf-8"?>
<!-- edited with XML Spy v4.4 U (http://www.xmlspy.com) by eduardo spotorno (private) -->
<xsl:stylesheet version="2.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:fn="http://www.w3.org/2005/04/xpath-functions"
	xmlns:saxon="http://saxon.sf.net/"
	extension-element-prefixes="saxon"
	exclude-result-prefixes="saxon">
			
	<!-- -->
	<!-- get_length -->
	<!-- -->
	<xsl:template match="field" mode="get_length"><!--{{{-->
		<xsl:choose>
			<xsl:when test="@max_length">
				<xsl:value-of select="@max_length"/>
			</xsl:when>
			<xsl:when test="contains(@type,'(')">
				<xsl:value-of select="substring-after(substring-before(@type,')'),'(')"/>
			</xsl:when>
			<xsl:when test="not(@type) or @type='' or not(contains(@type,'('))">
				<xsl:value-of select="0"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="0"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template><!--}}}-->
	<!-- -->
	<!-- get_type -->
	<!-- -->
	<xsl:template match="field" mode="get_type"><!--{{{-->
		<xsl:variable name="type">
			<xsl:choose>
				<xsl:when test="contains(@type,'(')">
					<xsl:value-of select="substring-before(@type,'(')"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:choose>
						<xsl:when test="@type">
							<xsl:value-of select="@type"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="'char'"/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:variable name="return">
			<xsl:choose>
				<xsl:when test="$datatypes//xtype[type/@name=$type]/@name">
					<xsl:value-of select="$datatypes//xtype[type/@name=$type]/@name"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="@type"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<!-- <xsl:message>type: <xsl:value-of select="@type"/> - <xsl:value-of select="$return"/></xsl:message> -->
		<xsl:value-of select="$return"/>
	</xsl:template><!--}}}-->

</xsl:stylesheet>
<!--  vim600: fdm=marker sw=3 ts=8 ai:
-->

