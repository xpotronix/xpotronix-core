<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" 
		xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
		xmlns:saxon="http://saxon.sf.net/" 
		xmlns:xp="http://xpotronix.com/namespace/xpotronix/functions/"
		extension-element-prefixes="saxon">

<xsl:template match="/" mode="debug">

		<!-- debug collections -->

		<xsl:if test="$debug">

			<xsl:result-document method="xml" version="1.0" encoding="UTF-8" href="table_collection.xml">
				<xsl:sequence select="$table_collection"/>
			</xsl:result-document>

			<xsl:result-document method="xml" version="1.0" encoding="UTF-8" href="database_collection.xml">
				<xsl:sequence select="$database_collection"/>
			</xsl:result-document>

			<xsl:result-document method="xml" version="1.0" encoding="UTF-8" href="model_collection.xml">
				<xsl:sequence select="$model_collection"/>
			</xsl:result-document>

			<xsl:result-document method="xml" version="1.0" encoding="UTF-8" href="code_collection.xml">
				<xsl:sequence select="$code_collection"/>
			</xsl:result-document>

			<xsl:result-document method="xml" version="1.0" encoding="UTF-8" href="file_collection.xml">
				<xsl:sequence select="$file_collection"/>
			</xsl:result-document>

			<xsl:result-document method="xml" version="1.0" encoding="UTF-8" href="queries_collection.xml">
				<xsl:sequence select="$queries_collection"/>
			</xsl:result-document>

			<xsl:result-document method="xml" version="1.0" encoding="UTF-8" href="processes_collection.xml">
				<xsl:sequence select="$processes_collection"/>
			</xsl:result-document>

			<xsl:result-document method="xml" version="1.0" encoding="UTF-8" href="menu_collection.xml">
				<xsl:sequence select="$menu_collection"/>
			</xsl:result-document>

			<xsl:result-document method="xml" version="1.0" encoding="UTF-8" href="views_collection.xml">
				<xsl:sequence select="$views_collection"/>
			</xsl:result-document>

			<xsl:result-document method="xml" version="1.0" encoding="UTF-8" href="feat_collection.xml">
				<xsl:sequence select="$feat_collection"/>
			</xsl:result-document>

			<xsl:result-document method="xml" version="1.0" encoding="UTF-8" href="config_collection.xml">
				<xsl:sequence select="$config_collection"/>
			</xsl:result-document>


		</xsl:if>

		<!-- <xsl:message terminate="yes"><xsl:sequence select="$database_collection"/></xsl:message> -->
		<!-- <xsl:message terminate="yes">code: <xsl:value-of select="count($code_collection//code)"/></xsl:message> -->
		<!-- <xsl:message terminate="yes"><xsl:sequence select="$queries_collection"/></xsl:message> -->

	</xsl:template>

</xsl:stylesheet>
