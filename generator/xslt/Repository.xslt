<?xml version="1.0" encoding="UTF-8"?>
<!-- 
	xpotronix 0.98 - Areco
-->
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:fn="http://www.w3.org/2005/04/xpath-functions" 
	xmlns:saxon="http://saxon.sf.net/" 
	xmlns:local="http://localhost/" 
	extension-element-prefixes="saxon" 
	exclude-result-prefixes="saxon">
	<xsl:output method="text" version="1.0" encoding="UTF-8" indent="yes"/>

	<!-- -->
	<!-- class -->
	<!-- -->

	<xsl:template match="table" mode="repository">

		<xsl:param name="type"/>
		<xsl:param name="mode"/>

		<xsl:variable name="type" select="string('class')"/>

		<xsl:variable name="path_prefix">
			<xsl:value-of select="concat($application_path,'/src/Repository/')"/>
		</xsl:variable>

		<xsl:variable name="class_name">
			<xsl:choose>
				<xsl:when test="$camelize_class">
					<xsl:value-of select="local:snake2camel(@name)"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="@name"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>

		<!-- <xsl:variable name="class_file_name" select="concat($path_prefix,$class_name,'.class.php')"/> -->
        <xsl:variable name="class_file_name" select="concat($path_prefix,$final_mapping_path,'/',$class_name,'Repository.php')"/>

		<xsl:variable name="table_name" select="@name"/>

		<xsl:variable name="table_metadata">
			<xsl:apply-templates select="." mode="get_metadata"/>
		</xsl:variable>

		<xsl:variable name="source">
			<xsl:apply-templates select="." mode="generate_repository">
				<xsl:with-param name="class_name" select="$class_name"/>
				<xsl:with-param name="table_name" select="$table_name"/>
				<xsl:with-param name="table_metadata" select="$table_metadata"/>
				<xsl:with-param name="class_file_name" select="$class_file_name"/>
			</xsl:apply-templates>
		</xsl:variable>

		<!--<xsl:message terminate="yes"><xsl:value-of select="$class_file_name"/></xsl:message> -->

		<xsl:result-document method="text" encoding="UTF-8" href="file:///{$class_file_name}">
			<xsl:value-of select="$source"/>
		</xsl:result-document>

	</xsl:template>

	<xsl:template match="table" mode="generate_repository">

		<xsl:param name="class_name"/>
		<xsl:param name="table_name"/>
		<xsl:param name="table_metadata"/>
		<xsl:param name="class_file_name"/>

		<xsl:variable name="license" select="$all_documents/license"/>&lt;?php
/*
* Archivo: <xsl:value-of select="$class_file_name"/>
*/

namespace App\Repository<xsl:value-of select="$final_mapping_path_suffix"/>;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;

use XpotronixUtilsBundle\Repository\Common\FilterBuilder;
use App\Entity<xsl:value-of select="$final_mapping_path_suffix"/>\<xsl:value-of select="$class_name"/>;

/**
* <xsl:value-of select="$class_name"/> Repository
*/

class <xsl:value-of select="$class_name"/>Repository extends ServiceEntityRepository {

	use FilterBuilder;

<xsl:if test="@RepositoryTrait">
use App\Repository<xsl:value-of select="$final_mapping_path_suffix"/>\Common\<xsl:value-of select="$class_name"/>Trait;
</xsl:if>


	private $translator;
	private $locale;

    public function __construct(ManagerRegistry $registry, TranslatorInterface $translator)
    {/*{{{*/
        parent::__construct($registry, <xsl:value-of select="$class_name"/>::class);
		$this->translator = $translator;
		$this->locale = $translator->getLocale();
    }/*}}}*/

	public function getListQuery() 
	{/*{{{*/

		$qb = $this->createQueryBuilder('<xsl:value-of select="$class_name"/>')
		// $qb->select('<xsl:value-of select="$class_name"/>');

		<xsl:choose>
			<xsl:when test="count($table_metadata/obj/attr[not(@alias_of) and not(@extra='NO_SQL')])">
			<xsl:for-each select="$table_metadata/obj/attr[not(@alias_of) and not(@extra='NO_SQL')]">
				-&gt;addSelect( "<xsl:value-of select="$class_name"/>.<xsl:value-of select="@name"/> AS <xsl:value-of select="@name"/>" )<xsl:if test="position()=last()">;</xsl:if></xsl:for-each>
			</xsl:when>
			<xsl:otherwise>
				-&gt;addSelect( "<xsl:value-of select="$class_name"/>" );
			</xsl:otherwise>
		</xsl:choose>

		return $qb;

	}/*}}}*/
}

?></xsl:template>

</xsl:stylesheet>
<!--  vim600: fdm=marker sw=3 ts=8 ai:
-->
