<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="html"/>
	<!-- strip white spaces between tags -->
	<xsl:strip-space elements="*"/>
	
	<xsl:param name="one_page"/>
	
	<xsl:template match="/">
		<xsl:apply-templates/>
	</xsl:template>
	<xsl:template name="organization" match="organization">
		<html>
			<head>
				<title><xsl:value-of select="title"/></title>
			</head>
			<body>
				<h1><xsl:value-of select="title"/></h1>
				<xsl:apply-templates/>
			</body>
		</html>		
	</xsl:template>
	<xsl:template name="item" match="item">
		<ul>
		<li>
		<xsl:choose>
			<xsl:when test="@identifierref!=''">
				<a>
					<xsl:if test="$one_page != 'y'">
						<xsl:attribute name="href"><xsl:value-of select="substring-after(@identifier,'sco_')"/>/index.html</xsl:attribute>
						<xsl:attribute name="target">content</xsl:attribute>
					</xsl:if>
					<xsl:if test="$one_page = 'y'">
						<xsl:attribute name="href">#sco<xsl:value-of select="substring-after(@identifier,'sco_')"/></xsl:attribute>
					</xsl:if>
					<xsl:value-of select="title"/>
				</a>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="title"/>
			</xsl:otherwise>
		</xsl:choose>
		<xsl:for-each select="item">
			<xsl:call-template name="item"/>
		</xsl:for-each>
		</li>
		</ul>
	</xsl:template>
</xsl:stylesheet>
