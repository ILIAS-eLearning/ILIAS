<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
								xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
								xmlns:xhtml="http://www.w3.org/1999/xhtml">

<xsl:output method="html"/>

<!-- changing the default template to output all unknown tags -->
<xsl:template match="*">
  <xsl:copy-of select="."/>
</xsl:template>

<!-- dump MetaData -->
<xsl:template match="MetaData"/>

<!-- PageObject -->
<xsl:param name="mode"/>
<xsl:param name="pg_title"/>
<xsl:template match="PageObject">
	<xsl:if test="$pg_title != ''">
		<div class="ilc_PageTitle">
		<xsl:value-of select="$pg_title"/>
		</div>
	</xsl:if>
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

	<!-- Footnote List -->
	<xsl:if test="count(//Footnote) > 0">
		<hr />
		<xsl:for-each select="//Footnote">
			<div class="ilc_Footnote">
			<a>
			<xsl:attribute name="name">fn<xsl:number count="Footnote" level="any"/></xsl:attribute>
			<span class="ilc_Strong">[<xsl:number count="Footnote" level="any"/>] </span>
			</a>
			<xsl:value-of select="."/>
			</div>
		</xsl:for-each>
	</xsl:if>
</xsl:template>


<!-- PageContent -->
<xsl:template match="PageContent">
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
			<!-- <xsl:value-of select="../@HierId"/> -->
			<input type="checkbox" name="target[]">
				<xsl:attribute name="value"><xsl:value-of select="../@HierId"/>
				</xsl:attribute>
			</input>
			<select size="1" class="ilEditSelect">
				<xsl:attribute name="name">command<xsl:value-of select="../@HierId"/>
				</xsl:attribute>
			<option value="edit">edit</option>
			<option value="insert_par">insert Paragr.</option>
			<option value="insert_tab">insert Table</option>
			<option value="delete">delete</option>
			<option value="moveAfter">move after</option>
			<option value="moveBefore">move before</option>
			</select>
			<input class="ilEditSubmit" type="submit" value="Go">
				<xsl:attribute name="name">cmd[exec_<xsl:value-of select="../@HierId"/>]
				</xsl:attribute>
			</input>
		</xsl:if>
	</p>
</xsl:template>

<!-- Emph, Strong, Comment, Quotation -->
<xsl:template match="Emph|Strong|Comment|Quotation">
	<xsl:variable name="Tagname" select="name()"/>
	<span class="ilc_{$Tagname}"><xsl:apply-templates/></span>
</xsl:template>

<!-- Code -->
<xsl:template match="Code">
	<code><xsl:apply-templates/></code>
</xsl:template>

<!-- Footnote (Links) -->
<xsl:template match="Footnote"><a class="ilc_FootnoteLink"><xsl:attribute name="href">#fn<xsl:number count="Footnote" level="any"/></xsl:attribute>[<xsl:number count="Footnote" level="any"/>]
	</a>
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
								<option value="newRowAfter">new Row after</option>
								<option value="newRowBefore">new Row before</option>
								<option value="newColAfter">new Col after</option>
								<option value="newColBefore">new Col before</option>
								<option value="deleteRow">delete Row</option>
								<option value="deleteCol">delete Col</option>
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
		<!-- <xsl:value-of select="../@HierId"/> -->
		<input type="checkbox" name="target[]">
			<xsl:attribute name="value"><xsl:value-of select="../@HierId"/>
			</xsl:attribute>
		</input>
		<select size="1" class="ilEditSelect">
			<xsl:attribute name="name">command<xsl:value-of select="../@HierId"/>
			</xsl:attribute>
		<option value="edit">edit properties</option>
		<option value="insert_par">insert Paragr.</option>
		<option value="insert_tab">insert Table</option>
		<option value="delete">delete</option>
		<option value="moveAfter">move after</option>
		<option value="moveBefore">move before</option>
		</select>
		<input class="ilEditSubmit" type="submit" value="Go">
			<xsl:attribute name="name">cmd[exec_<xsl:value-of select="../@HierId"/>]
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
