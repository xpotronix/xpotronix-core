<?xml version="1.0" encoding="UTF-8"?>

<!-- joomla.xsl 


Este template es nada mas que un copy/paste del codigo fuente de una pagina de demo de joomla.
A partir de aqui, de acuerdo a cada uno de los bloques de los divs, se deberan reemplazar los estaticos por llamados a sub-templates
Nota: los &nbsp; fueron removidos ya que este archivo no incluye los ENTITIES

es.

-->

<xsl:stylesheet version="2.0"
	xmlns="http://www.w3.org/TR/REC-html40"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns:xpotronix="http://xpotronix.com/namespace/xpotronix/" 
	exclude-result-prefixes="#all">

	<xsl:preserve-space elements="text"/>
	<xsl:output method="html" version="4.0" encoding="UTF-8" indent="yes"/>

	<xsl:template match="/">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-gb" lang="en-gb" dir="ltr" >
<head>
	  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="robots" content="index, follow" />
  <meta name="keywords" content="joomla, Joomla" />
  <meta name="description" content="Joomla! - the dynamic portal engine and content management system" />
  <meta name="generator" content="Joomla! 1.5 - Open Source Content Management" />
  <title>Welcome to the Frontpage</title>
  <link href="http://osc.template-help.com/joomla_23773/index.php?format=feed&amp;type=rss" rel="alternate" type="application/rss+xml" title="RSS 2.0" />

  <link href="http://osc.template-help.com/joomla_23773/index.php?format=feed&amp;type=atom" rel="alternate" type="application/atom+xml" title="Atom 1.0" />
  <link href="http://osc.template-help.com/joomla_23773/templates/theme258/favicon.ico" rel="shortcut icon" type="image/x-icon" />
  <script type="text/javascript" src="http://osc.template-help.com/joomla_23773/media/system/js/mootools.js"></script>
  <script type="text/javascript" src="http://osc.template-help.com/joomla_23773/media/system/js/caption.js"></script>

	<link rel="stylesheet" href="http://osc.template-help.com/joomla_23773/templates/theme258/css/template.css" type="text/css" />
	<link rel="stylesheet" href="http://osc.template-help.com/joomla_23773/templates/theme258/css/constant.css" type="text/css" />
    <script type="text/javascript" src="http://osc.template-help.com/joomla_23773/media/system/js/mootools.js"></script>

	<script type="text/javascript" src="http://osc.template-help.com/joomla_23773/templates/theme258/scripts/maxheight.js"></script><!--equal-->
</head>
<body id="body">
	<div class="tail-header clear">
    	<div class="main header-bg">
        	<div class="header clear">
            	<div class="fright">		<div class="moduletable-login">
					
