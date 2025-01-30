<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:saxon="http://saxon.sf.net/" 
	xmlns:xp="http://xpotronix.com/namespace/xpotronix/functions/"
	xmlns:ext4="http://xpotronix.com/templates/ext4/"
	xmlns:ext="http://xpotronix.com/templates/ext/"
	extension-element-prefixes="saxon">

	<!-- -->
	<!-- params -->
	<!-- -->

	<xsl:param name="xpotronix_path"/>
	<xsl:param name="project_path"/>
	<xsl:param name="config_path"/>
    <!-- <xsl:param name="application_path"/> -->
	<!-- default template directory -->
	<xsl:param name="config_file" select="string(concat($project_path,'/config.xml'))"/>
	<xsl:param name="feat_file" select="string(concat($project_path,'/feat.xml'))"/>
	<xsl:param name="module"/>
	<xsl:param name="debug" select="true()"/>

	<!-- -->
	<!-- globals -->
	<!-- -->

	<!-- includes en xml aparte -->

	<xsl:variable name="includes" select="document(concat($project_path,'/includes.xml'))/includes/include"/>

	<xsl:variable name="default_template" select="document(concat($project_path,'/includes.xml'))/includes/@default-template"/>

	<xsl:variable name="documents_collection"><!--{{{-->

		<collection>

			<xsl:for-each select="collection(concat('file:///',$project_path,'?select=*.xml;stable=yes'))">
				<doc type="{*/name()}" href="{base-uri(.)}"/>
			</xsl:for-each>

			<xsl:for-each select="collection(concat($project_path,'/templates/',$default_template,'/?select=*.xml;stable=yes'))">
				<doc type="{*/name()}" href="{document-uri(.)}"/>
			</xsl:for-each>

			<xsl:for-each select="$includes">

				<xsl:choose>

					<xsl:when test="unparsed-text-available(concat($project_path,'/',@path))">

						<xsl:for-each select="collection(concat($project_path,'/',@path,'?select=*.xml;stable=yes'))">
							<doc type="{*/name()}" href="{document-uri(.)}"/>
						</xsl:for-each>

						<xsl:if test="unparsed-text-available(concat($project_path,'/',@path,'/templates/',$default_template,'/'))">

							<xsl:for-each select="collection(concat($project_path,'/',@path,'/templates/',$default_template,'/?select=*.xml;stable=yes'))">
								<doc type="{*/name()}" href="{document-uri(.)}"/>
							</xsl:for-each>

						</xsl:if>

					</xsl:when>

					<xsl:when test="unparsed-text-available(concat($xpotronix_path,'/',@path))">

						<xsl:for-each select="collection(concat($xpotronix_path,'/',@path,'?select=*.xml;stable=yes'))">
							<doc type="{*/name()}" href="{document-uri(.)}"/>
						</xsl:for-each>

						<xsl:if test="unparsed-text-available(concat($xpotronix_path,'/',@path,'/templates/',$default_template,'/'))">

							<xsl:for-each select="collection(concat($xpotronix_path,'/',@path,'/templates/',$default_template,'/?select=*.xml;stable=yes'))">
								<doc type="{*/name()}" href="{document-uri(.)}"/>
							</xsl:for-each>

						</xsl:if>

					</xsl:when>

					<xsl:otherwise>
						<xsl:message>No encuentro la coleccion <xsl:value-of select="@path"/> ni en <xsl:value-of select="$project_path"/> ni en <xsl:value-of select="$xpotronix_path"/></xsl:message>
					</xsl:otherwise>

				</xsl:choose>

			</xsl:for-each>

		</collection>

	</xsl:variable><!--}}}-->

	<xsl:variable name="all_documents"><!--{{{-->

		<xsl:for-each select="$documents_collection//doc">

			<xsl:element name="{@type}">
				<xsl:attribute name="href" select="@href"/>
				<xsl:sequence select="document(@href)/*/*"/>
			</xsl:element>


		</xsl:for-each>

	</xsl:variable><!--}}}-->

	<xsl:variable name="mapping_path_suffix" select="$config_collection/config/mapping_path_suffix"/>

	<!-- all files -->

	<xsl:variable name="datatypes" select="document('datatypes.xml')"/>

	<!-- collections -->

	<xsl:variable name="table_collection"><!--{{{-->

		<xsl:variable name="tmp">
			<xsl:sequence select="$all_documents/tables/table"/>
		</xsl:variable>

		<xsl:sequence select="$tmp/table[not(@name=preceding-sibling::table/@name)]"/>

	</xsl:variable><!--}}}-->

	<xsl:variable name="model_collection"><!--{{{-->

		<xsl:for-each-group select="$all_documents/model/table" group-by="@name">
			<xsl:copy>
				<xsl:sequence select="@*"/>
				<xsl:sequence select="*"/>
			</xsl:copy>
		</xsl:for-each-group>

	</xsl:variable><!--}}}-->

	<xsl:variable name="code_collection"><!--{{{-->

		<!-- merge de los metodos del objeto entre la configuracion y los common/code -->
		<!-- <xsl:for-each-group select="$all_documents/code/table[code]" group-by="@name"> -->
		<!-- asi los copia y los repite, al armar la clase se hace el merge de los metodos -->

		<xsl:for-each-group select="$all_documents/code/table[code]" group-by="@name">
			<xsl:copy>
				<xsl:sequence select="@name"/>
				<xsl:sequence select="$all_documents/model/table[@name=current-grouping-key()]/@*"/>
				<xsl:sequence select="*"/>
			</xsl:copy>
		</xsl:for-each-group>

		<!-- </xsl:for-each-group> -->

	</xsl:variable><!--}}}-->

	<xsl:variable name="file_collection"><!--{{{-->

		<xsl:sequence select="$all_documents//file[not(@name=preceding-sibling::file/@name)]"/>

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

	<xsl:variable name="enums_collection"><!--{{{-->

		<xsl:element name="enums">
			<xsl:sequence select="$all_documents/enums/@*"/>
			<xsl:sequence select="$all_documents/enums/*"/>
		</xsl:element>

	</xsl:variable><!--}}}-->

	<xsl:variable name="views_collection"><!--{{{-->

		<xsl:sequence select="$all_documents/views/table"/>

	</xsl:variable><!--}}}-->

	<xsl:variable name="feat_collection"><!--{{{-->

		<feat>
			<xsl:sequence select="$all_documents/feat[1]/*"/>

			<xsl:for-each select="$all_documents/feat[position()>1]/*">
				<xsl:choose>
					<xsl:when test="not(@name) and not($all_documents/feat[1]/*[name()=current()/name()])">
						<xsl:sequence select="."/>
					</xsl:when>
					<xsl:when test="@name and not($all_documents/feat/*[name()=current()/name() and @name=current()/@name])">
						<xsl:sequence select="."/>
					</xsl:when>
				</xsl:choose>
			</xsl:for-each>

		</feat>

	</xsl:variable><!--}}}-->

	<xsl:variable name="config_collection"><!--{{{-->

		<config>

			<xsl:sequence select="$all_documents/config[1]/*"/>

			<xsl:for-each select="$all_documents/config[position()>1]/*">
				<xsl:choose>
					<xsl:when test="not(@name) and not($all_documents/config[1]/*[name()=current()/name()])">
						<xsl:sequence select="."/>
					</xsl:when>
					<xsl:when test="@name and not($all_documents/config[1]/*[name()=current()/name() and @name=current()/@name])">
						<xsl:sequence select="."/>
					</xsl:when>
				</xsl:choose>
			</xsl:for-each>

		</config>

	</xsl:variable><!--}}}-->

</xsl:stylesheet>
