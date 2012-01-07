<?xml version="1.0" encoding="UTF-8"?>

<!--
	@package xpotronix
	@version 2.0 - Areco 
	@copyright Copyright &copy; 2003-2011, Eduardo Spotorno
	@author Eduardo Spotorno
 
	Licensed under GPL v3
	@license http://www.gnu.org/licenses/gpl-3.0.txt
-->

<xsl:stylesheet version="1.0"
	xmlns="http://www.w3.org/TR/REC-html40"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:xpotronix="http://xpotronix.com/namespace/xpotronix/">

	<xsl:preserve-space elements="text"/>
	<xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes"/>

	<xsl:template match="/"><!--{{{--> 
		<xsl:apply-templates select="//xpotronix:dataset/class/obj"/>
	</xsl:template><!--}}}-->

	<xsl:template match="obj"><!--{{{-->
		<table>
			<xsl:apply-templates select="attr"/>
		</table>
	</xsl:template><!--}}}-->

	<xsl:template match="attr"><!--{{{-->
		<xsl:variable name="obj_name" select="../@name"/>
		<xsl:variable name="attr_name" select="@name"/>
		<tr>
			<td width="10%"><xsl:apply-templates select="." mode="translate"/></td>
			<td><xsl:choose>
					<xsl:when test="@label"><xsl:value-of select="@label"/></xsl:when>
					<xsl:otherwise><xsl:value-of select="." disable-output-escaping="yes"/></xsl:otherwise>
			</xsl:choose></td>
		</tr>
	</xsl:template><!--}}}-->

        <xsl:template match="attr" mode="translate"><!--{{{-->


		<xsl:variable name="obj_name" select="../@name"/>
		<xsl:variable name="attr_name" select="@name"/>
		

		<xsl:variable name="attr_m" select="//xpotronix:metadata/obj[@name=$obj_name]/attr[@name=$attr_name]"/>

		<xsl:message><xsl:copy-of select="$attr_m"/></xsl:message>

                <xsl:choose>
                        <xsl:when test="$attr_m/@translate!=''">
                                <xsl:value-of select="$attr_m/@translate"/>
                        </xsl:when>
                        <xsl:otherwise>
                                <xsl:value-of select="$attr_m/@name"/>
                        </xsl:otherwise>
                </xsl:choose>
        </xsl:template><!--}}}-->


</xsl:stylesheet>
<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