<form action="http://osc.template-help.com/joomla_23773/index.php" method="post" name="login" class="form-login">
	        <div class="clear row-form">

        	<div class="col-1">
            	<label for="mod_login_username">
					Username                </label>
                <input name="username" id="mod_login_username" type="text" class="inputbox" alt="Username" />
            </div>
            <div class="col-2">
            	 <label for="mod_login_password">
					Password                </label>

                <input type="password" id="mod_login_password" name="passwd" class="inputbox"  alt="Password" />
            </div>
            <div class="col-3"><input type="submit" name="Submit" class="button indent-button" value="Log in" /> </div>
        </div>
        <div class="clear row-form-link">
        	<div class="col-1">
            	<div class="clear extra-indent-link">
                     <div class="indent-top-button"><input type="checkbox" name="remember" id="mod_login_remember" class="checkbox" value="yes" alt="Remember Me" />

                        <label for="mod_login_remember" class="remember">
                            Remember Me                        </label>
                    </div>
                   
                </div>
            </div>
            <div class="col-2">
            	<div class="extra-indent-link">
                    <p>

                    <a class="link-1" href="http://osc.template-help.com/joomla_23773/index.php?option=com_user&amp;view=reset#content">
                        Forgot your password?</a>
                    </p>
                    <p>
                    <a class="link-1" href="http://osc.template-help.com/joomla_23773/index.php?option=com_user&amp;view=remind#content">
                        Forgot your username?</a>
                    </p>
                     						<p>

							No Account Yet?							<a class="link-2" href="http://osc.template-help.com/joomla_23773/index.php?option=com_user&amp;task=register#content">
								Create an account</a>
						</p>
						                </div>
            </div>
        </div>
        
    
   
	<input type="hidden" name="option" value="com_user" />
	<input type="hidden" name="task" value="login" />

	<input type="hidden" name="return" value="L2pvb21sYV8yMzc3My8jY29udGVudA==" />
	<input type="hidden" name="46a305ddbd14362373b65fa0d76bd450" value="1" /></form>
		</div>
	</div>
                <div class="fleft"><h1><a href="http://osc.template-help.com/joomla_23773/index.php"></a></h1></div>
            </div>
            <div class="top-menu">		<div class="moduletable">
					<ul class="menu-nav"><li class="item53"><a href="http://osc.template-help.com/joomla_23773/index.php"><span>Home</span></a></li><li class="item54"><a href="http://osc.template-help.com/joomla_23773/index.php?option=com_content&amp;view=category&amp;layout=blog&amp;id=31&amp;Itemid=54"><span>Blog</span></a></li><li class="item55"><a href="http://osc.template-help.com/joomla_23773/index.php?option=com_newsfeeds&amp;view=category&amp;id=4&amp;Itemid=55"><span>News Feeds</span></a></li><li class="item56"><a href="http://osc.template-help.com/joomla_23773/index.php?option=com_wrapper&amp;view=wrapper&amp;Itemid=56"><span>Wrapper</span></a></li><li class="item57"><a href="http://osc.template-help.com/joomla_23773/index.php?option=com_weblinks&amp;view=category&amp;id=2&amp;Itemid=57"><span>Links</span></a></li><li class="item58"><a href="http://osc.template-help.com/joomla_23773/index.php?option=com_content&amp;view=category&amp;layout=blog&amp;id=31&amp;Itemid=58"><span>FAQs</span></a></li><li class="item59"><a href="http://osc.template-help.com/joomla_23773/index.php?option=com_content&amp;view=category&amp;layout=blog&amp;id=1&amp;Itemid=59"><span>News</span></a></li><li class="item60"><a href="http://osc.template-help.com/joomla_23773/index.php?option=com_contact&amp;view=contact&amp;id=1&amp;Itemid=60"><span>Contacts</span></a></li></ul>		</div>

	</div>
        </div>
    </div>
    <div class="tail-breadcrumbs   clear">
    	<div class="main">
        	<div class="row-breadcrumbs clear">
            	<div class="fright">		<div class="moduletable">
					<h3>Who's Online</h3>

					 We have<span class="count">2 guests</span>online		</div>
	</div>
                <div class="fleft">		<div class="moduletable">
					<span class="breadcrumbs pathway">
Home</span>
		</div>
	</div>

            </div>
        </div>
    </div>
    <div class="tail-content clear">
    	<div class="main">
            <div id="content">
                <div class="corner-bottom-left">
                	<div class="corner-bottom-right clear">
                    	<div class="content-indent">

                            <div class="clear">
                                                                <div id="left" class="equal">
                                    <div class="left-indent">
                                        <div class="clear">
                                        			
             <div class="wrapper-box module s2">
                <div class="box-title">
                   <div class="clear">
                                            </div>
                </div>

               <div class="box-content">
                    <div class="box-content-indent"><div class="clear"><form action="index.php" method="post">
	<div class="search s2">
		<input name="searchword" id="mod_search_searchword" maxlength="20" alt="Search" class="inputbox s2" type="text" size="20" value="search..."  onblur="if(this.value=='') this.value='search...';" onfocus="if(this.value=='search...') this.value='';" /><input type="image" value="Search" class="button s2" src="http://osc.template-help.com/joomla_23773/templates/theme258/images/searchButton.gif" onclick="this.form.searchword.focus();"/>	</div>
	<input type="hidden" name="task"   value="search" />
	<input type="hidden" name="option" value="com_search" />
