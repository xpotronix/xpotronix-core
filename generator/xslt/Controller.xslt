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

namespace App\Controller<xsl:value-of select="$final_mapping_path_prefix"/>;


use App\Entity<xsl:value-of select="$final_mapping_path_prefix"/>\<xsl:value-of select="$class_name"/>;
use App\Repository<xsl:value-of select="$final_mapping_path_prefix"/>\<xsl:value-of select="$class_name"/>Repository;
use App\Form<xsl:value-of select="$final_mapping_path_prefix"/>\<xsl:value-of select="$class_name"/>Type;

use App\Form\ImportType;

use XpotronixUtilsBundle\Controller\Common\MetadataBuilder;
use XpotronixUtilsBundle\Service\Common\XpotronixService;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

/**
* <xsl:value-of select="$class_name"/> Controller
*/

#[Route('/<xsl:value-of select="$table_name"/>')]
class <xsl:value-of select="$class_name"/>Controller extends AbstractController {

	use MetadataBuilder;

    function __construct( private EntityManagerInterface $em, 
        private TranslatorInterface $translator) {}

    #[Route('/', name: 'app_<xsl:value-of select="$table_name"/>_index', methods: ['GET','POST'])]
	public function index(
        Request $request,
        <xsl:value-of select="$class_name"/>Repository $repository,
        XpotronixService $service
	): Response
        {/*{{{*/

		$entity = new <xsl:value-of select="$class_name"/>();

		return $this->gridWithForm( $request, $repository, $service, $entity);

    }/*}}}*/

	#[Route(path: '/export', name: '<xsl:value-of select="$table_name"/>_export', methods: ['GET'])]
    public function exportData(Request $request, <xsl:value-of select="$class_name"/>Repository $repository, XpotronixService $service )
	{/*{{{*/
		return $this->doExportData($request, $repository, $service);
    }/*}}}*/

    #[Route(path: '/ajax/list', name: '<xsl:value-of select="$table_name"/>_ajax_list', methods: ['GET'])]
    public function ajaxList(XpotronixService $service, Request $request, PaginatorInterface $paginator, <xsl:value-of select="$class_name"/>Repository $repository)
	{/*{{{*/
		return $this->getAjaxList($request, $paginator, $repository, $service);
	}/*}}}*/

	#[Route(path: '/<xsl:apply-templates select="$table_metadata/obj/primary_key" mode="generate_route_keys"/>/edit', name: '<xsl:value-of select="$table_name"/>_edit', methods: ['GET', 'POST'])]
    public function edit( XpotronixService $service, Request $request, <xsl:value-of select="$class_name"/> $entity, <xsl:value-of select="$class_name"/>Repository $repository): Response
	{/*{{{*/
		return $this->editorAjax( $request, $entity, $repository, $service );
    }/*}}}*/

	#[Route(path: '/new', name: '<xsl:value-of select="$table_name"/>_new', methods: ['GET', 'POST'])]
	public function new( XpotronixService $service, Request $request, <xsl:value-of select="$class_name"/>Repository $repository): Response
	{/*{{{*/
		$entity = new <xsl:value-of select="$class_name"/>();
		return $this->editorAjax( $request, $entity, $repository, $service );
    }/*}}}*/

	#[Route(path: '/show/<xsl:apply-templates select="$table_metadata/obj/primary_key" mode="generate_route_keys"/>', name: '<xsl:value-of select="$table_name"/>_show', methods: ['GET', 'POST'])]
	public function show(XpotronixService $service, Request $request, <xsl:value-of select="$class_name"/> $entity, <xsl:value-of select="$class_name"/>Repository $repository): Response
	{/*{{{*/
		return $this->editor( $request, $entity, $repository, $service );
	}/*}}}*/

	#[Route(path: '/delete', name: '<xsl:value-of select="$table_name"/>_delete_many', methods: ['POST'])]
	public function deleteMany( Request $request, <xsl:value-of select="$class_name"/>Repository $repository ): Response
	{/*{{{*/
		return $this->deleterAjax($request, $repository);
	}/*}}}*/

	#[Route(path: '/<xsl:apply-templates select="$table_metadata/obj/primary_key" mode="generate_route_keys"/>/{child}', name: '<xsl:value-of select="$table_name"/>_children', methods: ['GET', 'POST'])]
	public function children( XpotronixService $service, Request $request, <xsl:value-of select="$class_name"/> $entity, string $child )
	{/*{{{*/
		return $this->getJsonChild( $request, $entity, $child, $service ); 
	}/*}}}*/

}

?></xsl:template>

<xsl:template match="primary_key" mode="generate_route_keys">
	<xsl:for-each select="primary">{<xsl:value-of select="@name"/>}<xsl:if test="position()!=last()">/</xsl:if></xsl:for-each>
</xsl:template>


</xsl:stylesheet>
<!--  vim600: fdm=marker sw=3 ts=8 ai:
-->
