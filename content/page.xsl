<?xml version="1.0" encoding="UTF-8"?>
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

<!-- PageObject -->
<xsl:param name="mode"/>
<xsl:template match="PageObject">
	<xsl:if test="$mode = 'edit'">
		<select size="1" class="ilEditSelect">
			<xsl:attribute name="name">command0</xsl:attribute>
		<option value="insert">insert</option>
		</select>
		<input class="ilEditSubmit" type="submit" value="Go">
			<xsl:attribute name="name">cmd[exec_0]</xsl:attribute>
		</input>
	</xsl:if>
	<xsl:apply-templates/>
</xsl:template>

<!-- Paragraph -->
<xsl:template match="Paragraph">
	<p class="ilParagraph">
		<!-- checkbox -->
		<xsl:if test="$mode = 'edit'">
			<input type="checkbox" name="target[]">
				<xsl:attribute name="value"><xsl:value-of select="@ed_id"/>
				</xsl:attribute>
			</input>
		</xsl:if>
		<!-- content -->
		<xsl:apply-templates/>
		<!-- command selectbox -->
		<xsl:if test="$mode = 'edit'">
			<select size="1" class="ilEditSelect">
				<xsl:attribute name="name">command<xsl:value-of select="@ed_id"/>
				</xsl:attribute>
			<option value="edit">edit</option>
			<option value="insert">insert</option>
			<option value="delete">delete</option>
			<option value="moveAfter">move after</option>
			<option value="moveBefore">move before</option>
			</select>
			<input class="ilEditSubmit" type="submit" value="Go">
				<xsl:attribute name="name">cmd[exec_<xsl:value-of select="@ed_id"/>]
				</xsl:attribute>
			</input>
		</xsl:if>
	</p>
</xsl:template>

<xsl:template match="Emph|Strong|Comment">
	<xsl:variable name="Tagname" select="name()"/>
	<span class="il{$Tagname}"><xsl:apply-templates/></span>
</xsl:template>

<xsl:template match="Table">
	<xsl:for-each select="Title">
		<xsl:value-of select="."/>
	<br/>
	</xsl:for-each>
	<table class="Table" id="lo_view" border="1">
	<xsl:for-each select="TableRow">
		<tr class="TableRow" id="lo_view" valign="top">
			<xsl:for-each select="TableData">
				<td class="TableData" id="lo_view">
					<!-- insert commands -->
					<xsl:if test="$mode = 'edit'">
						<select size="1" class="ilEditSelect">
							<xsl:attribute name="name">command<xsl:value-of select="@ed_id"/>
							</xsl:attribute>
						<option value="insert">insert</option>
						</select>
						<input class="ilEditSubmit" type="submit" value="Go">
							<xsl:attribute name="name">cmd[exec_<xsl:value-of select="@ed_id"/>]</xsl:attribute>
						</input>
					</xsl:if>
					<!-- content -->
					<xsl:apply-templates/>
				</td>
			</xsl:for-each>
		</tr>
	</xsl:for-each>
	</table>
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
