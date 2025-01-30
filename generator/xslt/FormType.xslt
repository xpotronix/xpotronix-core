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

	<xsl:template match="table" mode="FormType">

		<xsl:param name="type"/>
		<xsl:param name="mode"/>

		<xsl:variable name="type" select="string('class')"/>

		<xsl:variable name="path_prefix">
			<xsl:value-of select="concat($application_path,'/src/Form/')"/>
		</xsl:variable>

		<xsl:variable name="class_name">
			<xsl:value-of select="@name"/>
		</xsl:variable>

		<!-- <xsl:variable name="class_file_name" select="concat($path_prefix,$class_name,'.class.php')"/> -->
        <xsl:variable name="class_file_name" select="concat($path_prefix,'/',$mapping_path_suffix,'/',$class_name,'FormType.php')"/>

		<xsl:variable name="table_name" select="@name"/>

		<xsl:variable name="table_metadata">
			<xsl:apply-templates select="." mode="get_metadata"/>
		</xsl:variable>

		<xsl:variable name="source">
			<xsl:apply-templates select="." mode="generate_FormType">
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

	<xsl:template match="table" mode="generate_FormType">

		<xsl:param name="class_name"/>
		<xsl:param name="table_name"/>
		<xsl:param name="table_metadata"/>
		<xsl:param name="class_file_name"/>

		<xsl:variable name="license" select="$all_documents/license"/>&lt;?php
/*
* Archivo: <xsl:value-of select="$class_file_name"/>
*/

namespace App\Form\<xsl:value-of select="$mapping_path_suffix"/>;


use App\Entity\<xsl:value-of select="$mapping_path_suffix"/>\<xsl:value-of select="$class_name"/>;
use App\Repository\<xsl:value-of select="$mapping_path_suffix"/>\<xsl:value-of select="$class_name"/>Repository;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;

/**
* <xsl:value-of select="$class_name"/> FormType
*/

class <xsl:value-of select="$class_name"/>FormType extends AbstractType {


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
$builder

<xsl:for-each select="$table_metadata/obj/attr[not(@alias_of) and not(@extra='NO_SQL')]">
	-&gt;add( "<xsl:value-of select="@name"/>" )</xsl:for-each>
	;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => <xsl:value-of select="$class_name"/>::class,
        ]);
    }


}

?></xsl:template>

</xsl:stylesheet>
<!--  vim600: fdm=marker sw=3 ts=8 ai:
-->
