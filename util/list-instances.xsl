<?xml version="1.0" encoding="utf-8"?>

		<!-- corre sobre config.xml (varios) y genera el grant -->


<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2005/xpath-functions">


	<xsl:param name="path" select="'/usr/share/xpotronix/projects'"/>
	<xsl:variable name="auth_plugin" select="'mysql_native_password'"/>

	<xsl:output method="html" version="4.0" encoding="UTF-8" indent="yes"/>

	<xsl:template match="/">
		<h1>xPay Instances</h1>
		<table>

		<thr>
		<td>Database</td>
		<td>Username</td>
		<td>Host</td>
		</thr>


		<xsl:for-each-group 
			select="collection(concat('file://',$path, '?select=config.xml;recurse=yes'))/config/db_instance" 
			group-by="database">
					<xsl:apply-templates select="." mode="instance"/>
			<!--process nodes-->
		</xsl:for-each-group>
		</table>
	</xsl:template>

	<xsl:template match="db_instance" mode="instance">
		<tr>
		<td><xsl:value-of select="database"/></td>
		<td><xsl:value-of select="user"/></td>
		<td><xsl:value-of select="host"/></td>
		</tr>
	</xsl:template>

</xsl:stylesheet>
