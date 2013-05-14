<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml"/>
	<xsl:param name="pageheight" select="'29.7cm'"/>
	<xsl:param name="pagewidth" select="'21cm'"/>
	<xsl:param name="backgroundimage"/>
	<xsl:param name="marginbody" select="'0cm 1cm 0cm 1cm'"/>
	<xsl:param name="paddingtop" select="'10cm'"/>

	<xsl:template match="/">
		<xsl:apply-templates select="node()"/>
	</xsl:template>

	<xsl:template match="//body">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format" font-family="Helvetica, unifont">
			<fo:layout-master-set>
				<fo:simple-page-master master-name="ILIAS_certificate">
					<xsl:attribute name="page-height">
						<xsl:value-of select="$pageheight"/>
					</xsl:attribute>
					<xsl:attribute name="page-width">
						<xsl:value-of select="$pagewidth"/>
					</xsl:attribute>
					<fo:region-body>
						<xsl:attribute name="margin">
							<xsl:value-of select="$marginbody"/>
						</xsl:attribute>
					</fo:region-body>
					<xsl:if test="$backgroundimage">
						<fo:region-before region-name="background-image" extent="0"/>
					</xsl:if>
				</fo:simple-page-master>
			</fo:layout-master-set>

			<fo:page-sequence master-reference="ILIAS_certificate">
				<xsl:if test="$backgroundimage">
					<fo:static-content flow-name="background-image">
						<fo:block-container absolute-position="absolute" top="0cm" left="0cm"
							z-index="0">
							<fo:block>
								<fo:external-graphic>
									<xsl:attribute name="src">
										<xsl:value-of select="concat('url(', $backgroundimage, ')')"
										/>
									</xsl:attribute>
									<xsl:attribute name="content-height">
										<xsl:value-of select="$pageheight"/>
									</xsl:attribute>
									<xsl:attribute name="content-width">
										<xsl:value-of select="$pagewidth"/>
									</xsl:attribute>
								</fo:external-graphic>
							</fo:block>
						</fo:block-container>
					</fo:static-content>
				</xsl:if>
				<fo:flow>
					<xsl:attribute name="flow-name">
						<xsl:text>xsl-region-body</xsl:text>
					</xsl:attribute>
					<fo:block padding-top="10cm">
						<xsl:attribute name="padding-top">
							<xsl:value-of select="$paddingtop"/>
						</xsl:attribute>
					</fo:block>
					<fo:block>
						<xsl:apply-templates select="node()"/>
					</fo:block>
				</fo:flow>
			</fo:page-sequence>
		</fo:root>
	</xsl:template>

	<xsl:template match="//p">
		<fo:block xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:if test="contains(@style, 'font-size')">
				<xsl:attribute name="font-size">
					<xsl:call-template name="trim">
						<xsl:with-param name="s">
							<xsl:call-template name="getFontSize">
								<xsl:with-param name="s">
									<xsl:value-of
										select="substring-before(substring-after(@style, 'font-size:'), ';')"
									/>
								</xsl:with-param>
							</xsl:call-template>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'text-decoration')">
				<xsl:attribute name="text-decoration">
					<xsl:call-template name="firstelement">
						<xsl:with-param name="s">
							<xsl:call-template name="trim">
								<xsl:with-param name="s">
									<xsl:value-of
										select="substring-before(substring-after(@style, 'text-decoration:'), ';')"
									/>
								</xsl:with-param>
							</xsl:call-template>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'font-style')">
				<xsl:attribute name="font-style">
					<xsl:call-template name="firstelement">
						<xsl:with-param name="s">
							<xsl:call-template name="trim">
								<xsl:with-param name="s">
									<xsl:value-of
										select="substring-before(substring-after(@style, 'font-style:'), ';')"
									/>
								</xsl:with-param>
							</xsl:call-template>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'font-weight')">
				<xsl:attribute name="font-weight">
					<xsl:call-template name="firstelement">
						<xsl:with-param name="s">
							<xsl:call-template name="trim">
								<xsl:with-param name="s">
									<xsl:value-of
										select="substring-before(substring-after(@style, 'font-weight:'), ';')"
									/>
								</xsl:with-param>
							</xsl:call-template>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'padding-left')">
				<xsl:attribute name="padding-left">
					<xsl:call-template name="trim">
						<xsl:with-param name="s">
							<xsl:value-of
								select="substring-before(substring-after(@style, 'padding-left:'), ';')"
							/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'padding-right')">
				<xsl:attribute name="padding-right">
					<xsl:call-template name="trim">
						<xsl:with-param name="s">
							<xsl:value-of
								select="substring-before(substring-after(@style, 'padding-right:'), ';')"
							/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'padding-top')">
				<xsl:attribute name="padding-top">
					<xsl:call-template name="trim">
						<xsl:with-param name="s">
							<xsl:value-of
								select="substring-before(substring-after(@style, 'padding-top:'), ';')"
							/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'padding-bottom')">
				<xsl:attribute name="padding-bottom">
					<xsl:call-template name="trim">
						<xsl:with-param name="s">
							<xsl:value-of
								select="substring-before(substring-after(@style, 'padding-bottom:'), ';')"
							/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'padding:')">
				<xsl:attribute name="padding">
					<xsl:call-template name="trim">
						<xsl:with-param name="s">
							<xsl:value-of
								select="substring-before(substring-after(@style, 'padding:'), ';')"
							/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:choose>
				<xsl:when test="@align='left'">
					<xsl:attribute name="text-align">
						<xsl:text>left</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="contains(@style, 'text-align') and contains(@style, 'left')">
					<xsl:attribute name="text-align">
						<xsl:text>left</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="@align='right'">
					<xsl:attribute name="text-align">
						<xsl:text>right</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="contains(@style, 'text-align') and contains(@style, 'right')">
					<xsl:attribute name="text-align">
						<xsl:text>right</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="@align='center'">
					<xsl:attribute name="text-align">
						<xsl:text>center</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="contains(@style, 'text-align') and contains(@style, 'center')">
					<xsl:attribute name="text-align">
						<xsl:text>center</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="@align='justify'">
					<xsl:attribute name="text-align">
						<xsl:text>justify</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="contains(@style, 'text-align') and contains(@style, 'justify')">
					<xsl:attribute name="text-align">
						<xsl:text>justify</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="@class='emptyrow'">
					<xsl:text disable-output-escaping="yes">&amp;#160;</xsl:text>
				</xsl:when>
			</xsl:choose>
			<xsl:apply-templates select="node()"/>
		</fo:block>
	</xsl:template>

	<xsl:template match="//strong">
		<fo:inline font-weight="bold" xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:apply-templates select="node()"/>
		</fo:inline>
	</xsl:template>

	<xsl:template match="//em">
		<fo:inline font-style="italic" xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:apply-templates select="node()"/>
		</fo:inline>
	</xsl:template>

	<xsl:template match="//font">
		<fo:inline xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:choose>
				<xsl:when test="@size='1'">
					<xsl:attribute name="font-size">
						<xsl:text>8pt</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="@size='2'">
					<xsl:attribute name="font-size">
						<xsl:text>10pt</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="@size='3'">
					<xsl:attribute name="font-size">
						<xsl:text>12pt</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="@size='4'">
					<xsl:attribute name="font-size">
						<xsl:text>14pt</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="@size='5'">
					<xsl:attribute name="font-size">
						<xsl:text>18pt</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="@size='6'">
					<xsl:attribute name="font-size">
						<xsl:text>24pt</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="@size='7'">
					<xsl:attribute name="font-size">
						<xsl:text>36pt</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="contains(@style, 'xx-small')">
					<xsl:attribute name="font-size">
						<xsl:text>8pt</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="contains(@style, 'x-small')">
					<xsl:attribute name="font-size">
						<xsl:text>10pt</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="contains(@style, 'small')">
					<xsl:attribute name="font-size">
						<xsl:text>12pt</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="contains(@style, 'medium')">
					<xsl:attribute name="font-size">
						<xsl:text>14pt</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="contains(@style, 'xx-large')">
					<xsl:attribute name="font-size">
						<xsl:text>36pt</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="contains(@style, 'x-large')">
					<xsl:attribute name="font-size">
						<xsl:text>24pt</xsl:text>
					</xsl:attribute>
				</xsl:when>
				<xsl:when test="contains(@style, 'large')">
					<xsl:attribute name="font-size">
						<xsl:text>18pt</xsl:text>
					</xsl:attribute>
				</xsl:when>
			</xsl:choose>
			<xsl:if test="@face">
				<xsl:attribute name="font-family">
					<xsl:choose>
						<xsl:when test="string-length(substring-before(@face, ','))">
							<xsl:text>'</xsl:text>
							<xsl:value-of select="substring-before(@face, ',')"/>
							<xsl:text>'</xsl:text>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>'</xsl:text>
							<xsl:value-of select="@face"/>
							<xsl:text>'</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select="node()"/>
		</fo:inline>
	</xsl:template>

	<xsl:template match="//span">
		<fo:inline xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:if test="contains(@style, 'font-size')">
				<xsl:attribute name="font-size">
					<xsl:call-template name="trim">
						<xsl:with-param name="s">
							<xsl:call-template name="getFontSize">
								<xsl:with-param name="s">
									<xsl:value-of
										select="substring-before(substring-after(@style, 'font-size:'), ';')"
									/>
								</xsl:with-param>
							</xsl:call-template>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'font-family')">
				<xsl:attribute name="font-family">
					<xsl:call-template name="element">
						<xsl:with-param name="s">
							<xsl:call-template name="trim">
								<xsl:with-param name="s">
									<xsl:value-of
										select="substring-before(substring-after(@style, 'font-family:'), ';')"
									/>
								</xsl:with-param>
							</xsl:call-template>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'text-decoration')">
				<xsl:attribute name="text-decoration">
					<xsl:call-template name="firstelement">
						<xsl:with-param name="s">
							<xsl:call-template name="trim">
								<xsl:with-param name="s">
									<xsl:value-of
										select="substring-before(substring-after(@style, 'text-decoration:'), ';')"
									/>
								</xsl:with-param>
							</xsl:call-template>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'font-style')">
				<xsl:attribute name="font-style">
					<xsl:call-template name="firstelement">
						<xsl:with-param name="s">
							<xsl:call-template name="trim">
								<xsl:with-param name="s">
									<xsl:value-of
										select="substring-before(substring-after(@style, 'font-style:'), ';')"
									/>
								</xsl:with-param>
							</xsl:call-template>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'font-weight')">
				<xsl:attribute name="font-weight">
					<xsl:call-template name="firstelement">
						<xsl:with-param name="s">
							<xsl:call-template name="trim">
								<xsl:with-param name="s">
									<xsl:value-of
										select="substring-before(substring-after(@style, 'font-weight:'), ';')"
									/>
								</xsl:with-param>
							</xsl:call-template>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'color')">
				<xsl:attribute name="color">
					<xsl:call-template name="trim">
						<xsl:with-param name="s">
							<xsl:call-template name="getColor">
								<xsl:with-param name="s">
									<xsl:value-of
										select="substring-before(substring-after(@style, 'color:'), ';')"
									/>
								</xsl:with-param>
							</xsl:call-template>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'padding-left')">
				<xsl:attribute name="padding-left">
					<xsl:call-template name="trim">
						<xsl:with-param name="s">
							<xsl:value-of
								select="substring-before(substring-after(@style, 'padding-left:'), ';')"
							/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'padding-right')">
				<xsl:attribute name="padding-right">
					<xsl:call-template name="trim">
						<xsl:with-param name="s">
							<xsl:value-of
								select="substring-before(substring-after(@style, 'padding-right:'), ';')"
							/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'padding-top')">
				<xsl:attribute name="padding-top">
					<xsl:call-template name="trim">
						<xsl:with-param name="s">
							<xsl:value-of
								select="substring-before(substring-after(@style, 'padding-top:'), ';')"
							/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'padding-bottom')">
				<xsl:attribute name="padding-bottom">
					<xsl:call-template name="trim">
						<xsl:with-param name="s">
							<xsl:value-of
								select="substring-before(substring-after(@style, 'padding-bottom:'), ';')"
							/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'padding:')">
				<xsl:attribute name="padding">
					<xsl:call-template name="trim">
						<xsl:with-param name="s">
							<xsl:value-of
								select="substring-before(substring-after(@style, 'padding:'), ';')"
							/>
						</xsl:with-param>
					</xsl:call-template>
				</xsl:attribute>
			</xsl:if>

			<xsl:apply-templates select="node()"/>
		</fo:inline>
	</xsl:template>

	<xsl:template match="//u">
		<fo:inline text-decoration="underline" xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:apply-templates select="node()"/>
		</fo:inline>
	</xsl:template>

	<xsl:attribute-set name="ul">
		<xsl:attribute name="space-before">1em</xsl:attribute>
		<xsl:attribute name="space-after">1em</xsl:attribute>
	</xsl:attribute-set>

	<xsl:template match="ul">
		<fo:list-block xsl:use-attribute-sets="ul" xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:apply-templates select="node()"/>
		</fo:list-block>
	</xsl:template>

	<xsl:attribute-set name="ul-li">
		<!-- for (unordered)fo:list-item -->
		<xsl:attribute name="relative-align">baseline</xsl:attribute>
	</xsl:attribute-set>

	<xsl:template match="ul/li">
		<fo:list-item xsl:use-attribute-sets="ul-li" xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:call-template name="process-ul-li"/>
		</fo:list-item>
	</xsl:template>

	<xsl:attribute-set name="ul-nested">
		<xsl:attribute name="space-before">0pt</xsl:attribute>
		<xsl:attribute name="space-after">0pt</xsl:attribute>
	</xsl:attribute-set>

	<xsl:template match="li//ul">
		<fo:list-block xsl:use-attribute-sets="ul-nested"
			xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:apply-templates select="node()"/>
		</fo:list-block>
	</xsl:template>

	<xsl:param name="ul-label-1">&#x2022;</xsl:param>
	<xsl:attribute-set name="ul-label-1">
		<xsl:attribute name="font">1em serif</xsl:attribute>
	</xsl:attribute-set>

	<xsl:param name="ul-label-2">o</xsl:param>

	<xsl:attribute-set name="ul-label-2">
		<xsl:attribute name="font">0.67em monospace</xsl:attribute>
		<xsl:attribute name="baseline-shift">0.25em</xsl:attribute>
	</xsl:attribute-set>

	<xsl:param name="ul-label-3">-</xsl:param>
	<xsl:attribute-set name="ul-label-3">
		<xsl:attribute name="font">bold 0.9em sans-serif</xsl:attribute>

		<xsl:attribute name="baseline-shift">0.05em</xsl:attribute>
	</xsl:attribute-set>

	<xsl:template name="process-ul-li">
		<fo:list-item-label end-indent="label-end()" text-align="end" wrap-option="no-wrap"
			xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:block>
				<xsl:variable name="depth" select="count(ancestor::ul)"/>
				<xsl:choose>
					<xsl:when test="$depth = 1">

						<fo:inline xsl:use-attribute-sets="ul-label-1">
							<xsl:value-of select="$ul-label-1"/>
						</fo:inline>
					</xsl:when>
					<xsl:when test="$depth = 2">
						<fo:inline xsl:use-attribute-sets="ul-label-2">
							<xsl:value-of select="$ul-label-2"/>
						</fo:inline>
					</xsl:when>

					<xsl:otherwise>
						<fo:inline xsl:use-attribute-sets="ul-label-3">
							<xsl:value-of select="$ul-label-3"/>
						</fo:inline>
					</xsl:otherwise>
				</xsl:choose>
			</fo:block>
		</fo:list-item-label>
		<fo:list-item-body start-indent="body-start()" xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:block>
				<xsl:apply-templates/>
			</fo:block>
		</fo:list-item-body>
	</xsl:template>

	<xsl:attribute-set name="ol">
		<xsl:attribute name="space-before">1em</xsl:attribute>
		<xsl:attribute name="space-after">1em</xsl:attribute>
	</xsl:attribute-set>

	<xsl:attribute-set name="ol-nested">
		<xsl:attribute name="space-before">0pt</xsl:attribute>
		<xsl:attribute name="space-after">0pt</xsl:attribute>
	</xsl:attribute-set>

	<xsl:attribute-set name="ol-li">
		<!-- for (ordered)fo:list-item -->
		<xsl:attribute name="relative-align">baseline</xsl:attribute>
	</xsl:attribute-set>

	<xsl:template match="ol">
		<fo:list-block xsl:use-attribute-sets="ol" xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:apply-templates select="node()"/>
		</fo:list-block>
	</xsl:template>

	<xsl:template match="li//ol">
		<fo:list-block xsl:use-attribute-sets="ol-nested"
			xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:apply-templates select="node()"/>
		</fo:list-block>
	</xsl:template>

	<xsl:template match="ol/li">
		<fo:list-item xsl:use-attribute-sets="ol-li" xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:call-template name="process-ol-li"/>
		</fo:list-item>
	</xsl:template>

	<xsl:param name="ol-label-1">1.</xsl:param>
	<xsl:attribute-set name="ol-label-1"/>

	<xsl:param name="ol-label-2">a.</xsl:param>
	<xsl:attribute-set name="ol-label-2"/>

	<xsl:param name="ol-label-3">i.</xsl:param>
	<xsl:attribute-set name="ol-label-3"/>

	<xsl:template name="process-ol-li">
		<fo:list-item-label end-indent="label-end()" text-align="end" wrap-option="no-wrap"
			xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:block>
				<xsl:variable name="depth" select="count(ancestor::ol)"/>
				<xsl:choose>

					<xsl:when test="$depth = 1">
						<fo:inline xsl:use-attribute-sets="ol-label-1">
							<xsl:number format="{$ol-label-1}"/>
						</fo:inline>
					</xsl:when>
					<xsl:when test="$depth = 2">
						<fo:inline xsl:use-attribute-sets="ol-label-2">
							<xsl:number format="{$ol-label-2}"/>
						</fo:inline>

					</xsl:when>
					<xsl:otherwise>
						<fo:inline xsl:use-attribute-sets="ol-label-3">
							<xsl:number format="{$ol-label-3}"/>
						</fo:inline>
					</xsl:otherwise>
				</xsl:choose>
			</fo:block>
		</fo:list-item-label>
		<fo:list-item-body start-indent="body-start()" xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:block>
				<xsl:apply-templates/>
			</fo:block>
		</fo:list-item-body>
	</xsl:template>

	<xsl:template match="//br">
		<fo:block xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:text disable-output-escaping="yes">&amp;#160;</xsl:text>
		</fo:block>
	</xsl:template>

	<xsl:template name="left-trim">
		<xsl:param name="s"/>
		<xsl:choose>
			<xsl:when test="substring($s, 1, 1) = ''">
				<xsl:value-of select="$s"/>
			</xsl:when>
			<xsl:when test="normalize-space(substring($s, 1, 1)) = ''">
				<xsl:call-template name="left-trim">
					<xsl:with-param name="s" select="substring($s, 2)"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$s"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="right-trim">
		<xsl:param name="s"/>
		<xsl:choose>
			<xsl:when test="substring($s, 1, 1) = ''">
				<xsl:value-of select="$s"/>
			</xsl:when>
			<xsl:when test="normalize-space(substring($s, string-length($s))) = ''">
				<xsl:call-template name="right-trim">
					<xsl:with-param name="s" select="substring($s, 1, string-length($s) - 1)"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$s"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="trim">
		<xsl:param name="s"/>
		<xsl:call-template name="right-trim">
			<xsl:with-param name="s">
				<xsl:call-template name="left-trim">
					<xsl:with-param name="s" select="$s"/>
				</xsl:call-template>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template name="getFontSize">
		<xsl:param name="s"/>
		<xsl:choose>
			<xsl:when test="contains($s, 'xx-small')">
				<xsl:text>8pt</xsl:text>
			</xsl:when>
			<xsl:when test="contains($s, 'x-small')">
				<xsl:text>10pt</xsl:text>
			</xsl:when>
			<xsl:when test="contains($s, 'small')">
				<xsl:text>12pt</xsl:text>
			</xsl:when>
			<xsl:when test="contains($s, 'medium')">
				<xsl:text>14pt</xsl:text>
			</xsl:when>
			<xsl:when test="contains($s, 'xx-large')">
				<xsl:text>36pt</xsl:text>
			</xsl:when>
			<xsl:when test="contains($s, 'x-large')">
				<xsl:text>24pt</xsl:text>
			</xsl:when>
			<xsl:when test="contains($s, 'large')">
				<xsl:text>18pt</xsl:text>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of
					select="$s"
				/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template name="getColor">
		<xsl:param name="s"/>
		<xsl:value-of select="$s" />
	</xsl:template>

	<xsl:template name="firstelement">
		<xsl:param name="s"/>
		<xsl:choose>
			<xsl:when test="string-length(substring-before($s, ','))">
				<xsl:value-of select="substring-before($s, ',')"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$s"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="element">
		<xsl:param name="s" />
		<xsl:value-of select="$s" />
	</xsl:template>

</xsl:stylesheet>
