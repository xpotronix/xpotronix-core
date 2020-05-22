<?xml version="1.0" encoding="utf-8"?>

		<!-- corre sobre config.xml (varios) y genera el grant -->


<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2005/xpath-functions">


	<xsl:param name="path" select="'/usr/share/xpotronix/projects'"/>

	<xsl:output method="text" encoding="UTF-8" indent="yes"/>

	<xsl:template match="/">

		<xsl:for-each-group select="collection(concat('file://',$path, '?select=config.xml;recurse=yes'))/config/db_instance[user!='root' and implementation='mysqli' or implementation='mysql']" group-by="database">
				<xsl:apply-templates select="." mode="mysql-grant"/>
			<!--process nodes-->
			</xsl:for-each-group>

	</xsl:template>

	<xsl:template match="db_instance" mode="mysql-grant">
CREATE USER '<xsl:value-of select="user"/>'@'%' IDENTIFIED BY '<xsl:value-of select="password"/>';
	GRANT ALL PRIVILEGES ON `<xsl:value-of select="database"/>`.* TO '<xsl:value-of select="user"/>'@'%' WITH GRANT OPTION;
	<!--  GRANT ALL PRIVILEGES ON *.`<xsl:value-of select="database"/>` to `<xsl:value-of select="user"/>`@`%` identified by '<xsl:value-of select="password"/>'; -->
	</xsl:template>

</xsl:stylesheet>
