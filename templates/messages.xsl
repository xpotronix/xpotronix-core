<?xml version="1.0" encoding="UTF-8"?>

<!--
	@package xpotronix
	@version 2.0 - Areco 
	@copyright Copyright &copy; 2003-2011, Eduardo Spotorno
	@author Eduardo Spotorno
 
	Licensed under GPL v3
	@license http://www.gnu.org/licenses/gpl-3.0.txt
-->

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:template match="/"><!--{{{--> 
<html>
	<head>
		<title>Mensajes de la Aplicación</title>
	</head>
	<body>
		<xsl:apply-templates select="//xpotronix:messages/messages"/>
	</body>
</html>
	</xsl:template><!--}}}-->

	<xsl:template match="messages">
		<table border="1">
			<tr>
			<th>mensaje</th>
			<th>tipo</th>
			<th>nivel</th>
			<th>archivo</th>
			<th>linea</th>
			</tr>
			<xsl:apply-templates select="message"/>
		</table>
	</xsl:template>

	<xsl:template match="message">

		<tr>
		<td><xsl:value-of select="."/></td>
		<td><xsl:value-of select="@type"/></td>
		<td><xsl:value-of select="@level"/></td>
		<td><xsl:value-of select="@file"/></td>
		<td><xsl:value-of select="@line"/></td>
		</tr>
	</xsl:template>

</xsl:stylesheet>

<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
