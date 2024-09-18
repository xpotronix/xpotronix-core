<?xml version="1.0" encoding="UTF-8"?>
<!-- 
	xpotronix 0.98 - Areco
-->
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:fn="http://www.w3.org/2005/04/xpath-functions" 
	xmlns:saxon="http://saxon.sf.net/" 
	extension-element-prefixes="saxon" 
	exclude-result-prefixes="saxon">
	<xsl:output method="text" version="1.0" encoding="UTF-8" indent="yes"/>

	<!-- -->
	<!-- class -->
	<!-- -->

	<xsl:template match="table" mode="entity">

		<xsl:param name="type"/>
		<xsl:param name="mode"/>

		<xsl:variable name="type" select="string('class')"/>

		<xsl:variable name="path_prefix">
			<xsl:value-of select="concat($application_path,'/src/Entity/')"/>
		</xsl:variable>

		<xsl:variable name="class_name">
			<xsl:value-of select="@name"/>
		</xsl:variable>

		<!-- <xsl:variable name="class_file_name" select="concat($path_prefix,$class_name,'.class.php')"/> -->
		<xsl:variable name="class_file_name" select="concat($path_prefix,$class_name,'.php')"/>

		<xsl:variable name="table_name" select="@name"/>

		<xsl:variable name="table_metadata">
			<xsl:apply-templates select="." mode="get_metadata"/>
		</xsl:variable>

		<xsl:variable name="source">
			<xsl:apply-templates select="." mode="generate_entity">
				<xsl:with-param name="class_name" select="$class_name"/>
				<xsl:with-param name="table_name" select="$table_name"/>
				<xsl:with-param name="table_metadata" select="$table_metadata"/>
				<xsl:with-param name="class_file_name" select="$class_file_name"/>
			</xsl:apply-templates>
		</xsl:variable>

		<!--<xsl:message terminate="yes"><xsl:value-of select="$class_file_name"/></xsl:message> -->

		<xsl:result-document method="text" encoding="UTF-8" href="{$class_file_name}">
			<xsl:value-of select="$source"/>
		</xsl:result-document>

	</xsl:template>

	<xsl:template match="table" mode="generate_entity">

		<xsl:param name="class_name"/>
		<xsl:param name="table_name"/>
		<xsl:param name="table_metadata"/>
		<xsl:param name="class_file_name"/>

		<xsl:variable name="license" select="$all_documents/license"/>&lt;?php
/*
* Archivo: <xsl:value-of select="$class_file_name"/>
*/

namespace App\Entity\Main;

<xsl:if test="$table_metadata/obj/@persistent='1'">

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

</xsl:if>

/**
* <xsl:value-of select="$class_name"/>
*/


<xsl:if test="$table_metadata/obj/@persistent='1'">
#[ORM\Table(name: '<xsl:value-of select="$table_name"/>')]<xsl:for-each select="$table_collection//table[@name=$table_name]/index[@name!='PRIMARY']">
#[ORM\Index(name: '<xsl:value-of select="@name"/>', columns: [<xsl:apply-templates select="." mode="index_columns"/>])]</xsl:for-each>
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
</xsl:if>
class <xsl:value-of select="$class_name"/>
{

<xsl:if test="$table_metadata/obj/@persistent='1'">
    #[ORM\PreUpdate]
	public function PreUpdate() {
		// $this->setAgregado( new \DateTime() );
	}

    #[ORM\PrePersist]
	public function PrePersist() {
		// $this->setAgregado( new \DateTime() );
		// $this->setModificado( new \DateTime() );
	}
</xsl:if>

<xsl:for-each select="$table_metadata/obj/attr">

	<xsl:variable name="options">

		<xsl:if test="@dbtype=('char','longchar')">
			<xsl:element name="option">
				<xsl:attribute name="key" select="'fixed'"/>
				<xsl:attribute name="value" select="'true'"/>
			</xsl:element>
		</xsl:if>

		<!-- has_default="1" default_value="0" -->

		<xsl:if test="@has_default='1'">
			<xsl:element name="option">
				<xsl:attribute name="key" select="'default'"/>
				<xsl:attribute name="value">'<xsl:value-of select="@value"/>'</xsl:attribute>
			</xsl:element>
		</xsl:if>

	</xsl:variable>

	<xsl:variable name="options_decl"><xsl:for-each select="$options/*:option">'<xsl:value-of select="@key"/>'=&gt;<xsl:value-of select="@value"/><xsl:if test="position()!=last()">,</xsl:if></xsl:for-each></xsl:variable>
	
	<xsl:if test="$table_metadata/obj/@persistent='1'">

	<xsl:variable name="is_primary_key" select="count($table_metadata/obj/primary_key/primary[@name=current()/@name])"/>

	<xsl:variable name="ORMColumnDef">
		#[ORM\Column(name: '<xsl:value-of select="@name"/>'
		, type: '<xsl:value-of select="@doctrineType"/>'
	<xsl:if test="count($options/*:option)">, options:[<xsl:value-of select="$options_decl"/>]</xsl:if>
	<xsl:if test="@doctrineType='string'">, length: <xsl:value-of select="@length"/></xsl:if>
	<xsl:if test="@not_null=1">, nullable: false</xsl:if>)]</xsl:variable>


<xsl:text>	</xsl:text><xsl:value-of select="normalize-space($ORMColumnDef)"/>
	<xsl:if test="@primary_key='1' or $is_primary_key">
	#[ORM\Id]
	<xsl:if test="@dbtype='char' and @length='32'">
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
	#[ORM\CustomIdGenerator(class:"App\Common\Generator\IdGenerator")]
	</xsl:if>

</xsl:if>

</xsl:if>

	private $<xsl:value-of select="@name"/>;

</xsl:for-each>

}

?></xsl:template>


<xsl:template match="index" mode="index_columns">
	<xsl:for-each select="tokenize(.,',')">'<xsl:value-of select="."/>'<xsl:if test="position()!=last()">,</xsl:if></xsl:for-each>
</xsl:template>


</xsl:stylesheet>
<!--  vim600: fdm=marker sw=3 ts=8 ai:
-->
