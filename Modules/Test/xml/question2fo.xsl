<?xml version="1.0" encoding="UTF-8"?>
 <xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="xml"></xsl:output>
	<xsl:param name="pageheight" select="'29.7cm'"/>
	<xsl:param name="pagewidth" select="'21cm'"/>
	<xsl:param name="backgroundimage"/>
	<xsl:param name="marginbody" select="'1cm 1cm 1cm 1cm'"/>

	<xsl:template match="/">
		<xsl:apply-templates select="node()"></xsl:apply-templates>
	</xsl:template>
	
	<xsl:template match="body">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format" font-family="Helvetica, unifont">
			<fo:layout-master-set>
				<fo:simple-page-master>
					<xsl:attribute name="master-name"><xsl:value-of select="//title"/></xsl:attribute>
					<xsl:attribute name="page-height"><xsl:value-of select="$pageheight"></xsl:value-of></xsl:attribute>
					<xsl:attribute name="page-width"><xsl:value-of select="$pagewidth"></xsl:value-of></xsl:attribute>
						<fo:region-body>
							<xsl:attribute name="margin"><xsl:value-of select="$marginbody"/></xsl:attribute>
						</fo:region-body>
					<xsl:if test="$backgroundimage">
						<fo:region-before region-name="background-image" extent="0"/>
					</xsl:if>
				</fo:simple-page-master>
			</fo:layout-master-set>
			
			<fo:page-sequence>
				<xsl:if test="$backgroundimage">
					<fo:static-content flow-name="background-image">
						<fo:block-container absolute-position="absolute" top="0cm" left="0cm" z-index="0">
							<fo:block>
								<fo:external-graphic>
									<xsl:attribute name="src">
										<xsl:value-of select="concat('url(', $backgroundimage, ')')"></xsl:value-of>
									</xsl:attribute>
									<xsl:attribute name="content-height"><xsl:value-of select="$pageheight"></xsl:value-of></xsl:attribute>
									<xsl:attribute name="content-width"><xsl:value-of select="$pagewidth"></xsl:value-of></xsl:attribute>
								</fo:external-graphic>
							</fo:block>
						</fo:block-container>
					</fo:static-content>
				</xsl:if>	
				<xsl:attribute name="master-reference"><xsl:value-of select="//title"/></xsl:attribute>
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
			<xsl:when test="contains(@class, 'questionPrintview')">
				<fo:block xmlns:fo="http://www.w3.org/1999/XSL/Format">
					<xsl:attribute name="background-color">#F0F0F0</xsl:attribute>
					<xsl:attribute name="margin-top">1em</xsl:attribute>
					<xsl:attribute name="padding">0.25em</xsl:attribute>
					<xsl:attribute name="border-style">solid</xsl:attribute>
					<xsl:attribute name="border-color">#C0C0C0</xsl:attribute>
					<xsl:attribute name="border-width">1px</xsl:attribute>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</fo:block>
			</xsl:when>
			<xsl:when test="@id='dontprint'"></xsl:when>
			<xsl:otherwise>
				<fo:block xmlns:fo="http://www.w3.org/1999/XSL/Format">
					<xsl:apply-templates/>
				</fo:block>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="h1">
		<fo:block xmlns:fo="http://www.w3.org/1999/XSL/Format" keep-with-next="always">
			<fo:inline>
				<xsl:attribute name="font-weight">bold</xsl:attribute>
				<xsl:attribute name="font-size">18pt</xsl:attribute>
				<xsl:apply-templates/>
			</fo:inline>
		</fo:block>
	</xsl:template>

	<xsl:template match="h2">
		<fo:block xmlns:fo="http://www.w3.org/1999/XSL/Format" keep-with-next="always">
			<fo:inline>
				<xsl:attribute name="font-weight">bold</xsl:attribute>
				<xsl:attribute name="font-size">14pt</xsl:attribute>
				<xsl:apply-templates/>
			</fo:inline>
		</fo:block>
	</xsl:template>
	
	<xsl:template match="//p">
		<xsl:choose>
			<xsl:when test="@class='noprint'"></xsl:when>
			<xsl:otherwise>
				<fo:block xmlns:fo="http://www.w3.org/1999/XSL/Format">
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
						<xsl:when test="@class='questionTitle'">
							<xsl:attribute name="keep-with-next">always</xsl:attribute>
							<xsl:attribute name="border-bottom-style">solid</xsl:attribute>
							<xsl:attribute name="border-bottom-color">#333333</xsl:attribute>
							<xsl:attribute name="border-bottom-width">thin</xsl:attribute>
							<xsl:attribute name="font-weight">bold</xsl:attribute>
							<xsl:attribute name="margin-bottom">0.5em</xsl:attribute>
						</xsl:when>
					</xsl:choose>
					<xsl:apply-templates select="node()"></xsl:apply-templates>
				</fo:block>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	
	<xsl:template match="//strong">
		<fo:inline font-weight="bold" xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:apply-templates select="node()"></xsl:apply-templates>
		</fo:inline>
	</xsl:template>

	<xsl:template match="//em">
		<fo:inline font-style="italic" xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:apply-templates select="node()"></xsl:apply-templates>
		</fo:inline>
	</xsl:template>

	<xsl:template match="//font">
		<fo:inline xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:choose>
				<xsl:when test="@size='1'">
					<xsl:attribute name="font-size"><xsl:text>8pt</xsl:text></xsl:attribute>
				</xsl:when>
				<xsl:when test="@size='2'">
					<xsl:attribute name="font-size"><xsl:text>10pt</xsl:text></xsl:attribute>
				</xsl:when>
				<xsl:when test="@size='3'">
					<xsl:attribute name="font-size"><xsl:text>12pt</xsl:text></xsl:attribute>
				</xsl:when>
				<xsl:when test="@size='4'">
					<xsl:attribute name="font-size"><xsl:text>14pt</xsl:text></xsl:attribute>
				</xsl:when>
				<xsl:when test="@size='5'">
					<xsl:attribute name="font-size"><xsl:text>18pt</xsl:text></xsl:attribute>
				</xsl:when>
				<xsl:when test="@size='6'">
					<xsl:attribute name="font-size"><xsl:text>24pt</xsl:text></xsl:attribute>
				</xsl:when>
				<xsl:when test="@size='7'">
					<xsl:attribute name="font-size"><xsl:text>36pt</xsl:text></xsl:attribute>
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
		<fo:inline text-decoration="underline" xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:apply-templates select="node()"></xsl:apply-templates>
		</fo:inline>
	</xsl:template>
	
	<xsl:attribute-set name="ul">
		<xsl:attribute name="space-before">1em</xsl:attribute>
		<xsl:attribute name="space-after">1em</xsl:attribute>
	</xsl:attribute-set>
	
	<xsl:template match="ul">
		<fo:list-block xsl:use-attribute-sets="ul" xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:apply-templates select="node()"></xsl:apply-templates>
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
		<fo:list-block xsl:use-attribute-sets="ul-nested" xmlns:fo="http://www.w3.org/1999/XSL/Format">
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
			text-align="end" wrap-option="no-wrap" xmlns:fo="http://www.w3.org/1999/XSL/Format">
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
			<xsl:apply-templates select="node()"></xsl:apply-templates>
		</fo:list-block>
	</xsl:template>
	
	<xsl:template match="li//ol">
		<fo:list-block xsl:use-attribute-sets="ol-nested" xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:apply-templates select="node()"></xsl:apply-templates>
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
		<fo:list-item-label end-indent="label-end()"
			text-align="end" wrap-option="no-wrap" xmlns:fo="http://www.w3.org/1999/XSL/Format">
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
		<fo:list-item-body start-indent="body-start()" xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:block>
				<xsl:apply-templates/>
			</fo:block>
		</fo:list-item-body>
	</xsl:template>
		
	<xsl:template match="//br">
		<fo:block xmlns:fo="http://www.w3.org/1999/XSL/Format"><xsl:text disable-output-escaping="yes">&amp;#160;</xsl:text></fo:block>
	</xsl:template>
	
	<xsl:template match="table">
		<fo:table xmlns:fo="http://www.w3.org/1999/XSL/Format">
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
			
			<xsl:if test="colgroup">
					<xsl:apply-templates select="colgroup"></xsl:apply-templates>
			</xsl:if>
			<xsl:if test="thead">
				<fo:table-header xmlns:fo="http://www.w3.org/1999/XSL/Format">
					<xsl:apply-templates select="thead"></xsl:apply-templates>
				</fo:table-header>
			</xsl:if>
			<fo:table-body xmlns:fo="http://www.w3.org/1999/XSL/Format">
				<xsl:apply-templates select="tr"></xsl:apply-templates>
			</fo:table-body>
		</fo:table>
	</xsl:template>

	<xsl:template match="thead">
		<xsl:apply-templates/>
	</xsl:template>
	
	<xsl:template match="tr">
		<fo:table-row xmlns:fo="http://www.w3.org/1999/XSL/Format">
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
			</xsl:choose>
			
			<xsl:apply-templates/>
		</fo:table-row>
	</xsl:template>

	<xsl:template match="td">
		<fo:table-cell xmlns:fo="http://www.w3.org/1999/XSL/Format">
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
					<xsl:attribute name="vertical-align">top</xsl:attribute>
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
					<xsl:attribute name="vertical-align">top</xsl:attribute>
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
					<xsl:attribute name="vertical-align">top</xsl:attribute>
				</xsl:when>
				<xsl:when test="@class='middle'">
					<xsl:attribute name="vertical-align">middle</xsl:attribute>
				</xsl:when>
			</xsl:choose>
			
			<xsl:if test="@colspan">
				<xsl:attribute name="number-columns-spanned"><xsl:value-of select="@colspan"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="@rowspan">
				<xsl:attribute name="number-rows-spanned"><xsl:value-of select="@rowspan"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="@colspan">
				<xsl:attribute name="number-columns-spanned"><xsl:value-of select="@colspan"/></xsl:attribute>
			</xsl:if>
			<fo:block xmlns:fo="http://www.w3.org/1999/XSL/Format">
				<xsl:apply-templates/>
			</fo:block>
		</fo:table-cell>
	</xsl:template>
	
	<xsl:template match="th">
		<fo:table-cell xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<xsl:if test="@rowspan">
				<xsl:attribute name="number-rows-spanned"><xsl:value-of select="@rowspan"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="@colspan">
				<xsl:attribute name="number-columns-spanned"><xsl:value-of select="@colspan"/></xsl:attribute>
			</xsl:if>
			<fo:block xmlns:fo="http://www.w3.org/1999/XSL/Format">
				<xsl:apply-templates/>
			</fo:block>
		</fo:table-cell>
	</xsl:template>
	
 	<xsl:template match="colgroup">
 		<xsl:apply-templates/>
 	</xsl:template>
 	
 	<xsl:template match="col">
 		<fo:table-column xmlns:fo="http://www.w3.org/1999/XSL/Format">
 			<xsl:attribute name="column-width"><xsl:value-of select="@width"/></xsl:attribute>
 		</fo:table-column>
 	</xsl:template>
 	
 	
	<xsl:template match="img">
		<fo:inline xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:external-graphic xmlns:fo="http://www.w3.org/1999/XSL/Format">
				<xsl:attribute name="src"><xsl:value-of select="@src"/></xsl:attribute>
				<xsl:attribute name="vertical-align">middle</xsl:attribute>
				<xsl:choose>
					<xsl:when test="@class='imagemap'">
						<xsl:attribute name="width">15cm</xsl:attribute>
						<xsl:attribute name="content-width">scale-to-fit</xsl:attribute>
						<xsl:attribute name="height">auto</xsl:attribute>
						<xsl:attribute name="content-height">scale-to-fit</xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
						<xsl:choose>
							<xsl:when test="@width &gt;= @height">
								<xsl:choose>
									<xsl:when test="@width &gt; 600">
										<xsl:attribute name="width">6cm</xsl:attribute>
										<xsl:attribute name="content-width">scale-to-fit</xsl:attribute>
										<xsl:attribute name="height">auto</xsl:attribute>
										<xsl:attribute name="content-height">scale-to-fit</xsl:attribute>
									</xsl:when>
									<xsl:otherwise>
										<xsl:attribute name="width"><xsl:value-of select="(@width div 600)*6" /><xsl:text>cm</xsl:text></xsl:attribute>
										<xsl:attribute name="content-width">scale-to-fit</xsl:attribute>
										<xsl:attribute name="height">auto</xsl:attribute>
										<xsl:attribute name="content-height">scale-to-fit</xsl:attribute>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:when>
							<xsl:when test="@height &gt; @width">
								<xsl:choose>
									<xsl:when test="@height &gt; 600">
										<xsl:attribute name="width"><xsl:value-of select="6 div (@height div @width)" /><xsl:text>cm</xsl:text></xsl:attribute>
										<xsl:attribute name="content-width">scale-to-fit</xsl:attribute>
										<xsl:attribute name="height">auto</xsl:attribute>
										<xsl:attribute name="content-height">scale-to-fit</xsl:attribute>
									</xsl:when>
									<xsl:otherwise>
										<xsl:attribute name="width"><xsl:value-of select="(6 div (600 div @height)) div (@height div @width)" /><xsl:text>cm</xsl:text></xsl:attribute>
										<xsl:attribute name="content-width">scale-to-fit</xsl:attribute>
										<xsl:attribute name="height">auto</xsl:attribute>
										<xsl:attribute name="content-height">scale-to-fit</xsl:attribute>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:when>
						</xsl:choose>
					</xsl:otherwise>
				</xsl:choose>
			</fo:external-graphic>
		</fo:inline>
	</xsl:template>
	
	<xsl:template match="span">
		<fo:inline xmlns:fo="http://www.w3.org/1999/XSL/Format">
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


