<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format"
	xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:fn="http://www.w3.org/2004/07/xpath-functions"
	xmlns:xdt="http://www.w3.org/2004/07/xpath-datatypes" xmlns:fox="http://xml.apache.org/fop/extensions"
	xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:exsl="http://exslt.org/common"
	xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:func="http://exslt.org/functions"
	xmlns:php="http://php.net/xsl" xmlns:str="http://exslt.org/strings"
	xmlns:axf="http://www.antennahouse.com/names/XSL/Extensions"
	extension-element-prefixes="func php str" xmlns:functx="http://www.functx.com" exclude-result-prefixes="xhtml">

	<xsl:import href="pdf_params.xsl"></xsl:import>
	<xsl:import href="pdf_terms.xsl"></xsl:import>	
		
	<xsl:output method="xml" version="1.0" encoding="UTF-8"
		indent="yes"></xsl:output>

	<xsl:variable name="lang">
		<xsl:value-of select="/loop/meta/lang"></xsl:value-of>
	</xsl:variable>
	
	<xsl:template match="loop">
		<fo:root>
			<xsl:attribute name="hyphenate">true</xsl:attribute>
			<fo:layout-master-set>
				<fo:simple-page-master master-name="cover-page"
					page-height="{$pageheight}" page-width="{$pagewidth}" margin-top="10mm"
					margin-bottom="10mm" margin-left="25mm" margin-right="15mm">
					<fo:region-body margin-top="10mm" margin-bottom="15mm" />
				</fo:simple-page-master>
				<fo:simple-page-master master-name="full-page"
					page-height="{$pageheight}" page-width="{$pagewidth}" margin-top="10mm"
					margin-bottom="5mm" margin-left="25mm" margin-right="15mm">
					<fo:region-body margin-top="15mm" margin-bottom="15mm" />
					<fo:region-before extent="20mm" />
					<fo:region-after extent="15mm" />
				</fo:simple-page-master>
				<fo:simple-page-master master-name="default-page"
					page-height="{$pageheight}" page-width="{$pagewidth}" margin-top="10mm"
					margin-bottom="5mm" margin-left="25mm" margin-right="15mm">
					<fo:region-body margin-top="15mm" margin-bottom="15mm"
						margin-left="20mm"/>
					<fo:region-before extent="20mm" />
					<fo:region-after extent="15mm" />
				</fo:simple-page-master>
				<fo:simple-page-master master-name="full-page-2column"
					page-height="{$pageheight}" page-width="{$pagewidth}" margin-top="10mm"
					margin-bottom="5mm" margin-left="25mm" margin-right="15mm">
					<fo:region-body margin-top="15mm" margin-bottom="15mm" column-count="2" column-gap="10mm"/>
					<fo:region-before extent="20mm" />
					<fo:region-after extent="15mm" />
				</fo:simple-page-master>				
			</fo:layout-master-set>
			
			<xsl:call-template name="make-declarations"></xsl:call-template>
			
			<xsl:call-template name="page-sequence-cover"></xsl:call-template>	
			<xsl:call-template name="page-sequence-table-of-content"></xsl:call-template>
			<xsl:call-template name="page-sequence-contentpages"></xsl:call-template>				
		
		</fo:root>
	</xsl:template>
	
	
	<xsl:template name="make-declarations">
		<axf:document-info name="document-title" >
			<xsl:attribute name="value"><xsl:value-of select="/loop/meta/title"></xsl:value-of></xsl:attribute>
		</axf:document-info>
		
		<!-- ToDo: add more infos, see https://www.antennahouse.com/product/ahf65/ahf-ext.html#axf.document-info -->
	</xsl:template>		
	
	
	<!-- Page Sequence für Cover-Page -->
	<xsl:template name="page-sequence-cover">
		<fo:page-sequence master-reference="cover-page" id="cover_sequence">
			<fo:flow font-family="{$font_family}" flow-name="xsl-region-body">
				<xsl:call-template name="page-content-cover"></xsl:call-template>
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>	
	
	<!-- Page Content der Cover-Page -->
	<xsl:template name="page-content-cover">
		<fo:block text-align="right" font-size="26pt" font-weight="bold"
			id="cover" margin-bottom="10mm" margin-top="40mm" hyphenate="false">
			<xsl:value-of select="/loop/meta/title"></xsl:value-of>
		</fo:block>
		<fo:block text-align="right" font-size="14pt" font-weight="normal"
			margin-bottom="5mm">
			<xsl:value-of select="/loop/meta/url"></xsl:value-of>
		</fo:block>
		<fo:block text-align="right" font-size="12pt" margin-bottom="10mm">
			<xsl:value-of select="$word_state"></xsl:value-of>
			<xsl:text> </xsl:text>
			<xsl:value-of select="/loop/meta/date_generated"></xsl:value-of>
		</fo:block>
		
	</xsl:template>	
	
	
	<!-- Page Sequence für Inhaltsverzeichnis -->
	<xsl:template name="page-sequence-table-of-content">
		<fo:page-sequence master-reference="full-page"
			id="table_of_content_sequence">
			<fo:static-content font-family="{$font_family}"
				flow-name="xsl-region-before">
				<xsl:call-template name="default-header"></xsl:call-template>
			</fo:static-content>
			<fo:static-content font-family="{$font_family}"
				flow-name="xsl-region-after">
				<xsl:call-template name="default-footer"></xsl:call-template>
			</fo:static-content>
			<fo:flow font-family="{$font_family}" flow-name="xsl-region-body"
				text-align="justify" font-size="11.5pt" line-height="15.5pt"
				orphans="3">
				<xsl:call-template name="page-content-table-of-content"></xsl:call-template>
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>	
	
	<!-- Page Content des Inhaltsverzeichnises -->
	<xsl:template name="page-content-table-of-content">
		<fo:block>
			<fo:marker marker-class-name="page-title-left">
				<xsl:value-of select="//loop/meta/title"></xsl:value-of>
			</fo:marker>
		</fo:block>
		<fo:block>
			<fo:marker marker-class-name="page-title-right">
				<xsl:value-of select="$word_content"></xsl:value-of>
			</fo:marker>
		</fo:block>
		<fo:block id="table_of_content">
			<xsl:call-template name="font_head"></xsl:call-template>
			<xsl:value-of select="$word_content"></xsl:value-of>
		</fo:block>
		
		<xsl:call-template name="make-toc"></xsl:call-template>	
		
	</xsl:template>		
	
	
	<xsl:template name="make-toc">
		<xsl:apply-templates select="toc"  mode="toc"></xsl:apply-templates>
	</xsl:template>
	
	<xsl:template match="toc" mode="toc">
		<xsl:apply-templates select="chapter"  mode="toc"></xsl:apply-templates>
	</xsl:template>
	
	<xsl:template match="chapter" mode="toc">
		<xsl:apply-templates select="page"  mode="toc"></xsl:apply-templates>
	</xsl:template>

	<xsl:template match="page" mode="toc">
		<fo:block text-align-last="justify">
			<xsl:call-template name="font_normal"></xsl:call-template>	
			<xsl:if test="@toclevel &gt; 0">
				<xsl:attribute name="margin-left">
					<xsl:value-of select="@toclevel - 1"></xsl:value-of><xsl:text>em</xsl:text>
				</xsl:attribute>
			</xsl:if>
			

			<fo:basic-link color="black">
				<xsl:attribute name="internal-destination" >
				<!--  <xsl:value-of select="@title"></xsl:value-of>
				<xsl:value-of select="generate-id()"/> -->
				<xsl:value-of select="@id"></xsl:value-of>
				</xsl:attribute>
				<xsl:value-of select="@tocnumber"></xsl:value-of>
				<xsl:text> </xsl:text>
				<xsl:value-of select="@toctext"></xsl:value-of>
			</fo:basic-link>
			<fo:inline keep-together.within-line="always">
				<fo:leader leader-pattern="dots"></fo:leader>
				<fo:page-number-citation>
					<xsl:attribute name="ref-id" >
					<!-- <xsl:value-of select="@title"></xsl:value-of>
					<xsl:value-of select="generate-id()"/> -->
					<xsl:value-of select="@id"></xsl:value-of>			
					</xsl:attribute>
				</fo:page-number-citation>
			</fo:inline>				
		</fo:block>
		<xsl:apply-templates select="chapter"  mode="toc"></xsl:apply-templates>	
		
		
		
	</xsl:template>			
	
	<!-- Page Sequence für Wiki-Seiten -->
	<xsl:template name="page-sequence-contentpages">
		<fo:page-sequence master-reference="default-page"
			id="contentpages_sequence">
			<fo:static-content font-family="{$font_family}"
				flow-name="xsl-region-before">
				<xsl:call-template name="default-header"></xsl:call-template>
			</fo:static-content>
			<fo:static-content font-family="{$font_family}"
				flow-name="xsl-region-after">
				<xsl:call-template name="default-footer"></xsl:call-template>
			</fo:static-content>
			<fo:flow font-family="{$font_family}" flow-name="xsl-region-body"
				text-align="justify" font-size="11.5pt" line-height="15.5pt"
				orphans="3">
				<xsl:call-template name="page-content-contentpages"></xsl:call-template>
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>

	<!-- Page Content einer Wiki-Seite -->
	<xsl:template name="page-content-contentpages">
		<xsl:apply-templates select="articles/article"></xsl:apply-templates>
	</xsl:template>	
	
	<!-- Page Content einer Wiki-Seite -->
	<xsl:template match="article">
		<xsl:variable name="toclevel" select="@toclevel"></xsl:variable>
		<xsl:choose>
			<xsl:when test="@toclevel=''">
				<xsl:apply-templates></xsl:apply-templates>
			</xsl:when>
			<xsl:otherwise>
		<fo:block >
			<xsl:attribute name="id">
				<!-- <xsl:value-of select="generate-id()"></xsl:value-of> -->
				<xsl:value-of select="@id"></xsl:value-of>
			</xsl:attribute>
			<xsl:choose>
				<xsl:when test="$toclevel &lt; 2"> 
					<xsl:attribute name="break-before">page</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<fo:block margin-top="10mm">
					</fo:block>		
				</xsl:otherwise>
			</xsl:choose>
			<fo:block>
				<fo:marker marker-class-name="page-title-left">
				<xsl:choose>
					<xsl:when test="@toclevel=0">
						<xsl:value-of select="//loop/@title"></xsl:value-of>
					</xsl:when>
					<xsl:when test="@toclevel=1">
						<xsl:value-of select="//loop/@title"></xsl:value-of>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="preceding-sibling::node()[@toclevel &lt; $toclevel][1]/@tocnumber"></xsl:value-of>
						<xsl:text> </xsl:text>
						<xsl:value-of select="preceding-sibling::node()[@toclevel &lt; $toclevel][1]/@toctext"></xsl:value-of>
					</xsl:otherwise>					
				</xsl:choose>
				</fo:marker>
			</fo:block>
			<fo:block>
				<fo:marker marker-class-name="page-title-right">
					<xsl:value-of select="@tocnumber"></xsl:value-of>
					<xsl:text> </xsl:text>
					<xsl:choose>
						<xsl:when test="string-length(@toctext) &gt; 63">
							<xsl:value-of select="concat(substring(@toctext,0,60),'...')"></xsl:value-of>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="@toctext"></xsl:value-of>
						</xsl:otherwise>
					</xsl:choose>
				</fo:marker>
			</fo:block>
			<fo:block keep-with-next.within-page="always">
				<xsl:call-template name="font_head"></xsl:call-template>
				<xsl:value-of select="@tocnumber"></xsl:value-of>
				<xsl:text> </xsl:text>
				<xsl:value-of select="@toctext"></xsl:value-of>
			</fo:block>
			<fo:block keep-with-previous.within-page="always">
				<xsl:call-template name="font_normal"></xsl:call-template>
				<xsl:apply-templates></xsl:apply-templates>
			</fo:block>
		</fo:block>

			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>	
	
	
	
	
	
	
	<!-- Default Header -->
	<xsl:template name="default-header">
		<fo:table table-layout="fixed" width="100%" margin-bottom="2mm">
			<fo:table-body>
				<fo:table-row>
					<fo:table-cell text-align="left">
						<fo:block line-height="13pt" margin-bottom="-3mm"
							font-weight="bold">
							<fo:retrieve-marker retrieve-class-name="page-title-left"
								retrieve-position="first-starting-within-page"
								retrieve-boundary="page-sequence"></fo:retrieve-marker>
						</fo:block>
					</fo:table-cell>
					<fo:table-cell text-align="right">
						<fo:block line-height="13pt" margin-bottom="-3mm">
							<fo:retrieve-marker retrieve-class-name="page-title-right"
								retrieve-position="first-including-carryover" retrieve-boundary="page-sequence"></fo:retrieve-marker>
						</fo:block>
					</fo:table-cell>
				</fo:table-row>
			</fo:table-body>
		</fo:table>
		<fo:block>
			<fo:leader leader-pattern="rule" leader-length="100%"
				rule-thickness="0.5pt" rule-style="solid" color="black"
				display-align="after"></fo:leader>
		</fo:block>
	</xsl:template>	
	
	<!-- Default Footer -->
	<xsl:template name="default-footer">
		<xsl:param name="last-page-sequence-name">
			<xsl:call-template name="last-page-sequence-name"></xsl:call-template>
		</xsl:param>
		<fo:block>
			<fo:leader leader-pattern="rule" leader-length="100%"
				rule-thickness="0.5pt" rule-style="solid" color="black"
				display-align="before"></fo:leader>
		</fo:block>
		<fo:block text-align="right">
			<fo:page-number></fo:page-number>
			/
			<fo:page-number-citation-last ref-id="{$last-page-sequence-name}"></fo:page-number-citation-last>
		</fo:block>
	</xsl:template>
		
	<xsl:template name="last-page-sequence-name">
		<xsl:text>contentpages_sequence</xsl:text>		
	</xsl:template>
	
	
	
