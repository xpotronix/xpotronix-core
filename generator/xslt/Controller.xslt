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

	<xsl:template match="table" mode="controller">

		<xsl:param name="type"/>
		<xsl:param name="mode"/>

		<xsl:variable name="type" select="string('class')"/>

		<xsl:variable name="path_prefix">
			<xsl:value-of select="concat($application_path,'/src/Controller/')"/>
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
        <xsl:variable name="class_file_name" select="concat($path_prefix,$final_mapping_path,'/',$class_name,'Controller.php')"/>

		<xsl:variable name="table_name" select="@name"/>

		<xsl:variable name="table_metadata">
			<xsl:apply-templates select="." mode="get_metadata"/>
		</xsl:variable>

		<xsl:variable name="source">
			<xsl:apply-templates select="." mode="generate_controller">
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

	<xsl:template match="table" mode="generate_controller">

		<xsl:param name="class_name"/>
		<xsl:param name="table_name"/>
		<xsl:param name="table_metadata"/>
		<xsl:param name="class_file_name"/>

		<xsl:variable name="license" select="$all_documents/license"/>&lt;?php
/*
* Archivo: <xsl:value-of select="$class_file_name"/>
*/

namespace App\Controller<xsl:value-of select="$final_mapping_path_suffix"/>;


use App\Entity<xsl:value-of select="$final_mapping_path_suffix"/>\<xsl:value-of select="$class_name"/>;
use App\Repository<xsl:value-of select="$final_mapping_path_suffix"/>\<xsl:value-of select="$class_name"/>Repository;
use App\Form<xsl:value-of select="$final_mapping_path_suffix"/>\<xsl:value-of select="$class_name"/>FormType;

use App\Controller\Common\MetadataBuilder;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
* <xsl:value-of select="$class_name"/> Controller
*/

#[Route('/<xsl:value-of select="$table_name"/>')]
class <xsl:value-of select="$class_name"/>Controller extends AbstractController {

	use MetadataBuilder;

	private $em;

	function __construct( EntityManagerInterface $em , TranslatorInterface $translator) {
		$this->em = $em;
		$this->translator = $translator;
	}

    #[Route('/', name: 'app_<xsl:value-of select="$table_name"/>_index', methods: ['GET'])]
	public function index(
        Request $request,
        <xsl:value-of select="$class_name"/>Repository $repository,
        TranslatorInterface $translator
	): Response
	{/*{{{*/

        $qb = $repository->getListQuery( $request->getLocale() );

        return $this->render('default/index.html.twig', [
            'gridConfig' => [
                $repository->getEntityNameSneak() =>
                    $this->getGridConfig($repository, $qb)
            ]
		]);

    }/*}}}*/

	#[Route(path: '/export', name: '<xsl:value-of select="$table_name"/>_export', methods: ['GET'])]
    public function exportData(Request $request, <xsl:value-of select="$class_name"/>Service $service )
	{/*{{{*/
		return $this->doExportData($request, $service);
    }/*}}}*/

	#[Route(path: '/ajax/list', name: '<xsl:value-of select="$table_name"/>_ajax_list', methods: ['GET'])]
    public function ajaxList(Request $request, PaginatorInterface $paginator, <xsl:value-of select="$class_name"/>Repository $repository)
	{/*{{{*/
		return $this->getAjaxList($request, $paginator, $repository);
	}/*}}}*/

	#[Route(path: '/new', name: '<xsl:value-of select="$table_name"/>_new', methods: ['GET', 'POST'])]
    public function new(Request $request, <xsl:value-of select="$class_name"/>Repository $repository): Response
	{/*{{{*/
		return $this->editor( $request, new <xsl:value-of select="$class_name"/>(), $repository, <xsl:value-of select="$class_name"/>FormType::class );
    }/*}}}*/

	#[Route(path: '/<xsl:apply-templates select="$table_metadata/obj/primary_key" mode="generate_route_keys"/>/edit', name: '<xsl:value-of select="$table_name"/>_edit', methods: ['GET', 'POST'])]
	#[Route(path: '/<xsl:apply-templates select="$table_metadata/obj/primary_key" mode="generate_route_keys"/>', name: '<xsl:value-of select="$table_name"/>_show', methods: ['GET', 'POST', 'DELETE'])]
    public function edit(Request $request, <xsl:value-of select="$class_name"/> $entity, <xsl:value-of select="$class_name"/>Repository $repository): Response
	{/*{{{*/
		return $this->editor( $request, $entity, $repository, <xsl:value-of select="$class_name"/>FormType::class );
    }/*}}}*/

}

?></xsl:template>

<xsl:template match="primary_key" mode="generate_route_keys">
	<xsl:for-each select="primary">{<xsl:value-of select="@name"/>}<xsl:if test="position()!=last()">/</xsl:if></xsl:for-each>
</xsl:template>


</xsl:stylesheet>
<!--  vim600: fdm=marker sw=3 ts=8 ai:
-->
