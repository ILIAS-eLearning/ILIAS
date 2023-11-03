<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="html"/>
	<xsl:template match="/">
		<xsl:apply-templates select="node()"/>
	</xsl:template>
	<xsl:template match="PageObject">
		<table class="il-style-layout-preview-wrapper">
			<tr>
				<td valign="top" style="padding-left:0">
					<xsl:apply-templates/>
				</td>
			</tr>
		</table>
	</xsl:template>
<!-- PageContent -->
	<xsl:template match="PageContent">
		<xsl:apply-templates/>
	</xsl:template>
	<xsl:template match="PlaceHolder">
		<xsl:if test="@ContentClass='Media'">
			<div class="ilc_MediaPlaceHolderThumb">
				<xsl:attribute name="style">
					<xsl:if test="@Height != ''"> height:<xsl:value-of select="@Height"/>; </xsl:if>
					<xsl:if test="@Width != ''"> width:<xsl:value-of select="@Width"/>; </xsl:if>
				</xsl:attribute>
			</div>
		</xsl:if>
		<xsl:if test="@ContentClass='Text'">
			<div class="ilc_TextPlaceHolderThumb">
				<xsl:attribute name="style">
					<xsl:if test="@Height != ''"> height:<xsl:value-of select="@Height"/>; </xsl:if>
					<xsl:if test="@Width != ''"> width:<xsl:value-of select="@Width"/>; </xsl:if>
				</xsl:attribute>
			</div>
		</xsl:if>
		<xsl:if test="@ContentClass='Question'">
			<div class="ilc_QuestionPlaceHolderThumb">
				<xsl:attribute name="style">
					<xsl:if test="@Height != ''"> height:<xsl:value-of select="@Height"/>; </xsl:if>
					<xsl:if test="@Width != ''"> width:<xsl:value-of select="@Width"/>; </xsl:if>
				</xsl:attribute>
			</div>
		</xsl:if>
	</xsl:template>
<!-- Paragraph -->
	<xsl:template match="Paragraph">
		<xsl:if test="@Characteristic = 'Headline1' or @Characteristic = 'Headline2' or @Characteristic = 'Headline3'">
		<div>
			<xsl:attribute name="class">ilc_HeadlineThumb</xsl:attribute>
			<xsl:apply-templates/>
		</div>
		</xsl:if>
		<xsl:if test="@Characteristic != 'Headline1' and @Characteristic != 'Headline2' and @Characteristic != 'Headline3'">
		<div class="ilc_PredTextPlaceHolderThumb" style="height:10px;">
		</div>
		</xsl:if>
	</xsl:template>
	<xsl:template match="Table">
		<table style="border:1px;" cellpadding="0" cellspacing="0">
			<xsl:attribute name="border">0</xsl:attribute>
			<xsl:if test="@Width">
				<xsl:attribute name="width">
					<xsl:value-of select="@Width"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select="TableRow"/>
		</table>
	</xsl:template>
	<xsl:template match="TableRow">
		<tr>
			<xsl:apply-templates/>
		</tr>
	</xsl:template>
	<xsl:template match="TableData">
		<td valign="top">
			<xsl:if test="@Width">
				<xsl:attribute name="width">
					<xsl:value-of select="@Width"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates/>
		</td>
	</xsl:template>
	<xsl:template match="Grid">
		<div class="row row-flex row-flex-wrap" style="margin:0">
			<xsl:apply-templates select="GridCell"/>
		</div>
	</xsl:template>

	<!-- GridCell -->
	<xsl:template match="GridCell">
		<div style="padding-left:0; padding-right:0">
			<xsl:attribute name="class">
				<xsl:if test="@WIDTH_S != ''"> col-xs-<xsl:value-of select="@WIDTH_S"/></xsl:if>
				<xsl:if test="@WIDTH_M != ''"> col-sm-<xsl:value-of select="@WIDTH_M"/></xsl:if>
				<xsl:if test="@WIDTH_L != ''"> col-md-<xsl:value-of select="@WIDTH_L"/></xsl:if>
				<xsl:if test="@WIDTH_XL != ''"> col-lg-<xsl:value-of select="@WIDTH_XL"/></xsl:if>
				<xsl:if test="@WIDTH_S = '' and @WIDTH_M = '' and @WIDTH_L = '' and @WIDTH_XL = ''">col-xs-12</xsl:if>
			</xsl:attribute>
			<div class="flex-col flex-grow">
				<div style="height:100%">	<!-- this div enforces margin collapsing, see bug 31536, for height see 32067 -->
					<xsl:apply-templates select="PageContent"/>
					<xsl:comment>End of Grid Cell</xsl:comment>
				</div>
			</div>
		</div>
	</xsl:template>
</xsl:stylesheet>
