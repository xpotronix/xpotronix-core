<?xml version="1.0" encoding="UTF-8"?>
<!-- 
	make:entity en xpotronix
-->
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:fn="http://www.w3.org/2005/04/xpath-functions" 
	xmlns:saxon="http://saxon.sf.net/" 
	xmlns:local="http://localhost/" 
	extension-element-prefixes="saxon" 
	exclude-result-prefixes="saxon">
	<xsl:output method="text" version="1.0" encoding="UTF-8" indent="yes"/>

	<xsl:variable name="single_quote"><xsl:text>'</xsl:text></xsl:variable>
	<xsl:variable name="double_quote"><xsl:text>"</xsl:text></xsl:variable>

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
		<xsl:variable name="class_file_name" select="concat($path_prefix,'/',$mapping_path_suffix,'/',$class_name,'.php')"/>

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

namespace App\Entity\<xsl:value-of select="$mapping_path_suffix"/>;
<xsl:if test="$table_metadata/obj/@persistent='1'">
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\<xsl:value-of select="$mapping_path_suffix"/>\<xsl:value-of select="$class_name"/>Repository;
<xsl:if test="@Trait">
use App\Entity\<xsl:value-of select="$mapping_path_suffix"/>\Common\<xsl:value-of select="$class_name"/>Trait;
</xsl:if>
</xsl:if>

/**
* <xsl:value-of select="$class_name"/>
*/

<xsl:if test="$table_metadata/obj/@persistent='1'">
#[ORM\Table(name: '<xsl:value-of select="$table_name"/>')]
<xsl:apply-templates select="$table_collection//table[@name=$table_name]/index[@name!='PRIMARY']" mode="index_decl"/>
#[ORM\Entity(repositoryClass: <xsl:value-of select="$class_name"/>Repository::class)]
#[ORM\HasLifecycleCallbacks]
</xsl:if>
class <xsl:value-of select="$class_name"/>
{
<xsl:if test="@Trait">
use <xsl:value-of select="$class_name"/>Trait;
</xsl:if>
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

<xsl:for-each select="$table_metadata/obj/attr[not(@alias_of) and not(@extra='NO_SQL')]">

<!-- options del Column/field/attr -->

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
				<xsl:attribute name="value">'<xsl:value-of select="@default_value"/>'</xsl:attribute>
				<xsl:attribute name="extra">'<xsl:value-of select="@extra"/>'</xsl:attribute>
			</xsl:element>
		</xsl:if>

		<xsl:if test="@comment">
			<xsl:element name="option">
				<xsl:attribute name="key" select="'comment'"/>
				<xsl:attribute name="value">'<xsl:value-of select="@comment"/>'</xsl:attribute>
			</xsl:element>
		</xsl:if>

		<xsl:if test="contains(@dbtype,'unsigned')">

			<xsl:element name="option">
				<xsl:attribute name="key" select="'unsigned'"/>
				<xsl:attribute name="value">true</xsl:attribute>
			</xsl:element>

		</xsl:if>

	</xsl:variable>

	<xsl:variable name="options_decl"><xsl:for-each select="$options/*:option">'<xsl:value-of select="@key"/>'=&gt;<xsl:value-of select="@value"/><xsl:if test="position()!=last()">,</xsl:if></xsl:for-each></xsl:variable>
	
	<xsl:if test="$table_metadata/obj/@persistent='1'">

	<xsl:variable name="is_primary_key" select="count($table_metadata/obj/primary_key/primary[@name=current()/@name])"/>

	<xsl:variable name="ORMColumnDef">
		#[ORM\Column(name: '<xsl:value-of select="@name"/>', type: '<xsl:value-of select="@doctrineType"/>'
	<xsl:if test="@precision">, precision: <xsl:value-of select="@precision"/></xsl:if>
	<xsl:if test="@scale">, scale: <xsl:value-of select="@scale"/></xsl:if>
	<xsl:if test="count($options/*:option)">, options:[<xsl:value-of select="$options_decl"/>]</xsl:if>
	<xsl:if test="@dbtype=('enum')">, columnDefinition: "ENUM(<xsl:value-of select="@enums"/>)"</xsl:if>
	<!-- xsl:if test="@dbtype=('enum')">, columnDefinition: 'ENUM(<xsl:value-of select="replace(@enums, $single_quote, $double_quote)"/>)'</xsl:if -->
	<xsl:if test="@doctrineType=('string','text')">, length: <xsl:value-of select="@length"/></xsl:if>
	, nullable: <xsl:choose><xsl:when test="@not_null=1">false</xsl:when><xsl:otherwise>true</xsl:otherwise></xsl:choose>)]</xsl:variable>
