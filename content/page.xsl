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
			<xsl:attribute name="name">command<xsl:value-of select="@HierId"/></xsl:attribute>
			<option value="insert_par">insert Paragr.</option>
			<option value="insert_tab">insert Table</option>
		</select>
		<input class="ilEditSubmit" type="submit" value="Go">
			<xsl:attribute name="name">cmd[exec_<xsl:value-of select="@HierId"/>]</xsl:attribute>
		</input>
		<br/>
	</xsl:if>
	<xsl:apply-templates/>
</xsl:template>

<!-- Paragraph -->
<xsl:template match="Paragraph">
	<p class="ilParagraph">
		<!-- <xsl:value-of select="@HierId"/> -->
		<!-- checkbox -->
		<!--
		<xsl:if test="$mode = 'edit'">
			<input type="checkbox" name="target[]">
				<xsl:attribute name="value"><xsl:value-of select="@HierId"/>
				</xsl:attribute>
			</input>
		</xsl:if> -->
		<!-- content -->
		<xsl:apply-templates/>
		<!-- command selectbox -->
		<xsl:if test="$mode = 'edit'">
			<br />
			<!-- <xsl:value-of select="@HierId"/> -->
			<input type="checkbox" name="target[]">
				<xsl:attribute name="value"><xsl:value-of select="@HierId"/>
				</xsl:attribute>
			</input>
			<select size="1" class="ilEditSelect">
				<xsl:attribute name="name">command<xsl:value-of select="@HierId"/>
				</xsl:attribute>
			<option value="edit">edit</option>
			<option value="insert_par">insert Paragr.</option>
			<option value="insert_tab">insert Table</option>
			<option value="delete">delete</option>
			<option value="moveAfter">move after</option>
			<option value="moveBefore">move before</option>
			</select>
			<input class="ilEditSubmit" type="submit" value="Go">
				<xsl:attribute name="name">cmd[exec_<xsl:value-of select="@HierId"/>]
				</xsl:attribute>
			</input>
		</xsl:if>
	</p>
</xsl:template>

<xsl:template match="Emph|Strong|Comment">
	<xsl:variable name="Tagname" select="name()"/>
	<span class="il{$Tagname}"><xsl:apply-templates/></span>
</xsl:template>

<!-- Tables -->
<xsl:template match="Table">
	<!-- <xsl:value-of select="@HierId"/> -->
	<xsl:if test="$mode = 'edit'">
		<!--<input type="checkbox" name="target[]">
			<xsl:attribute name="value"><xsl:value-of select="@HierId"/>
			</xsl:attribute>
		</input> -->
		<br/>
	</xsl:if>

	<table>
	<xsl:attribute name="width"><xsl:value-of select="@Width"/></xsl:attribute>
	<xsl:attribute name="border"><xsl:value-of select="@Border"/></xsl:attribute>
	<xsl:attribute name="cellspacing"><xsl:value-of select="@CellSpacing"/></xsl:attribute>
	<xsl:attribute name="cellpadding"><xsl:value-of select="@CellPadding"/></xsl:attribute>
	<!--<xsl:for-each select="HeaderCaption">
		<caption align="top">
		<xsl:value-of select="."/>
		</caption>
	</xsl:for-each>-->
	<xsl:for-each select="Caption">
		<caption>
		<xsl:attribute name="align"><xsl:value-of select="@Align"/></xsl:attribute>
		<xsl:value-of select="."/>
		</caption>
	</xsl:for-each>
	<xsl:for-each select="TableRow">
		<tr valign="top">
			<xsl:for-each select="TableData">
				<td>
					<xsl:attribute name="class"><xsl:value-of select="@Class"/></xsl:attribute>
					<xsl:attribute name="width"><xsl:value-of select="@Width"/></xsl:attribute>
					<!-- insert commands -->
					<!-- <xsl:value-of select="@HierId"/> -->
					<xsl:if test="$mode = 'edit' or $mode = 'table_edit'">
						<!-- checkbox -->
						<input type="checkbox" name="target[]">
							<xsl:attribute name="value"><xsl:value-of select="@HierId"/>
							</xsl:attribute>
						</input>
						<!-- insert select list -->
						<xsl:if test="$mode = 'edit'">
							<select size="1" class="ilEditSelect">
								<xsl:attribute name="name">command<xsl:value-of select="@HierId"/>
								</xsl:attribute>
								<option value="insert_par">insert Paragr.</option>
								<option value="insert_tab">insert Table</option>
							</select>
							<input class="ilEditSubmit" type="submit" value="Go">
								<xsl:attribute name="name">cmd[exec_<xsl:value-of select="@HierId"/>]</xsl:attribute>
							</input>
							<br/>
						</xsl:if>
					</xsl:if>
					<!-- class and width output for table edit -->
					<xsl:if test="$mode = 'table_edit'">
					<br />
					<b>Class: <xsl:value-of select="@Class"/></b><br />
					<b>Width: <xsl:value-of select="@Width"/></b><br />
					</xsl:if>
					<!-- content -->
					<xsl:apply-templates/>
				</td>
			</xsl:for-each>
		</tr>
	</xsl:for-each>
	</table>
	<!-- command selectbox -->
	<xsl:if test="$mode = 'edit'">
		<!-- <xsl:value-of select="@HierId"/> -->
		<input type="checkbox" name="target[]">
			<xsl:attribute name="value"><xsl:value-of select="@HierId"/>
			</xsl:attribute>
		</input>
		<select size="1" class="ilEditSelect">
			<xsl:attribute name="name">command<xsl:value-of select="@HierId"/>
			</xsl:attribute>
		<option value="edit">edit properties</option>
		<option value="insert_par">insert Paragr.</option>
		<option value="insert_tab">insert Table</option>
		<option value="delete">delete</option>
		<option value="moveAfter">move after</option>
		<option value="moveBefore">move before</option>
		</select>
		<input class="ilEditSubmit" type="submit" value="Go">
			<xsl:attribute name="name">cmd[exec_<xsl:value-of select="@HierId"/>]
			</xsl:attribute>
		</input>
		<br/>
	</xsl:if>
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
