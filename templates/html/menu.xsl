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
                <menu>
               	<xsl:apply-templates/>
                </menu>
        </xsl:template>

        <xsl:template match="menu">
                <xsl:element name="ul">
			<!--<xsl:attribute name="id"><xsl:value-of select="concat('menu_',translate(@n,' ','_'))"/></xsl:attribute> -->
			<xsl:attribute name="id">mainMenu</xsl:attribute>
                        <xsl:apply-templates/>
                </xsl:element>
        </xsl:template>

        <xsl:template match="menu/menu">
                <xsl:element name="li">
			<xsl:attribute name="id"><xsl:value-of select="concat('sub_menu_',translate(@n,' ','_'))"/></xsl:attribute>
			<a href="#"><xsl:value-of select="@n"/></a>
			<xsl:element name="ul">
				<xsl:attribute name="id"><xsl:value-of select="concat('sub_menu_',translate(@n,' ','_'))"/></xsl:attribute>
                        	<xsl:apply-templates/>
			</xsl:element>
                </xsl:element>
        </xsl:template>

        <xsl:template match="item">
                <xsl:element name="li">
			<xsl:attribute name="id"><xsl:value-of select="concat('item_',translate(@n,' ','_'))"/></xsl:attribute>
			<xsl:element name="a">
				<xsl:attribute name="href"><xsl:value-of select="@h"/></xsl:attribute>
				<xsl:value-of select="@n"/>
			</xsl:element>
                </xsl:element>
        </xsl:template>

</xsl:stylesheet>