<xsl:template name="font_icon">
		<xsl:attribute name="font-size" >8.5pt</xsl:attribute>
		<xsl:attribute name="font-weight" >bold</xsl:attribute>
		<xsl:attribute name="line-height" >12pt</xsl:attribute>
		<xsl:attribute name="margin-bottom" >1mm</xsl:attribute>
	</xsl:template>	

	<xsl:template name="font_small">
		<xsl:attribute name="font-size">9.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">normal</xsl:attribute>
		<xsl:attribute name="line-height">12.5pt</xsl:attribute>
	</xsl:template>
	<xsl:template name="font_normal">
		<xsl:attribute name="font-size">11.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">normal</xsl:attribute>
		<xsl:attribute name="line-height">18.5pt</xsl:attribute>
	</xsl:template>
	<xsl:template name="font_big">
		<xsl:attribute name="font-size">12.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">normal</xsl:attribute>
		<xsl:attribute name="line-height">18.5pt</xsl:attribute>
	</xsl:template>
	
	
	<xsl:template name="font_subsubsubsubhead">
		<xsl:attribute name="font-size">11.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">bold</xsl:attribute>
		<xsl:attribute name="line-height">18.5pt</xsl:attribute>
	</xsl:template>		
	<xsl:template name="font_subsubsubhead">
		<xsl:attribute name="font-size">11.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">bold</xsl:attribute>
		<xsl:attribute name="line-height">18.5pt</xsl:attribute>
		<xsl:attribute name="margin-top">7pt</xsl:attribute>
	</xsl:template>	
		
	<xsl:template name="font_subsubhead">
		<xsl:attribute name="font-size">12.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">bold</xsl:attribute>
		<xsl:attribute name="line-height">18.5pt</xsl:attribute>
		<xsl:attribute name="margin-top">7pt</xsl:attribute>
	</xsl:template>
	<xsl:template name="font_subhead">
		<xsl:attribute name="font-size">13.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">bold</xsl:attribute>
		<xsl:attribute name="line-height">15.5pt</xsl:attribute>
		<xsl:attribute name="margin-top">7pt</xsl:attribute>
	</xsl:template>
	<xsl:template name="font_head">
		<xsl:attribute name="font-size">14.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">bold</xsl:attribute>
		<xsl:attribute name="line-height">16.5pt</xsl:attribute>
		<xsl:attribute name="margin-top">7pt</xsl:attribute>
	</xsl:template>	
	
	<xsl:template name="font_object_title">
		<xsl:attribute name="font-size">9.5pt</xsl:attribute>
		<xsl:attribute name="font-weight">normal</xsl:attribute>
		<xsl:attribute name="line-height">12.5pt</xsl:attribute>
	</xsl:template>	
	
	<xsl:template match="paragraph">
		<xsl:choose>
			<xsl:when test="preceding::*[1][name()='heading' and (@level='4' or @level='5')]">
				<fo:block >
					<xsl:call-template name="font_normal"></xsl:call-template>
					<xsl:apply-templates></xsl:apply-templates>
				</fo:block>	
			</xsl:when>
			<xsl:otherwise>
	
				<fo:block margin-top="7pt">
					<xsl:call-template name="font_normal"></xsl:call-template>
					<xsl:apply-templates></xsl:apply-templates>
				</fo:block>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="preblock" >
		<xsl:apply-templates></xsl:apply-templates>
	</xsl:template>

	<xsl:template match="preline" >
		<fo:block font-family="{$font_family}">
	    	<xsl:apply-templates></xsl:apply-templates>
    	</fo:block>
	</xsl:template>		
	
	<xsl:template match="space">
		<xsl:text> </xsl:text>
	</xsl:template>		
	
	<xsl:template match="br">
		<xsl:choose>
			<xsl:when test="preceding::node()[1][name()='br']">
				<fo:block white-space-collapse="false" white-space-treatment="preserve" font-size="0pt" >.</fo:block>	
			</xsl:when>
			<xsl:otherwise>
				<fo:block></fo:block>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="xhtml:br">
		<xsl:choose>
			<xsl:when test="preceding::node()[1][name()='xhtml:br']">
				<fo:block white-space-collapse="false" white-space-treatment="preserve" font-size="0pt" >.</fo:block>	
			</xsl:when>
			<xsl:otherwise>
				<fo:block></fo:block>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>		
	

	<xsl:template match="sub">
		<fo:inline vertical-align="sub" font-size="0.8em"><xsl:apply-templates></xsl:apply-templates></fo:inline>
	</xsl:template>	
	
	<xsl:template match="sup">
		<fo:inline vertical-align="super" font-size="0.8em"><xsl:apply-templates></xsl:apply-templates></fo:inline>
	</xsl:template>	

	<xsl:template match="xhtml:sub">
		<fo:inline vertical-align="sub" font-size="0.8em"><xsl:apply-templates></xsl:apply-templates></fo:inline>
	</xsl:template>	
	
	<xsl:template match="xhtml:sup">
		<fo:inline vertical-align="super" font-size="0.8em"><xsl:apply-templates></xsl:apply-templates></fo:inline>
	</xsl:template>	
	
	<xsl:template match="big">
		<fo:inline>
			<xsl:call-template name="font_big"></xsl:call-template>
			<xsl:apply-templates></xsl:apply-templates>
		</fo:inline>
	</xsl:template>	
	
	<xsl:template match="small">
		<fo:inline>
			<xsl:call-template name="font_small"></xsl:call-template>
			<xsl:apply-templates></xsl:apply-templates>
		</fo:inline>
	</xsl:template>			

	<xsl:template match="xhtml:big">
		<fo:inline>
			<xsl:call-template name="font_big"></xsl:call-template>
			<xsl:apply-templates></xsl:apply-templates>
		</fo:inline>
	</xsl:template>	
	
	<xsl:template match="xhtml:small">
		<fo:inline>
			<xsl:call-template name="font_small"></xsl:call-template>
			<xsl:apply-templates></xsl:apply-templates>
		</fo:inline>
	</xsl:template>		

	<xsl:template match="bold">
		<fo:inline font-weight="bold">
			<xsl:apply-templates></xsl:apply-templates>
		</fo:inline>
	</xsl:template>
	<xsl:template match="b">
		<fo:inline font-weight="bold">
			<xsl:apply-templates></xsl:apply-templates>
		</fo:inline>
	</xsl:template>
	<xsl:template match="strong">
		<fo:inline font-weight="bold">
			<xsl:apply-templates></xsl:apply-templates>
		</fo:inline>
	</xsl:template>	

	<xsl:template match="italics">
		<fo:inline font-style="italic">
			<xsl:apply-templates></xsl:apply-templates>
		</fo:inline>
	</xsl:template>	
	
	
	
	<xsl:template match="heading">
		<xsl:variable name="level" select="@level"></xsl:variable>
		<xsl:choose>
			<xsl:when test=".=ancestor::article/@title">
			
			</xsl:when>
			<xsl:otherwise>
				<fo:block keep-with-next.within-page="always">
					<xsl:attribute name="id">
					<xsl:value-of select="generate-id()"/>
					<!-- 
						<xsl:value-of select="ancestor::article/@title"></xsl:value-of>
						<xsl:text>#</xsl:text>
						<xsl:value-of select="."></xsl:value-of>
						 -->
					</xsl:attribute>
					<xsl:choose>
						<xsl:when test="$level='1'">
							<xsl:call-template name="font_head"></xsl:call-template>
						</xsl:when>
						<xsl:when test="$level='2'">
							<xsl:call-template name="font_subhead"></xsl:call-template>
						</xsl:when>
						<xsl:when test="$level='3'">
							<xsl:call-template name="font_subsubhead"></xsl:call-template>
						</xsl:when>
						<xsl:when test="$level='4'">
							<xsl:call-template name="font_subsubsubhead"></xsl:call-template>
						</xsl:when>						
						<xsl:otherwise>
							<xsl:call-template name="font_subsubsubsubhead"></xsl:call-template>
						</xsl:otherwise>
					</xsl:choose>
					<!-- <xsl:value-of select="."></xsl:value-of> -->
					<xsl:apply-templates></xsl:apply-templates>
				</fo:block>
			</xsl:otherwise>
		</xsl:choose>
		
	</xsl:template>			
	
	
	<xsl:template match="link">
		<xsl:apply-templates select="php:function('LoopXml::transform_link', .)"></xsl:apply-templates>
	</xsl:template> 
	
