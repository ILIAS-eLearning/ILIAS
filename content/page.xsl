<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
								xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
								xmlns:xhtml="http://www.w3.org/1999/xhtml">

<xsl:output method="html"/>

<!-- changing the default template to output all unknown tags -->
<xsl:template match="*">
  <xsl:copy-of select="."/>
</xsl:template>

<!-- creating a template for the root-node
		 so that everything below it will be transformed
		 different treatments for different aggregation levels
		 highest aggregation level at the moment is 2 -->

<!-- we dump the MetaData and Bibliography -->
<xsl:template match="MetaData"/>

<!-- start of explicit template declaration -->

<xsl:template match="Paragraph">
	<p id="lo_view">
		<xsl:apply-templates/>
	</p>
</xsl:template>

<xsl:template match="Item/Paragraph">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="Definition/Paragraph">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="Text">
	<xsl:apply-templates/>
</xsl:template>

</xsl:stylesheet>