</form></div></div>
               </div>
           </div>

			
             <div class="wrapper-box module_menu">
                <div class="box-title">
                   <div class="clear">
                                                    <h3>Main Menu</h3>
                                            </div>
                </div>
               <div class="box-content">
                    <div class="box-content-indent"><div class="clear"><ul class="menu"><li id="current" class="active item1"><a href="http://osc.template-help.comhttp://osc.template-help.com/joomla_23773/"><span>Home</span></a></li><li class="item2"><a href="http://osc.template-help.com/joomla_23773/index.php?option=com_content&amp;view=article&amp;id=5&amp;Itemid=2"><span>Joomla! License</span></a></li><li class="item62"><a href="http://osc.template-help.com/joomla_23773/index.php?option=com_content&amp;view=category&amp;layout=blog&amp;id=31&amp;Itemid=62"><span>Blog</span></a></li><li class="item69"><a href="http://osc.template-help.com/joomla_23773/index.php?option=com_content&amp;view=category&amp;layout=blog&amp;id=1&amp;Itemid=69"><span>News</span></a></li><li class="item63"><a href="http://osc.template-help.com/joomla_23773/index.php?option=com_contact&amp;view=contact&amp;id=1&amp;Itemid=63"><span>Contact Us</span></a></li><li class="item64"><a href="http://osc.template-help.com/joomla_23773/index.php?option=com_search&amp;view=search&amp;Itemid=64"><span>Search</span></a></li><li class="item68"><a href="http://osc.template-help.com/joomla_23773/index.php?option=com_newsfeeds&amp;view=category&amp;id=4&amp;Itemid=68"><span>News Feeds</span></a></li><li class="item66"><a href="http://osc.template-help.com/joomla_23773/index.php?option=com_content&amp;view=category&amp;layout=blog&amp;id=31&amp;Itemid=66"><span>FAQs</span></a></li><li class="item67"><a href="http://osc.template-help.com/joomla_23773/index.php?option=com_wrapper&amp;view=wrapper&amp;Itemid=67"><span>Wrapper</span></a></li></ul></div></div>

               </div>
           </div>
			
             <div class="wrapper-box module">
                <div class="box-title">
                   <div class="clear">
                                                    <h3>Polls</h3>
                                            </div>
                </div>

               <div class="box-content">
                    <div class="box-content-indent"><div class="clear"><form action="index.php" method="post" name="form2">

<div class="poll">
	<div class="question">
		Joomla! is used for?	</div>
	<div class="body">
					<div class="section">
				<div class="radio sectiontableentry2">

					<input type="radio" name="voteid" id="voteid1" value="1" alt="1" />
				</div>
				<div class="var sectiontableentry2">
					<label for="voteid1">
						 Absolutely simple					</label>
				</div>
			</div>
								<div class="section">

				<div class="radio sectiontableentry1">
					<input type="radio" name="voteid" id="voteid2" value="2" alt="2" />
				</div>
				<div class="var sectiontableentry1">
					<label for="voteid2">
						Reasonably easy					</label>
				</div>
			</div>

								<div class="section">
				<div class="radio sectiontableentry2">
					<input type="radio" name="voteid" id="voteid3" value="3" alt="3" />
				</div>
				<div class="var sectiontableentry2">
					<label for="voteid3">
						Not straight-forward but I worked it out					</label>
				</div>

			</div>
								<div class="section">
				<div class="radio sectiontableentry1">
					<input type="radio" name="voteid" id="voteid4" value="4" alt="4" />
				</div>
				<div class="var sectiontableentry1">
					<label for="voteid4">
						I had to install extra server stuff					</label>

				</div>
			</div>
								<div class="section">
				<div class="radio sectiontableentry2">
					<input type="radio" name="voteid" id="voteid5" value="5" alt="5" />
				</div>
				<div class="var sectiontableentry2">
					<label for="voteid5">
						I had no idea and got my friend to do it					</label>

				</div>
			</div>
								<div class="section">
				<div class="radio sectiontableentry1">
					<input type="radio" name="voteid" id="voteid6" value="6" alt="6" />
				</div>
				<div class="var sectiontableentry1">
					<label for="voteid6">
						My dog ran away with the					</label>

				</div>
			</div>
						</div>
	<div class="buttons clear">
		<div class="fright"><input type="button" name="option" class="button result" value="Results" onclick="document.location.href='http://osc.template-help.com/joomla_23773/index.php?option=com_poll&amp;id=14:joomla-is-used-for'" /></div>
		<div class="fleft"><input type="submit" name="task_button" class="button" value="Vote" /></div>
	</div>
