<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fn="http://www.w3.org/2005/04/xpath-functions">
	<xsl:output method="text" version="1.0" encoding="UTF-8" indent="yes"/>
	<!-- -->
	<!-- list_sql -->
	<!-- -->
	<xsl:template match="table" mode="list_sql">
		<xsl:param name="parent"/>
		<xsl:param name="mode"/>
		<xsl:param name="entry_help"/>
		<xsl:param name="type"/>
		<xsl:variable name="table_name" select="@name"/>
		<!-- added_sql busca si hay un join agregado para enlazar a un nieto con su abuelo -->
		<!-- explicacion: en ui.xml se puede poner en un child el nombre de un entry helper para hacer una referencia de un 
		nieto a su abuelo. la variable que sigue trae, de acuerdo al mode en que esta el nombre del entry helper. -->
		<xsl:variable name="added_sql">
			<xsl:apply-templates select="." mode="get_added_sql">
				<xsl:with-param name="parent" select="$parent"/>
				<xsl:with-param name="mode" select="$mode"/>
			</xsl:apply-templates>
		</xsl:variable>
		<!-- trae el entry help que se corresponde a la tabla referenciada por added_sql -->
		<xsl:variable name="entry_help_table">
			<xsl:apply-templates select="." mode="get_entry_help">
				<xsl:with-param name="mode" select="$mode"/>
				<xsl:with-param name="entry_help_n" select="$added_sql/*/@entry_helper"/>
			</xsl:apply-templates>
		</xsl:variable>

		/* table:list_sql */

		/* entry_helper: <xsl:value-of select="$entry_help"/> */
		
		$_sql = new DBquery;

		<!-- en pop_up s, classes, si viene de un entry_help, se agrega el entry_helper_id y entry_helper_label 
			para que el objeto tenga su identificador y su pas simbolico. 
			Esto lo detecta de la primera ocurrencia que haya un entry_help 
			con un form que coincida con el nombre de la tabla. Ojo que toma el primero. 
			Para este caso, no hay forma de identificar cual es el que se necesita especificamente para la tabla, 
			solamente toma el primero que encuentre en el archivo entry_helper -->
		<xsl:if test="$type='pop_up' or $type='class' and $entry_help!=''">
			<xsl:choose>
				<!-- le quita el alias para volver a la referencia original de la tabla -->
				<xsl:when test="$entry_help/*/as!=''">
	$_sql->addQuery("<xsl:value-of select="fn:replace($entry_help/*/id,concat($entry_help/*/as,'\.'),concat($entry_help/*/from,'.'))"/> as entry_helper_id");
	$_sql->addQuery("<xsl:value-of select="fn:replace($entry_help/*/label,concat($entry_help/*/as,'\.'),concat($entry_help/*/from,'.'))"/> as entry_helper_label");
	<!-- agrego el join correspondiente al entry_help para poder completar la referencia del identificador y su par simbolico -->
	<xsl:for-each select="$entry_help/*/join">
		$_sql->addJoin("<xsl:value-of select="@table"/>","<xsl:value-of select="@alias"/>","<xsl:value-of select="fn:replace(where,concat($entry_help/*/as,'\.'),concat($entry_help/*/from,'.'))"/>","<xsl:value-of select="@type"/>");
	</xsl:for-each>
				</xsl:when>
				<xsl:otherwise>
				<!-- no hay alias, asi que se copian como estan -->
				$_sql->addQuery("<xsl:value-of select="$entry_help/*/id"/> as entry_helper_id");
				$_sql->addQuery("<xsl:value-of select="$entry_help/*/label"/> as entry_helper_label");
	<!-- agrego el join de esta relacion -->
	<xsl:for-each select="$entry_help/*/join">
		$_sql->addJoin("<xsl:value-of select="@table"/>","<xsl:value-of select="@alias"/>","<xsl:value-of select="where"/>","<xsl:value-of select="@type"/>");
	</xsl:for-each>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>

		<!-- lista los campos correspodientes al SELECT: list_field_sql lista los campos del entry help correspondiente al field -->
		<xsl:apply-templates select="field" mode="list_field_sql">
			<xsl:with-param name="mode" select="$mode"/>
		</xsl:apply-templates>
		<!-- si tiene parent y added_sql con su entry helper, debo agregar el campo id para las consultas -->
		<!-- por ejemplo, para conectar con un padre al hijo y al abuelo -->
		<xsl:if test="$parent!='' and $added_sql/*/@entry_helper!='' and $entry_help_table/*/label!=''">
			$_sql->addQuery("<xsl:value-of select="$entry_help_table/*/label"/>");
		</xsl:if>
		<!-- el FROM asi de una, tal vez faltaria completar con otros objetos -->		
			$_sql->addTable("<xsl:value-of select="@name"/>");
		<xsl:choose>
			<xsl:when test="$parent!='' and $added_sql/*/@entry_helper!=''">
				<!-- trael los joins de los added_sql -->
				<xsl:for-each select="$entry_help_table/*/join">
				$_sql->addJoin("<xsl:value-of select="@table"/>","<xsl:value-of select="@alias"/>","<xsl:value-of select="where"/>","<xsl:value-of select="@type"/>");
				</xsl:for-each>
			</xsl:when>
		</xsl:choose>
		<!-- llama al template, ahora para los join de los entry_helpers del objeto -->
		<xsl:apply-templates select="field" mode="join_sql">
			<xsl:with-param name="mode" select="$mode"/>
		</xsl:apply-templates>
	</xsl:template>
	<!-- -->
	<!-- join_sql: crea el join para el entry helper en forma automatica, en base al id y al label.
		aparte, incluye los joins que este entry_helper necesite. -->
	<!-- -->
	<xsl:template match="field" mode="join_sql">
		<xsl:param name="mode"/>
		<!-- trae el entry_help en una variable -->
		<xsl:variable name="entry_help">
			<xsl:apply-templates select="." mode="get_entry_help">
				<xsl:with-param name="mode" select="$mode"/>
			</xsl:apply-templates>
		</xsl:variable>
		<xsl:choose>
			<xsl:when test="count($entry_help/*)>0">
				<!-- hace el bind del objeto a la consulta del entry help en forma automatica -->
				$_sql->addJoin("<xsl:value-of select="$entry_help/*/from"/>","
				<xsl:choose>
					<xsl:when test="count($entry_help/*/as)>0">
						<xsl:value-of select="$entry_help/*/as"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="$entry_help/*/from"/>
					</xsl:otherwise>
				</xsl:choose>","<xsl:value-of select="../@name"/>.<xsl:value-of select="@name"/>=<xsl:value-of select="$entry_help/*/id"/>");
				<xsl:for-each select="$entry_help/*/join">
				$_sql->addJoin("<xsl:value-of select="@table"/>","<xsl:value-of select="@alias"/>","<xsl:value-of select="where"/>","<xsl:value-of select="@type"/>");
				</xsl:for-each>
			</xsl:when>
		</xsl:choose>
	</xsl:template>
	<!-- -->
	<!-- list_field_sql -->
	<!-- -->
	<xsl:template match="field" mode="list_field_sql">
		<xsl:param name="mode"/>
		<!-- trae el entry_help en una variable -->
		<xsl:variable name="entry_help">
			<xsl:apply-templates select="." mode="get_entry_help">
				<xsl:with-param name="mode" select="$mode"/>
			</xsl:apply-templates>
		</xsl:variable>
		<xsl:if test="count($entry_help/*/select)>0">
			<!-- campos para el select pedidos por el entry_help -->
		$_sql->addQuery("<xsl:value-of select="$entry_help/*/select"/>");
		</xsl:if>
		<xsl:choose>
			<xsl:when test="@alias_of!=''">
				<!-- tiene un alias, es un campo calculado -->
				$_sql->addQuery("<xsl:value-of select="@alias_of"/> as <xsl:value-of select="@name"/>");
			</xsl:when>
			<xsl:otherwise>
				<!-- arma el nombre completo del campo: tabla.campo -->
				$_sql->addQuery("<xsl:value-of select="../@name"/>.<xsl:value-of select="@name"/>");
			</xsl:otherwise>
		</xsl:choose>
		<!-- si este campo tiene un entry help, agrego un campo identico con _label para poner -->
		<!-- el campo calculado asociado al entry help -->
		<xsl:if test="count($entry_help/*)>0">
		$_sql->addQuery("<xsl:value-of select="$entry_help/*/label"/> as <xsl:value-of select="translate(@name,'.','_')"/>_label");
		</xsl:if>
	</xsl:template>
	<!-- -->
	<!-- entry_helper: do_sql genera la consulta SQL en base al entry helper -->
	<!-- -->
	<xsl:template match="entry_helper" mode="do_sql">
		<!-- SELECT del entry help id y su label -->

		/* entry_helper:do_sql */

		$_sql = new DBquery;

		$_sql->addQuery("<xsl:value-of select="id"/> as entry_helper_id");
		$_sql->addQuery("<xsl:value-of select="label"/> as entry_helper_label");
			<!-- si tiene el select, trae los campos -->
		<xsl:if test="count(select)>0">
		$_sql->addQuery("<xsl:value-of select="select"/>");
		</xsl:if>
		<!-- si tiene from -->
		<xsl:if test="count(from)>0">
		$_sql->addTable("<xsl:value-of select="from"/>"<xsl:if test="count(as)>0">, "<xsl:value-of select="as"/>"</xsl:if>);
		</xsl:if>
		<!-- si tiene join -->
		<xsl:if test="count(join)>0">
			<xsl:for-each select="join">
			$_sql->addJoin("<xsl:value-of select="@table"/>","<xsl:value-of select="@alias"/>","<xsl:value-of select="where"/>","<xsl:value-of select="@type"/>");
			</xsl:for-each>
		</xsl:if>
		<!-- where -->
		<xsl:if test="count(where)>0">
		$_sql->addWhere("<xsl:value-of select="where"/>");
		</xsl:if>
		<!-- order by -->
		<xsl:if test="count(order_by)>0">
			<xsl:for-each select="order_by">
				$_sql->addOrder("<xsl:value-of select="."/>");
			</xsl:for-each>
		</xsl:if>
		<xsl:text>;</xsl:text>
	</xsl:template>
	<!-- -->
</xsl:stylesheet>
<!--  vim600: fdm=marker sw=3 ts=8 ai:
-->

