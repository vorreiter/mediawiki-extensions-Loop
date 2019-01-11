<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
	xmlns="http://www.w3.org/2001/10/synthesis" 
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
	xsi:schemaLocation="http://www.w3.org/2001/10/synthesis	http://www.w3.org/TR/speech-synthesis11/synthesis.xsd" 
	 xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:func="http://exslt.org/functions" extension-element-prefixes="func" xmlns:functx="http://www.functx.com">
	
	<xsl:import href="ssml_terms.xsl"></xsl:import>	
	
	<xsl:output method="xml" version="1.0" encoding="UTF-8"	indent="yes"></xsl:output>
	
	<xsl:variable name="lang">
		<xsl:value-of select="/loop/meta/lang"></xsl:value-of>
	</xsl:variable>	

	<xsl:template match="loop">
		<speak version="1.1">
			<xsl:call-template name="introduction"></xsl:call-template>
			<xsl:call-template name="contentpages"></xsl:call-template>
		</speak>
	</xsl:template>
	
	
	<xsl:template name="introduction">
		<p>Titel: <xsl:value-of select="/loop/meta/title"></xsl:value-of></p>
		<break strength="strong"/>
		<p>URL: <xsl:value-of select="/loop/meta/url"></xsl:value-of></p>
		<p>Datum: <say-as interpret-as="date" format="dmy"><xsl:value-of select="/loop/meta/date_generated"></xsl:value-of></say-as></p>
		<break strength="strong"/>
	</xsl:template>
	
	
	<xsl:template name="contentpages">
		<xsl:apply-templates select="articles/article"></xsl:apply-templates>
	</xsl:template>	
	

	<xsl:template match="article">
		<mark>
			<xsl:attribute name="name">
				<xsl:text>begin_</xsl:text><xsl:value-of select="@id"></xsl:value-of>
			</xsl:attribute>
		</mark>
		<p>Kapitel <xsl:value-of select="@tocnumber"></xsl:value-of><xsl:text> </xsl:text><xsl:value-of select="@toctext"></xsl:value-of></p>
		
		<xsl:apply-templates></xsl:apply-templates>
		
		<mark>
			<xsl:attribute name="name">
				<xsl:text>end_</xsl:text><xsl:value-of select="@id"></xsl:value-of>
			</xsl:attribute>
		</mark>	
	</xsl:template>	


	<xsl:template match="paragraph">
		<p><xsl:apply-templates></xsl:apply-templates></p>
	</xsl:template>
	
	<xsl:template match="preblock" >
		<xsl:apply-templates></xsl:apply-templates>
	</xsl:template>

	<xsl:template match="preline" >
		<p>
	    	<xsl:apply-templates></xsl:apply-templates>
    	</p>
	</xsl:template>		
	
	<xsl:template match="space">
		<xsl:text> </xsl:text>
	</xsl:template>		
	
	<xsl:template match="br">
		<xsl:choose>
			<xsl:when test="preceding::node()[1][name()='br']">
				<!-- <break/> -->	
			</xsl:when>
			<xsl:otherwise>
				<break/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>



</xsl:stylesheet>

