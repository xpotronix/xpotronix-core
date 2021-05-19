<?xml version="1.0" encoding="utf-8"?>

<!--
	@package xpotronix
	@version 2.0 - Areco 
	@copyright Copyright &copy; 2003-2011, Eduardo Spotorno
	@author Eduardo Spotorno
 
	Licensed under GPL v3
	@license http://www.gnu.org/licenses/gpl-3.0.txt
-->

<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" version="1.0" encoding="utf-8" indent="yes"/>
	<xsl:template match="/">
		<xsl:apply-templates select="tables"/>
	</xsl:template>
	<xsl:template match="tables">
		<xsl:element name="model">
			<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
			<xsl:apply-templates select="table"/>
			<xsl:call-template name="default-includes"/>

		</xsl:element>
	</xsl:template>
	<xsl:template match="table">
		<xsl:element name="table">
			<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
			<xsl:attribute name="translate"><xsl:value-of select="@name"/></xsl:attribute>
			<panel type="xpGrid"/>
			<panel type="xpForm" display="inspect"/>
			<xsl:apply-templates select="field"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="field">
		<xsl:element name="field">
			<xsl:attribute name="name"><xsl:value-of select="@name"/></xsl:attribute>
			<xsl:attribute name="translate"><xsl:value-of select="@name"/></xsl:attribute>
			<xsl:attribute name="validate"></xsl:attribute>
			<xsl:attribute name="display"></xsl:attribute>
		</xsl:element>
	</xsl:template>

	<!-- exceptions -->

	<xsl:template match="table[@name='audit' or
		@name='gacl_acl' or
		@name='gacl_acl_sections' or
		@name='gacl_aco' or
		@name='gacl_aco_map' or
		@name='gacl_aco_sections' or
		@name='gacl_aro' or
		@name='gacl_aro_groups' or
		@name='gacl_aro_groups_map' or
		@name='gacl_aro_map' or
		@name='gacl_aro_sections' or
		@name='gacl_axo' or
		@name='gacl_axo_groups' or
		@name='gacl_axo_groups_map' or
		@name='gacl_axo_map' or
		@name='gacl_axo_sections' or
		@name='gacl_groups_aro_map' or
		@name='gacl_groups_axo_map' or
		@name='gacl_phpgacl' or

		@name='help' or
		@name='home' or
		@name='tip' or
		@name='sessions' or

		@name='users' or
		@name='user_preferences']"/>

	<xsl:template name="default-includes">
	<xsl:comment>Default Includes</xsl:comment><xsl:text>
</xsl:text>

        <include path="projects/plugins/common"/>
        <include path="projects/plugins/acl"/>
        <include path="projects/plugins/audit"/>
        <include path="projects/plugins/users"/>

        <include path="projects/plugins/messages"/>
        <include path="projects/plugins/sessions"/>
        <include path="projects/plugins/file"/>
        <include path="projects/plugins/home"/><xsl:text>
</xsl:text>

	</xsl:template>

</xsl:stylesheet>
