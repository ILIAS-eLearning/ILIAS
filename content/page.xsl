<?xml version="1.0"?>
<xsl:stylesheet version="1.0"
								xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
								xmlns:xhtml="http://www.w3.org/1999/xhtml">

<xsl:output method="html"/>

<!-- changing the default template to output all unknown tags -->
<xsl:template match="*">
  <xsl:copy-of select="."/>
</xsl:template>

<!-- we dump the MetaData and Bibliography -->
<xsl:template match="MetaData"/>

<!-- start of explicit template declaration -->
<xsl:template match="PageObject">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="Paragraph">
	<p class="ilParagraph">
		<input type="checkbox" name="target[]">
			<xsl:attribute name="value"><xsl:value-of select="position()"/>
			</xsl:attribute>
		</input>
		<xsl:apply-templates/>
		<select size="1" class="ilEditSelect">
			<xsl:attribute name="name">command<xsl:value-of select="position()"/>
			</xsl:attribute>
		<option value="edit">edit</option>
		<option value="insert">insert</option>
		<option value="delete">delete</option>
		<option value="moveAfter">move after</option>
		<option value="moveBefore">move before</option>
		</select>
		<input class="ilEditSubmit" type="submit" value="Go">
			<xsl:attribute name="name">cmd[exec_<xsl:value-of select="position()"/>]
			</xsl:attribute>
		</input>
	</p>
</xsl:template>

<!--
<xsl:template match="Item/Paragraph">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="Definition/Paragraph">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="Text">
	<xsl:apply-templates/>
</xsl:template>-->

</xsl:stylesheet>
