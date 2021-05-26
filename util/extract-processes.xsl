<?xml version="1.0"?>

<!--
extract-process: extrae los elemnetos del UI del archivo process.xml
usage: xputil extract-process model.xml > templates/ext/process.xml
-->

<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">


	<xsl:output method="xml" cdata-section-elements="script dialog" indent="no"/>

	<xsl:template match="/database"><xsl:text>
</xsl:text>
<application><xsl:text>
		</xsl:text><xsl:apply-templates select="//table[
		process/script or process/dialog]"/>
</application>
</xsl:template>

	<xsl:template match="table">
		<xsl:copy>
			<xsl:copy-of select="@name | @n"/><xsl:comment>{{{</xsl:comment>
			<xsl:apply-templates select="process[script or dialog]"/>
			</xsl:copy><xsl:comment>}}}</xsl:comment><xsl:text>

	</xsl:text>
</xsl:template>


	<xsl:template match="process">

		<xsl:copy>
			<xsl:copy-of select="@name | @n"/>
			<xsl:apply-templates select="script|dialog"/>
		</xsl:copy>

	</xsl:template>

    <xsl:template match="@*|node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>


</xsl:stylesheet>
