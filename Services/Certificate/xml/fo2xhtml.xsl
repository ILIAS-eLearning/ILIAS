<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="xml"/>
	<xsl:param name="pageheight" select="'29.7cm'"/>
	<xsl:param name="pagewidth" select="'21cm'"/>
	<xsl:param name="backgroundimage"/>
	<xsl:param name="marginbody" select="'0cm 1cm 0cm 1cm'"/>

	<xsl:template match="/">
		<xsl:apply-templates select="node()"/>
	</xsl:template>

	<xsl:template match="//fo:page-sequence" xmlns:fo="http://www.w3.org/1999/XSL/Format">
		<xsl:apply-templates select="node()"/>
	</xsl:template>

	<xsl:template match="//fo:flow" xmlns:fo="http://www.w3.org/1999/XSL/Format">
		<xsl:apply-templates select="node()"/>
	</xsl:template>

	<xsl:template name="handleBlock">
		<p>
			<xsl:choose>
				<xsl:when test="current()='&#160;'">
				</xsl:when>
				<xsl:when test="current()='&#xA0;'">
				</xsl:when>
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
			<xsl:choose>
				<xsl:when test="@padding or @padding-left or @padding-right or @padding-top or @padding-bottom or (@margin and @margin != 0) or @margin-left or @margin-right or @margin-top or @margin-bottom">
						<xsl:attribute name="style">
							<xsl:if test="@padding">
								<xsl:text>padding: </xsl:text>
								<xsl:value-of select="@padding"/>
								<xsl:text>; </xsl:text>
							</xsl:if>
							<xsl:if test="@padding-left">
								<xsl:text>padding-left: </xsl:text>
								<xsl:value-of select="@padding-left"/>
								<xsl:text>; </xsl:text>
							</xsl:if>
							<xsl:if test="@padding-right">
								<xsl:text>padding-right: </xsl:text>
								<xsl:value-of select="@padding-right"/>
								<xsl:text>; </xsl:text>
							</xsl:if>
							<xsl:if test="@padding-top">
								<xsl:text>padding-top: </xsl:text>
								<xsl:value-of select="@padding-top"/>
								<xsl:text>; </xsl:text>
							</xsl:if>
							<xsl:if test="@padding-bottom">
								<xsl:text>padding-bottom: </xsl:text>
								<xsl:value-of select="@padding-bottom"/>
								<xsl:text>; </xsl:text>
							</xsl:if>
							<!-- Prevet dummy margin=0 to appear in HTML -->
							<xsl:if test="@margin and @margin != 0">
								<xsl:text>margin: </xsl:text>
								<xsl:value-of select="@margin"/>
								<xsl:text>; </xsl:text>
							</xsl:if>
							<xsl:if test="@margin-left">
								<xsl:text>margin-left: </xsl:text>
								<xsl:value-of select="@margin-left"/>
								<xsl:text>; </xsl:text>
							</xsl:if>
							<xsl:if test="@margin-right">
								<xsl:text>margin-right: </xsl:text>
								<xsl:value-of select="@margin-right"/>
								<xsl:text>; </xsl:text>
							</xsl:if>
							<xsl:if test="@margin-top">
								<xsl:text>margin-top: </xsl:text>
								<xsl:value-of select="@margin-top"/>
								<xsl:text>; </xsl:text>
							</xsl:if>
							<xsl:if test="@margin-bottom">
								<xsl:text>margin-bottom: </xsl:text>
								<xsl:value-of select="@margin-bottom"/>
								<xsl:text>; </xsl:text>
							</xsl:if>
						</xsl:attribute>
				</xsl:when>
			</xsl:choose>
			<xsl:apply-templates select="node()"/>
		</p>
	</xsl:template>

	<xsl:template match="fo:flow/fo:block//fo:block" xmlns:fo="http://www.w3.org/1999/XSL/Format">
		<xsl:call-template name="handleBlock"/>
	</xsl:template>

	<xsl:template match="//fo:inline" xmlns:fo="http://www.w3.org/1999/XSL/Format">
		<xsl:if test="@font-weight='bold'">
			<xsl:text disable-output-escaping="yes">&lt;strong&gt;</xsl:text>
		</xsl:if>
		<xsl:if test="@font-style='italic'">
			<xsl:text disable-output-escaping="yes">&lt;em&gt;</xsl:text>
		</xsl:if>
		<xsl:if test="@text-decoration='underline'">
			<xsl:text disable-output-escaping="yes">&lt;u&gt;</xsl:text>
		</xsl:if>
		<xsl:choose>
			<xsl:when test="@font-family or @font-size or @padding or @padding-left or @padding-right or @padding-top or @padding-bottom or @color">
				<xsl:element name="span">
					<xsl:attribute name="style">
						<xsl:if test="@font-family">
							<xsl:choose>
								<xsl:when test="contains(@font-family, &quot;'&quot;)">
									<xsl:value-of
										select="substring(@font-family, 2, string-length(@font-family)-2)"
									/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:text>font-family: </xsl:text>
									<xsl:value-of select="@font-family"/>
									<xsl:text>;</xsl:text>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:if>
						<xsl:if test="@font-size">
							<xsl:text>font-size: </xsl:text>
							<xsl:value-of select="@font-size"/>
							<xsl:text>; </xsl:text>
						</xsl:if>
						<xsl:if test="@padding">
							<xsl:text>padding: </xsl:text>
							<xsl:value-of select="@padding"/>
							<xsl:text>; </xsl:text>
						</xsl:if>
						<xsl:if test="@padding-left">
							<xsl:text>padding-left: </xsl:text>
							<xsl:value-of select="@padding-left"/>
							<xsl:text>; </xsl:text>
						</xsl:if>
						<xsl:if test="@padding-right">
							<xsl:text>padding-right: </xsl:text>
							<xsl:value-of select="@padding-right"/>
							<xsl:text>; </xsl:text>
						</xsl:if>
						<xsl:if test="@padding-top">
							<xsl:text>padding-top: </xsl:text>
							<xsl:value-of select="@padding-top"/>
							<xsl:text>; </xsl:text>
						</xsl:if>
						<xsl:if test="@padding-bottom">
							<xsl:text>padding-bottom: </xsl:text>
							<xsl:value-of select="@padding-bottom"/>
							<xsl:text>; </xsl:text>
						</xsl:if>
						<xsl:if test="@color">
							<xsl:text>color: </xsl:text>
							<xsl:value-of select="@color"/>
							<xsl:text>; </xsl:text>
						</xsl:if>
					</xsl:attribute>
					<xsl:apply-templates select="node()"/>
				</xsl:element>
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates select="node()"/>

			</xsl:otherwise>
		</xsl:choose>


		<xsl:if test="@text-decoration='underline'">
			<xsl:text disable-output-escaping="yes">&lt;/u&gt;</xsl:text>
		</xsl:if>
		<xsl:if test="@font-style='italic'">
			<xsl:text disable-output-escaping="yes">&lt;/em&gt;</xsl:text>
		</xsl:if>
		<xsl:if test="@font-weight='bold'">
			<xsl:text disable-output-escaping="yes">&lt;/strong&gt;</xsl:text>
		</xsl:if>
	</xsl:template>

	<!-- Lists -->

	<xsl:template match="fo:list-block" xmlns:fo="http://www.w3.org/1999/XSL/Format">
		<xsl:choose>
			<xsl:when test="contains(fo:list-item/fo:list-item-label/fo:block/fo:inline, '.')">
				<ol>
					<xsl:apply-templates select="node()"/>
				</ol>
			</xsl:when>
			<xsl:otherwise>
				<ul>
					<xsl:apply-templates select="node()"/>
				</ul>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="//fo:list-item" xmlns:fo="http://www.w3.org/1999/XSL/Format">
		<li>
			<xsl:variable name="block" select="fo:list-item-body/fo:block"/>
			<xsl:if test="$block/@padding or $block/@padding-left or $block/@padding-right or $block/@padding-top or $block/@padding-bottom or
			@margin or @margin-left or @margin-right or @margin-top or @margin-bottom or $block/@text-align">
				<xsl:attribute name="style">
					<xsl:if test="$block/@padding">
						<xsl:text>padding: </xsl:text>
						<xsl:value-of select="$block/@padding"/>
						<xsl:text>; </xsl:text>
					</xsl:if>
					<xsl:if test="$block/@padding-left">
						<xsl:text>padding-left: </xsl:text>
						<xsl:value-of select="$block/@padding-left"/>
						<xsl:text>; </xsl:text>
					</xsl:if>
					<xsl:if test="$block/@padding-right">
						<xsl:text>padding-right: </xsl:text>
						<xsl:value-of select="$block/@padding-right"/>
						<xsl:text>; </xsl:text>
					</xsl:if>
					<xsl:if test="$block/@padding-top">
						<xsl:text>padding-top: </xsl:text>
						<xsl:value-of select="$block/@padding-top"/>
						<xsl:text>; </xsl:text>
					</xsl:if>
					<xsl:if test="$block/@padding-bottom">
						<xsl:text>padding-bottom: </xsl:text>
						<xsl:value-of select="$block/@padding-bottom"/>
						<xsl:text>; </xsl:text>
					</xsl:if>
					<xsl:if test="@margin">
						<xsl:text>margin: </xsl:text>
						<xsl:value-of select="@margin"/>
						<xsl:text>; </xsl:text>
					</xsl:if>
					<xsl:if test="@margin-left">
						<xsl:text>margin-left: </xsl:text>
						<xsl:value-of select="@margin-left"/>
						<xsl:text>; </xsl:text>
					</xsl:if>
					<xsl:if test="@margin-right">
						<xsl:text>margin-right: </xsl:text>
						<xsl:value-of select="@margin-right"/>
						<xsl:text>; </xsl:text>
					</xsl:if>
					<xsl:if test="@margin-top">
						<xsl:text>margin-top: </xsl:text>
						<xsl:value-of select="@margin-top"/>
						<xsl:text>; </xsl:text>
					</xsl:if>
					<xsl:if test="@margin-bottom">
						<xsl:text>margin-bottom: </xsl:text>
						<xsl:value-of select="@margin-bottom"/>
						<xsl:text>; </xsl:text>
					</xsl:if>
					<xsl:if test="$block/@text-align">
						<xsl:text>text-align: </xsl:text>
						<xsl:value-of select="$block/@text-align"/>
						<xsl:text>; </xsl:text>
					</xsl:if>
				</xsl:attribute>
			</xsl:if>

			<xsl:apply-templates select="node()"/>
		</li>
	</xsl:template>

	<xsl:template match="//fo:list-item-label" xmlns:fo="http://www.w3.org/1999/XSL/Format"> </xsl:template>

	<xsl:template match="//fo:list-item-body" xmlns:fo="http://www.w3.org/1999/XSL/Format">
		<xsl:apply-templates select="node()"/>
	</xsl:template>

	<xsl:template match="fo:list-item-body/fo:block" xmlns:fo="http://www.w3.org/1999/XSL/Format">
		<xsl:apply-templates select="node()"/>
	</xsl:template>
</xsl:stylesheet>