<xsl:text>	</xsl:text><xsl:value-of select="normalize-space($ORMColumnDef)"/>
	<xsl:if test="@primary_key='1' or $is_primary_key">
	#[ORM\Id]
		<xsl:choose>
			<xsl:when test="@auto_increment='1'">
	#[ORM\GeneratedValue(strategy: 'AUTO')]
			</xsl:when>
			<xsl:when test="@dbtype='char' and @length='32'">
	#[ORM\GeneratedValue(strategy: 'CUSTOM')]
	#[ORM\CustomIdGenerator(class:"App\Common\Generator\IdGenerator")]
			</xsl:when>
			<xsl:otherwise>
	#[ORM\GeneratedValue(strategy: 'NONE')]
			</xsl:otherwise>
		</xsl:choose>
	</xsl:if>
</xsl:if>
	private $<xsl:value-of select="@name"/>;
</xsl:for-each>

<!-- constructor (puede ser definido via Trait) -->


<!-- getters egrep: 

     10  ?array
     46  ?float
    164  ?\DateTimeInterface
    514  ?int
    733  ?string

-->



<!-- getters/setters -->
<xsl:for-each select="$table_metadata/obj/attr">

	<xsl:variable name="returnType">
		<xsl:choose>
			<xsl:when test="@doctrineType='integer'">int</xsl:when>
			<xsl:when test="@doctrineType='boolean'">bool</xsl:when>
			<xsl:when test="@doctrineType='decimal'">string</xsl:when>
			<xsl:when test="@doctrineType='json'">array</xsl:when>
			<xsl:when test="@doctrineType=('date','datetime','time')">\DateTimeInterface</xsl:when>
			<xsl:otherwise><xsl:value-of select="@doctrineType"/></xsl:otherwise>
		</xsl:choose>
	</xsl:variable>

	<xsl:variable name="nullableSign"><xsl:choose><xsl:when test="@not_null=1"></xsl:when><xsl:otherwise>?</xsl:otherwise></xsl:choose></xsl:variable>

	<xsl:variable name="camelized" select="local:snake2camel(concat('',(@name)))"/>
    public function get<xsl:value-of select="$camelized"/>(): ?<xsl:value-of select="$returnType"/> {/*{{{*/
        return $this-><xsl:value-of select="@name"/>;
    }/*}}}*/
    public function set<xsl:value-of select="$camelized"/>(<xsl:value-of select="$nullableSign"/><xsl:value-of select="$returnType"/><xsl:text> </xsl:text>$<xsl:value-of select="@name"/>): static {/*{{{*/
        $this-><xsl:value-of select="@name"/> = $<xsl:value-of select="@name"/>;
        return $this;
    }/*}}}*/
</xsl:for-each>
}

?></xsl:template>

<xsl:template match="index" mode="index_decl">
	<xsl:variable name="lengths">
		<xsl:if test="contains(.,'(')">
			<xsl:for-each select="tokenize(.,',')">
				<xsl:element name="length"><xsl:value-of select="substring-after(substring-before(.,')'),'(')"/></xsl:element>
			</xsl:for-each>
		</xsl:if>
	</xsl:variable>#[ORM\Index(name: '<xsl:value-of select="@name"/>', columns: [<xsl:apply-templates select="." mode="index_columns"/>]<xsl:if test="count($lengths/*:length)">, options:['lengths'=>[<xsl:for-each select="$lengths/*:length"><xsl:choose><xsl:when test=".!=''"><xsl:value-of select="."/></xsl:when><xsl:otherwise><xsl:text>null</xsl:text></xsl:otherwise></xsl:choose><xsl:if test="position()!=last()">,</xsl:if></xsl:for-each>]]</xsl:if>)]
</xsl:template>

<xsl:template match="index" mode="index_columns">
	<xsl:for-each select="tokenize(.,',')">'<xsl:choose><xsl:when test="contains(.,'(')"><xsl:value-of select="substring-before(.,'(')"/></xsl:when><xsl:otherwise><xsl:value-of select="."/></xsl:otherwise></xsl:choose>'<xsl:if test="position()!=last()">,</xsl:if></xsl:for-each>
</xsl:template>


<xsl:function name="local:snake2camel">

    <xsl:param name="columnName"/>
    <xsl:value-of select="
	concat(
		upper-case(substring($columnName, 1, 1)),
		substring(string-join(for $word in tokenize($columnName, '_')
			return concat(
			upper-case(substring($word, 1, 1)),
			substring($word, 2)), '')
	, 2))" />
</xsl:function>


</xsl:stylesheet>
<!--  vim600: fdm=marker sw=3 ts=8 ai:
-->
