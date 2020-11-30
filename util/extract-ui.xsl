<?xml version="1.0"?>
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

	<xsl:output method="xml" cdata-section-elements="config event renderer" indent="no"/>

	<xsl:template match="/database"><xsl:text>
</xsl:text>
<application><xsl:text>
		</xsl:text><xsl:apply-templates select="//table[
				panel/* or 
				layout or 
				config or
				items or
				button or
				storeCbk or
				field/listeners or 
				field/renderer or
				field/editor]"/>
</application>
</xsl:template>

	<xsl:template match="table">
		<xsl:copy>
			<xsl:copy-of select="@name | @n"/><xsl:comment>{{{</xsl:comment>
			<xsl:apply-templates select="panel[*]"/>
			<xsl:apply-templates select="layout"/>
			<xsl:apply-templates select="config"/>
			<xsl:apply-templates select="items"/>
			<xsl:apply-templates select="button"/>
			<xsl:apply-templates select="storeCbk"/>
			<xsl:apply-templates select="field[listeners or renderer or editor]"/>
			</xsl:copy><xsl:comment>}}}</xsl:comment><xsl:text>

	</xsl:text>
	</xsl:template>

	<xsl:template match="field">

		<xsl:copy>
			<xsl:copy-of select="@name | @n"/>
			<xsl:copy-of select="renderer|listeners|editor"/>
		</xsl:copy>

	</xsl:template>

    <xsl:template match="@*|node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>


</xsl:stylesheet>
