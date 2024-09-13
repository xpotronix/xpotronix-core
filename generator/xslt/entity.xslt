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

		<xsl:variable name="source">
			<xsl:apply-templates select="." mode="generate_entity">
				<xsl:with-param name="class_name" select="$class_name"/>
				<xsl:with-param name="table_name" select="$table_name"/>
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
		<xsl:param name="class_file_name"/>
		<xsl:variable name="license" select="$all_documents/license"/>&lt;?php
/*
* Archivo: <xsl:value-of select="$class_file_name"/>
*/

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
* <xsl:value-of select="$class_name"/>
*/
#[ORM\Table(name: '<xsl:value-of select="$table_name"/>')]<xsl:for-each select="$table_collection//table[@name=$table_name]/index[@name!='PRIMARY']">
#[ORM\Index(name: '<xsl:value-of select="@name"/>', columns: ['<xsl:value-of select="."/>'])]</xsl:for-each>
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class <xsl:value-of select="$class_name"/>
{

    #[ORM\PreUpdate]
	public function PreUpdate() {
		// $this->setAgregado( new \DateTime() );
	}

    #[ORM\PrePersist]
	public function PrePersist() {
		// $this->setAgregado( new \DateTime() );
		// $this->setModificado( new \DateTime() );
	}


	<xsl:apply-templates select="." mode="get_properties"/>

}

?></xsl:template>

	<xsl:template match="table" mode="get_properties"><!--{{{-->

		<xsl:variable name="table_name" select="@name"/>
		<xsl:variable name="ui_table" select="$model_collection/table[@name=$table_name]"/>
		<xsl:variable name="tb_table" select="$table_collection/table[@name=$table_name]"/>
		<xsl:variable name="db_table" select="$database_collection/table[@name=$table_name]"/>
		<xsl:variable name="cd_table" select="$code_collection/table[@name=$table_name]"/>


		<xsl:element name="obj">

			<xsl:attribute name="name" select="@name"/>
			<xsl:attribute name="database" select="../@name"/>

			<xsl:sequence select="@*"/>
			<xsl:sequence select="$tb_table/@*"/>
			<xsl:sequence select="$db_table/@*"/>
			<xsl:sequence select="$model_collection/table[@name=$table_name]/@*"/>

			<xsl:choose>
				<xsl:when test="not($tb_table)">
				<xsl:attribute name="virtual" select="1"/>
				</xsl:when>
			     	<xsl:otherwise>
					<xsl:attribute name="persistent" select="1"/>
				</xsl:otherwise>
			</xsl:choose>

			<xsl:sequence select="$ui_table/sync"/>
			<xsl:sequence select="$ui_table/dbi"/>


    /**
     * @var string
     */
    #[ORM\Column(name: 'ID', type: 'string', length: 32, nullable: false, options: ['fixed' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class:"App\Common\Generator\IdGenerator")]

	private $id;


			<!-- primary_key -->
			<xsl:apply-templates select="." mode="get_primary_key"/>

			<!-- fields -->
			<xsl:variable name="field_collection">
				<xsl:apply-templates select="." mode="field_collection"/>
			</xsl:variable>

			<xsl:apply-templates select="$field_collection//field" mode="property_definition">
				<xsl:with-param name="tb_table" select="$tb_table"/>
			</xsl:apply-templates>

			<!-- index -->
			<xsl:sequence select="$tb_table/index"/>

		</xsl:element>

	</xsl:template><!--}}}-->


	<xsl:template match="field" mode="property_definition"><!--{{{-->
		<xsl:param name="tb_table"/>

		<!-- DEBUG: aca cambia el espacio de nombres de field a attr -->

		<xsl:variable name="table_name" select="../@name"/>
		<xsl:variable name="field_name" select="@name"/>
		<xsl:variable name="tb_field" select="$table_collection//table[@name=$table_name]/field[@name=$field_name]"/>

		<xsl:variable name="type">
			<xsl:choose>
				<xsl:when test="@type">
                        		<xsl:apply-templates select="." mode="get_type"/>
				</xsl:when>
				<xsl:when test="$tb_field">
                        		<xsl:apply-templates select="$tb_field" mode="get_type"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="'xpstring'"/>
				</xsl:otherwise>
			</xsl:choose>
                </xsl:variable>

		<!-- <xsl:message>@type: <xsl:value-of select="@type"/> $type <xsl:value-of select="$type"/></xsl:message> -->

                <xsl:variable name="length">
			<xsl:choose>
				<xsl:when test="@length">
					<xsl:value-of select="@length"/>
				</xsl:when>
				<xsl:when test="$tb_field">
                        		<xsl:apply-templates select="$tb_field" mode="get_length"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="0"/>
				</xsl:otherwise>
			</xsl:choose>
                </xsl:variable>

		<xsl:variable name="eh_name" select="@eh"/>

		<!-- toma el primer query de la transformacion, el resto son ignorados -->
		<xsl:variable name="query" select="$queries_collection//query[@name=$eh_name][1]"/>

		<xsl:element name="attr">
			<xsl:sequence select="@*"/>

			<xsl:attribute name="table" select="$table_name"/>
			<xsl:attribute name="name" select="$field_name"/>
			<xsl:attribute name="type" select="$type"/>
			<xsl:if test="$tb_field/@type">
				<xsl:attribute name="dbtype" select="$tb_field/@type"/>
			</xsl:if>

			<xsl:attribute name="length" select="$length"/>

			<xsl:if test="$eh_name!=''">
				<xsl:attribute name="entry_help"><xsl:value-of select="$eh_name"/></xsl:attribute>
				<!-- DEBUG: fix para que tome el nombre relativo de la tabla, no el absoluto cuando tiene el nombre de la base de datos delante -->

				<xsl:choose>
					<xsl:when test="contains($query/from,'.')">
						<xsl:attribute name="entry_help_table"><xsl:value-of select="substring-after($query/from,'.')"/></xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
						<xsl:attribute name="entry_help_table"><xsl:value-of select="$query/from"/></xsl:attribute>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:if>
			<xsl:if test="not($tb_field)">
				<xsl:if test="$tb_table">
					<xsl:message>atributo virtual: <xsl:value-of select="concat($table_name,'/',$field_name)"/></xsl:message>
				</xsl:if>
				<xsl:attribute name="virtual" select="1"/>
			</xsl:if>
			<xsl:sequence select="./*"/>
		</xsl:element>

	</xsl:template><!--}}}--> 


</xsl:stylesheet>
<!--  vim600: fdm=marker sw=3 ts=8 ai:
-->
