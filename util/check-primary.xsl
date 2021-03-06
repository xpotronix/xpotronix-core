<?xml version="1.0" encoding="utf-8"?>

<!--
	@package xpotronix
	@version 2.0 - Areco 
	@copyright Copyright &copy; 2003-2011, Eduardo Spotorno
	@author Eduardo Spotorno
 
	Licensed under GPL v3
	@license http://www.gnu.org/licenses/gpl-3.0.txt
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes"/>
	<xsl:template match="/">
		<xsl:apply-templates select="database"/>
	</xsl:template>
	<xsl:template match="database">
		<xsl:element name="database">
			<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
				<xsl:apply-templates select="table"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="table">
		<xsl:element name="table">
			<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
			<xsl:element name="primary">
				<xsl:choose>
					<xsl:when test="primary/@name!=''">
						<xsl:attribute name="name">
							<xsl:value-of select="primary/@name"/>
						</xsl:attribute>
						<xsl:attribute name="exists">
							<xsl:value-of select="'true'"/>
						</xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
						<xsl:attribute name="name">
							<xsl:value-of select="field[1]/@name"/>
						</xsl:attribute>
						<xsl:attribute name="exists">
							<xsl:value-of select="'false'"/>
						</xsl:attribute>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:element>
			<xsl:element name="auto">
				<xsl:choose>
					<xsl:when test="auto/@name!=''">
						<xsl:attribute name="name">
							<xsl:value-of select="auto/@name"/>
						</xsl:attribute>
						<xsl:attribute name="exists">
							<xsl:value-of select="'true'"/>
						</xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
						<xsl:attribute name="name">
							<xsl:value-of select="field[1]/@name"/>
						</xsl:attribute>
						<xsl:attribute name="exists">
							<xsl:value-of select="'false'"/>
						</xsl:attribute>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:element>
		</xsl:element>
	</xsl:template>
</xsl:stylesheet>
