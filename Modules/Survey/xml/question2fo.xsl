<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
	<xsl:output method="xml"></xsl:output>
	<xsl:param name="pageheight" select="'29.7cm'"/>
	<xsl:param name="pagewidth" select="'21cm'"/>
	<xsl:param name="backgroundimage"/>
	<xsl:param name="marginbody" select="'1cm 1cm 1cm 1cm'"/>

	<xsl:template match="/">
		<xsl:apply-templates select="node()"></xsl:apply-templates>
	</xsl:template>
	
	<xsl:template match="body">
		<fo:root font-family="Helvetica, unifont">
			<fo:layout-master-set>
				<fo:simple-page-master>
					<xsl:attribute name="master-name"><xsl:value-of select="//title"/></xsl:attribute>
					<xsl:attribute name="page-height"><xsl:value-of select="$pageheight"></xsl:value-of></xsl:attribute>
					<xsl:attribute name="page-width"><xsl:value-of select="$pagewidth"></xsl:value-of></xsl:attribute>
						<fo:region-body>
							<xsl:attribute name="margin"><xsl:value-of select="$marginbody"/></xsl:attribute>
							<xsl:if test="$backgroundimage">
								<xsl:attribute name="background-image">
									<xsl:value-of select="concat('url(', $backgroundimage, ')')"></xsl:value-of>
								</xsl:attribute>
							</xsl:if>
						</fo:region-body>
				</fo:simple-page-master>
			</fo:layout-master-set>
			
			<fo:page-sequence>
				<xsl:attribute name="master-reference"><xsl:value-of select="//title"/></xsl:attribute>
				<xsl:attribute name="font-size">60%</xsl:attribute>
				<xsl:attribute name="line-height">1.4em</xsl:attribute>
				<fo:flow>
					<xsl:attribute name="flow-name"><xsl:text>xsl-region-body</xsl:text></xsl:attribute>
					<xsl:apply-templates/>
				</fo:flow>
			</fo:page-sequence>
		</fo:root>
	</xsl:template>
	
	<xsl:template match="title"></xsl:template>
	
	<xsl:template match="//div">
		<xsl:choose>
			<xsl:when test="@class='solutionbox'">
				<fo:block>
					<xsl:attribute name="border-width">1</xsl:attribute>
					<xsl:attribute name="border-style">solid</xsl:attribute>
					<xsl:attribute name="border-color">#808080</xsl:attribute>
					<xsl:attribute name="margin-top">1em</xsl:attribute>
					<xsl:attribute name="margin-left">0.25em</xsl:attribute>
					<xsl:attribute name="margin-right">0.25em</xsl:attribute>
					<xsl:attribute name="padding-left">0.25em</xsl:attribute>
					<xsl:attribute name="padding-right">0.25em</xsl:attribute>
					<xsl:attribute name="padding-top">2px</xsl:attribute>
					<xsl:attribute name="padding-bottom">2px</xsl:attribute>
					<xsl:attribute name="background-color">#FFFFFF</xsl:attribute>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</fo:block>
			</xsl:when>
			<xsl:when test="@class='surveySheet'">
				<fo:block>
					<!-- <xsl:attribute name="page-break-inside">avoid</xsl:attribute> -->
					<xsl:attribute name="background-color">#F0F0F0</xsl:attribute>
					<xsl:attribute name="margin-top">1em</xsl:attribute>
					<xsl:attribute name="padding">0.25em</xsl:attribute>
					<xsl:attribute name="border-style">solid</xsl:attribute>
					<xsl:attribute name="border-color">#C0C0C0</xsl:attribute>
					<xsl:attribute name="border-width">1px</xsl:attribute>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</fo:block>
			</xsl:when>
			<xsl:when test="@class='questionTitle'">
				<fo:block>
					<xsl:attribute name="border-bottom-style">solid</xsl:attribute>
					<xsl:attribute name="border-bottom-color">#333333</xsl:attribute>
					<xsl:attribute name="border-bottom-width">thin</xsl:attribute>
					<xsl:attribute name="font-weight">bold</xsl:attribute>
					<xsl:attribute name="padding-top">1em</xsl:attribute>
					<xsl:attribute name="margin-bottom">0.5em</xsl:attribute>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</fo:block>
			</xsl:when>
			<xsl:when test="@class='questionblockTitle'">
				<fo:block>
					<xsl:attribute name="text-align">center</xsl:attribute>
					<xsl:attribute name="font-style">italic</xsl:attribute>
					<xsl:attribute name="padding-top">0.5em</xsl:attribute>
					<xsl:attribute name="padding-bottom">0.5em</xsl:attribute>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</fo:block>
			</xsl:when>
			<xsl:otherwise>
				<fo:block>
					<xsl:apply-templates/>
				</fo:block>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="h1">
		<fo:block>
			<fo:inline>
				<xsl:attribute name="font-weight">bold</xsl:attribute>
				<xsl:attribute name="font-size">125$</xsl:attribute>
				<xsl:apply-templates/>
			</fo:inline>
		</fo:block>
	</xsl:template>

	<xsl:template match="h2">
		<fo:block>
			<fo:inline>
				<xsl:attribute name="font-weight">bold</xsl:attribute>
				<xsl:attribute name="font-size">110%</xsl:attribute>
				<xsl:apply-templates/>
			</fo:inline>
		</fo:block>
	</xsl:template>
	
	<xsl:template match="//p">
		<xsl:choose>
			<xsl:when test="@class='noprint'"></xsl:when>
			<xsl:otherwise>
				<fo:block>
					<xsl:choose>
						<xsl:when test="@align='left'">
							<xsl:attribute name="text-align">
								<xsl:text>left</xsl:text>
							</xsl:attribute>
						</xsl:when>
						<xsl:when test="@align='right'">
							<xsl:attribute name="text-align">
								<xsl:text>right</xsl:text>
							</xsl:attribute>
						</xsl:when>
						<xsl:when test="@align='center'">
							<xsl:attribute name="text-align">
								<xsl:text>center</xsl:text>
							</xsl:attribute>
						</xsl:when>
						<xsl:when test="@align='justify'">
							<xsl:attribute name="text-align">
								<xsl:text>justify</xsl:text>
							</xsl:attribute>
						</xsl:when>
					</xsl:choose>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</fo:block>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="//strong">
		<fo:inline font-weight="bold">
			<xsl:apply-templates select="node()"></xsl:apply-templates>
		</fo:inline>
	</xsl:template>

	<xsl:template match="//em">
		<fo:inline font-style="italic">
			<xsl:apply-templates select="node()"></xsl:apply-templates>
		</fo:inline>
	</xsl:template>

	<xsl:template match="//font">
		<fo:inline>
			<xsl:choose>
				<xsl:when test="@size='1'">
					<xsl:attribute name="font-size"><xsl:text>80%</xsl:text></xsl:attribute>
				</xsl:when>
				<xsl:when test="@size='2'">
					<xsl:attribute name="font-size"><xsl:text>90%</xsl:text></xsl:attribute>
				</xsl:when>
				<xsl:when test="@size='3'">
					<xsl:attribute name="font-size"><xsl:text>100%</xsl:text></xsl:attribute>
				</xsl:when>
				<xsl:when test="@size='4'">
					<xsl:attribute name="font-size"><xsl:text>110%</xsl:text></xsl:attribute>
				</xsl:when>
				<xsl:when test="@size='5'">
					<xsl:attribute name="font-size"><xsl:text>120%</xsl:text></xsl:attribute>
				</xsl:when>
				<xsl:when test="@size='6'">
					<xsl:attribute name="font-size"><xsl:text>130%</xsl:text></xsl:attribute>
				</xsl:when>
				<xsl:when test="@size='7'">
					<xsl:attribute name="font-size"><xsl:text>140%</xsl:text></xsl:attribute>
				</xsl:when>
			</xsl:choose>
			<xsl:if test="@face">
				<xsl:attribute name="font-family">
					<xsl:choose>
						<xsl:when test="string-length(substring-before(@face, ','))">
							<xsl:text>'</xsl:text><xsl:value-of select="substring-before(@face, ',')"></xsl:value-of><xsl:text>'</xsl:text>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>'</xsl:text><xsl:value-of select="@face"></xsl:value-of><xsl:text>'</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select="node()"></xsl:apply-templates>
		</fo:inline>
	</xsl:template>
	
	<xsl:template match="//u">
		<fo:inline text-decoration="underline">
			<xsl:apply-templates select="node()"></xsl:apply-templates>
		</fo:inline>
	</xsl:template>
	
	<xsl:attribute-set name="ul">
		<xsl:attribute name="space-before">1em</xsl:attribute>
		<xsl:attribute name="space-after">1em</xsl:attribute>
	</xsl:attribute-set>
	
	<xsl:template match="ul">
		<fo:list-block xsl:use-attribute-sets="ul">
			<xsl:apply-templates select="node()"></xsl:apply-templates>
		</fo:list-block>
	</xsl:template>
	
	<xsl:attribute-set name="ul-li">
		<!-- for (unordered)fo:list-item -->
		<xsl:attribute name="relative-align">baseline</xsl:attribute>
	</xsl:attribute-set>

	<xsl:template match="ul/li">
		<fo:list-item xsl:use-attribute-sets="ul-li">
			<xsl:call-template name="process-ul-li"/>
		</fo:list-item>
	</xsl:template>
	
	<xsl:attribute-set name="ul-nested">
		<xsl:attribute name="space-before">0pt</xsl:attribute>
		<xsl:attribute name="space-after">0pt</xsl:attribute>	
	</xsl:attribute-set>
	
	<xsl:template match="li//ul">
		<fo:list-block xsl:use-attribute-sets="ul-nested">
			<xsl:apply-templates select="node()"></xsl:apply-templates>
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
		<fo:list-item-label end-indent="label-end()"
			text-align="end" wrap-option="no-wrap">
			<fo:block>
				<xsl:variable name="depth" select="count(ancestor::ul)" />
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
		<fo:list-item-body start-indent="body-start()">
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
		<fo:list-block xsl:use-attribute-sets="ol">
			<xsl:apply-templates select="node()"></xsl:apply-templates>
		</fo:list-block>
	</xsl:template>
	
	<xsl:template match="li//ol">
		<fo:list-block xsl:use-attribute-sets="ol-nested">
			<xsl:apply-templates select="node()"></xsl:apply-templates>
		</fo:list-block>
	</xsl:template>
	
	<xsl:template match="ol/li">
		<fo:list-item xsl:use-attribute-sets="ol-li">
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
		<fo:list-item-label end-indent="label-end()"
			text-align="end" wrap-option="no-wrap">
			<fo:block>
				<xsl:variable name="depth" select="count(ancestor::ol)" />
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
		<fo:list-item-body start-indent="body-start()">
			<fo:block>
				<xsl:apply-templates/>
			</fo:block>
		</fo:list-item-body>
	</xsl:template>
		
	<xsl:template match="//br">
		<fo:block><xsl:text disable-output-escaping="yes">&amp;#160;</xsl:text></fo:block>
	</xsl:template>
	
	<xsl:template match="table">
		<fo:table>
			<xsl:choose>
				<xsl:when test="@class='fullwidth'">
					<xsl:attribute name="color">#222222</xsl:attribute>
					<xsl:attribute name="background-color">#bbbbbb</xsl:attribute>
					<xsl:attribute name="width">100%</xsl:attribute>
					<xsl:attribute name="border-spacing">0px</xsl:attribute>
					<xsl:attribute name="border-collapse">collapse</xsl:attribute>
					<xsl:attribute name="border-width">1px</xsl:attribute>
					<xsl:attribute name="border-style">solid</xsl:attribute>
					<xsl:attribute name="border-color">#9EADBA</xsl:attribute>
				</xsl:when>
			</xsl:choose>
			
			<xsl:if test="thead">
				<fo:table-header>
					<xsl:apply-templates select="thead"></xsl:apply-templates>
				</fo:table-header>
			</xsl:if>
			<fo:table-body>
				<xsl:apply-templates select="tr"></xsl:apply-templates>
			</fo:table-body>
		</fo:table>
	</xsl:template>

	<xsl:template match="thead">
		<xsl:apply-templates/>
	</xsl:template>
	
	<xsl:template match="tr">
		<fo:table-row>
			<xsl:choose>
				<xsl:when test="@class='tblheader'">
					<xsl:attribute name="color">#000000</xsl:attribute>
					<xsl:attribute name="font-weight">bold</xsl:attribute>
					<xsl:attribute name="background-color">#E2EAF4</xsl:attribute>
					<xsl:attribute name="border-top-width">1px</xsl:attribute>
					<xsl:attribute name="border-top-color">#9EADBA</xsl:attribute>
					<xsl:attribute name="border-top-style">solid</xsl:attribute>
				</xsl:when>
				<xsl:when test="@class='tblfooter'">
					<xsl:attribute name="color">#000000</xsl:attribute>
					<xsl:attribute name="font-weight">normal</xsl:attribute>
					<xsl:attribute name="background-color">#EDEDED</xsl:attribute>
					<xsl:attribute name="border-top-width">1px</xsl:attribute>
					<xsl:attribute name="border-top-color">#9EADBA</xsl:attribute>
					<xsl:attribute name="border-top-style">solid</xsl:attribute>
				</xsl:when>
				<xsl:when test="@class='tblrow1'">
					<xsl:attribute name="background-color">#FFFFFF</xsl:attribute>
					<xsl:attribute name="color">#222222</xsl:attribute>
					<xsl:attribute name="padding">3px</xsl:attribute>
				</xsl:when>
				<xsl:when test="@class='tblrow2'">
					<xsl:attribute name="background-color">#F1F1F1</xsl:attribute>
					<xsl:attribute name="color">#222222</xsl:attribute>
					<xsl:attribute name="padding">3px</xsl:attribute>
				</xsl:when>
			</xsl:choose>
			
			<xsl:apply-templates/>
		</fo:table-row>
	</xsl:template>

	<xsl:template match="td">
		<fo:table-cell>
			<xsl:choose>
				<xsl:when test="@class='tblrow1'">
					<xsl:attribute name="background-color">#FFFFFF</xsl:attribute>
					<xsl:attribute name="color">#222222</xsl:attribute>
					<xsl:attribute name="padding">3px</xsl:attribute>
				</xsl:when>
				<xsl:when test="@class='tblrow1top'">
					<xsl:attribute name="background-color">#FFFFFF</xsl:attribute>
					<xsl:attribute name="color">#222222</xsl:attribute>
					<xsl:attribute name="padding">3px</xsl:attribute>
				</xsl:when>
				<xsl:when test="@class='tblrow2'">
					<xsl:attribute name="background-color">#F1F1F1</xsl:attribute>
					<xsl:attribute name="color">#222222</xsl:attribute>
					<xsl:attribute name="padding">3px</xsl:attribute>
				</xsl:when>
				<xsl:when test="@class='tblrow2top'">
					<xsl:attribute name="background-color">#F1F1F1</xsl:attribute>
					<xsl:attribute name="color">#222222</xsl:attribute>
					<xsl:attribute name="padding">3px</xsl:attribute>
				</xsl:when>
				<xsl:when test="@class='tblrowmarked'">
					<xsl:attribute name="background-color">#FFE4E4</xsl:attribute>
					<xsl:attribute name="color">#222222</xsl:attribute>
					<xsl:attribute name="padding">3px</xsl:attribute>
				</xsl:when>
				<xsl:when test="@class='tblrowmarkedtop'">
					<xsl:attribute name="background-color">#FFE4E4</xsl:attribute>
					<xsl:attribute name="color">#222222</xsl:attribute>
					<xsl:attribute name="padding">3px</xsl:attribute>
				</xsl:when>
				<xsl:when test="@class='bipolar'">
					<xsl:attribute name="background-color">#FFE4E4</xsl:attribute>
					<xsl:attribute name="color">#222222</xsl:attribute>
					<xsl:attribute name="padding">3px</xsl:attribute>
					<xsl:attribute name="text-align">center</xsl:attribute>
					<xsl:attribute name="display-align">center</xsl:attribute>
					<xsl:attribute name="border-width">1px</xsl:attribute>
					<xsl:attribute name="border-style">solid</xsl:attribute>
					<xsl:attribute name="border-color">#9EADBA</xsl:attribute>
				</xsl:when>
				<xsl:when test="@class='middle'">
					<xsl:attribute name="display-align">center</xsl:attribute>
				</xsl:when>
				<xsl:when test="@class='center'">
					<xsl:attribute name="display-align">center</xsl:attribute>
				</xsl:when>
			</xsl:choose>

			<xsl:if test="contains(@style, 'border-right')">
				<xsl:attribute name="border-right-width">1px</xsl:attribute>
				<xsl:attribute name="border-right-style">solid</xsl:attribute>
				<xsl:attribute name="border-right-color">#808080</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'border-left')">
				<xsl:attribute name="border-left-width">1px</xsl:attribute>
				<xsl:attribute name="border-left-style">solid</xsl:attribute>
				<xsl:attribute name="border-left-color">#808080</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'border-top')">
				<xsl:attribute name="border-top-width">1px</xsl:attribute>
				<xsl:attribute name="border-top-style">solid</xsl:attribute>
				<xsl:attribute name="border-top-color">#808080</xsl:attribute>
			</xsl:if>
			<xsl:if test="contains(@style, 'border-bottom')">
				<xsl:attribute name="border-bottom-width">1px</xsl:attribute>
				<xsl:attribute name="border-bottom-style">solid</xsl:attribute>
				<xsl:attribute name="border-bottom-color">#808080</xsl:attribute>
			</xsl:if>
			
			<xsl:if test="@width">
				<xsl:attribute name="width"><xsl:value-of select="@width"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="@rowspan">
				<xsl:attribute name="number-rows-spanned"><xsl:value-of select="@rowspan"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="@colspan">
				<xsl:attribute name="number-columns-spanned"><xsl:value-of select="@colspan"/></xsl:attribute>
			</xsl:if>
			<fo:block>
				<xsl:choose>
					<xsl:when test="@class='center'">
						<xsl:attribute name="text-align">center</xsl:attribute>
					</xsl:when>
					<xsl:when test="@align='center'">
						<xsl:attribute name="text-align">center</xsl:attribute>
					</xsl:when>
					<xsl:when test="@class='column'">
						<xsl:attribute name="text-align">center</xsl:attribute>
					</xsl:when>
				</xsl:choose>
				<xsl:apply-templates/>
			</fo:block>
		</fo:table-cell>
	</xsl:template>
	
	<xsl:template match="th">
		<fo:table-cell>
			<xsl:if test="@width">
				<xsl:attribute name="width"><xsl:value-of select="@width"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="@rowspan">
				<xsl:attribute name="number-rows-spanned"><xsl:value-of select="@rowspan"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="@colspan">
				<xsl:attribute name="number-columns-spanned"><xsl:value-of select="@colspan"/></xsl:attribute>
			</xsl:if>
			<fo:block>
				<xsl:apply-templates/>
			</fo:block>
		</fo:table-cell>
	</xsl:template>
	
	<xsl:template match="img">
		<fo:inline>
			<fo:external-graphic>
				<xsl:attribute name="src"><xsl:value-of select="@src"/></xsl:attribute>
				<xsl:attribute name="vertical-align">middle</xsl:attribute>
				<xsl:if test="@width">
					<xsl:choose>
						<xsl:when test="@width &gt; 700">
							<xsl:attribute name="content-width"><xsl:value-of select="round(@width * (700 div @width))"/></xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="content-width"><xsl:value-of select="@width"/></xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
				<xsl:if test="@height">
					<xsl:choose>
						<xsl:when test="@width &gt; 700">
							<xsl:attribute name="content-height"><xsl:value-of select="round(@height * (700 div @width))"/></xsl:attribute>
						</xsl:when>
						<xsl:otherwise>
							<xsl:attribute name="content-height"><xsl:value-of select="@height"/></xsl:attribute>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
				<xsl:if test="@style='border-style: inset; border-width: 1px;'">
					<xsl:attribute name="border-style">inset</xsl:attribute>
					<xsl:attribute name="border-width">1px</xsl:attribute>
				</xsl:if>
				<xsl:if test="@style='vertical-align: top;'">
					<xsl:attribute name="vertical-align">top</xsl:attribute>
				</xsl:if>
			</fo:external-graphic>
		</fo:inline>
	</xsl:template>
	
	<xsl:template match="span">
		<fo:inline>
			<xsl:choose>
				<xsl:when test="@class='solutionbox'">
					<xsl:attribute name="border-width">1</xsl:attribute>
					<xsl:attribute name="border-style">solid</xsl:attribute>
					<xsl:attribute name="border-color">#808080</xsl:attribute>
					<xsl:attribute name="padding-left">0.25em</xsl:attribute>
					<xsl:attribute name="padding-right">0.25em</xsl:attribute>
					<xsl:attribute name="padding-top">2px</xsl:attribute>
					<xsl:attribute name="padding-bottom">2px</xsl:attribute>
					<xsl:attribute name="background-color">#FFFFFF</xsl:attribute>
				</xsl:when>
				<xsl:when test="@class='nowrap'">
					<xsl:attribute name="white-space">nowrap</xsl:attribute>
				</xsl:when>
			</xsl:choose>
			<xsl:apply-templates/>
		</fo:inline>
	</xsl:template>
</xsl:stylesheet>