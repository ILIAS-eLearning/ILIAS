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

<!-- dummy node for output (this is necessary because all media
	objects follow in sequence to the page object, the page contains
	media aliases only (and their own layout information). the dummy
	node wraps the pageobject and the mediaobject tags. -->
<xsl:template match="dummy">
	<xsl:apply-templates/>
</xsl:template>

<!-- PageObject -->
<xsl:param name="mode"/>
<xsl:param name="pg_title"/>
<xsl:param name="ref_id"/>
<xsl:param name="pg_frame"/>
<xsl:param name="webspace_path"/>
<xsl:param name="enlarge_path"/>
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
			<option value="insert_mob">insert Media</option>
			<option value="insert_list">insert List</option>
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
	<xsl:if test="$mode = 'edit'">
		<div class="il_editarea">
		<xsl:apply-templates/>
		</div>
	</xsl:if>
	<xsl:if test="$mode != 'edit'">
		<xsl:apply-templates/>
	</xsl:if>
</xsl:template>


<!-- Paragraph -->
<xsl:template match="Paragraph">
	<p>
		<xsl:if test="not(@Characteristic)">
		<xsl:attribute name="class">ilc_Standard</xsl:attribute>
		</xsl:if>
		<xsl:if test="@Characteristic">
		<xsl:attribute name="class">ilc_<xsl:value-of select="@Characteristic"/></xsl:attribute>
		</xsl:if>
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
			<option value="insert_mob">insert Media</option>
			<option value="insert_list">insert List</option>
			<option value="delete">delete</option>
			<option value="moveAfter">move after</option>
			<option value="moveBefore">move before</option>
			</select>
			<input class="ilEditSubmit" type="submit" value="Go">
				<xsl:attribute name="name">cmd[exec_<xsl:value-of select="../@HierId"/>]</xsl:attribute>
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

<!-- IntLink -->
<xsl:template match="IntLink">
	<a class="ilc_IntLink">
		<xsl:if test="@Type = 'PageObject'">
			<xsl:if test="$mode = 'edit'">
				<xsl:attribute name="href">lm_edit.php?cmd=view&amp;ref_id=<xsl:value-of select="$ref_id"/>&amp;obj_id=<xsl:value-of select="substring-after(@Target,'_')"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="$mode = 'preview'">
				<xsl:attribute name="href">lm_edit.php?cmd=preview&amp;ref_id=<xsl:value-of select="$ref_id"/>&amp;obj_id=<xsl:value-of select="substring-after(@Target,'_')"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="$mode = 'presentation'">
				<xsl:attribute name="href">lm_presentation.php?cmd=layout&amp;frame=<xsl:value-of select="$pg_frame"/>&amp;ref_id=<xsl:value-of select="$ref_id"/>&amp;obj_id=<xsl:value-of select="substring-after(@Target,'_')"/></xsl:attribute>
			</xsl:if>
		</xsl:if>
		<xsl:apply-templates/>
	</a>
</xsl:template>


<!-- ExtLink -->
<xsl:template match="ExtLink">
	<a class="ilc_ExtLink" target="_new">
		<xsl:attribute name="href"><xsl:value-of select="@Href"/></xsl:attribute>
		<xsl:apply-templates/>
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
								<option value="insert_mob">insert Media</option>
								<option value="insert_list">insert List</option>
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
		<option value="insert_mob">insert Media</option>
		<option value="insert_list">insert List</option>
		<option value="delete">delete</option>
		<option value="moveAfter">move after</option>
		<option value="moveBefore">move before</option>
		</select>
		<input class="ilEditSubmit" type="submit" value="Go">
			<xsl:attribute name="name">cmd[exec_<xsl:value-of select="../@HierId"/>]</xsl:attribute>
		</input>
		<br/>
	</xsl:if>
</xsl:template>


<!-- Lists -->
<xsl:template match="List">
	<!-- <xsl:value-of select="..@HierId"/> -->
	<xsl:if test="@Type = 'Ordered'">
		<ol>
		<xsl:choose>
			<xsl:when test="@NumberingType = 'Roman'"><xsl:attribute name="type">I</xsl:attribute></xsl:when>
			<xsl:when test="@NumberingType = 'roman'"><xsl:attribute name="type">i</xsl:attribute></xsl:when>
			<xsl:when test="@NumberingType = 'Alphabetic'"><xsl:attribute name="type">A</xsl:attribute></xsl:when>
			<xsl:when test="@NumberingType = 'alphabetic'"><xsl:attribute name="type">a</xsl:attribute></xsl:when>
		</xsl:choose>
		<xsl:apply-templates/>
		</ol>
	</xsl:if>
	<xsl:if test="@Type = 'Unordered'">
		<ul>
		<xsl:apply-templates/>
		</ul>
	</xsl:if>
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
		<option value="insert_mob">insert Media</option>
		<option value="insert_list">insert List</option>
		<option value="delete">delete</option>
		<option value="moveAfter">move after</option>
		<option value="moveBefore">move before</option>
		</select>
		<input class="ilEditSubmit" type="submit" value="Go">
			<xsl:attribute name="name">cmd[exec_<xsl:value-of select="../@HierId"/>]</xsl:attribute>
		</input>
		<br/>
	</xsl:if>
</xsl:template>

<!-- List Item -->
<xsl:template match="Item">
	<li>
	<!-- insert commands -->
	<!-- <xsl:value-of select="@HierId"/> -->
	<xsl:if test="$mode = 'edit'">
		<!-- checkbox -->
		<input type="checkbox" name="target[]">
			<xsl:attribute name="value"><xsl:value-of select="@HierId"/>
			</xsl:attribute>
		</input>
		<select size="1" class="ilEditSelect">
			<xsl:attribute name="name">command<xsl:value-of select="@HierId"/>
			</xsl:attribute>
			<option value="insert_par">insert Paragr.</option>
			<option value="insert_tab">insert Table</option>
			<option value="insert_mob">insert Media</option>
			<option value="insert_list">insert List</option>
			<option value="newItemAfter">new Item after</option>
			<option value="newItemBefore">new Item before</option>
			<option value="deleteItem">delete Item</option>
		</select>
		<input class="ilEditSubmit" type="submit" value="Go">
			<xsl:attribute name="name">cmd[exec_<xsl:value-of select="@HierId"/>]</xsl:attribute>
		</input>
		<br/>
	</xsl:if>

	<xsl:apply-templates/>
	</li>
</xsl:template>


<!-- MediaAlias -->
<xsl:template match="MediaAlias">

	<!-- Alignment Part 1 (Left, Center, Right)-->
	<xsl:if test="../MediaItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'Left'">
		<div align="left" style="clear:both;">
		<xsl:call-template name="MOBTable"/>
		</div>
	</xsl:if>
	<xsl:if test="../MediaItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'Center'">
		<div align="center" style="clear:both;">
		<xsl:call-template name="MOBTable"/>
		</div>
	</xsl:if>
	<xsl:if test="../MediaItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'Right'">
		<div align="right" style="clear:both;">
		<xsl:call-template name="MOBTable"/>
		</div>
	</xsl:if>
	<xsl:if test="../MediaItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'RightFloat'">
		<xsl:call-template name="MOBTable"/>
	</xsl:if>
	<xsl:if test="../MediaItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'LeftFloat'">
		<xsl:call-template name="MOBTable"/>
	</xsl:if>
	<xsl:if test="count(../MediaItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign) = 0">
		<div align="left" style="clear:both;">
		<xsl:call-template name="MOBTable"/>
		</div>
	</xsl:if>
</xsl:template>

<!-- MOBTable: display multimedia objects within a layout table> -->
<xsl:template name="MOBTable">
	<xsl:variable name="cmobid" select="@OriginId"/>

	<table class="ilc_MobTable" width="1">
		<!-- Alignment Part 2 (LeftFloat, RightFloat) -->
		<xsl:if test="../MediaItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'LeftFloat'">
			<xsl:attribute name="style">float:left; clear:both; margin-left: 0px;</xsl:attribute>
		</xsl:if>
		<xsl:if test="../MediaItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'RightFloat'">
			<xsl:attribute name="style">float:right; clear:both; margin-right: 0px;</xsl:attribute>
		</xsl:if>

		<!-- make object fit to left/right border -->
		<xsl:if test="../MediaItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'Left'">
			<xsl:attribute name="style">margin-left: 0px;</xsl:attribute>
		</xsl:if>
		<xsl:if test="../MediaItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'Right'">
			<xsl:attribute name="style">margin-right: 0px;</xsl:attribute>
		</xsl:if>

		<!-- build object tag -->
		<tr><td class="ilc_Mob"><object>
			<!-- standard -->
			<xsl:for-each select="../MediaItem[@Purpose='Standard']">

				<!-- data / Location -->
				<xsl:variable name="curItemNr"><xsl:number count="MediaItem" from="MediaAlias"/></xsl:variable>
				<xsl:variable name="curType" select="//MediaObject[@Id=$cmobid]/Technical/Location[position()=$curItemNr]/@Type"/>
				<xsl:if test="$curType = 'LocalFile'">
					<xsl:attribute name="data"><xsl:value-of select="$webspace_path"/>/mobs/mm_<xsl:value-of select="$cmobid"/>/<xsl:value-of select="//MediaObject[@Id=$cmobid]/Technical/Location[position()=$curItemNr]"/></xsl:attribute>
				</xsl:if>
				<xsl:if test="$curType = 'Reference'">
					<xsl:attribute name="data"><xsl:value-of select="//MediaObject[@Id=$cmobid]/Technical/Location[position()=$curItemNr]"/></xsl:attribute>
				</xsl:if>

				<!-- type / Format -->
				<xsl:attribute name="type"><xsl:value-of select="//MediaObject[@Id=$cmobid]/Technical/Format[position()=$curItemNr]"/></xsl:attribute>

				<!-- width and height -->
				<xsl:choose>
					<xsl:when test="../MediaItem[@Purpose='Standard']/Layout[1]/@Width != '' or ../MediaItem[@Purpose='Standard']/Layout[1]/@Height != ''">
						<xsl:attribute name="width"><xsl:value-of select="../MediaItem[@Purpose='Standard']/Layout[1]/@Width"/></xsl:attribute>
						<xsl:attribute name="height"><xsl:value-of select="../MediaItem[@Purpose='Standard']/Layout[1]/@Height"/></xsl:attribute>
					</xsl:when>
					<xsl:when test="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose='Standard']/Layout[1]/@Width != '' or
						//MediaObject[@Id=$cmobid]/MediaItem[@Purpose='Standard']/Layout[1]/@Height != ''">
						<xsl:attribute name="width"><xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose='Standard']/Layout[1]/@Width"/></xsl:attribute>
						<xsl:attribute name="height"><xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose='Standard']/Layout[1]/@Height"/></xsl:attribute>
					</xsl:when>
				</xsl:choose>

			</xsl:for-each>
		</object></td></tr>

		<!-- mob caption / standard -->
		<xsl:choose>			<!-- derive -->
			<xsl:when test="count(../MediaItem[@Purpose='Standard']/Caption[1]) != 0">
				<tr><td class="ilc_MobCaption">
				<xsl:call-template name="FullscreenLink">
					<xsl:with-param name="cmobid" select="$cmobid"/>
				</xsl:call-template>
				<xsl:value-of select="../MediaItem[@Purpose='Standard']/Caption[1]"/>
				</td></tr>
			</xsl:when>
			<xsl:when test="count(//MediaObject[@Id=$cmobid]/MediaItem[@Purpose='Standard']/Caption[1]) != 0">
				<tr><td class="ilc_MobCaption">
				<xsl:call-template name="FullscreenLink">
					<xsl:with-param name="cmobid" select="$cmobid"/>
				</xsl:call-template>
				<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose='Standard']/Caption[1]"/>
				</td></tr>
			</xsl:when>
			<xsl:otherwise>
				<xsl:if test="count(../MediaItem[@Purpose='Fullscreen']) = 1">
					<tr><td class="ilc_MobCaption">
					<xsl:call-template name="FullscreenLink">
						<xsl:with-param name="cmobid" select="$cmobid"/>
					</xsl:call-template>
					</td></tr>
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>

		<tr><td>
			<!-- command selectbox -->
			<xsl:if test="$mode = 'edit'">
				<!-- <xsl:value-of select="../../@HierId"/> -->
				<input type="checkbox" name="target[]">
					<xsl:attribute name="value"><xsl:value-of select="../../@HierId"/>
					</xsl:attribute>
				</input>
				<select size="1" class="ilEditSelect">
					<xsl:attribute name="name">command<xsl:value-of select="../../@HierId"/>
					</xsl:attribute>
				<option value="edit">edit properties</option>
				<option value="insert_par">insert Paragr.</option>
				<option value="insert_tab">insert Table</option>
				<option value="insert_mob">insert Media</option>
				<option value="insert_list">insert List</option>
				<option value="delete">delete</option>
				<option value="moveAfter">move after</option>
				<option value="moveBefore">move before</option>
				<option value="leftAlign">align: left</option>
				<option value="rightAlign">align: right</option>
				<option value="centerAlign">align: center</option>
				<option value="leftFloatAlign">align: left float</option>
				<option value="rightFloatAlign">align: right float</option>
				</select>
				<input class="ilEditSubmit" type="submit" value="Go">
					<xsl:attribute name="name">cmd[exec_<xsl:value-of select="../../@HierId"/>]</xsl:attribute>
				</input>
			</xsl:if>
		</td></tr>
	</table>
</xsl:template>

<!-- Fullscreen Link -->
<xsl:template name="FullscreenLink">
	<xsl:param name="cmobid"/>
	<xsl:if test="count(../MediaItem[@Purpose='Fullscreen']) = 1">
		<a target="_new">
		<xsl:attribute name="href">lm_presentation.php?cmd=fullscreen&amp;mob_id=<xsl:value-of select="$cmobid"/>&amp;ref_id=<xsl:value-of select="$ref_id"/></xsl:attribute>
		<img border="0" align="right">
		<xsl:attribute name="src"><xsl:value-of select="$enlarge_path"/></xsl:attribute>
		</img>
		</a>
	</xsl:if>
</xsl:template>


<!-- MediaObject -->
<xsl:template match="MediaObject">
	<xsl:apply-templates select="MediaAlias"/>
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
