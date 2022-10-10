<?xml version="1.0" encoding="utf-8"?>

<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" indent="yes" encoding="utf-8"/>

	<!-- path de archivo overrides -->

	<xsl:param name="override_file_path"/>

	<xsl:variable name="overrides" select="document($override_file_path)" />

	<xsl:template match="@* | node()">
		<xsl:copy>
			<xsl:apply-templates select="@* | node()"/>
		</xsl:copy>
	</xsl:template>

	<xsl:template match="config">

		<xsl:copy>

			<xsl:comment/>
			<xsl:comment>Configuracion Local</xsl:comment>
			<xsl:comment/>

			<xsl:copy-of select="$overrides/config/*"/>

			<xsl:comment/>
			<xsl:comment>Configuracion Default</xsl:comment>
			<xsl:comment/>

			<xsl:for-each select="*">

				<xsl:variable name="node_name" select="name()"/>
				<xsl:variable name="name" select="@name"/>
				<xsl:choose>
					<xsl:when test="not(@name) and not($overrides/config[1]/*[name()=$node_name])">
						<xsl:copy-of select="."/>
					</xsl:when>
					<xsl:when test="@name and not($overrides/config[1]/*[name()=$node_name and @name=$name])">
						<xsl:copy-of select="."/>
					</xsl:when>
				</xsl:choose>

			</xsl:for-each>

		</xsl:copy>

	</xsl:template>

</xsl:stylesheet>
