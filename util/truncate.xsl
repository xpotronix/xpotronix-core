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
	<xsl:output method="text" version="1.0" encoding="UTF-8" indent="no"/>
	<xsl:template match="/">
		<xsl:apply-templates select="database"/>
	</xsl:template>

	<xsl:template match="database">
		<xsl:apply-templates select="table"/>
	</xsl:template>

<xsl:template match="table">truncate table `<xsl:value-of select="@name"/>`;
</xsl:template>

<xsl:template match="table[@name='config']"/>
<xsl:template match="table[@name='config_list']"/>
<xsl:template match="table[@name='contacts']"/>
<xsl:template match="table[@name='gacl_acl']"/>
<xsl:template match="table[@name='gacl_acl_sections']"/>
<xsl:template match="table[@name='gacl_acl_seq']"/>
<xsl:template match="table[@name='gacl_aco']"/>
<xsl:template match="table[@name='gacl_aco_map']"/>
<xsl:template match="table[@name='gacl_aco_sections']"/>
<xsl:template match="table[@name='gacl_aco_sections_seq']"/>
<xsl:template match="table[@name='gacl_aco_seq']"/>
<xsl:template match="table[@name='gacl_aro']"/>
<xsl:template match="table[@name='gacl_aro_groups']"/>
<xsl:template match="table[@name='gacl_aro_groups_id_seq']"/>
<xsl:template match="table[@name='gacl_aro_groups_map']"/>
<xsl:template match="table[@name='gacl_aro_map']"/>
<xsl:template match="table[@name='gacl_aro_sections']"/>
<xsl:template match="table[@name='gacl_aro_sections_seq']"/>
<xsl:template match="table[@name='gacl_aro_seq']"/>
<xsl:template match="table[@name='gacl_axo']"/>
<xsl:template match="table[@name='gacl_axo_groups']"/>
<xsl:template match="table[@name='gacl_axo_groups_id_seq']"/>
<xsl:template match="table[@name='gacl_axo_groups_map']"/>
<xsl:template match="table[@name='gacl_axo_map']"/>
<xsl:template match="table[@name='gacl_axo_sections']"/>
<xsl:template match="table[@name='gacl_axo_sections_seq']"/>
<xsl:template match="table[@name='gacl_axo_seq']"/>
<xsl:template match="table[@name='gacl_groups_aro_map']"/>
<xsl:template match="table[@name='gacl_groups_axo_map']"/>
<xsl:template match="table[@name='gacl_phpgacl']"/>
<xsl:template match="table[@name='modules']"/>
<xsl:template match="table[@name='sessions']"/>
<xsl:template match="table[@name='syskeys']"/>
<xsl:template match="table[@name='sysvals']"/>
<xsl:template match="table[@name='users']"/>
<xsl:template match="table[@name='user_access_log']"/>
<xsl:template match="table[@name='user_preferences']"/>
<xsl:template match="table[@name='user_roles']"/>
<xsl:template match="table[@name='user_task_pin']"/>
<xsl:template match="table[@name='user_tasks']"/>

</xsl:stylesheet>
<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
