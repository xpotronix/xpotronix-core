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

    /**
     * @var string
     */
    #[ORM\Column(name: 'ID', type: 'string', length: 32, nullable: false, options: ['fixed' => true])]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class:"App\Common\Generator\IdGenerator")]
	private $id;

<!-- <xsl:message><xsl:copy-of select="$table_metadata/obj/attr[@name='ID']"/></xsl:message> -->

<xsl:for-each select="$table_metadata/obj/attr[not(@primary_key='1')]">

	<xsl:variable name="ORMColumnDef">
	@ORM\Column(type="<xsl:value-of select="@doctrineType"/>"
	<xsl:if test="@doctrineType='string'">, length=<xsl:value-of select="@length"/></xsl:if>
	<xsl:if test="@not_null=1">, nullable=false</xsl:if>)
	</xsl:variable>

	/**
	* <xsl:value-of select="normalize-space($ORMColumnDef)"/>
    */
	private $<xsl:value-of select="@name"/>;

</xsl:for-each>

}

?></xsl:template>


</xsl:stylesheet>
<!--  vim600: fdm=marker sw=3 ts=8 ai:
-->
