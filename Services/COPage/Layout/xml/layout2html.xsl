<?xml version="1.0"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="html"/>
	<xsl:template match="/">
		<xsl:apply-templates select="node()"/>
	</xsl:template>
	<xsl:template match="PageObject">
		<table width="120" height="160" cellcpacing="0" cellpadding="0" style="margin:3px; border-width:1px; border-style:solid;-webkit-box-shadow: 5px 5px 5px #888;-moz-box-shadow: 5px 5px 5px #888;">
			<tr>
				<td valign="top">
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
</xsl:stylesheet>
