<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="xml"></xsl:output>
	<xsl:param name="pageheight" select="'29.7cm'"/>
	<xsl:param name="pagewidth" select="'21cm'"/>
	<xsl:param name="backgroundimage"/>
	<xsl:param name="marginbody" select="'0cm 1cm 0cm 1cm'"/>
	<xsl:param name="paddingtop" select="'10cm'"/>

	<xsl:template match="/">
		<xsl:apply-templates select="node()"></xsl:apply-templates>
	</xsl:template>
		
	<xsl:template match="//fo:page-sequence" xmlns:fo="http://www.w3.org/1999/XSL/Format">
		<xsl:apply-templates select="node()"></xsl:apply-templates>
	</xsl:template>
	
	<xsl:template match="//fo:flow" xmlns:fo="http://www.w3.org/1999/XSL/Format">
		<xsl:apply-templates select="node()"></xsl:apply-templates>
	</xsl:template>
	
	<xsl:template match="//fo:block" xmlns:fo="http://www.w3.org/1999/XSL/Format">
		<xsl:if test="string-length(node())">
			<xsl:choose>
				<xsl:when test="node()='&#160;'">
					<br />
				</xsl:when>
				<xsl:otherwise>
					<p>
						<xsl:choose>
							<xsl:when test="@text-align='left'">
								<xsl:attribute name="align">
									<xsl:text>left</xsl:text>
								</xsl:attribute>
							</xsl:when>
							<xsl:when test="@text-align='right'">
								<xsl:attribute name="align">
									<xsl:text>right</xsl:text>
								</xsl:attribute>
							</xsl:when>
							<xsl:when test="@text-align='center'">
								<xsl:attribute name="align">
									<xsl:text>center</xsl:text>
								</xsl:attribute>
							</xsl:when>
							<xsl:when test="@text-align='justify'">
								<xsl:attribute name="align">
									<xsl:text>justify</xsl:text>
								</xsl:attribute>
							</xsl:when>
						</xsl:choose>
						<xsl:apply-templates select="node()"></xsl:apply-templates>
					</p>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="//fo:inline" xmlns:fo="http://www.w3.org/1999/XSL/Format">
		<xsl:choose>
			<xsl:when test="@font-weight='bold'">
				<strong>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</strong>
			</xsl:when>
			<xsl:when test="@font-style='italic'">
				<em>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</em>
			</xsl:when>
			<xsl:when test="@text-decoration='underline'">
				<u>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</u>
			</xsl:when>
			<xsl:when test="@font-size='8pt'">
				<font>
					<xsl:attribute name="size"><xsl:text>1</xsl:text></xsl:attribute>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</font>
			</xsl:when>
			<xsl:when test="@font-size='10pt'">
				<font>
					<xsl:attribute name="size"><xsl:text>2</xsl:text></xsl:attribute>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</font>
			</xsl:when>
			<xsl:when test="@font-size='12pt'">
				<font>
					<xsl:attribute name="size"><xsl:text>3</xsl:text></xsl:attribute>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</font>
			</xsl:when>
			<xsl:when test="@font-size='14pt'">
				<font>
					<xsl:attribute name="size"><xsl:text>4</xsl:text></xsl:attribute>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</font>
			</xsl:when>
			<xsl:when test="@font-size='18pt'">
				<font>
					<xsl:attribute name="size"><xsl:text>5</xsl:text></xsl:attribute>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</font>
			</xsl:when>
			<xsl:when test="@font-size='24pt'">
				<font>
					<xsl:attribute name="size"><xsl:text>6</xsl:text></xsl:attribute>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</font>
			</xsl:when>
			<xsl:when test="@font-size='36pt'">
				<font>
					<xsl:attribute name="size"><xsl:text>7</xsl:text></xsl:attribute>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</font>
			</xsl:when>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>

