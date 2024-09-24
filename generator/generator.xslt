<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" 
		xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
		xmlns:saxon="http://saxon.sf.net/" 
		xmlns:xp="http://xpotronix.com/namespace/xpotronix/functions/"
		extension-element-prefixes="saxon">

	<!-- -->
	<!-- output -->
	<!-- -->

	<xsl:output method="text" encoding="UTF-8" indent="yes"/>
	<xsl:strip-space elements="*"/>

	<!-- -->
	<!-- includes -->
	<!-- -->

	<xsl:include href="globals.xslt"/>
	<xsl:include href="xslt/metadata.xslt"/>
	<xsl:include href="xslt/entity.xslt"/>
	<xsl:include href="xslt/repository.xslt"/>
	<xsl:include href="xslt/class.xslt"/>
	<xsl:include href="xslt/field.xslt"/>
	<xsl:include href="xslt/model.xslt"/>
	<xsl:include href="debug.xslt"/>

	<!-- -->
	<!-- Plantilla Principal -->
	<!-- -->

	<xsl:template match="/"><!--{{{-->

		<xsl:if test="$debug">
			<xsl:apply-templates select="." mode="debug"/>
		</xsl:if>

		<!-- -->
		<!-- Info del Sistema -->
		<!-- -->

		<xsl:message></xsl:message>
		<xsl:message><xpotronix/></xsl:message>
		<xsl:message>http://xpotronix.com/</xsl:message>
		<xsl:message></xsl:message>
		<xsl:message>Parametros de la Transformacion:</xsl:message>

		<xsl:message>xpotronix_path: <xsl:value-of select="$xpotronix_path"/></xsl:message>
		<xsl:message>project_path: <xsl:value-of select="$project_path"/></xsl:message>
		<xsl:message>config_path: <xsl:value-of select="$config_path"/></xsl:message>
		<xsl:message>application_path: <xsl:value-of select="$application_path"/></xsl:message>
		<xsl:message>config_file: <xsl:value-of select="$config_file"/></xsl:message>
		<xsl:message>feat_file: <xsl:value-of select="$feat_file"/></xsl:message>
		<xsl:message>default_template: <xsl:value-of select="$default_template"/></xsl:message>
		<xsl:message>module: <xsl:value-of select="$module"/></xsl:message>

		<xsl:message></xsl:message>
		<xsl:message>documentos xml leidos: <xsl:value-of select="count($all_documents/*)"/></xsl:message>
		<xsl:message></xsl:message>

		<xsl:if test="$config_path=''">
			<xsl:message>Necesita especificar el parametro 'config_path' en la transformacion.</xsl:message>
		</xsl:if>

		<xsl:if test="$project_path=''">
			<xsl:message>Necesita especificar el parametro 'project_path' en la transformacion.</xsl:message>
		</xsl:if>

		<xsl:if test="$application_path=''">
			<xsl:message>Necesita especificar el parametro 'application_path' en la transformacion.</xsl:message>
		</xsl:if>

		<xsl:if test="$config_path='' or $project_path='' or $application_path=''">
			<xsl:message terminate="yes">Transformacion interrumpida: faltan parametros</xsl:message>
		</xsl:if>

		<!-- -->
		<!-- Transformacion Principal -->
		<!-- -->

		<!-- archivos que quedan dentro del DocumentRoot de la aplicacion -->

		<xsl:call-template name="datatypes"/>
		<xsl:call-template name="ui_file"/>

		<!-- configs, feat y menu -->

		<xsl:apply-templates select="$feat_collection/feat" mode="feat"/>
		<xsl:apply-templates select="$config_collection/config" mode="config"/>
		<xsl:apply-templates select="$menu_collection/menu" mode="menu"/>


		<xsl:choose>
			<xsl:when test="$module=''">
				<xsl:message>transformando todos los modulos</xsl:message>
				<xsl:apply-templates select="$model_collection/table[@source!='']" mode="class_main"/>
				<xsl:apply-templates select="$code_collection/table[code[@type='php'] and not(@source)]" mode="class_main"/>
				<xsl:apply-templates select="$model_collection/table" mode="metadata"/>
				<xsl:apply-templates select="$model_collection/table" mode="model"/>
				<xsl:apply-templates select="$model_collection/table" mode="processes"/>
				<xsl:apply-templates select="$model_collection/table" mode="views"/>
				<xsl:apply-templates select="$code_collection/table" mode="js_code"/>

				<!-- Symfony 7.x Decl -->

				<xsl:apply-templates select="$model_collection/table" mode="entity"/>
				<xsl:apply-templates select="$model_collection/table" mode="repository"/>

			</xsl:when>

			<xsl:otherwise>
				<xsl:message>transformando solo el modulo <xsl:value-of select="$module"/></xsl:message>
				<xsl:apply-templates select="$model_collection/table[@name=$module]" mode="class_main"/>
				<xsl:apply-templates select="$model_collection/table[@name=$module]" mode="metadata"/>
				<xsl:apply-templates select="$model_collection/table[@name=$module]" mode="model"/>
				<xsl:apply-templates select="$model_collection/table[@name=$module]" mode="processes"/>
				<xsl:apply-templates select="$model_collection/table[@name=$module]" mode="views"/>
				<xsl:apply-templates select="$code_collection/table[@name=$module]" mode="js_code"/>
			</xsl:otherwise>
		</xsl:choose>

		<xsl:apply-templates select="$file_collection/file" mode="extra_code"/>

		<xsl:message>Fin del proceso de transformacion.</xsl:message>

	</xsl:template><!--}}}-->

	<xsl:template match="file" mode="extra_code"><!--{{{-->

			<xsl:variable name="file_name" select="concat($application_path,'/',@name)"/>
			<xsl:choose>
				<xsl:when test="@type='xml'">
					<xsl:result-document method="xml" encoding="UTF-8" href="{$file_name}">
						<xsl:sequence select="./*"/>
					</xsl:result-document>
				</xsl:when>
				<xsl:otherwise>
					<xsl:result-document method="text" encoding="UTF-8" href="{$file_name}">
						<xsl:value-of select="." />
					</xsl:result-document>
				</xsl:otherwise>
			</xsl:choose>

	</xsl:template><!--}}}-->

	<xsl:template name="ui_file"><!--{{{-->

		<xsl:variable name="href" select="concat($application_path,'/conf/',$default_template,'-ui.xml')"/>

		<!-- <xsl:message terminate="yes"><xsl:copy-of select="$href"/></xsl:message>
		<xsl:message terminate="yes"><xsl:copy-of select="$all_documents/*:ui|$all_documents/*:processes"/></xsl:message> -->

		<xsl:result-document method="xml" encoding="UTF-8" href="{$href}">
			<xsl:element name="{$default_template}:application" namespace="{concat('http://xpotronix.com/templates/',$default_template,'/')}">
			<xsl:sequence select="$all_documents/*:ui"/>
			<xsl:sequence select="$all_documents/*:processes"/>
			</xsl:element>
		</xsl:result-document>

	</xsl:template><!--}}}-->

	<xsl:template name="datatypes"><!--{{{-->

		<xsl:variable name="href" select="concat($application_path,'/conf/datatypes.xml')"/>
		<xsl:result-document method="xml" encoding="UTF-8" href="{$href}">
			<xsl:sequence select="$datatypes" />
		</xsl:result-document>

	</xsl:template><!--}}}-->

	<xsl:template match="table" mode="js_code"><!--{{{-->

		<xsl:variable name="table_name" select="@name"/>

			<!-- DEBUG: modes es confuso, son las secciones del codigo para agrupar -->

		<xsl:variable name="modes">
			<xsl:for-each-group select="$code_collection//table[@name=current()/@name]/code[@type='js']" group-by="@mode">
				<xsl:element name="file">
					<xsl:attribute name="name" select="concat('modules/',$table_name,'/',@mode,'.js')"/>
					<xsl:attribute name="type" select="'js'"/>
					<xsl:attribute name="mode" select="current-grouping-key()"/>
				</xsl:element>
			</xsl:for-each-group>
		</xsl:variable>

			<!-- <xsl:message terminate="yes">MODES:<xsl:copy-of select="$modes"/></xsl:message> -->

		<xsl:for-each select="$modes/file">
			<xsl:variable name="mode" select="@mode"/>

			<xsl:variable name="file_name" select="concat($application_path,'/modules/',$table_name, '/',$mode,'.js')"/>
			<xsl:message>Creando archivo <xsl:value-of select="$file_name"/></xsl:message>
			<xsl:result-document method="text" encoding="UTF-8" href="{$file_name}">
				<xsl:sequence select="$code_collection//table[@name=$table_name]/code[@type='js' and @mode=$mode]"/>
			</xsl:result-document>

		</xsl:for-each>

	</xsl:template><!--}}}-->

	<!-- procesos de archivos xml, los mas sencillos -->

	<xsl:template match="table" mode="processes"><!--{{{-->
		<xsl:variable name="table_name" select="@name"/>
		<xsl:result-document method="xml" encoding="UTF-8" 
			href="{concat($application_path,'/modules/',@name,'/',@name,'.processes.xml' )}">
			<xsl:element name="processes">
				<xsl:attribute name="name" select="@name"/>
				<xsl:sequence select="$processes_collection/table[@name=$table_name]/process"/>
				<!-- para todos los objetos -->
				<xsl:sequence select="$processes_collection/table[@name='*']/process"/>
			</xsl:element>
		</xsl:result-document>
	</xsl:template><!--}}}-->

	<xsl:template match="table" mode="views"><!--{{{-->
		<xsl:variable name="table_name" select="@name"/>
		<xsl:result-document method="xml" encoding="UTF-8" 
			href="{concat($application_path,'/modules/',@name,'/',@name,'.views.xml' )}">
			<xsl:element name="views">
				<xsl:attribute name="name" select="@name"/>
				<xsl:sequence select="$views_collection//table[@name=$table_name]/view"/>
			</xsl:element>
		</xsl:result-document>
	</xsl:template><!--}}}-->

	<xsl:template match="menu" mode="menu"><!--{{{-->
		<xsl:result-document method="xml" encoding="UTF-8" indent="yes" href="{concat($application_path,'/conf/menu.xml')}">
			<xsl:sequence select="."/>
		</xsl:result-document>
	</xsl:template><!--}}}-->

	<xsl:template match="config" mode="config"><!--{{{-->
		<xsl:variable name="application_name" select="$feat_collection//application[1]"/>

			<!-- <xsl:message terminate="yes"><xsl:value-of select="concat($config_path,'/conf/',$application_name,'/config.xml')"/></xsl:message> -->
		<xsl:variable name="output_file" select="concat($config_path,'/conf/',$application_name,'/config.xml')"/>
		<xsl:message>generando archivo de configuracion en <xsl:value-of select="$output_file"/></xsl:message>
		<xsl:result-document method="xml" encoding="UTF-8" indent="yes" href="{$output_file}">
			<xsl:sequence select="."/>
		</xsl:result-document>
	</xsl:template><!--}}}-->

	<xsl:template match="feat" mode="feat"><!--{{{-->
		<xsl:variable name="feats" select="."/>

		<xsl:result-document 	method="xml" encoding="UTF-8" indent="yes" href="{concat($application_path,'/conf/feat.xml')}">
			<feat>
			<xsl:for-each-group select="./*" group-by="name()">
				<!-- <xsl:sort select="name()"/> -->
				<xsl:variable name="cgk" select="current-grouping-key()"/>
				<xsl:element name="{$cgk}">
					<xsl:if test="//*[name()=$cgk][1]/@type">
						<xsl:attribute name="type" select="//*[name()=$cgk][1]/@type"/>
					</xsl:if>
					<xsl:value-of select="//*[name()=$cgk][last()]"/>
				</xsl:element>
	    		</xsl:for-each-group>
			</feat>
		</xsl:result-document>
	</xsl:template><!--}}}-->

	<!-- utilitarios -->

	<!-- toma la refrencia a archivos en dos directorios distintos -->

	<xsl:function name="xp:get_document"><!--{{{-->

		<xsl:param name="path"/>
		<xsl:param name="file"/>

		<xsl:choose>
			<xsl:when test="unparsed-text-available(concat($project_path,'/',$path,'/',$file))">
				<xsl:sequence select="document(concat($project_path,'/',$path,'/',$file))"/>
			</xsl:when>
			<xsl:when test="unparsed-text-available(concat($xpotronix_path,'/',$path,'/',$file))">
				<xsl:sequence select="document(concat($xpotronix_path,'/',$path,'/',$file))"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:message>No encuentro al archivo <xsl:value-of select="concat($path,'/',$file)"/> ni en <xsl:value-of select="$project_path"/> ni en <xsl:value-of select="$xpotronix_path"/></xsl:message>
			</xsl:otherwise>
		</xsl:choose>

	</xsl:function><!--}}}-->

</xsl:stylesheet>
<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
