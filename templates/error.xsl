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
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="/"><!--{{{--> 
<html>
	<head>
		<title>::error</title>
	</head>
	<body>
		<xsl:apply-templates select="//messages"/>
	</body>
</html>
	</xsl:template><!--}}}-->

	<xsl:template match="messages">
		<h1>Mensaje del Servidor</h1>
		<hr/>
		<ul>
			<xsl:apply-templates select="message"/>
		</ul>
	</xsl:template>

	<xsl:template match="message">
		<li><xsl:value-of select="text()"/></li>
	</xsl:template>

</xsl:stylesheet>

<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