<xsl:template match="php_link">
		<xsl:value-of select="."></xsl:value-of>
	</xsl:template>
	
	<xsl:template match="php_link_external">
		<fo:basic-link>
			<xsl:attribute name="external-destination"><xsl:value-of select="@href"></xsl:value-of></xsl:attribute>
			<fo:inline text-decoration="underline"><xsl:value-of select="."></xsl:value-of></fo:inline>
			<xsl:text> </xsl:text>
			<fo:inline ><fo:external-graphic scaling="uniform" content-height="scale-to-fit" content-width="2mm" src="/opt/www/loop.oncampus.de/mediawiki/skins/loop/images/print/www_link.png"></fo:external-graphic></fo:inline>
		</fo:basic-link>
	</xsl:template>	

	<xsl:template match="php_link_internal">
		<fo:basic-link text-decoration="underline">
			<xsl:attribute name="internal-destination"><xsl:value-of select="@href"></xsl:value-of></xsl:attribute>
			<xsl:value-of select="."></xsl:value-of>
		</fo:basic-link>
	</xsl:template>		
	
	<xsl:template match="php_link_image">
	
		<xsl:variable name="align">
			<xsl:choose>
				<xsl:when test="ancestor::extension[@extension_name='loop_figure']">inside</xsl:when>			
				<xsl:when test="@align='left'">start</xsl:when>
				<xsl:when test="@align='right'">end</xsl:when>
				<xsl:otherwise>none</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>	
	
	<fo:float>
		<xsl:attribute name="float" value="$align"></xsl:attribute>				
				<xsl:choose>
				<xsl:when test="$align='start'">
					<xsl:attribute name="axf:float-margin-x">5mm</xsl:attribute>
				</xsl:when>			
				<xsl:when test="$align='end'">
					<xsl:attribute name="axf:float-margin-x">5mm</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					
				</xsl:otherwise>
			</xsl:choose>
		
		<fo:block font-size="0pt" line-height="0pt" padding-start="0pt" padding-end="0pt" padding-top="0pt" padding-bottom="0pt" padding-left="0pt" padding-right="0pt">
			<fo:external-graphic scaling="uniform" content-height="scale-to-fit"  dominant-baseline="reset-size">
				<!-- <xsl:choose>
					<xsl:when test="$align='start'">
						<xsl:attribute name="padding-right">7mm</xsl:attribute>				
					</xsl:when>
					<xsl:when test="$align='end'">
						<xsl:attribute name="padding-right">7mm</xsl:attribute>				
					</xsl:when>					
					<xsl:otherwise>
						<xsl:attribute name="padding-left">0mm</xsl:attribute>
					</xsl:otherwise>
				</xsl:choose> -->							
				<xsl:attribute name="src" ><xsl:value-of select="@imagepath"></xsl:value-of></xsl:attribute>
				<xsl:attribute name="content-width" ><xsl:value-of select="@imagewidth"></xsl:value-of></xsl:attribute>
			</fo:external-graphic>
		</fo:block>
	</fo:float>		
			
		
	</xsl:template>	
	
</xsl:stylesheet>