</div>

<input type="hidden" name="option" value="com_poll" />

<input type="hidden" name="task" value="vote" />
<input type="hidden" name="id" value="14" />
<input type="hidden" name="46a305ddbd14362373b65fa0d76bd450" value="1" /></form></div></div>
               </div>
           </div>
			
             <div class="wrapper-box module s1">
                <div class="box-title">
                   <div class="clear">
                                                    <h3>Syndication</h3>
                                            </div>

                </div>
               <div class="box-content">
                    <div class="box-content-indent"><div class="clear"><a href="http://osc.template-help.com/joomla_23773/index.php?format=feed&amp;type=rss">
	<img src="http://osc.template-help.com/joomla_23773/templates/theme258/images/livemarks.png" alt="feed-image"  /> <span>Feed Entries</span></a></div></div>
               </div>
           </div>
	
                                        </div>
                                    </div>

                                </div>
                                                                <div id="container" class="equal">
                                    <div class="container-indent">
                                        <div class="clear">
                                                                                        <div class="indent-text"><table class="blog" cellpadding="0" cellspacing="0">
<tr>
	<td valign="top">
						<div>

<div class="wrapper-title ">

    <div class="clear contentpaneopen">
        <div class="fright">
            <div class="icon-indent">
                                                                            </div>
        </div>
                <div class="fleft contentheading">
            <div class="title">            Think big. Win the world!            </div>
            </div>

                 </div>  
</div>
<div class="clear contentheading">
	                <div class="createdate">
                    Saturday, 07 July 2007 09:54                </div>
            			            <div class="small">
                <span>
                    Written by Administrator                </span>
                

            </div>
            </div>                          
    

<div class="content-text">
    <div class="clear"><table class="contentpaneopen">
        
        
    <tr>
    <td valign="top" colspan="2" class="article_indent">
        <img  src="http://osc.template-help.com/joomla_23773/images/stories/1page_img_1.jpg" alt="" border="0" />
<div class="indent-top">
	<strong class="br">Lorem ipsum dolor sit ame onsectetuer adipiscing eliraesent vestibulum molestie lacu</strong>

    At vero eo etusam. Iustdigne imos similique cobidolo eligenuoptio. Solunobis est eligendi cumqedit quo ms tristique orci ac sem. Duis ultricies arra magnaeum san malesuaifend, elda onec sit amet erorem ips dolor sitauris fermentum dictum magna. Sed laoreet aliquam leo. 
</div>
<div>                
                    <div class="readmore">
                <a href="http://osc.template-help.com/joomla_23773/index.php?option=com_content&amp;view=article&amp;id=45:think-big-win-the-world&amp;catid=1:latest-news&amp;Itemid=69" class="readon">
                                            Read more...                    </a>
            </div>
            </div>
            </td>
    </tr>
    
    
    
    </table></div>

</div>
<div class="indent-article-separator"><span class="article_separator"></span></div>
</div>
		</td>
</tr>

<tr>
	<td valign="top">
		<table width="100%"  cellpadding="0" cellspacing="0">
		<tr>
										<td valign="top" width="100%" class="article_column">
				


