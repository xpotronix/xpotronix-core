<?xml version="1.0" encoding="UTF-8"?>

<!--
	@package xpotronix
	@version 2.0 - Areco 
	@copyright Copyright &copy; 2003-2011, Eduardo Spotorno
	@author Eduardo Spotorno
 
	Licensed under GPL v3
	@license http://www.gnu.org/licenses/gpl-3.0.txt
-->

<xsl:stylesheet version="2.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:strip-space elements="rr soa"/>
	<xsl:output method="text" encoding="utf-8"/>

	<xsl:variable name="base_path" select="'var/cache/bind/'"/>

	<xsl:template match="/">
		<xsl:result-document method="text" encoding="UTF-8" href="named.conf.local">

		<xsl:for-each select="c_/soa">
			<xsl:variable name="file_name" select="concat($base_path,origin,'db')"/>
zone "<xsl:value-of select="origin"/>in-addr.arpa" {
        type master;
        file "<xsl:value-of select="$file_name"/>";
};
		</xsl:for-each>
		</xsl:result-document>	
	
		<xsl:apply-templates/>
	</xsl:template>

	<xsl:template match="c_">
		<xsl:apply-templates/>
	</xsl:template>

	<xsl:template match="soa">

	<xsl:variable name="file_name" select="concat($base_path,origin,'db')"/>
	<xsl:result-document encoding="UTF-8" href="{$file_name}">
;
; BIND data file for <xsl:value-of select="origin"/> 
;
$TTL    604800
<xsl:value-of select="origin"/>       IN      SOA     <xsl:value-of select="ns"/><xsl:text> </xsl:text><xsl:value-of select="mbox"/> (
	<xsl:value-of select="serial"/> ; Serial
	<xsl:value-of select="refresh"/> ; Refresh
	<xsl:value-of select="retry"/> ; Retry
	<xsl:value-of select="expire"/> ; Expire
	<xsl:value-of select="minimum"/> ) ; Negative TTL
;
; Hosts
; 
@       IN      NS      <xsl:value-of select="ns"/>
<xsl:text>
</xsl:text><xsl:apply-templates select="c_/rr"/><xsl:text>
</xsl:text>
        </xsl:result-document>
	</xsl:template>

	<xsl:template match="rr">
		<xsl:choose><xsl:when test="name/text()"><xsl:value-of select="name"/></xsl:when><xsl:otherwise><xsl:value-of select="'@'"/></xsl:otherwise></xsl:choose><xsl:text>&#9;</xsl:text><xsl:value-of select="type"/><xsl:text>&#9;</xsl:text><xsl:value-of select="data"/><xsl:if test="type='MX'"><xsl:text>&#9;</xsl:text><xsl:value-of select="aux"/></xsl:if><xsl:text>
</xsl:text>
	</xsl:template>

<xsl:template match="*">
	<xsl:value-of select="."/>
</xsl:template>

</xsl:stylesheet>

<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
