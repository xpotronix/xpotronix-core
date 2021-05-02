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

	<xsl:param name="xpotronix_path"/>
	<xsl:param name="project_path"/>
	<xsl:param name="config_path"/>
	<xsl:param name="application_path"/>
	<!-- default template directory -->
	<xsl:param name="config_file" select="string(concat($project_path,'/config.xml'))"/>
	<xsl:param name="feat_file" select="string(concat($project_path,'/feat.xml'))"/>
	<xsl:param name="module"/>
	<xsl:param name="debug" select="false()"/>



	

	<!-- -->
	<!-- includes -->
	<!-- -->

	<xsl:include href="xslt/metadata.xslt"/>
	<xsl:include href="xslt/class.xslt"/>
	<xsl:include href="xslt/field.xslt"/>
	<xsl:include href="xslt/model.xslt"/>

	<!-- -->
	<!-- globals -->
	<!-- -->


	<!-- all files -->

	<xsl:variable name="includes" select="document(concat($project_path,'/model.xml'))/model/include"/>

	<xsl:variable name="all_documents">
		<xsl:sequence select="collection(concat($project_path,'?select=*.xml'))"/>
		<xsl:for-each select="$includes">

			<xsl:choose>
				<xsl:when test="unparsed-text-available(concat($project_path,'/',@path))">
					<xsl:sequence select="collection(concat($project_path,'/',@path,'?select=*.xml'))"/>
				</xsl:when>
				<xsl:when test="unparsed-text-available(concat($xpotronix_path,'/',@path))">
					<xsl:sequence select="collection(concat($xpotronix_path,'/',@path,'?select=*.xml'))"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:message>No encuentro la coleccion <xsl:value-of select="@path"/> ni en <xsl:value-of select="$project_path"/> ni en <xsl:value-of select="$xpotronix_path"/></xsl:message>
				</xsl:otherwise>
			</xsl:choose>

		</xsl:for-each>
	</xsl:variable>

	<!-- all files -->

	<xsl:variable name="datatypes" select="document('datatypes.xml')"/>

	<!-- includes list -->


	<!-- collections -->

	<xsl:variable name="table_collection"><!--{{{-->

		<xsl:variable name="tmp">
			<xsl:sequence select="$all_documents/tables/table"/>
		</xsl:variable>

		<xsl:sequence select="$tmp/table[not(@name=preceding-sibling::table/@name)]"/>

	</xsl:variable><!--}}}-->

	<xsl:variable name="model_collection"><!--{{{-->

		<xsl:variable name="tmp">
			<xsl:sequence select="$all_documents/model/table"/>
		</xsl:variable>

		<xsl:sequence select="$tmp/table[not(@name=preceding-sibling::table/@name)]"/>

	</xsl:variable><!--}}}-->

	<xsl:variable name="code_collection"><!--{{{-->

		<!-- merge de los metodos del objeto entre la configuracion y los common/code -->
		<xsl:for-each select="$all_documents/model/table">
			<xsl:variable name="name" select="@name"/>
			<xsl:copy>
				<xsl:sequence select="@name"/>
				<xsl:sequence select="$all_documents/code/table[@name=$name]/code"/>
			</xsl:copy>
		</xsl:for-each>
	</xsl:variable><!--}}}-->

	<xsl:variable name="file_collection"><!--{{{-->

		<xsl:variable name="tmp">
			<xsl:sequence select="$all_documents//file[not(@name=preceding-sibling::file/@name)]"/>
		</xsl:variable>


	</xsl:variable><!--}}}-->

	<xsl:variable name="database_collection"><!--{{{-->

		<xsl:variable name="tmp">
			<xsl:sequence select="$all_documents/database/table"/>
		</xsl:variable>

		<xsl:sequence select="$tmp/table[not(@name=preceding-sibling::table/@name)]"/>

	</xsl:variable><!--}}}-->

	<xsl:variable name="queries_collection"><!--{{{-->

		<xsl:variable name="tmp">
			<xsl:sequence select="$all_documents/queries/query"/>
		</xsl:variable>

		<queries>
			<xsl:sequence select="$tmp/query[not(@name=preceding-sibling::query/@name)]"/>
		</queries>

	</xsl:variable><!--}}}-->

	<xsl:variable name="processes_collection"><!--{{{-->

		<xsl:variable name="tmp">
			<xsl:sequence select="$all_documents/processes/table"/>
		</xsl:variable>

		<xsl:sequence select="$tmp/table[not(@name=preceding-sibling::table/@name)]"/>

	</xsl:variable><!--}}}-->

	<xsl:variable name="menu_collection"><!--{{{-->

		<xsl:element name="menu">
			<xsl:sequence select="$all_documents/menu/@*"/>
			<xsl:sequence select="$all_documents/menu/*"/>
		</xsl:element>

	</xsl:variable><!--}}}-->

	<xsl:variable name="views_collection"><!--{{{-->

		<xsl:sequence select="$all_documents/views/table"/>

	</xsl:variable><!--}}}-->

	<xsl:variable name="feat_collection"><!--{{{-->
		<xsl:element name="feat">
			<xsl:sequence select="$all_documents/feat/*"/>
		</xsl:element>
	</xsl:variable><!--}}}-->

	<xsl:variable name="config_collection"><!--{{{-->
		<xsl:element name="config">
			<xsl:sequence select="$all_documents/config/*"/>
		</xsl:element>
	</xsl:variable><!--}}}-->

	<!-- -->
	<!-- Plantilla Principal -->
	<!-- -->

	<xsl:template match="/"><!--{{{-->

		<xsl:if test="$debug">

			<xsl:include href="debug.xslt"/>
			<xsl:apply-templates select="." mode="debug"/>

		</xsl:if>

		<!-- <xsl:message terminate="yes"><xsl:sequence select="$database_collection"/></xsl:message> -->
		<!-- <xsl:message terminate="yes">code: <xsl:value-of select="count($code_collection//code)"/></xsl:message> -->
		<!-- <xsl:message terminate="yes"><xsl:sequence select="$queries_collection"/></xsl:message> -->

		<!-- -->
		<!-- Info del Sistema -->
		<!-- -->

		<xsl:message></xsl:message>
		<xsl:message><xpotronix/></xsl:message>
		<xsl:message>http://xpotronix.com/</xsl:message>
		<xsl:message></xsl:message>
		<xsl:message>Parametros de la Transformacion:</xsl:message>

		<xsl:message>xpotronix_path: &#x9;<xsl:value-of select="$xpotronix_path"/></xsl:message>
		<xsl:message>project_path: &#x9;&#x9;<xsl:value-of select="$project_path"/></xsl:message>
		<xsl:message>config_path: &#x9;&#x9;<xsl:value-of select="$config_path"/></xsl:message>
		<xsl:message>application_path: &#x9;<xsl:value-of select="$application_path"/></xsl:message>
		<xsl:message>config_file: &#x9;&#x9;<xsl:value-of select="$config_file"/></xsl:message>
		<xsl:message>feat_file: &#x9;&#x9;<xsl:value-of select="$feat_file"/></xsl:message>
		<xsl:message>module: &#x9;&#x9;<xsl:value-of select="$module"/></xsl:message>

		<xsl:message></xsl:message>

		<xsl:message>documentos xml leidos: <xsl:value-of select="count($all_documents)"/></xsl:message>

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

		<xsl:call-template name="datatypes"/>

		<xsl:apply-templates select="$feat_collection/feat" mode="feat"/>
		<xsl:apply-templates select="$config_collection/config" mode="config"/>

		<xsl:apply-templates select="$menu_collection/menu" mode="menu"/>


		<xsl:choose>
			<xsl:when test="$module=''">
				<xsl:message>transformando todos los modulos</xsl:message>
				<xsl:apply-templates select="$model_collection/table" mode="class_main"/>
				<xsl:apply-templates select="$model_collection/table" mode="metadata"/>
				<xsl:apply-templates select="$model_collection/table" mode="model"/>
				<xsl:apply-templates select="$model_collection/table" mode="processes"/>
				<xsl:apply-templates select="$model_collection/table" mode="views"/>
				<xsl:apply-templates select="$code_collection/table" mode="js_code"/>
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

	<xsl:template name="datatypes"><!--{{{-->
		<xsl:variable name="href" select="concat($application_path,'/conf/datatypes.xml')"/>
		<xsl:result-document method="xml" encoding="UTF-8" href="{$href}">
			<xsl:sequence select="$datatypes" />
		</xsl:result-document>
	</xsl:template><!--}}}-->

	<xsl:template match="table" mode="js_code"><!--{{{-->
		<xsl:variable name="table_name" select="@name"/>
			<xsl:variable name="modes">
				<xsl:for-each-group select="$code_collection//table[@name=$table_name]/code[@type='js']" group-by="@mode">
					<xsl:element name="file">
						<xsl:attribute name="name" select="concat('modules/',$table_name,'/',@mode,'.js')"/>
						<xsl:attribute name="type" select="'js'"/>
						<xsl:attribute name="mode" select="current-grouping-key()"/>
					</xsl:element>
				</xsl:for-each-group>
			</xsl:variable>
	
		<xsl:for-each select="$modes/file">
			<xsl:variable name="mode" select="@mode"/>

			<xsl:variable name="file_name" select="concat($application_path,'/modules/',$table_name, '/',$mode,'.js')"/>
			<xsl:message>Creando archivo <xsl:value-of select="$file_name"/></xsl:message>
			<xsl:result-document method="text" encoding="UTF-8" href="{$file_name}">
				<xsl:sequence select="$code_collection//table[@name=$table_name]/code[@type='js' and @mode=$mode]"/>
			</xsl:result-document>

		</xsl:for-each>

		<xsl:result-document method="xml" encoding="UTF-8" href="{concat($application_path,'/modules/',$table_name, '/js.xml' )}">
			<xsl:element name="obj">
				<xsl:attribute name="name" select="$table_name"/>
				<xsl:sequence select="$modes"/>
			</xsl:element>
		</xsl:result-document>

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