<div class="wrapper-title ">
    <div class="clear contentpaneopen">
        <div class="fright">
            <div class="icon-indent">
                                                                            </div>
        </div>
                <div class="fleft contentheading">
            <div class="title">            lATEST NEWS            </div>

            </div>
                 </div>  
</div>
<div class="clear contentheading">
				</div>                          
    

<div class="content-text">
    <div class="clear"><table class="contentpaneopen">
        
        
    <tr>
    <td valign="top" colspan="2" class="article_indent">
        <div class="clear row-list">

	<ul>
    	<li><a href="#">Phasellus portce suscipit vari</a></li>
        <li><a href="#">Cum sociis natoque pena</a></li>
        <li><a href="#">Fusce feugiat malesua</a></li>
        <li><a href="#">Lorem ipsum dolor smolesa</a></li>
        <li><a href="#">Nulla venenatisn pede maliqu</a></li>    	
    </ul>

    <ul class="list-left">
    	<li><a href="#">Lorem ipsum dolor smolesa</a></li>
        <li><a href="#">Nulla venenatisn pede maliqu</a></li>
        <li><a href="#">Aliquam dapibus tincidunt metu</a></li>
        <li><a href="#">Phasellus portce suscipit vari</a></li>
        <li><a href="#">Cum sociis natoque pena</a></li>

    </ul>
    <ul class="list-left">
    	<li><a href="#">Cum sociis natoque pena</a></li>
        <li><a href="#">Phasellus portce suscipit vari</a></li>
        <li><a href="#">Fusce feugiat malesua</a></li>
        <li><a href="#">Lorem ipsum dolor smolesa</a></li>
        <li><a href="#">Nulla venenatisn pede maliqu</a></li>

    </ul>
</div>                
            </td>
    </tr>
    
    
    
    </table></div>
</div>
<div class="indent-article-separator"><span class="article_separator"></span></div>


<div class="wrapper-title ">
    <div class="clear contentpaneopen">
        <div class="fright">

            <div class="icon-indent">
                                                                            </div>
        </div>
                <div class="fleft contentheading">
            <div class="title">            Joomla! Security Strike Team            </div>
            </div>
                 </div>  
</div>

<div class="clear contentheading">
	                <div class="createdate">
                    Saturday, 07 July 2007 09:54                </div>
            			            <div class="small">
                <span>
                    Written by Administrator                </span>
                
            </div>

            </div>                          
    

<div class="content-text">
    <div class="clear"><table class="contentpaneopen">
        
        
    <tr>
    <td valign="top" colspan="2" class="article_indent">
        Duis ultricies arra magnaeum san malesuaifend, elda ond, elit. Aenean auctor wisi et urna. Aliquam erat volutpat. Duis ac turpis. Integer rutrum ante eu lacestibulnec sit amet erorem ips dolor sitauris fermentum dictum magna. Sed laoreet aliquam leo.
<div>                
                    <div class="readmore">
                <a href="http://osc.template-help.com/joomla_23773/index.php?option=com_content&amp;view=article&amp;id=44:joomla-security-strike-team&amp;catid=1:latest-news&amp;Itemid=69" class="readon">
                                            Read more...                    </a>

            </div>
	</div>
            </td>
    </tr>
    
    
    
    </table></div>
</div>
<div class="indent-article-separator"><span class="article_separator"></span></div>
				</td>
						</tr>
		</table>
	</td>

</tr>

</table>
</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div id="footer"  class="clear">
                <div class="indent"><a href="http://www.joomla.org">Joomla!</a>is Free Software released under the GNU/GPL License</div>
            </div>
        </div>
    </div>
<!--osc3.template-help.com -->

<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));

</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-7078796-1");
pageTracker._trackPageview();
} catch(err) {}</script>
</body>
</html>


	</xsl:template>

</xsl:stylesheet>

<!-- vim600: fdm=marker sw=3 ts=8 ai: 
-->
