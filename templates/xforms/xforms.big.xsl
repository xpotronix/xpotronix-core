<?xml version="1.0" encoding="UTF-8"?>

<!--
	@package xpotronix
	@version 2.0 - Areco 
	@copyright Copyright &copy; 2003-2011, Eduardo Spotorno
	@author Eduardo Spotorno
 
	Licensed under GPL v3
	@license http://www.gnu.org/licenses/gpl-3.0.txt
-->

<!-- xforms.xsl

plantilla para xforms !!
eduardo spotorno, julio 2007

-->
<xsl:stylesheet version="2.0" 

        xmlns="http://www.w3.org/1999/xhtml"
        xmlns:f="http://www.w3.org/2002/xforms"
        xmlns:xsd="http://www.w3.org/2001/XMLSchema"
        xmlns:ev="http://www.w3.org/2001/xml-events"
        xmlns:ftype="http://xforms.example/xforms/types"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:xpotronix="http://xpotronix.com/namespace/xpotronix/" 
	xmlns:fn="http://www.w3.org/2005/04/xpath-functions">

	<xsl:preserve-space elements="text"/>
	<xsl:output method="html" version="4.0" encoding="UTF-8" indent="yes"/>
	<xsl:variable name="warning_message"><xsl:text></xsl:text></xsl:variable>
	<xsl:variable name="doctype_decl"><![CDATA[<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">]]></xsl:variable>
	<xsl:variable name="doctype_decl_strict"><![CDATA[<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">]]></xsl:variable>

	<xsl:template match="/"><!--{{{--> 
<xsl:value-of select="$doctype_decl" disable-output-escaping="yes"/>

	<html 	xmlns="http://www.w3.org/1999/xhtml"
        	xmlns:f="http://www.w3.org/2002/xforms"
        	xmlns:xsd="http://www.w3.org/2001/XMLSchema"
        	xmlns:ev="http://www.w3.org/2001/xml-events"
        	xmlns:ftype="http://xforms.example/xforms/types">

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

<!--
<xsl:call-template name="css"/>
<xsl:call-template name="js"/>
-->

	<!--{{{ model --> <!--}}}-->

	<xsl:apply-templates select="/xpotronix:document/xpotronix:model"/>

	<xsl:apply-templates select="/xpotronix:document/xpotronix:metadata" mode="local_css"/>

</head>

	</xsl:template><!--}}}-->

	<xsl:template match="field" mode="unnormalized"><!--{{{-->

		<xsl:element name="{@name}">
		<xsl:copy-of select="@*[not(name()='name')]"/>
		<xsl:value-of select="."/>
		</xsl:element>

	</xsl:template><!--}}}-->

	<xsl:template match="row" mode="unnormalized"><!--{{{-->

		<xsl:element name="{@name}">
		<xsl:copy-of select="@*[not(name()='name')]"/>
		<xsl:apply-templates mode="unnormalized"/>
		</xsl:element>

	</xsl:template><!--}}}-->

	<xsl:template match="table" mode="unnormalized"><!--{{{-->

		<xsl:element name="class" namespace="">
		<xsl:copy-of select="@*[not(name()='name')]"/>
		<xsl:apply-templates mode="unnormalized"/>
		</xsl:element>

	</xsl:template><!--}}}-->

	<xsl:template match="xpotronix:model"><!--{{{-->
		<xsl:element name="f:model">
			<xsl:element name="f:instance">
				<xsl:attribute name="id"><xsl:value-of select="table/@name"/>_instance</xsl:attribute>

				<xsl:apply-templates 
					select="/xpotronix:document/xpotronix:dataset/table" 
					mode="unnormalized"/>
				<!-- <xsl:copy-of select="/xpotronix:document/xpotronix:dataset/*"/> -->
			</xsl:element>

			<!-- faltan los querys de los select para rellenar 
				salen del model con su q -->
	
                <xsl:element name="f:submission">
			<xsl:attribute name="id"><xsl:value-of select="table/@name"/>_save</xsl:attribute>
			<xsl:attribute name="action"><xsl:value-of select="concat(/xpotronix:document/xpotronix:dataset/table/@name,'.xml')"/></xsl:attribute>
                        <xsl:attribute name="method">put</xsl:attribute>
			<xsl:attribute name="indent">true</xsl:attribute>
			<xsl:attribute name="omit-xml-declaration">true</xsl:attribute>
			<xsl:attribute name="includenamespaceprefixes">#default</xsl:attribute>
                </xsl:element>

	
                <xsl:element name="f:submission">
			<xsl:attribute name="id"><xsl:value-of select="table/@name"/>send</xsl:attribute>
			<xsl:attribute name="action">?a=store</xsl:attribute>
                        <xsl:attribute name="method">post</xsl:attribute>
			<xsl:attribute name="indent">false</xsl:attribute>
			<xsl:attribute name="omit-xml-declaration">false</xsl:attribute>
			<xsl:attribute name="includenamespaceprefixes">#default</xsl:attribute>
                </xsl:element>

		<!-- aca van mas submission que copio del model (ui.xml) -->

		</xsl:element>
	</xsl:template><!--}}}-->

	<xsl:template match="table"><!--{{{-->
		<xsl:variable name="table_name" select="@name"/>
		<xsl:apply-templates select="." mode="title"/>
		<xsl:apply-templates select="/xpotronix:document/xpotronix:metadata/table[@name=$table_name]" mode="search_form"/>
		<xsl:apply-templates select="." mode="grid"/>
	   </xsl:template><!--}}}-->

	<xsl:template match="table" mode="title"><!--{{{-->

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

	<xsl:template match="xpotronix:metadata" mode="local_css"><!--{{{-->
	
	<xsl:element name="style">
		<xsl:attribute name="type">text/css</xsl:attribute>
		<xsl:text>
                @namespace xforms url("http://www.w3.org/2002/xforms");
                xforms|*:invalid .xf-value { background-color: #ffc; }
                *:required:before { content: "*"; color: red; }
                .boxed {
                        border: 2px solid black;
                        padding: 0.5em;
                }
                h1 { font-size: 125%; }
                h2 { font-size: 110%; }
        	</xsl:text>
	</xsl:element>

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

	</xsl:template><!--}}}-->

	<xsl:template name="body"><!--{{{-->
	<body>
	<script type="text/javascript">
		document.title= '<xsl:value-of select="//feat/page_title"/>';
	</script>
	<xsl:call-template name="application_context"/>
	</body>
	</xsl:template><!--}}}-->

	<xsl:template name="application_context"><!--{{{-->
	<table width="100%" border="0">
	<tr><td><xsl:call-template name="banner"/></td></tr>
	<!-- <tr><td class="nav" align="left"><xsl:call-template name="menu"/></td></tr>-->
	<tr><td><xsl:call-template name="wellcome"/></td></tr>
	<tr><td><xsl:call-template name="message"/></td></tr>
	</table>
	<xsl:call-template name="main_content"/>
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
			<xsl:apply-templates select="." mode="messages"/>
			<xsl:apply-templates select="." mode="buttons"/>
	
			<xsl:apply-templates select="." mode="switch"/>

			</div>

	</xsl:template><!--}}}-->

	<xsl:template match="*" mode="messages"><!--{{{-->

		<p>
			<ul>
			<li><span style="color:red">*</span> Los campos marcados con un asterisco rojo son requeridos.</li>
			<li>Los campos con un <span style="background-color: #ffc;"> fondo amarillo </span> no han sido completados.</li> 
			<li>No puede guardar o enviar el formulario si no ha sido completo en todas sus fichas.</li>
			</ul>
		</p>

	</xsl:template><!--}}}-->

	<xsl:template match="*" mode="buttons"><!--{{{-->

		<xsl:for-each select="/xpotronix:document/xpotronix:metadata//table">
			<xsl:element name="f:trigger">
				<xsl:attribute name="id"><xsl:value-of select="@name"/>_button</xsl:attribute>
				<xsl:element name="label"><xsl:value-of select="@name"/></xsl:element>
				<xsl:element name="toggle">
					<xsl:attribute name="ev:event">DOMActivate</xsl:attribute>
					<xsl:attribute name="case"><xsl:value-of select="@name"/>_case</xsl:attribute>
				</xsl:element>
			</xsl:element>
		</xsl:for-each>

		<f:submit submission="save">
			<f:label>Guardar Datos</f:label>
		</f:submit>

		<f:submit submission="send">
			<f:label>Enviar Datos</f:label>
		</f:submit>

		<br/>

	</xsl:template><!--}}}-->

	<xsl:template match="*" mode="switch"><!--{{{-->
		<f:switch>
			<xsl:apply-templates 
				select="/xpotronix:document/xpotronix:metadata//table" 
				mode="case"/>
		</f:switch>
	</xsl:template><!--}}}-->

	<xsl:template match="table" mode="case"><!--{{{-->
		<xsl:element name="f:case">
			<xsl:attribute name="id"><xsl:value-of select="@name"/>_case</xsl:attribute>
			<xsl:attribute name="selected">true</xsl:attribute>
			<p>este es el form: <xsl:value-of select="@name"/></p>
			<xsl:apply-templates select="field" mode="input"/>
		</xsl:element>
	</xsl:template><!--}}}-->

	<xsl:template match="field" mode="input"><!--{{{-->
		<xsl:element name="f:input">
			<xsl:attribute name="ref"><xsl:value-of select="concat('field[@name=',@name,']')"/></xsl:attribute>
			<xsl:element name="f:label">
				<xsl:value-of select="@name"/>
			</xsl:element>
		</xsl:element>
	</xsl:template><!--}}}-->

	<xsl:template name="static_model"><!--{{{-->

	<f:model id="addressModel">

		<f:instance id="addressData" xmlns="">
		<addresses>
		<address>
			<name>Constanza Spotorno</name>
			<street>Campos Salles 2253</street>
			<city>Buenos Aires</city>
			<state>Buenos Aires</state>
			<zip>1429</zip>
		</address>
		<address>
			<name>Constanza Spotorno</name>
			<street>Campos Salles 2253</street>
			<city>Buenos Aires</city>
			<state>Buenos Aires</state>
			<zip>1429</zip>
		</address>
		</addresses>

		</f:instance>
	<!--	
		<f:bind id="name" nodeset="/address/name" />
		<f:bind id="street" nodeset="/address/street" />
		<f:bind id="city" nodeset="/address/city" />
		<f:bind id="state" nodeset="/address/state" />
		<f:bind id="zip" nodeset="/address/zip" />
	-->
		<f:submission id="save" 
			action="address.xml"
			method="put"
			indent="false" 
			includenamespaceprefixes="#default"
			omit-xml-declaration="true"
			/>
	</f:model>
	
	<f:model id="statesModel">
		<f:instance id="statesData" src="xsl/states.xml" xmlns=""/>
	</f:model>

	</xsl:template><!--}}}-->

	<xsl:template name="static_input"><!--{{{-->

    <f:group id="product-repeat" 
                   nodeset="instance('product_instance')/class/product[2]">
		<f:output ref="ID"/>
		<f:output ref="code"/>
		<f:output ref="name"/>
		<f:output ref="PC"/>
		<br/>
    </f:group>
	<f:submit submission="save">
		<f:label>Guardar</f:label>
	</f:submit>




<table>
    <tr>
        <thead>
            <th>ID</th>
            <th>code</th>
            <th>name</th>
            <th>PC</th>
        </thead>
    </tr>

	<h1>Address Form</h1>
    <f:repeat id="product-repeat" 
                   nodeset="instance('addressData')/address">
		<f:input ref="name"/>
		<f:input ref="street"/>
		<f:input ref="city"/>

		<f:select1 ref="state">
			<f:itemset model="statesModel" nodeset="/stateList/state">
				<f:label ref="full"/>
				<f:value ref="abbrev"/>
			</f:itemset>
		</f:select1>

		<f:input ref="zip"/>
		<br/>
    </f:repeat>
	<f:submit submission="save">
		<f:label>Guardar</f:label>
	</f:submit>
</table>	


	</xsl:template><!--}}}-->

</xsl:stylesheet>

<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
