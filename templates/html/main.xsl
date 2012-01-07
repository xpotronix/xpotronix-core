<?xml version="1.0" encoding="UTF-8"?>

<!--
	@package xpotronix
	@version 2.0 - Areco 
	@copyright Copyright &copy; 2003-2011, Eduardo Spotorno
	@author Eduardo Spotorno
 
	Licensed under GPL v3
	@license http://www.gnu.org/licenses/gpl-3.0.txt
-->

<!-- main.xsl

plantilla de interfaz usuario
simil xpotronix miramar
eduardo spotorno, julio 2007

-->
<!DOCTYPE stylesheet [
<!ENTITY raquo  "&#187;" >
]>
<xsl:stylesheet version="2.0" 
	xmlns="http://www.w3.org/1999/xhtml"
	xmlns:f="http://www.w3.org/2002/xforms"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:xpotronix="http://xpotronix.com/namespace/xpotronix/" 
	xmlns:fn="http://www.w3.org/2005/04/xpath-functions">

	<xsl:preserve-space elements="text"/>
	<xsl:output method="html" version="4.0" encoding="UTF-8" indent="yes"/>
	<xsl:variable name="warning_message"><xsl:text></xsl:text></xsl:variable>
	<xsl:variable name="doctype_decl"><![CDATA[<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">]]>
	</xsl:variable>

	<xsl:template match="/"><!--{{{--> 
<xsl:value-of select="$doctype_decl" disable-output-escaping="yes"/>
    	<!-- <html xmlns="http://www.w3.org/1999/xhtml" xmlns:f="http://www.w3.org/2002/xforms" lang="en" xml:lang="en"> -->
	<html>
	<xsl:call-template name="head"/>
	<xsl:call-template name="body"/>
	</html>
	</xsl:template><!--}}}-->

	<xsl:template name="head"><!--{{{-->
<head>
<xsl:element name="meta">
	<xsl:attribute name="name">Description</xsl:attribute>
	<xsl:attribute name="content">Xpotronix Application</xsl:attribute>
</xsl:element>
<xsl:element name="meta">
	<xsl:attribute name="name">Version</xsl:attribute>
	<xsl:attribute name="content"><xsl:value-of select="//feat/version"/></xsl:attribute>
</xsl:element>
<xsl:element name="meta">
	<xsl:attribute name="http-equiv">Content-Type</xsl:attribute>
	<xsl:attribute name="content">text/html;charset=<xsl:value-of select="//feat/charset"/></xsl:attribute>
</xsl:element>
<xsl:element name="meta">
	<xsl:attribute name="name">Keywords</xsl:attribute>
	<xsl:attribute name="content">Xpotronix Rocks !!!</xsl:attribute>
</xsl:element>

<xsl:element name="title">
<xsl:value-of select="//feat/application"/> :: <xsl:value-of select="//feat/version"/><xsl:if test="//feat/title_message">| <xsl:value-of select="//feat/title_message"/>
</xsl:if>
</xsl:element>

<xsl:element name="link">
	<xsl:attribute name="rel">shortcut icon</xsl:attribute>
	<xsl:attribute name="href">./style/<xsl:value-of select="//feat/uistyle"/>/images/favicon.ico</xsl:attribute>
	<xsl:attribute name="type">image/ico</xsl:attribute>
</xsl:element>

<xsl:call-template name="css"/>
<xsl:call-template name="js"/>
<xsl:call-template name="change_password_js_function"/>

<f:model>
        <f:submission action="http://example.com/search"
                      method="get" id="s"/>
</f:model>


</head>

	</xsl:template><!--}}}-->

	<xsl:template match="xpotronix:model"><!--{{{-->
		<xsl:apply-templates select="obj" mode="model"/>
	</xsl:template><!--}}}-->

	<xsl:template match="obj" mode="model"><!--{{{-->
		<ul>
		<li><h2><xsl:value-of select="@name"/></h2><xsl:apply-templates mode="model"/></li>
			<xsl:apply-templates select="obj"/>
		</ul>
	</xsl:template><!--}}}-->

	<xsl:template match="class"><!--{{{-->
		<xsl:variable name="obj_name" select="@name"/>
		<xsl:apply-templates select="." mode="title"/>
		<xsl:apply-templates select="." mode="search_form"/>
		<xsl:apply-templates select="." mode="grid"/>
	   </xsl:template><!--}}}-->

	<xsl:template match="class" mode="title"><!--{{{-->

	<table width="20%" border="0" cellpadding="1" cellspacing="1">
		<tr>
			<td align="left" width="10%" nowrap="nowrap">
				<h1><xsl:value-of select="@name"/></h1>
			</td>
			<td align="left" width="5%">
				<xsl:element name="a">
					<xsl:attribute name="href">javascript:togglevisible('buscar_<xsl:value-of select="@name"/>')</xsl:attribute>
					<img src="images/search.png" alt="buscar" border="0" />
				</xsl:element>
			</td>
			<td align="left" width="5%">
				<xsl:element name="a">
					<xsl:attribute name="href">javascript:togglevisible('buscar_<xsl:value-of select="@name"/>')</xsl:attribute>
					<img src="images/search.png" alt="buscar" border="0" />
				</xsl:element>
			</td>
		</tr>
	</table>

	</xsl:template><!--}}}-->

	<xsl:template match="class" mode="search_form"><!--{{{-->
		<xsl:element name="div">
			<xsl:variable name="obj_name" select="@name"/>
			<xsl:attribute name="id">buscar_<xsl:value-of select="@name"/></xsl:attribute>
			<xsl:attribute name="style">visibility:hidden;position:absolute;</xsl:attribute>
			<h2>Buscar</h2>
			<form name="searchFrm_registrant" id="searchFrm_registrant" action="?m=registrant&amp;a=list_registrant" method="get">
				<fieldset>
				<br/>
				<xsl:apply-templates select="//xpotronix:metadata/obj[@name=$obj_name]/attr" mode="input"/>
				<label for="rows_page" title="rows_page" class="xpotronix">Resultados Por Pagina</label>
				<select name="rows_page" id="rows_page" class="xpotronix">
					<option value="10">10</option>
					<option value="50" selected="selected">50</option>
					<option value="100">100</option>
					<option value="200">200</option>
				</select>
				<br/>
				<input type="submit" class="button" value="Ejecutar Busqueda"/>
				<input type="reset" class="button" value="Borrar Busqueda"/>
				</fieldset>
			</form>
		</xsl:element>
	</xsl:template><!-- }}}-->

	<xsl:template match="attr" mode="input"><!--{{{-->
		
		<xsl:if test="@display!='hide' or not(@display)">
		<xsl:element name="div">
			<xsl:attribute name="id">div_<xsl:value-of select="@name"/></xsl:attribute>
			<xsl:element name="label">
				<xsl:attribute name="for"><xsl:value-of select="../@name"/>_<xsl:value-of select="@name"/></xsl:attribute>
				<xsl:attribute name="title"><xsl:value-of select="@name"/></xsl:attribute>
				<xsl:attribute name="class">juscaba</xsl:attribute>
				<xsl:choose><xsl:when test="@translate!=''"><xsl:value-of select="@translate"/></xsl:when><xsl:otherwise><xsl:value-of select="@name"/></xsl:otherwise></xsl:choose>
			</xsl:element>
			<xsl:element name="input">
				<xsl:attribute name="type">text</xsl:attribute>
				<xsl:attribute name="id"><xsl:value-of select="../@name"/>_<xsl:value-of select="@name"/></xsl:attribute>
				<xsl:attribute name="name">s[<xsl:value-of select="../@name"/>.<xsl:value-of select="@name"/>]</xsl:attribute>
				<xsl:attribute name="class">juscaba</xsl:attribute>
				<xsl:attribute name="value"></xsl:attribute>
				<xsl:attribute name="maxlength"><xsl:value-of select="@length"/></xsl:attribute>
				<xsl:attribute name="size"><xsl:value-of select="@length"/></xsl:attribute>
			</xsl:element>
		</xsl:element>
			<xsl:if test="@entry_help!=''">
				<xsl:element name="div">
					<xsl:attribute name="class">autocomplete</xsl:attribute>
					<xsl:attribute name="id"><xsl:value-of select="../@name"/>_<xsl:value-of select="@name"/>_auto_complete</xsl:attribute>
				</xsl:element>
				<script type="text/javascript">
					new Ajax.Autocompleter(	'<xsl:value-of select="../@name"/>_<xsl:value-of select="@name"/>', 
								'<xsl:value-of select="../@name"/>_<xsl:value-of select="@name"/>_auto_complete', 
								'?m=<xsl:value-of select="@entry_help_table"/>
					<xsl:value-of disable-output-escaping="yes" select="'&amp;'"/>v=ulli', 
								{ tokens: ',' } )
				</script>
			</xsl:if>
			<br/>
		</xsl:if>
	</xsl:template><!--}}}-->
	
	<xsl:template match="class" mode="grid"><!--{{{-->

		<form id="selectFrm_registrant" name="selectFrm_registrant" action="/registro-software/index.php?m=registrant&amp;a=list_registrant" method="post">
			<table class="tbl" border="0" cellpadding="2" cellspacing="1" width="100%">
			<xsl:apply-templates select="." mode="table_head"/>
			<xsl:apply-templates select="." mode="table_body"/>
			<xsl:apply-templates select="." mode="table_foot"/>
			</table>
		</form>
	</xsl:template><!--}}}-->

	<xsl:template match="class" mode="table_head"><!--{{{-->
		<xsl:variable name="obj_name" select="@name"/>
		<tr>
			<xsl:apply-templates 
				select="//xpotronix:metadata/obj[@name=$obj_name]/attr" 
				mode="table_head"/>
		</tr>
	</xsl:template><!--}}}-->

	<xsl:template match="attr" mode="table_head"><!--{{{-->
		<xsl:if test="@display!='hide' or not(@display)">
			<th class="hdr">
				<xsl:element name="a">
					<xsl:attribute name="href">#</xsl:attribute>
					<xsl:value-of select="@translate"/>
				</xsl:element>
			</th>
		</xsl:if>
	</xsl:template><!--}}}-->

	<xsl:template match="class" mode="table_body"><!--{{{-->
		<xsl:variable name="obj_name" select="@name"/>
		<xsl:for-each select="obj">
			<tr class="bgRow">
				<xsl:apply-templates 
					select="//xpotronix:metadata/obj[@name=$obj_name]/attr" 
					mode="table_body">
					<xsl:with-param name="obj" select="."/>
				</xsl:apply-templates>
			</tr>
		</xsl:for-each>
	</xsl:template><!--}}}-->

	<xsl:template match="attr" mode="table_body"><!--{{{-->
		<xsl:param name="obj"/>
		<xsl:variable name="attr_name" select="@name"/>
		<xsl:variable name="value" select="$obj/attr[@name=$attr_name]/text()"/>
		<xsl:if test="@display!='hide' or not(@display)">
		<td>
			<xsl:element name="a">
				<xsl:attribute name="href">#</xsl:attribute>
				<xsl:apply-templates select="$obj/attr[@name=$attr_name]" mode="display_value"/>
			</xsl:element>
			<xsl:if test="@entry_help!='' and $value!=''">
			<xsl:element name="a">
				<xsl:attribute name="href"><xsl:apply-templates select="." mode="related_ref"><xsl:with-param name="value" select="$value"/></xsl:apply-templates></xsl:attribute>
			<xsl:text>[&raquo;]</xsl:text>
			</xsl:element>
			</xsl:if>
		</td>
		</xsl:if>
	</xsl:template><!--}}}-->

	<xsl:template match="attr" mode="related_ref">
		<xsl:param name="value"/>
		<xsl:variable name="entry_help" select="@entry_help"/>
		<xsl:variable name="query" select="//query[@name=$entry_help]"/>
		<xsl:value-of select="concat('?m=',@entry_help_table,'&amp;','s[',$query[1]/id[1]/text(),']=',$value)"/>
	</xsl:template>


	<xsl:template match="attr" mode="display_value"><!--{{{-->
		<xsl:choose>
			<xsl:when test="@label">
				<xsl:value-of select="@label"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="text()"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template><!--}}}-->

	<xsl:template match="class" mode="table_foot"><!--{{{-->
		<!-- <xsl:variable name="obj_name" select="@name"/>  -->
	</xsl:template><!--}}}-->

	<xsl:template name="css"><!--{{{-->

	<xsl:for-each select="//files/css">

	<xsl:element name="link">
		<xsl:attribute name="rel">stylesheet</xsl:attribute>
		<xsl:attribute name="type">text/css</xsl:attribute>
		<xsl:attribute name="href"><xsl:value-of select="@href"/></xsl:attribute>
		<xsl:attribute name="media"><xsl:value-of select="@media"/></xsl:attribute>
	</xsl:element>

	</xsl:for-each>

	</xsl:template><!--}}}-->

	<xsl:template name="js"><!--{{{-->

	<xsl:for-each select="//files/js">

	<xsl:element name="script">
		<xsl:attribute name="type">text/javascript</xsl:attribute>
		<xsl:attribute name="src"><xsl:value-of select="@href"/></xsl:attribute>
		<xsl:if test="@extra!=''">
		<script>
		<xsl:value-of select="@extra"/>
		</script>
		</xsl:if>
	</xsl:element>

	</xsl:for-each>

	<xsl:call-template name="local_css"/>

	</xsl:template><!--}}}-->

	<xsl:template name="change_password_js_function"><!--{{{-->

        <script language="javascript">
function popChgPwd() { window.open( '?<xsl:value-of select="//feat/user_change_password_url"/>', 'chpwd', 'top=250,left=250,width=350, height=220, scollbars=false' );}
	</script>

	</xsl:template><!--}}}-->

	<xsl:template name="body"><!--{{{-->
	<body onload="this.focus()">
	<script type="text/javascript">
		document.title= '<xsl:value-of select="//feat/page_title"/>';
	</script>
	<xsl:call-template name="application_context"/>
	</body>
	</xsl:template><!--}}}-->

	<xsl:template name="application_context"><!--{{{-->
	<body>
	<table width="100%" border="0">
	<tr><td><xsl:call-template name="banner"/></td></tr>
	<tr><td class="nav" align="left"><xsl:call-template name="menu"/></td></tr>
	<tr><td><xsl:call-template name="wellcome"/></td></tr>
	<tr><td><xsl:call-template name="message"/></td></tr>
	<tr><td><xsl:call-template name="main_content"/></td></tr>
	<tr><td><xsl:call-template name="test_block"/></td></tr>
	</table>
	</body>
	</xsl:template><!--}}}-->

	<xsl:template name="banner"><!--{{{-->
		<table width="100%" cellpadding="3" cellspacing="0" border="0">
		<tr>
		<xsl:element name="th">
			<xsl:attribute name="background">style/<xsl:value-of select="//feat/uistyle"/>/images/titlegrad.jpg</xsl:attribute>
			<xsl:attribute name="class">banner</xsl:attribute>
			<xsl:attribute name="align">left</xsl:attribute>
		<strong>
		<xsl:element name="a">
			<xsl:attribute name="style">color: white</xsl:attribute>
			<xsl:attribute name="href"><xsl:value-of select="//feat/base_url"/></xsl:attribute>
			<xsl:value-of select="//feat/page_title"/>
		</xsl:element>
		</strong>
		</xsl:element>
		</tr>
		</table>
	</xsl:template><!--}}}-->

	<xsl:template name="menu"><!--{{{-->
	<div id="navMenu"><xsl:copy-of select="//menu/*"/></div>
	</xsl:template><!--}}}-->

	<xsl:template name="message"><!--{{{-->
		<table width="100%" cellspacing="0" cellpadding="4" border="0">
		<tr>
		<td valign="top" align="left" width="98%">
		<xsl:value-of select="//feat/message"/>
		</td>
		</tr>
		</table>
	</xsl:template><!--}}}-->

	<xsl:template name="wellcome"><!--{{{-->
         <table cellspacing="0" cellpadding="3" border="0" width="100%">
         <tr>
         <td width="100%">Bienvenido <xsl:value-of select="//feat/user_first_name"/> <xsl:value-of select="//feat/user_last_name"/> (<xsl:value-of select="//feat/user_username"/>) en la base de datos <xsl:value-of select="//feat/database"/>
	 </td>
         </tr>
         </table>

	</xsl:template><!--}}}-->

	<xsl:template name="main_content"><!--{{{-->
			<div id="main_content">
			<xsl:apply-templates select="//xpotronix:dataset/class"/>
			</div>
	</xsl:template><!--}}}-->

        <xsl:template name="local_css"><!--{{{-->

        <xsl:element name="style">
                <xsl:attribute name="type">text/css</xsl:attribute>
                <xsl:text>

div.autocomplete {
      position:absolute;
      width:250px;
      background-color:white;
      border:1px solid #888;
      margin:0px;
      padding:0px;
    }
    div.autocomplete ul {
      list-style-type:none;
      margin:0px;
      padding:0px;
    }
    div.autocomplete ul li.selected { background-color: #ffb;}
    div.autocomplete ul li {
      list-style-type:none;
      display:block;
      margin:0;
      padding:2px;
      height:32px;
      cursor:pointer;
    }


                </xsl:text>
        </xsl:element>

        </xsl:template><!--}}}-->

	<xsl:template name="test_block">



	</xsl:template>

</xsl:stylesheet>

<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
