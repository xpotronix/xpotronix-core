<?xml version="1.0" encoding="UTF-8"?>

<!--
	@package xpotronix
	@version 2.0 - Areco 
	@copyright Copyright &copy; 2003-2011, Eduardo Spotorno
	@author Eduardo Spotorno
 
	Licensed under GPL v3
	@license http://www.gnu.org/licenses/gpl-3.0.txt
-->

<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="text"/>
	<xsl:template match="/">
		<xsl:apply-templates select="//alias"/>
	</xsl:template>

	<xsl:template match="alias">

### Dir: <xsl:value-of select="."/> 

Alias /<xsl:value-of select="."/> /var/www/sites/xpotronix/<xsl:value-of select="."/>/
&lt;Directory /var/www/sites/xpotronix/<xsl:value-of select="."/>/&gt;
&lt;IfModule mod_php5.c&gt;
php_value include_path ".:/usr/share/php:/usr/share/php/adodb:/usr/share/xpotronix"
&lt;/IfModule&gt;
&lt;/Directory&gt;

	</xsl:template>

	</xsl:stylesheet>
<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
