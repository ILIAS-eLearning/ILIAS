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
	
	<xsl:template name="handleBlock">
		<xsl:choose>
			<xsl:when test="current()='&#160;'">
				<br />
			</xsl:when>
			<xsl:when test="current()='&#xA0;'">
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
	</xsl:template>

	<xsl:template match="fo:flow/fo:block//fo:block" xmlns:fo="http://www.w3.org/1999/XSL/Format">
		<xsl:call-template name="handleBlock"></xsl:call-template>
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
			<xsl:when test="@font-size">
				<span>
					<xsl:choose>
						<xsl:when test="@font-size='8pt'">
							<xsl:attribute name="style"><xsl:text>font-size: 8pt;</xsl:text></xsl:attribute>
						</xsl:when>
						<xsl:when test="@font-size='10pt'">
							<xsl:attribute name="style"><xsl:text>font-size: 10pt;</xsl:text></xsl:attribute>
						</xsl:when>
						<xsl:when test="@font-size='12pt'">
							<xsl:attribute name="style"><xsl:text>font-size: 12pt;</xsl:text></xsl:attribute>
						</xsl:when>
						<xsl:when test="@font-size='14pt'">
							<xsl:attribute name="style"><xsl:text>font-size: 14pt;</xsl:text></xsl:attribute>
						</xsl:when>
						<xsl:when test="@font-size='18pt'">
							<xsl:attribute name="style"><xsl:text>font-size: 18pt;</xsl:text></xsl:attribute>
						</xsl:when>
						<xsl:when test="@font-size='24pt'">
							<xsl:attribute name="style"><xsl:text>font-size: 24pt;</xsl:text></xsl:attribute>
						</xsl:when>
						<xsl:when test="@font-size='36pt'">
							<xsl:attribute name="style"><xsl:text>font-size: 36pt;</xsl:text></xsl:attribute>
						</xsl:when>
					</xsl:choose>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</span>
			</xsl:when>
			<xsl:when test="@font-family">
				<span>
					<xsl:attribute name="style">
						<xsl:choose>
							<xsl:when test="contains(@font-family, &quot;'&quot;)">
								<xsl:value-of select="substring(@font-family, 2, string-length(@font-family)-2)"/>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text>font-family: </xsl:text>
								<xsl:value-of select="@font-family"/>
								<xsl:text>;</xsl:text>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:attribute>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</span>
			</xsl:when>
		</xsl:choose>
	</xsl:template>

	<!-- Lists -->
	
	<xsl:template match="fo:list-block" xmlns:fo="http://www.w3.org/1999/XSL/Format">
		<xsl:choose>
			<xsl:when test="contains(fo:list-item/fo:list-item-label/fo:block/fo:inline, '.')">
				<ol>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</ol>
			</xsl:when>
			<xsl:otherwise>
				<ul>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</ul>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="//fo:list-item" xmlns:fo="http://www.w3.org/1999/XSL/Format">
		<li>
			<xsl:apply-templates select="node()"></xsl:apply-templates>
		</li>
	</xsl:template>

	<xsl:template match="//fo:list-item-label" xmlns:fo="http://www.w3.org/1999/XSL/Format">
	</xsl:template>

	<xsl:template match="//fo:list-item-body" xmlns:fo="http://www.w3.org/1999/XSL/Format">
		<xsl:apply-templates select="node()"></xsl:apply-templates>
	</xsl:template>

	<xsl:template match="fo:list-item-body/fo:block" xmlns:fo="http://www.w3.org/1999/XSL/Format">
		<xsl:apply-templates select="node()"></xsl:apply-templates>
	</xsl:template>
</xsl:stylesheet>

