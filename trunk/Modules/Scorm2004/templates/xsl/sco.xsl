<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:xhtml="http://www.w3.org/1999/xhtml">
	<!-- removed xmlns:str="http://exslt.org/strings" -->

	<xsl:output method="html"/>

	<!-- strip white spaces between tags -->
	<xsl:strip-space elements="*"/>


	<!-- dump MetaData -->
	<xsl:template match="MetaData"/>

	<!-- dummy node for output (this is necessary because all media
	objects follow in sequence to the page object, the page contains
	media aliases only (and their own layout information). the dummy
	node wraps the pageobject and the mediaobject tags. -->
	<xsl:template match="dummy">
		<xsl:apply-templates/>
		<xsl:if test="count(./PageObject) = 0">
			<xsl:call-template name="outputImageMaps"/>
		</xsl:if>
	</xsl:template>

	<!-- PageObject -->
	<xsl:template match="ContentObject">
		<html>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
			<link rel="stylesheet" type="text/css" href="./css/style.css" />
			<script src="./js/scorm.js" type="text/javascript" language="JavaScript1.2"/>
			<body onLoad="javascript:init(0);" onunload="javascript:finish();">
				<xsl:call-template name="PageObject"/>
			</body>
		</html>
	</xsl:template>

	<xsl:template name="PageObject" match="PageObject">

		<xsl:apply-templates/>

		<!-- Footnote List -->
		<xsl:if test="count(//Footnote) > 0">
			<hr/>
			<xsl:for-each select="//Footnote">
				<div class="ilc_Footnote">
					<a>
						<xsl:attribute name="name"> fn<xsl:number count="Footnote" level="any"/>
						</xsl:attribute>
						<span class="ilc_Strong"> [<xsl:number count="Footnote" level="any"/>]
						</span>
					</a>
					<xsl:value-of select="."/>
				</div>
			</xsl:for-each>
		</xsl:if>

		<!-- image map data -->
		<xsl:call-template name="outputImageMaps"/>

	</xsl:template>

	<!-- output image maps -->
	<xsl:template name="outputImageMaps">
		<xsl:for-each select="//MediaItem">
			<xsl:variable name="corig">
				<xsl:value-of select="../@Id"/>
			</xsl:variable>
			<xsl:variable name="corigp">
				<xsl:value-of select="@Purpose"/>
			</xsl:variable>

			<xsl:choose>
				<!-- If we got a alias item map, take this -->
				<xsl:when
					test="//MediaAlias[@OriginId = $corig]/../MediaAliasItem[@Purpose = $corigp]/MapArea[1]">
					<xsl:for-each
						select="//MediaAlias[@OriginId = $corig]/../MediaAliasItem[@Purpose = $corigp]/MapArea[1]">
						<map>
							<xsl:attribute name="name"> map_<xsl:value-of select="$corig"
									/>_<xsl:value-of select="$corigp"/>
							</xsl:attribute>
							<xsl:call-template name="outputImageMapAreas"/>
						</map>
					</xsl:for-each>
				</xsl:when>
				<xsl:otherwise>
					<!-- Otherwose, if we got an object item map, take this -->
					<xsl:for-each select="./MapArea[1]">
						<map>
							<xsl:attribute name="name"> map_<xsl:value-of select="$corig"
									/>_<xsl:value-of select="$corigp"/>
							</xsl:attribute>
							<xsl:call-template name="outputImageMapAreas"/>
						</map>
					</xsl:for-each>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:for-each>
	</xsl:template>

	<!-- output image map areas -->
	<xsl:template name="outputImageMapAreas">
		<xsl:for-each select="../MapArea">
			<area>
				<xsl:attribute name="shape">
					<xsl:value-of select="@Shape"/>
				</xsl:attribute>
				<xsl:attribute name="coords">
					<xsl:value-of select="@Coords"/>
				</xsl:attribute>
				<xsl:for-each select="./IntLink">

					<!-- determine link_href and link_target -->
					<xsl:variable name="target" select="@Target"/>
					<xsl:variable name="type" select="@Type"/>
					<xsl:variable name="targetframe">
						<xsl:choose>
							<xsl:when test="@TargetFrame and @TargetFrame!=''">
								<xsl:value-of select="@TargetFrame"/>
							</xsl:when>
							<xsl:otherwise>None</xsl:otherwise>
						</xsl:choose>
					</xsl:variable>
					<xsl:variable name="link_href">
						<xsl:value-of
							select="//IntLinkInfos/IntLinkInfo[@Type=$type and @TargetFrame=$targetframe and @Target=$target]/@LinkHref"
						/>
					</xsl:variable>
					<xsl:variable name="link_target">
						<xsl:value-of
							select="//IntLinkInfos/IntLinkInfo[@Type=$type and @TargetFrame=$targetframe and @Target=$target]/@LinkTarget"
						/>
					</xsl:variable>

					<!-- set attributes -->
					<xsl:attribute name="href">
						<xsl:value-of select="$link_href"/>
					</xsl:attribute>
					<xsl:if test="$link_target != ''">
						<xsl:attribute name="target">
							<xsl:value-of select="$link_target"/>
						</xsl:attribute>
					</xsl:if>

					<xsl:attribute name="title">
						<xsl:value-of select="."/>
					</xsl:attribute>
					<xsl:attribute name="alt">
						<xsl:value-of select="."/>
					</xsl:attribute>
				</xsl:for-each>
				<xsl:for-each select="./ExtLink">
					<xsl:attribute name="href">
						<xsl:value-of select="@Href"/>
					</xsl:attribute>
					<xsl:attribute name="title">
						<xsl:value-of select="."/>
					</xsl:attribute>
					<xsl:attribute name="alt">
						<xsl:value-of select="."/>
					</xsl:attribute>
					<xsl:attribute name="target">_blank</xsl:attribute>
				</xsl:for-each>
			</area>
		</xsl:for-each>
	</xsl:template>


	<!-- Bibliography-Tag nie ausgeben -->
	<xsl:template match="Bibliography"/>

	<!-- PageContent -->
	<xsl:template match="PageContent">
		<xsl:if test="(not(@Enabled) or @Enabled='True')">
			<xsl:if test="//PageObject/DivClass/@HierId = ./@HierId">
				<div>
					<xsl:attribute name="class">
						<xsl:value-of select="//PageObject/DivClass[@HierId = ./@HierId]/@Class"/>
					</xsl:attribute>
					<xsl:apply-templates>
						<xsl:with-param name="par_counter" select="position()"/>
					</xsl:apply-templates>
				</div>
			</xsl:if>
			<xsl:if test="not(//PageObject/DivClass/@HierId = ./@HierId)">
				<xsl:apply-templates>
					<xsl:with-param name="par_counter" select="position()"/>
				</xsl:apply-templates>
			</xsl:if>
		</xsl:if>
	</xsl:template>

	<!-- Icon -->
	<xsl:template name="Icon">
		<xsl:param name="img_src"/>
		<xsl:param name="img_id"/>
		<xsl:param name="float">n</xsl:param>

		<img border="0">
			<xsl:if test="$float = 'y'">
				<xsl:attribute name="style"/>
			</xsl:if>
			<xsl:attribute name="onMouseOver">doMouseOver(this.id);</xsl:attribute>
			<xsl:attribute name="onMouseOut">doMouseOut(this.id,false);</xsl:attribute>
			<xsl:attribute name="onMouseDown">doMouseDown(this.id);</xsl:attribute>
			<xsl:attribute name="onMouseUp">doMouseUp(this.id);</xsl:attribute>
			<xsl:attribute name="onClick">doMouseClick(event,this.id,'PageObject');</xsl:attribute>
			<xsl:attribute name="id">
				<xsl:value-of select="$img_id"/>
			</xsl:attribute>
			<xsl:attribute name="src">
				<xsl:value-of select="$img_src"/>
			</xsl:attribute>
		</img>
	</xsl:template>

	<!-- Drop Area for Adding -->
	<xsl:template name="DropArea">
		<xsl:param name="hier_id"/>
		<!-- <xsl:value-of select="$hier_id"/> -->
		<!-- Drop area -->
		<div class="il_droparea">
			<xsl:attribute name="id"> TARGET<xsl:value-of select="$hier_id"/>
			</xsl:attribute>
			<xsl:attribute name="onMouseOver">doMouseOver(this.id, 'il_droparea_active');</xsl:attribute>
			<xsl:attribute name="onMouseOut">doMouseOut(this.id, 'il_droparea');</xsl:attribute>
			<xsl:attribute name="onClick"> doMouseClick(event, 'TARGET' + '<xsl:value-of
					select="@HierId"/>'); </xsl:attribute>
			<img src="./templates/default/images/empty.png" border="0" width="8" height="8"/>
		</div>
	</xsl:template>

	<!-- Paragraph -->
	<xsl:template match="Paragraph">
		<xsl:param name="par_counter" select="-1"/>
		<div>
			<xsl:call-template name="ShowParagraph"/>
		</div>
	</xsl:template>

	<xsl:template name="ShowParagraph">
		<xsl:param name="p_id" select="-1"/>
		<xsl:if test="not(@Characteristic)">
			<xsl:attribute name="class">ilc_Standard</xsl:attribute>
		</xsl:if>
		<xsl:if test="@Characteristic and not (@Characteristic = 'Code')">
			<xsl:attribute name="class"> ilc_<xsl:value-of select="@Characteristic"/>
			</xsl:attribute>
		</xsl:if>
		<!-- content -->
		<xsl:apply-templates/>
	</xsl:template>

	<!-- plugins for code paragraphs -->
	<xsl:template name="plugins">
		<xsl:param name="pluginsString" select="'-1'"/>
		<xsl:param name="subchar" select="'-1'"/>
		<xsl:param name="par_vars" select="''"/>
		<xsl:choose>
			<xsl:when test="string-length(substring-before($pluginsString,'|')) =0">
				<xsl:call-template name="plugin">
					<xsl:with-param name="pluginString" select="$pluginsString"/>
					<xsl:with-param name="subchar" select="$subchar"/>
					<xsl:with-param name="par_vars" select="$par_vars"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="plugin">
					<xsl:with-param name="pluginString"
						select="substring-before($pluginsString,'|')"/>
					<xsl:with-param name="subchar" select="$subchar"/>
					<xsl:with-param name="par_vars" select="$par_vars"/>
				</xsl:call-template>
				<xsl:variable name="restString" select="substring-after($pluginsString,'|')"/>
				<xsl:if test="string-length($restString)>0">
					<xsl:call-template name="plugins">
						<xsl:with-param name="pluginsString" select="$restString"/>
						<xsl:with-param name="subchar" select="$subchar"/>
						<xsl:with-param name="par_vars" select="$par_vars"/>
					</xsl:call-template>
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!-- defines content plugin -->
	<xsl:template name="plugin">
		<xsl:param name="pluginString" select="'-1'"/>
		<xsl:param name="subchar" select="'-1'"/>
		<xsl:param name="par_vars" select="''"/>

		<xsl:variable name="filetype" select="substring-before($pluginString,'#')"/>
		<xsl:variable name="rest1" select="substring-after($pluginString,'#')"/>
		<xsl:variable name="title" select="substring-before($rest1,'#')"/>
		<xsl:variable name="rest2" select="substring-after($rest1,'#')"/>

		<xsl:variable name="linkNode">
			<xsl:choose>
				<xsl:when test="substring-before($rest2,'#')=''">
					<xsl:value-of select="$rest2"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="substring-before($rest2,'#')"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:variable name="link" select="concat(string($linkNode),$par_vars)"/>
		<xsl:variable name="image" select="substring-after($rest2,'#')"/>

		<xsl:if test="$subchar = $filetype or $filetype='-1'">
			<span style="margin-left: 5px">
				<a href="{$link}">
					<img src="{$image}" align="middle" alt="{$title}" border="0"/>
				</a>
			</span>
		</xsl:if>
	</xsl:template>

	<!-- Emph, Strong, Comment, Quotation -->
	<xsl:template match="Emph|Strong|Comment|Quotation">
		<xsl:variable name="Tagname" select="name()"/>
		<span class="ilc_{$Tagname}">
			<xsl:apply-templates/>
		</span>
	</xsl:template>

	<!-- Code -->
	<xsl:template match="Code">
		<code>
			<xsl:apply-templates/>
		</code>
	</xsl:template>

	<!-- Footnote (Links) -->
	<xsl:template match="Footnote">
		<a class="ilc_FootnoteLink">
			<xsl:attribute name="href"> #fn<xsl:number count="Footnote" level="any"/>
			</xsl:attribute>[<xsl:number count="Footnote" level="any"/>] </a>
	</xsl:template>

	<!-- PageTurn (Links) -->
	<xsl:template match="PageTurn">
		<xsl:variable name="entry_one">
			<xsl:value-of select="./BibItemIdentifier/@Entry"/>
		</xsl:variable>
	</xsl:template>

	<!-- IntLink -->
	<xsl:template match="IntLink">
		<xsl:choose>
			<!-- internal link to external resource (other installation) -->
			<xsl:when test="substring-after(@Target,'__') = ''"> [could not resolve link target:
					<xsl:value-of select="@Target"/>] </xsl:when>
			<!-- all internal links except inline mob vris -->
			<xsl:when test="@Type != 'MediaObject' or @TargetFrame">
				<xsl:variable name="target" select="@Target"/>
				<xsl:variable name="type" select="@Type"/>
				<xsl:variable name="targetframe">
					<xsl:choose>
						<xsl:when test="@TargetFrame">
							<xsl:value-of select="@TargetFrame"/>
						</xsl:when>
						<xsl:otherwise>None</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>
				<xsl:variable name="link_href">
					<xsl:value-of
						select="//IntLinkInfos/IntLinkInfo[@Type=$type and @TargetFrame=$targetframe and @Target=$target]/@LinkHref"
					/>
				</xsl:variable>
				<xsl:variable name="link_target">
					<xsl:value-of
						select="//IntLinkInfos/IntLinkInfo[@Type=$type and @TargetFrame=$targetframe and @Target=$target]/@LinkTarget"
					/>
				</xsl:variable>

			</xsl:when>
			<!-- inline mob vri -->
			<xsl:when test="@Type = 'MediaObject' and not(@TargetFrame)">
				<xsl:variable name="cmobid" select="@Target"/>

				<!-- determine location type (LocalFile, Reference) -->
				<xsl:variable name="curType">
					<xsl:value-of
						select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose = 'Standard']/Location/@Type"
					/>
				</xsl:variable>

				<!-- determine format (mime type) -->
				<xsl:variable name="type">
					<xsl:value-of
						select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose = 'Standard']/Format"
					/>
				</xsl:variable>

				<!-- determine location -->
				<xsl:variable name="data">
					<xsl:if test="$curType = 'LocalFile'"> ./objects/<xsl:value-of select="$cmobid"
							/>/<xsl:value-of
							select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose = 'Standard']/Location"
						/>
					</xsl:if>
					<xsl:if test="$curType = 'Reference'">
						<xsl:value-of
							select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose = 'Standard']/Location"
						/>
					</xsl:if>
				</xsl:variable>

				<!-- determine size mode (alias, mob or none) -->
				<xsl:variable name="sizemode">mob</xsl:variable>

				<!-- determine width -->
				<xsl:variable name="width">
					<xsl:value-of
						select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Layout[1]/@Width"
					/>
				</xsl:variable>

				<!-- determine height -->
				<xsl:variable name="height">
					<xsl:value-of
						select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Layout[1]/@Height"
					/>
				</xsl:variable>

				<xsl:call-template name="MOBTag">
					<xsl:with-param name="data" select="$data"/>
					<xsl:with-param name="type" select="$type"/>
					<xsl:with-param name="width" select="$width"/>
					<xsl:with-param name="height" select="$height"/>
					<xsl:with-param name="Standard">Standard</xsl:with-param>
					<xsl:with-param name="cmobid" select="$cmobid"/>
					<xsl:with-param name="location_mode">standard</xsl:with-param>
					<xsl:with-param name="curType" select="$curType"/>
					<xsl:with-param name="inline">y</xsl:with-param>
				</xsl:call-template>

			</xsl:when>
		</xsl:choose>
	</xsl:template>


	<!-- ExtLink -->
	<xsl:template match="ExtLink">
		<a class="ilc_ExtLink" target="_blank">
			<xsl:attribute name="href">
				<xsl:value-of select="@Href"/>
			</xsl:attribute>
			<xsl:apply-templates/>
		</a>
	</xsl:template>


	<!-- Tables -->
	<xsl:template match="Table">
		<!-- <xsl:value-of select="@HierId"/> -->
		<xsl:choose>
			<xsl:when test="@HorizontalAlign = 'Left'">
				<div align="left">
					<xsl:call-template name="TableTag"/>
				</div>
			</xsl:when>
			<xsl:when test="@HorizontalAlign = 'Right'">
				<div align="right">
					<xsl:call-template name="TableTag"/>
				</div>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="TableTag"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!-- Table Tag -->
	<xsl:template name="TableTag">
		<table>
			<xsl:attribute name="width">
				<xsl:value-of select="@Width"/>
			</xsl:attribute>
			<xsl:attribute name="border">
				<xsl:value-of select="@Border"/>
			</xsl:attribute>
			<xsl:attribute name="cellspacing">
				<xsl:value-of select="@CellSpacing"/>
			</xsl:attribute>
			<xsl:attribute name="cellpadding">
				<xsl:value-of select="@CellPadding"/>
			</xsl:attribute>
			<xsl:attribute name="align">
				<xsl:choose>
					<xsl:when test="@HorizontalAlign = 'RightFloat'">right</xsl:when>
					<xsl:when test="@HorizontalAlign = 'LeftFloat'">left</xsl:when>
					<xsl:when test="@HorizontalAlign = 'Center'">center</xsl:when>
				</xsl:choose>
			</xsl:attribute>
			<xsl:for-each select="Caption">
				<caption>
					<xsl:attribute name="align">
						<xsl:value-of select="@Align"/>
					</xsl:attribute>
					<xsl:value-of select="."/>
				</caption>
			</xsl:for-each>
			<xsl:for-each select="TableRow">
				<xsl:variable name="rowpos" select="position()"/>
				<tr valign="top">
					<xsl:for-each select="TableData">
						<td>
							<xsl:attribute name="class">
								<xsl:value-of select="@Class"/>
							</xsl:attribute>
							<xsl:attribute name="width">
								<xsl:value-of select="@Width"/>
							</xsl:attribute>
							<!-- insert commands -->
							<!-- <xsl:value-of select="@HierId"/> -->
							<!-- content -->
							<xsl:apply-templates/>
						</td>
					</xsl:for-each>
				</tr>
			</xsl:for-each>
		</table>
	</xsl:template>

	<!-- Table Data Menu -->
	<xsl:template name="TableDataMenu">

		<xsl:call-template name="TableRowMenu"/>

		<xsl:call-template name="TableColMenu"/>

	</xsl:template>

	<!-- Table Row Menu -->
	<xsl:template name="TableRowMenu">
		<xsl:variable name="ni">
			<xsl:number from="PageContent" level="single" count="TableRow"/>
		</xsl:variable>

	</xsl:template>

	<!-- Table Col Menu -->
	<xsl:template name="TableColMenu">

		<xsl:variable name="ni">
			<xsl:number from="TableRow" level="single" count="TableData"/>
		</xsl:variable>

	</xsl:template>

	<!-- Table Menu -->
	<xsl:template name="TableMenu">
		<xsl:param name="hier_id"/>
	</xsl:template>


	<!-- Lists -->
	<xsl:template match="List">
		<xsl:if test="@Type = 'Ordered'">
			<ol>
				<xsl:choose>
					<xsl:when test="@NumberingType = 'Roman'">
						<xsl:attribute name="type">I</xsl:attribute>
					</xsl:when>
					<xsl:when test="@NumberingType = 'roman'">
						<xsl:attribute name="type">i</xsl:attribute>
					</xsl:when>
					<xsl:when test="@NumberingType = 'Alphabetic'">
						<xsl:attribute name="type">A</xsl:attribute>
					</xsl:when>
					<xsl:when test="@NumberingType = 'alphabetic'">
						<xsl:attribute name="type">a</xsl:attribute>
					</xsl:when>
				</xsl:choose>
				<xsl:apply-templates/>
			</ol>
		</xsl:if>
		<xsl:if test="@Type = 'Unordered'">
			<ul>
				<xsl:apply-templates/>
			</ul>
		</xsl:if>
	</xsl:template>

	<!-- List Item -->
	<xsl:template match="ListItem">
		<li>
			<xsl:apply-templates/>
		</li>
	</xsl:template>

	<!-- SimpleBulletList -->
	<xsl:template match="SimpleBulletList">
		<ul>
			<xsl:apply-templates/>
		</ul>
	</xsl:template>
	<xsl:template match="SimpleNumberedList">
		<ol>
			<xsl:apply-templates/>
		</ol>
	</xsl:template>
	<xsl:template match="SimpleListItem">
		<li>
			<xsl:apply-templates/>
		</li>
	</xsl:template>

	<!-- FileList -->
	<xsl:template match="FileList">
		<table class="ilc_FileList">
			<tr>
				<th class="ilc_FileList">
					<xsl:value-of select="./Title"/>
				</th>
			</tr>
			<xsl:apply-templates/>
		</table>
	</xsl:template>

	<xsl:template match="FileItem">
		<tr class="ilc_FileItem">
			<td class="ilc_FileItem">
				<a>
					<xsl:attribute name="href">./objects/<xsl:value-of select="./Identifier/@Entry"/>/<xsl:value-of select="./Location"/></xsl:attribute>
					<xsl:call-template name="FileItemText"/>
				</a>
			</td>
		</tr>
	</xsl:template>
	
	<!-- FileItemText -->
	<xsl:template name="FileItemText">
		<xsl:value-of select="./Location"/>
		<xsl:if test="./Size">
			<xsl:choose>
				<xsl:when test="./Size > 1000000"> (<xsl:value-of
						select="round(./Size div 10000) div 100"/> MB) </xsl:when>
				<xsl:when test="./Size > 1000"> (<xsl:value-of select="round(./Size div 10) div 100"
					/> KB) </xsl:when>
				<xsl:otherwise> (<xsl:value-of select="./Size"/> B) </xsl:otherwise>
			</xsl:choose>
		</xsl:if>
	</xsl:template>

	<!-- MediaAlias -->
	<xsl:template match="MediaAlias">
		<!-- Alignment Part 1 (Left, Center, Right)-->
		<xsl:if test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'Left'">
			<div align="left" style="clear:both;">
				<xsl:call-template name="MOBTable"/>
			</div>
		</xsl:if>
		<xsl:if test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'Center'">
			<div align="center" style="clear:both;">
				<xsl:call-template name="MOBTable"/>
			</div>
		</xsl:if>
		<xsl:if test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'Right'">
			<div align="right" style="clear:both;">
				<xsl:call-template name="MOBTable"/>
			</div>
		</xsl:if>
		<xsl:if
			test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'RightFloat'">
			<xsl:call-template name="MOBTable"/>
		</xsl:if>
		<xsl:if
			test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'LeftFloat'">
			<xsl:call-template name="MOBTable"/>
		</xsl:if>
		<xsl:if test="count(../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign) = 0">
			<div align="left" style="clear:both;">
				<xsl:call-template name="MOBTable"/>
			</div>
		</xsl:if>
	</xsl:template>

	<!-- MOBTable: display multimedia objects within a layout table> -->
	<xsl:template name="MOBTable">
		<xsl:variable name="cmobid" select="@OriginId"/>

		<table class="ilc_Media" width="1">
			<!-- Alignment Part 2 (LeftFloat, RightFloat) -->
			<xsl:if
				test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'LeftFloat'">
				<xsl:attribute name="style">margin-left: 0px;</xsl:attribute>
				<xsl:attribute name="style">float:left; clear:both; margin-left:
				0px;</xsl:attribute>
			</xsl:if>
			<xsl:if
				test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'RightFloat'">
				<xsl:attribute name="style">margin-right: 0px;</xsl:attribute>
				<xsl:attribute name="style">float:right; clear:both; margin-right:
				0px;</xsl:attribute>
			</xsl:if>

			<!-- make object fit to left/right border -->
			<xsl:if
				test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'Left'">
				<xsl:attribute name="style">margin-left: 0px;</xsl:attribute>
			</xsl:if>
			<xsl:if
				test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'Right'">
				<xsl:attribute name="style">margin-right: 0px;</xsl:attribute>
			</xsl:if>

			<!-- build object tag -->
			<tr>
				<td class="ilc_Mob">
					<xsl:for-each select="../MediaAliasItem[@Purpose ='Standard']">

						<!-- data / Location -->
						<xsl:variable name="curItemNr">
							<xsl:number count="MediaItem" from="MediaAlias"/>
						</xsl:variable>

						<!-- determine location mode (Standard, standard) -->
						<xsl:variable name="location_mode">
							<xsl:choose>
								<xsl:when
									test="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose = 'Standard']/Location != ''"
									>Standard</xsl:when>
								<xsl:otherwise>Standard</xsl:otherwise>
							</xsl:choose>
						</xsl:variable>

						<!-- determine location type (LocalFile, Reference) -->
						<xsl:variable name="curType">
							<xsl:value-of
								select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose = 'Standard']/Location/@Type"
							/>
						</xsl:variable>

						<!-- determine format (mime type) -->
						<xsl:variable name="type">
							<xsl:value-of
								select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose = 'Standard']/Format"
							/>
						</xsl:variable>

						<!-- determine location -->
						<xsl:variable name="data">
							<xsl:if test="$curType = 'LocalFile'"> ./objects/<xsl:value-of
									select="$cmobid"/>/<xsl:value-of
									select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose = 'Standard']/Location"
								/>
							</xsl:if>
							<xsl:if test="$curType = 'Reference'">
								<xsl:value-of
									select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose = 'Standard']/Location"
								/>
							</xsl:if>
						</xsl:variable>

						<!-- determine size mode (alias, mob or none) -->
						<xsl:variable name="sizemode">
							<xsl:choose>
								<xsl:when
									test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@Width != '' or
							../MediaAliasItem[@Purpose='Standard']/Layout[1]/@Height != ''"
									>alias</xsl:when>
								<xsl:when
									test="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Layout[1]/@Width != '' or
							//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Layout[1]/@Height != ''"
									>mob</xsl:when>
								<xsl:otherwise>none</xsl:otherwise>
							</xsl:choose>
						</xsl:variable>

						<!-- determine width -->
						<xsl:variable name="width">
							<xsl:choose>
								<xsl:when test="$sizemode = 'alias'">
									<xsl:value-of
										select="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@Width"
									/>
								</xsl:when>
								<xsl:when test="$sizemode = 'mob'">
									<xsl:value-of
										select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Layout[1]/@Width"
									/>
								</xsl:when>
								<xsl:otherwise/>
							</xsl:choose>
						</xsl:variable>

						<!-- determine height -->
						<xsl:variable name="height">
							<xsl:choose>
								<xsl:when test="$sizemode = 'alias'">
									<xsl:value-of
										select="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@Height"
									/>
								</xsl:when>
								<xsl:when test="$sizemode = 'mob'">
									<xsl:value-of
										select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Layout[1]/@Height"
									/>
								</xsl:when>
								<xsl:otherwise/>
							</xsl:choose>
						</xsl:variable>

						<xsl:call-template name="MOBTag">
							<xsl:with-param name="data" select="$data"/>
							<xsl:with-param name="type" select="$type"/>
							<xsl:with-param name="width" select="$width"/>
							<xsl:with-param name="height" select="$height"/>
							<xsl:with-param name="Standard" select="'Standard'"/>
							<xsl:with-param name="cmobid" select="$cmobid"/>
							<xsl:with-param name="location_mode" select="$location_mode"/>
							<xsl:with-param name="curType" select="$curType"/>
						</xsl:call-template>
					</xsl:for-each>
				</td>
			</tr>
		</table>
	</xsl:template>

	<!-- MOBTag: display media object tag -->
	<xsl:template name="MOBTag">
		<xsl:param name="data"/>
		<xsl:param name="type"/>
		<xsl:param name="width"/>
		<xsl:param name="height"/>
		<xsl:param name="cmobid"/>
		<xsl:param name="Standard"/>
		<xsl:param name="location_mode"/>
		<xsl:param name="curType"/>
		<xsl:param name="inline">n</xsl:param>
		<xsl:choose>
			<!-- all image mime types, except svg -->
			<xsl:when
				test="substring($type, 1, 5) = 'image' and not(substring($type, 1, 9) = 'image/svg')">

				<img border="0">
					<xsl:attribute name="src">
						<xsl:value-of select="$data"/>
					</xsl:attribute>
					<xsl:if test="$width != ''">
						<xsl:attribute name="width">
							<xsl:value-of select="$width"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:if test="$height != ''">
						<xsl:attribute name="height">
							<xsl:value-of select="$height"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:if
						test="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose = 'Standard']/MapArea[1] or ./MapArea[1]">
						<xsl:attribute name="usemap"> #map_<xsl:value-of select="$cmobid"
								/>_<xsl:value-of select="'Standard'"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:if test="$inline = 'y'">
						<xsl:attribute name="align">middle</xsl:attribute>
					</xsl:if>
				</img>
			</xsl:when>

			<!-- flash -->
			<xsl:when test="$type = 'application/x-shockwave-flash'">
				<object>
					<xsl:attribute name="classid">clsid:D27CDB6E-AE6D-11cf-96B8-444553540000</xsl:attribute>
					<xsl:attribute name="codebase"
						>http://active.macromedia.com/flash2/cabs/swflash.cab#version=4,0,0,0</xsl:attribute>
					<xsl:attribute name="ID">
						<xsl:value-of select="$data"/>
					</xsl:attribute>
					<xsl:attribute name="width">
						<xsl:value-of select="$width"/>
					</xsl:attribute>
					<xsl:attribute name="height">
						<xsl:value-of select="$height"/>
					</xsl:attribute>
					<param>
						<xsl:attribute name="name">movie</xsl:attribute>
						<xsl:attribute name="value">
							<xsl:value-of select="$data"/>
						</xsl:attribute>
					</param>
					<xsl:call-template name="MOBParams">
						<xsl:with-param name="Standard" select="'Standard'"/>
						<xsl:with-param name="mode">elements</xsl:with-param>
						<xsl:with-param name="cmobid" select="$cmobid"/>
					</xsl:call-template>
					<embed>
						<xsl:attribute name="src">
							<xsl:value-of select="$data"/>
						</xsl:attribute>
						<xsl:attribute name="width">
							<xsl:value-of select="$width"/>
						</xsl:attribute>
						<xsl:attribute name="height">
							<xsl:value-of select="$height"/>
						</xsl:attribute>
						<xsl:attribute name="type">application/x-shockwave-flash</xsl:attribute>
						<xsl:attribute name="pluginspage"
							>http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash</xsl:attribute>
						<xsl:call-template name="MOBParams">
							<xsl:with-param name="Standard" select="'Standard'"/>
							<xsl:with-param name="mode">attributes</xsl:with-param>
							<xsl:with-param name="cmobid" select="$cmobid"/>
						</xsl:call-template>
					</embed>
				</object>
			</xsl:when>

			<!-- java -->
			<xsl:when test="$type = 'application/x-java-applet'">
				<xsl:variable name="upper-case" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÜ'"/>
				<xsl:variable name="lower-case" select="'abcdefghijklmnopqrstuvwxyzäöü'"/>

				<!-- filename normalisieren: trim, toLowerCase -->
				<xsl:variable name="_filename"
					select="normalize-space(translate(substring-after($data,'/'), $upper-case, $lower-case))"/>

				<applet width="{$width}" height="{$height}">

					<xsl:choose>
						<!-- if is single class file: filename ends-with (class) -->
						<xsl:when
							test="'class' = substring($_filename, string-length($_filename) - string-length('class') + 1)">
							<xsl:choose>
								<xsl:when test="$location_mode = 'Standard'">
									<xsl:if test="$curType = 'LocalFile'">
										<xsl:attribute name="code">
											<xsl:value-of
												select="substring-before(//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose = 'Standard']/Location,'.')"
											/>
										</xsl:attribute>
										<xsl:attribute name="codebase"> ./objects/<xsl:value-of
												select="$cmobid"/>/ </xsl:attribute>
									</xsl:if>
									<xsl:if test="$curType = 'Reference'">
										<xsl:attribute name="code">
											<xsl:value-of
												select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose = 'Standard']/Location"
											/>
										</xsl:attribute>
									</xsl:if>
								</xsl:when>
								<xsl:when test="$location_mode = 'standard'">
									<xsl:if test="$curType = 'LocalFile'">
										<xsl:attribute name="code">
											<xsl:value-of
												select="substring-before(//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose = 'Standard']/Location,'.')"
											/>
										</xsl:attribute>
										<xsl:attribute name="codebase"> ./objects/<xsl:value-of
												select="$cmobid"/>/ </xsl:attribute>
									</xsl:if>
									<xsl:if test="$curType = 'Reference'">
										<xsl:attribute name="code">
											<xsl:value-of
												select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose = 'Standard']/Location"
											/>
										</xsl:attribute>
									</xsl:if>
								</xsl:when>
							</xsl:choose>
							<xsl:call-template name="MOBParams">
								<xsl:with-param name="Standard" select="'Standard'"/>
								<xsl:with-param name="mode">elements</xsl:with-param>
								<xsl:with-param name="cmobid" select="$cmobid"/>
							</xsl:call-template>
						</xsl:when>

						<!-- assuming is applet archive: filename ends-with something else -->
						<xsl:otherwise>
							<xsl:choose>
								<xsl:when test="$location_mode = 'Standard'">
									<xsl:if test="$curType = 'LocalFile'">
										<xsl:attribute name="archive">
											<xsl:value-of
												select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose = 'Standard']/Location"
											/>
										</xsl:attribute>
										<xsl:attribute name="codebase"> ./objects/<xsl:value-of
												select="$cmobid"/>/ </xsl:attribute>
									</xsl:if>
									<xsl:if test="$curType = 'Reference'">
										<xsl:attribute name="archive">
											<xsl:value-of
												select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose = 'Standard']/Location"
											/>
										</xsl:attribute>
									</xsl:if>
								</xsl:when>
								<xsl:when test="$location_mode = 'standard'">
									<xsl:if test="$curType = 'LocalFile'">
										<xsl:attribute name="archive">
											<xsl:value-of
												select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose = 'Standard']/Location"
											/>
										</xsl:attribute>
										<xsl:attribute name="codebase"> ./objects/<xsl:value-of
												select="$cmobid"/>/ </xsl:attribute>
									</xsl:if>
									<xsl:if test="$curType = 'Reference'">
										<xsl:attribute name="archive">
											<xsl:value-of
												select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose = 'Standard']/Location"
											/>
										</xsl:attribute>
									</xsl:if>
								</xsl:when>
							</xsl:choose>
							<!-- object or instance parameters -->
							<!-- nescessary because attribute code is part of applet-tag and others are sub elements -->
							<!-- code attribute -->
							<xsl:choose>
								<xsl:when
									test="../MediaAliasItem[@Purpose='Standard']/Parameter[@Name = 'code']">
									<xsl:attribute name="code">
										<xsl:value-of
											select="../MediaAliasItem[@Purpose='Standard']/Parameter[@Name = 'code']/@Value"
										/>
									</xsl:attribute>
								</xsl:when>
								<xsl:otherwise>
									<xsl:attribute name="code">
										<xsl:value-of
											select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Parameter[@Name = 'code']/@Value"
										/>
									</xsl:attribute>
								</xsl:otherwise>
							</xsl:choose>

							<xsl:choose>

								<xsl:when test="../MediaAliasItem[@Purpose='Standard']/Parameter">
									<!-- alias parameters -->
									<xsl:for-each
										select="../MediaAliasItem[@Purpose = 'Standard']/Parameter">
										<xsl:if test="@Name != 'code'">
											<param>
												<xsl:attribute name="name">
												<xsl:value-of select="@Name"/>
												</xsl:attribute>
												<xsl:attribute name="value">
												<xsl:value-of select="@Value"/>
												</xsl:attribute>
											</param>
										</xsl:if>
									</xsl:for-each>
								</xsl:when>
								<!-- object parameters -->
								<xsl:otherwise>
									<xsl:for-each
										select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Parameter">
										<xsl:if test="@Name != 'code'">
											<param>
												<xsl:attribute name="name">
												<xsl:value-of select="@Name"/>
												</xsl:attribute>
												<xsl:attribute name="value">
												<xsl:value-of select="@Value"/>
												</xsl:attribute>
											</param>
										</xsl:if>
									</xsl:for-each>
								</xsl:otherwise>

							</xsl:choose>
						</xsl:otherwise>
					</xsl:choose>
				</applet>
			</xsl:when>

			<!-- text/html -->
			<xsl:when test="$type = 'text/html'">
				<iframe frameborder="0">
					<xsl:attribute name="src">
						<xsl:value-of select="$data"/>
					</xsl:attribute>
					<xsl:attribute name="width">
						<xsl:value-of select="$width"/>
					</xsl:attribute>
					<xsl:attribute name="height">
						<xsl:value-of select="$height"/>
					</xsl:attribute>
					<xsl:call-template name="MOBParams">
						<xsl:with-param name="Standard" select="'Standard'"/>
						<xsl:with-param name="mode">attributes</xsl:with-param>
						<xsl:with-param name="cmobid" select="$cmobid"/>
					</xsl:call-template>
				</iframe>
			</xsl:when>

			<!-- mp4 -->
			<xsl:when test="$type = 'video/mp4'">
				<embed pluginspage="http://www.apple.com/quicktime/download/">
					<xsl:attribute name="src">
						<xsl:value-of select="$data"/>
					</xsl:attribute>
					<xsl:attribute name="type">
						<xsl:value-of select="$type"/>
					</xsl:attribute>
					<xsl:attribute name="width">
						<xsl:value-of select="$width"/>
					</xsl:attribute>
					<xsl:attribute name="height">
						<xsl:value-of select="$height"/>
					</xsl:attribute>
					<xsl:call-template name="MOBParams">
						<xsl:with-param name="Standard" select="'Standard'"/>
						<xsl:with-param name="mode">attributes</xsl:with-param>
						<xsl:with-param name="cmobid" select="$cmobid"/>
					</xsl:call-template>
				</embed>
			</xsl:when>

			<!-- YouTube -->
			<xsl:when test="substring-after($data,'youtube.com') != ''">
				<object>
					<xsl:attribute name="width">
						<xsl:value-of select="$width"/>
					</xsl:attribute>
					<xsl:attribute name="height">
						<xsl:value-of select="$height"/>
					</xsl:attribute>
					<param name="movie">
						<xsl:attribute name="value"> http://www.youtube.com/v/<xsl:value-of
								select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Parameter[@Name='v']/@Value"
							/>&amp;hl=en&amp;fs=1&amp;rel=0 </xsl:attribute>
					</param>
					<param name="allowFullScreen" value="true"/>
					<embed type="application/x-shockwave-flash" allowfullscreen="true">
						<xsl:attribute name="width">
							<xsl:value-of select="$width"/>
						</xsl:attribute>
						<xsl:attribute name="height">
							<xsl:value-of select="$height"/>
						</xsl:attribute>
						<xsl:attribute name="src"> http://www.youtube.com/v/<xsl:value-of
								select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Parameter[@Name='v']/@Value"
							/>&amp;hl=en&amp;fs=1&amp;rel=0 </xsl:attribute>
					</embed>
				</object>
			</xsl:when>

			<!-- Flickr -->
			<xsl:when test="substring-after($data,'flickr.com') != ''">
				<xsl:variable name="flickr_tags">
					<xsl:if
						test="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Parameter[@Name='tags']/@Value != ''"
						> &amp;tags=<xsl:value-of
							select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Parameter[@Name='tags']/@Value"
						/>
					</xsl:if>
				</xsl:variable>
				<xsl:variable name="flickr_sets">
					<xsl:if
						test="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Parameter[@Name='sets']/@Value != ''"
						> &amp;set_id=<xsl:value-of
							select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Parameter[@Name='sets']/@Value"
						/>
					</xsl:if>
				</xsl:variable>
				<xsl:variable name="flickr_user_id"> user_id=<xsl:value-of
						select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Parameter[@Name='user_id']/@Value"
					/>
				</xsl:variable>
				<iframe frameBorder="0" scrolling="no">
					<xsl:attribute name="width">
						<xsl:value-of select="$width"/>
					</xsl:attribute>
					<xsl:attribute name="height">
						<xsl:value-of select="$height"/>
					</xsl:attribute>
					<xsl:attribute name="src">
							http://www.flickr.com/slideShow/index.gne?<xsl:value-of
							select="$flickr_user_id"/><xsl:value-of select="$flickr_tags"
							/><xsl:value-of select="$flickr_sets"/>
					</xsl:attribute>
				</iframe>
			</xsl:when>

			<!-- GoogleVideo -->
			<xsl:when test="substring-after($data,'video.google') != ''">
				<embed id="VideoPlayback" allowFullScreen="true"
					type="application/x-shockwave-flash">
					<xsl:attribute name="src">
							http://video.google.com/googleplayer.swf?docid=<xsl:value-of
							select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Parameter[@Name='docid']/@Value"
						/>&amp;fs=true </xsl:attribute>
					<xsl:attribute name="width">
						<xsl:value-of select="$width"/>
					</xsl:attribute>
					<xsl:attribute name="height">
						<xsl:value-of select="$height"/>
					</xsl:attribute>
				</embed>
			</xsl:when>

			<!-- GoogleDoc -->
			<xsl:when test="substring-after($data,'docs.google') != ''">
				<xsl:variable name="googledoc_action">
					<xsl:if
						test="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Parameter[@Name='type']/@Value = 'Presentation'"
						>EmbedSlideshow</xsl:if>
					<xsl:if
						test="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Parameter[@Name='type']/@Value = 'Document'"
						>View</xsl:if>
				</xsl:variable>
				<iframe frameborder="0">
					<xsl:attribute name="src"> http://docs.google.com/<xsl:value-of
							select="$googledoc_action"/>?docid=<xsl:value-of
							select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Parameter[@Name='docid']/@Value"
						/>
					</xsl:attribute>
					<xsl:attribute name="width">
						<xsl:value-of select="$width"/>
					</xsl:attribute>
					<xsl:attribute name="height">
						<xsl:value-of select="$height"/>
					</xsl:attribute>
				</iframe>
			</xsl:when>

			<!-- all other mime types: output standard object/embed tag -->
			<xsl:otherwise>
				<!--<object>
				<xsl:attribute name="data"><xsl:value-of select="$data"/></xsl:attribute>
				<xsl:attribute name="type"><xsl:value-of select="$type"/></xsl:attribute>
				<xsl:attribute name="width"><xsl:value-of select="$width"/></xsl:attribute>
				<xsl:attribute name="height"><xsl:value-of select="$height"/></xsl:attribute>
				<xsl:call-template name="MOBParams">
					<xsl:with-param name="Standard" select="'Standard'" />
					<xsl:with-param name="mode">elements</xsl:with-param>
					<xsl:with-param name="cmobid" select="$cmobid" />
				</xsl:call-template>-->
				<embed>
					<xsl:attribute name="src">
						<xsl:value-of select="$data"/>
					</xsl:attribute>
					<xsl:attribute name="type">
						<xsl:value-of select="$type"/>
					</xsl:attribute>
					<xsl:attribute name="width">
						<xsl:value-of select="$width"/>
					</xsl:attribute>
					<xsl:attribute name="height">
						<xsl:value-of select="$height"/>
					</xsl:attribute>
					<xsl:call-template name="MOBParams">
						<xsl:with-param name="Standard" select="'Standard'"/>
						<xsl:with-param name="mode">attributes</xsl:with-param>
						<xsl:with-param name="cmobid" select="$cmobid"/>
					</xsl:call-template>
				</embed>
				<!--</object>-->
			</xsl:otherwise>

		</xsl:choose>
	</xsl:template>

	<!-- MOB Parameters -->
	<xsl:template name="MOBParams">
		<xsl:param name="Standard"/>
		<xsl:param name="cmobid"/>
		<xsl:param name="mode"/>
		<!-- 'attributes' | 'elements' -->

		<xsl:choose>
			<!-- output parameters as attributes -->
			<xsl:when test="$mode = 'attributes'">
				<xsl:choose>
					<!-- take parameters from alias -->
					<xsl:when test="../MediaAliasItem[@Purpose = 'Standard']/Parameter">
						<xsl:for-each select="../MediaAliasItem[@Purpose = 'Standard']/Parameter">
							<xsl:attribute name="{@Name}">
								<xsl:value-of select="@Value"/>
							</xsl:attribute>
						</xsl:for-each>
					</xsl:when>
					<!-- take parameters from object -->
					<xsl:otherwise>
						<xsl:for-each
							select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Parameter">
							<xsl:attribute name="{@Name}">
								<xsl:value-of select="@Value"/>
							</xsl:attribute>
						</xsl:for-each>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<!-- output parameters as param elements -->
			<xsl:otherwise>
				<xsl:choose>
					<!-- take parameters from alias -->
					<xsl:when test="../MediaAliasItem[@Purpose = 'Standard']/Parameter">
						<xsl:for-each select="../MediaAliasItem[@Purpose = 'Standard']/Parameter">
							<param>
								<xsl:attribute name="name">
									<xsl:value-of select="@Name"/>
								</xsl:attribute>
								<xsl:attribute name="value">
									<xsl:value-of select="@Value"/>
								</xsl:attribute>
							</param>
						</xsl:for-each>
					</xsl:when>
					<!-- take parameters from object -->
					<xsl:otherwise>
						<xsl:for-each
							select="//MediaObject[MetaData/General/Identifier[@Entry=$cmobid]]/MediaItem[@Purpose='Standard']/Parameter">
							<param>
								<xsl:attribute name="name">
									<xsl:value-of select="@Name"/>
								</xsl:attribute>
								<xsl:attribute name="value">
									<xsl:value-of select="@Value"/>
								</xsl:attribute>
							</param>
						</xsl:for-each>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>

	</xsl:template>

	<!-- MediaObject -->
	<xsl:template match="MediaObject">
		<xsl:apply-templates select="MediaAlias"/>
	</xsl:template>

	<!-- Section -->
	<xsl:template match="Section">
		<div>
			<xsl:if test="@Characteristic">
				<xsl:attribute name="class">
					<xsl:value-of select="@Characteristic"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates/>
		</div>
	</xsl:template>

	<!-- Resources -->
	<xsl:template match="Resources">
		<div> [list-<xsl:value-of select="./ResourceList/@Type"/>] </div>
	</xsl:template>

	<!-- Map -->
	<xsl:template match="Map">
		<div style="clear:both;">
			<xsl:if test="./Layout[1]/@HorizontalAlign = 'Left'">
				<xsl:attribute name="align">left</xsl:attribute>
			</xsl:if>
			<xsl:if test="./Layout[1]/@HorizontalAlign = 'Center'">
				<xsl:attribute name="align">center</xsl:attribute>
			</xsl:if>
			<xsl:if test="./Layout[1]/@HorizontalAlign = 'Right'">
				<xsl:attribute name="align">right</xsl:attribute>
			</xsl:if>
			<xsl:if test="./Layout[1]/@HorizontalAlign = 'LeftFloat'">
				<xsl:attribute name="style">clear:both; float:left;</xsl:attribute>
			</xsl:if>
			<xsl:if test="./Layout[1]/@HorizontalAlign = 'RightFloat'">
				<xsl:attribute name="style">clear:both; float:right;</xsl:attribute>
			</xsl:if>
			<table class="ilc_Media" width="1">
				<xsl:if test="(./Layout[1]/@HorizontalAlign = 'LeftFloat')">
					<xsl:attribute name="style">margin-left: 0px;
					style="float:left;"</xsl:attribute>
				</xsl:if>
				<xsl:if test="./Layout[1]/@HorizontalAlign = 'RightFloat'">
					<xsl:attribute name="style">margin-right: 0px;
					style="float:right;</xsl:attribute>
				</xsl:if>
				<tr>
					<td class="ilc_Mob">
						<div> [[[[[Map;<xsl:value-of select="@Latitude"/>;<xsl:value-of
								select="@Longitude"/>;<xsl:value-of select="@Zoom"/>;<xsl:value-of
								select="./Layout[1]/@Width"/>;<xsl:value-of
								select="./Layout[1]/@Height"/>]]]]] </div>
					</td>
				</tr>
				<xsl:if test="count(./MapCaption[1]) != 0">
					<tr>
						<td class="ilc_MediaCaption">
							<xsl:value-of select="./MapCaption[1]"/>
						</td>
					</tr>
				</xsl:if>
			</table>
		</div>
	</xsl:template>

	<!-- Tabs -->
	<xsl:template match="Tabs">
		<xsl:if test="@Type = 'HorizontalTabs'">
			<div class="yui-skin-sam">
				<div>
					<xsl:attribute name="id"> tabs<xsl:number count="Tabs" level="any"/>
					</xsl:attribute>
					<xsl:attribute name="class">yui-navset</xsl:attribute>
					<ul class="yui-nav">
						<xsl:for-each select="./Tab">
							<li>
								<xsl:if test="position() = 1">
									<xsl:attribute name="class">selected</xsl:attribute>
								</xsl:if>
								<a>
									<xsl:attribute name="href"> #tab<xsl:number count="Tab"
											level="any"/>
									</xsl:attribute> &amp;nbsp;<xsl:value-of
										select="./TabCaption"/>&amp;nbsp; </a>
							</li>
						</xsl:for-each>
					</ul>
					<div class="yui-content">
						<xsl:apply-templates/>
					</div>
					<script type="text/javascript"> var tabView<xsl:number count="Tabs" level="any"
						/> = new YAHOO.widget.TabView('tabs<xsl:number count="Tabs" level="any"/>'); </script>
					<xsl:apply-templates/>
				</div>
			</div>
		</xsl:if>
		<xsl:if test="@Type = 'Accordion'">
			<ul>
				<xsl:apply-templates/>
			</ul>
		</xsl:if>
	</xsl:template>

	<!-- Tab -->
	<xsl:template match="Tab">
		<div>
			<xsl:attribute name="id"> tab<xsl:number count="Tab" level="any"/>
			</xsl:attribute>

			<xsl:attribute name="class">il_edit_pc_tab</xsl:attribute>
			<xsl:apply-templates select="PageContent"/>
		</div>
	</xsl:template>

	<!-- Plugged -->
	<xsl:template match="Plugged">
		<div> {{{{{Plugged;<xsl:value-of select="@PluginID"/>;<xsl:value-of select="@PluginVersion"
			/>}}}}} </div>
	</xsl:template>

	<!-- Question -->
	<xsl:template match="Question">
		<div class="ilc_Question">


			<xsl:call-template name="ShowQuestion"/>
			<!-- <xsl:apply-templates/> -->

			<!-- command selectbox -->

		</div>
	</xsl:template>

	<!-- ShowQuestion-->
	<xsl:template name="ShowQuestion">
		<xsl:for-each select="//questestinterop/item/presentation/flow">
			<xsl:apply-templates/>
		</xsl:for-each>
	</xsl:template>

	<!-- t&a: response_lid (multiple choice / ordering) -->
	<xsl:template match="response_lid">
		<xsl:choose>

			<!-- multiple choice single response -->
			<xsl:when test="@ident = 'MCSR'">
				<table class="nobackground">
					<xsl:for-each select="render_choice/response_label">
						<tr>
							<td class="nobackground" width="15">
								<input type="radio" name="multiple_choice_result">
									<xsl:attribute name="value">
										<xsl:value-of select="@ident"/>
									</xsl:attribute>
									<xsl:attribute name="id">
										<xsl:value-of select="@ident"/>
									</xsl:attribute>
									<xsl:attribute name="dummy"> mc<xsl:value-of select="@ident"/>
									</xsl:attribute>
								</input>
							</td>
							<td class="nobackground" width="left">
								<xsl:choose>
									<xsl:when test="material/matimage">
										<label>
											<xsl:attribute name="for">
												<xsl:value-of select="@ident"/>
											</xsl:attribute>
											<img>
												<xsl:attribute name="src">
												/assessment/<xsl:call-template
												name="replace-qtiident">
												<xsl:with-param name="original">
												<xsl:value-of
												select="//questestinterop/item/@ident"
												/>
												</xsl:with-param>
												<xsl:with-param name="substring"
												>qst_</xsl:with-param>
												</xsl:call-template>/images/<xsl:value-of
												select="material/matimage/@label"/>
												</xsl:attribute>
												<xsl:choose>
												<xsl:when test="string-length(material/mattext)">
												<xsl:attribute name="alt">
												<xsl:value-of select="material/mattext"
												/>
												</xsl:attribute>
												<xsl:attribute name="title">
												<xsl:value-of select="material/mattext"
												/>
												</xsl:attribute>
												</xsl:when>
												<xsl:otherwise>
												<xsl:attribute name="alt">
												<xsl:value-of
												select="material/matimage/@label"/>
												</xsl:attribute>
												<xsl:attribute name="title">
												<xsl:value-of
												select="material/matimage/@label"/>
												</xsl:attribute>
												</xsl:otherwise>
												</xsl:choose>
											</img>
											<xsl:if test="string-length(material/mattext)">
												<br/>
												<xsl:value-of select="material/mattext"/>
											</xsl:if>
										</label>
									</xsl:when>
									<xsl:otherwise>
										<label>
											<xsl:attribute name="for">
												<xsl:value-of select="@ident"/>
											</xsl:attribute>
											<xsl:value-of select="material/mattext"/>
										</label>
									</xsl:otherwise>
								</xsl:choose>
							</td>
						</tr>
					</xsl:for-each>
				</table>
			</xsl:when>

			<!-- multiple choice multiple response -->
			<xsl:when test="@ident = 'MCMR'">
				<table class="nobackground">
					<xsl:for-each select="render_choice/response_label">
						<tr>
							<td class="nobackground" width="15">
								<input type="checkbox">
									<xsl:attribute name="name"> multiple_choice_result_<xsl:value-of
											select="@ident"/>
									</xsl:attribute>
									<xsl:attribute name="dummy"> mc<xsl:value-of select="@ident"/>
									</xsl:attribute>
									<xsl:attribute name="value">
										<xsl:value-of select="@ident"/>
									</xsl:attribute>
									<xsl:attribute name="id">
										<xsl:value-of select="@ident"/>
									</xsl:attribute>
								</input>
							</td>
							<td class="nobackground" width="left">
								<xsl:choose>
									<xsl:when test="material/matimage">
										<label>
											<xsl:attribute name="for">
												<xsl:value-of select="@ident"/>
											</xsl:attribute>
											<img>
												<xsl:attribute name="src">
												/assessment/<xsl:call-template
												name="replace-qtiident">
												<xsl:with-param name="original">
												<xsl:value-of
												select="//questestinterop/item/@ident"
												/>
												</xsl:with-param>
												<xsl:with-param name="substring"
												>qst_</xsl:with-param>
												</xsl:call-template>/images/<xsl:value-of
												select="material/matimage/@label"/>
												</xsl:attribute>
												<xsl:choose>
												<xsl:when test="string-length(material/mattext)">
												<xsl:attribute name="alt">
												<xsl:value-of select="material/mattext"
												/>
												</xsl:attribute>
												<xsl:attribute name="title">
												<xsl:value-of select="material/mattext"
												/>
												</xsl:attribute>
												</xsl:when>
												<xsl:otherwise>
												<xsl:attribute name="alt">
												<xsl:value-of
												select="material/matimage/@label"/>
												</xsl:attribute>
												<xsl:attribute name="title">
												<xsl:value-of
												select="material/matimage/@label"/>
												</xsl:attribute>
												</xsl:otherwise>
												</xsl:choose>
											</img>
											<xsl:if test="string-length(material/mattext)">
												<br/>
												<xsl:value-of select="material/mattext"/>
											</xsl:if>
										</label>
									</xsl:when>
									<xsl:otherwise>
										<label>
											<xsl:attribute name="for">
												<xsl:value-of select="@ident"/>
											</xsl:attribute>
											<xsl:value-of select="material/mattext"/>
										</label>
									</xsl:otherwise>
								</xsl:choose>
							</td>
						</tr>
					</xsl:for-each>
				</table>
			</xsl:when>

			<!-- ordering -->
			<xsl:when test="@ident = 'OQT' or @ident = 'OQP'">
				<xsl:choose>
					<xsl:when test="@output='javascript'">
						<table border="0" width="100%">
							<tr>
								<td align="right">
									<a>
										<xsl:attribute name="href">javascript:restoreInitialOrder();</xsl:attribute>
										<xsl:choose>
											<xsl:when
												test="//render_choice/response_label/material/matimage">
												<xsl:value-of
												select="//LVs/LV[@name='reset_pictures']/@value"
												/>
											</xsl:when>
											<xsl:otherwise>
												<xsl:value-of
												select="//LVs/LV[@name='reset_definitions']/@value"
												/>
											</xsl:otherwise>
										</xsl:choose>
									</a>
								</td>
							</tr>
						</table>
						<ul>
							<xsl:attribute name="id">orderlist</xsl:attribute>
							<xsl:attribute name="class">boxy</xsl:attribute>
							<xsl:for-each select="render_choice/response_label">
								<li>
									<xsl:if test="material/mattext">
										<xsl:attribute name="id">
											<xsl:value-of select="@ident"/>
										</xsl:attribute>
										<xsl:value-of select="material/mattext"/>
									</xsl:if>
									<xsl:if test="material/matimage">
										<xsl:attribute name="id">
											<xsl:value-of select="@ident"/>
										</xsl:attribute>
										<table>
											<xsl:attribute name="border">0</xsl:attribute>
											<tr>
												<td align="left">
												<img>
												<xsl:attribute name="border">0</xsl:attribute>
												<xsl:attribute name="src">
												/assessment/<xsl:call-template
												name="replace-qtiident">
												<xsl:with-param name="original">
												<xsl:value-of
												select="//questestinterop/item/@ident"
												/>
												</xsl:with-param>
												<xsl:with-param name="substring"
												>qst_</xsl:with-param>
												</xsl:call-template>/images/<xsl:value-of
												select="material/matimage/@label"
												/>.thumb.jpg </xsl:attribute>
												</img>
												</td>
												<td valign="top">
												<a target="_new">
												<xsl:attribute name="href">
												/assessment/<xsl:call-template
												name="replace-qtiident">
												<xsl:with-param name="original">
												<xsl:value-of
												select="//questestinterop/item/@ident"
												/>
												</xsl:with-param>
												<xsl:with-param name="substring"
												>qst_</xsl:with-param>
												</xsl:call-template>/images/<xsl:value-of
												select="material/matimage/@label"/>
												</xsl:attribute>
												<img>
												<xsl:attribute name="border">0</xsl:attribute>
												<xsl:attribute name="src">
												<!--xsl:value-of select="$enlarge_path"/-->
												</xsl:attribute>
												</img>
												</a>
												</td>
											</tr>
										</table>
									</xsl:if>
								</li>
							</xsl:for-each>
						</ul>
						<input type="hidden" name="orderresult" value=""/>
						<script type="text/javascript"> /*solution*/ </script>
					</xsl:when>
					<xsl:otherwise>
						<table class="nobackground">
							<xsl:for-each select="render_choice/response_label">
								<tr>
									<td class="nobackground" width="30">
										<input type="text" size="2">
											<xsl:attribute name="name"> order_<xsl:value-of
												select="@ident"/>
											</xsl:attribute>
											<xsl:attribute name="id">
												<xsl:value-of select="@ident"/>
											</xsl:attribute>
											<xsl:attribute name="dummy"> ord<xsl:value-of
												select="@ident"/>
											</xsl:attribute>
										</input>
									</td>
									<td class="nobackground" width="left">
										<label>
											<xsl:attribute name="for">
												<xsl:value-of select="@ident"/>
											</xsl:attribute>
											<xsl:if test="material/mattext">
												<xsl:value-of select="material/mattext"/>
											</xsl:if>
											<xsl:if test="material/matimage">
												<a target="_new">
												<xsl:attribute name="href">
												/assessment/<xsl:call-template
												name="replace-qtiident">
												<xsl:with-param name="original">
												<xsl:value-of
												select="//questestinterop/item/@ident"
												/>
												</xsl:with-param>
												<xsl:with-param name="substring"
												>qst_</xsl:with-param>
												</xsl:call-template>/images/<xsl:value-of
												select="material/matimage/@label"/>
												</xsl:attribute>
												<img border="0">
												<xsl:attribute name="src">
												/assessment/<xsl:call-template
												name="replace-qtiident">
												<xsl:with-param name="original">
												<xsl:value-of
												select="//questestinterop/item/@ident"
												/>
												</xsl:with-param>
												<xsl:with-param name="substring"
												>qst_</xsl:with-param>
												</xsl:call-template>/images/<xsl:value-of
												select="material/matimage/@label"
												/>.thumb.jpg </xsl:attribute>
												</img>
												</a>
											</xsl:if>
										</label>
									</td>
								</tr>
							</xsl:for-each>
						</table>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
		</xsl:choose>
	</xsl:template>


	<!-- imagemaps -->
	<xsl:template match="response_xy">
		<xsl:choose>
			<xsl:when test="@ident = 'IM'">
				<table class="nobackground">
					<tr>
						<td class="nobackground" align="center">
							<map name="qmap">
								<xsl:for-each select="render_hotspot/response_label">
									<area nohref="nohref">
										<xsl:attribute name="id"> map<xsl:value-of select="@ident"/>
										</xsl:attribute>
										<xsl:attribute name="shape">
											<xsl:if test="@rarea='Rectangle'">
												<xsl:text>rect</xsl:text>
											</xsl:if>
											<xsl:if test="@rarea='Bounded'">
												<xsl:text>poly</xsl:text>
											</xsl:if>
											<xsl:if test="@rarea='Ellipse'">
												<xsl:text>circle</xsl:text>
											</xsl:if>
										</xsl:attribute>
										<xsl:attribute name="coords">
											<xsl:value-of
												select="substring(., 1, string-length(.)-string-length(material))"
											/>
										</xsl:attribute>
										<xsl:attribute name="alt">
											<xsl:value-of select="material/mattext"/>
										</xsl:attribute>
										<xsl:attribute name="title">
											<xsl:value-of select="material/mattext"/>
										</xsl:attribute>
									</area>
								</xsl:for-each>
							</map>
							<img border="0" usemap="#qmap">
								<xsl:attribute name="src"> /assessment/<xsl:call-template
										name="replace-qtiident">
										<xsl:with-param name="original">
											<xsl:value-of select="//questestinterop/item/@ident"/>
										</xsl:with-param>
										<xsl:with-param name="substring">qst_</xsl:with-param>
									</xsl:call-template>/images/<xsl:value-of
										select="render_hotspot/material/matimage/@label"/>
								</xsl:attribute>
							</img>
						</td>
					</tr>
				</table>
			</xsl:when>
		</xsl:choose>
	</xsl:template>

	<!-- t&a: dump qti data -->
	<xsl:template match="questestinterop"/>

	<!-- t&a: dump qti data -->
	<xsl:template match="matimage"/>

	<!-- t&a: text -->
	<xsl:template match="material">
		<xsl:for-each select="mattext">
			<xsl:choose>
				<xsl:when test="@label='applet params'"/>
				<xsl:when test="@label"/>
				<xsl:otherwise>
					<xsl:value-of select="text()"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:for-each>
		<xsl:for-each select="matapplet">
			<p>
				<applet>
					<xsl:choose>
						<xsl:when test="../mattext/@label='java_code'">
							<xsl:attribute name="code">
								<xsl:value-of select="../mattext"/>
							</xsl:attribute>
							<xsl:if test="contains(@uri, '.class')">
								<xsl:attribute name="codebase"> /assessment/<xsl:call-template
										name="replace-qtiident">
										<xsl:with-param name="original">
											<xsl:value-of select="//questestinterop/item/@ident"/>
										</xsl:with-param>
										<xsl:with-param name="substring">qst_</xsl:with-param>
									</xsl:call-template>/java/ </xsl:attribute>
							</xsl:if>
						</xsl:when>
					</xsl:choose>
					<xsl:if test="contains(@uri, '.jar')">
						<xsl:attribute name="archive"> /assessment/<xsl:call-template
								name="replace-qtiident">
								<xsl:with-param name="original">
									<xsl:value-of select="//questestinterop/item/@ident"/>
								</xsl:with-param>
								<xsl:with-param name="substring">qst_</xsl:with-param>
							</xsl:call-template>/java/<xsl:value-of select="@uri"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:attribute name="width">
						<xsl:value-of select="@width"/>
					</xsl:attribute>
					<xsl:attribute name="height">
						<xsl:value-of select="@height"/>
					</xsl:attribute>
					<xsl:for-each select="../mattext">
						<xsl:choose>
							<xsl:when test="@label='java_code'"/>
							<xsl:otherwise> &lt;param name=&quot;<xsl:value-of
									select="@label"/>&quot; value=&quot;<xsl:value-of
									select="text()"/>&quot; /&gt; </xsl:otherwise>
						</xsl:choose>
					</xsl:for-each>
				</applet>
			</p>
		</xsl:for-each>
	</xsl:template>

	<xsl:template match="response_num">
		<p>
			<input>
				<xsl:attribute name="name">numeric_result</xsl:attribute>
				<xsl:attribute name="type">text</xsl:attribute>
				<xsl:attribute name="size">
					<xsl:value-of select="render_fib/@maxchars"/>
				</xsl:attribute>
				<xsl:attribute name="maxlength">
					<xsl:value-of select="render_fib/@maxchars"/>
				</xsl:attribute>
			</input>
		</p>
	</xsl:template>

	<!-- t&a: response_str -->
	<xsl:template match="response_str">
		<xsl:choose>
			<xsl:when test="@ident='TEXT'">
				<br/>
				<textarea class="fullwidth" cols="40" rows="10">
					<xsl:attribute name="name">
						<xsl:value-of select="@ident"/>
					</xsl:attribute>
					<xsl:attribute name="id">
						<xsl:value-of select="@ident"/>
					</xsl:attribute>
					<xsl:if test="./render_fib/@maxchars">
						<xsl:attribute name="onKeyDown">
							<xsl:text>charCounter('</xsl:text>
							<xsl:value-of select="@ident"/>
							<xsl:text>', </xsl:text>
							<xsl:value-of select="./render_fib/@maxchars"/>
							<xsl:text>, 'charCount');</xsl:text>
						</xsl:attribute>
						<xsl:attribute name="onKeyUp">
							<xsl:text>charCounter('</xsl:text>
							<xsl:value-of select="@ident"/>
							<xsl:text>', </xsl:text>
							<xsl:value-of select="./render_fib/@maxchars"/>
							<xsl:text>, 'charCount');</xsl:text>
						</xsl:attribute>
						<xsl:attribute name="onChange">
							<xsl:text>charCounter('</xsl:text>
							<xsl:value-of select="@ident"/>
							<xsl:text>', </xsl:text>
							<xsl:value-of select="./render_fib/@maxchars"/>
							<xsl:text>, 'charCount');</xsl:text>
						</xsl:attribute>
					</xsl:if>
				</textarea>
				<xsl:if test="./render_fib/@maxchars">
					<br/>
					<script language="JavaScript" type="text/javascript">
						<xsl:text>counterOutput('</xsl:text>
						<xsl:value-of select="@ident"/>
						<xsl:text>', </xsl:text>
						<xsl:value-of select="./render_fib/@maxchars"/>
						<xsl:text>, 'charCount');</xsl:text>
					</script>
					<br/>
				</xsl:if>
			</xsl:when>
			<xsl:when test="substring(@ident,1,10)='TEXTSUBSET'">
				<p>
					<xsl:number level="any" count="response_str" format="1. "/>
					<input>
						<xsl:attribute name="type">
							<xsl:text>text</xsl:text>
						</xsl:attribute>
						<xsl:attribute name="name">
							<xsl:value-of select="@ident"/>
						</xsl:attribute>
						<xsl:attribute name="size">
							<xsl:value-of select="render_fib/@columns"/>
						</xsl:attribute>
					</input>
				</p>
			</xsl:when>
			<xsl:otherwise>
				<xsl:choose>
					<!-- text gap -->
					<xsl:when test="./render_fib">
						<input type="text">
							<xsl:attribute name="size">
								<xsl:value-of select="./render_fib/@columns"/>
							</xsl:attribute>
							<xsl:attribute name="name">
								<xsl:value-of select="@ident"/>
							</xsl:attribute>
							<xsl:attribute name="dummy"> t<xsl:value-of select="@ident"/>
							</xsl:attribute>
						</input>
					</xsl:when>

					<!-- select gap ? -->
					<xsl:otherwise>
						<select>
							<xsl:attribute name="name">
								<xsl:value-of select="@ident"/>
							</xsl:attribute>
							<xsl:variable name="crespstr">
								<xsl:value-of select="@ident"/>
							</xsl:variable>

							<option value="-1" selected="selected"> -- <xsl:value-of
									select="//LVs/LV[@name='please_select']/@value"/> -- </option>
							<xsl:for-each select="render_choice/response_label">
								<option>
									<xsl:attribute name="value">
										<xsl:value-of select="@ident"/>
									</xsl:attribute>
									<xsl:attribute name="dummy"> s<xsl:value-of select="$crespstr"
											/>_<xsl:value-of select="@ident"/>
									</xsl:attribute>
									<xsl:apply-templates/>
								</option>
							</xsl:for-each>
						</select>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!-- t&a: response_grp -->
	<xsl:template match="response_grp">
		<xsl:choose>
			<xsl:when test="@output='javascript'">

				<xsl:for-each select="render_choice/response_label">
					<xsl:if test="@match_max">
						<input type="hidden">
							<xsl:attribute name="name"> sel_matching_<xsl:value-of select="@ident"/>
							</xsl:attribute>
							<xsl:attribute name="id"> sel_matching_<xsl:value-of select="@ident"/>
							</xsl:attribute>
							<xsl:attribute name="value"> initial_value_<xsl:value-of select="@ident"
								/>
							</xsl:attribute>
						</input>
					</xsl:if>
				</xsl:for-each>
				<table border="0" width="100%">
					<tr>
						<td colspan="4" align="right">
							<a>
								<xsl:attribute name="href">javascript:resetAnimated();</xsl:attribute>
								<xsl:choose>
									<xsl:when
										test="//render_choice/response_label/material/matimage">
										<xsl:value-of
											select="//LVs/LV[@name='reset_pictures']/@value"/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of
											select="//LVs/LV[@name='reset_definitions']/@value"/>
									</xsl:otherwise>
								</xsl:choose>
							</a>
						</td>
					</tr>
					<!-- matching -->
					<xsl:variable name="count" select="count(//response_label) div 2"/>
					<xsl:for-each select="render_choice/response_label">
						<xsl:choose>
							<xsl:when test="@match_max"/>
							<xsl:otherwise>
								<tr>
									<td width="120">
										<xsl:if test="material/mattext">
											<div class="termtext">
												<xsl:attribute name="id"> term_<xsl:value-of
												select="@ident"/>
												</xsl:attribute>
												<xsl:value-of select="material/mattext"/>
											</div>
										</xsl:if>
									</td>
									<td width="50">
										<xsl:value-of select="//LVs/LV[@name='matches']/@value"/>
									</td>
									<td align="left" width="140">
										<!--xsl:value-of select="//LVs/LV[@name='drop_here']/@value"/>Matching picture/definition to &quot;<xsl:value-of select="material/mattext"/>&quot; Drop here-->
										<div class="dropzone">
											<xsl:attribute name="id"> dropzone_<xsl:value-of
												select="@ident"/>
											</xsl:attribute>
										</div>
									</td>
									<xsl:call-template name="termtaker">
										<xsl:with-param name="i" select="position() - $count"/>
									</xsl:call-template>
								</tr>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:for-each>
				</table>
				<p>
					<xsl:value-of
						select="//LVs/LV[@name='matching_question_javascript_hint']/@value"/>
				</p>
				<script type="text/javascript" src="./assessment/js/rico/prototype.js"/>
				<script type="text/javascript" src="./assessment/js/rico/rico.js"/>
				<script type="text/javascript"> var CustomDraggable = Class.create(); function
					getIdFromElementId(elementid) { var underscore = elementid.indexOf('_'); var id
					= ''; if (underscore >=0 ) { id = elementid.substr(underscore+1,
					elementid.length); } return id; } CustomDraggable.prototype = (new
					Rico.Draggable()).extend( { initialize: function( htmlElement, name ) {
					this.type = 'Custom'; this.htmlElement = $(htmlElement); this.name = name; },
					endDrag: function() { var el = this.htmlElement; var parent = el.parentNode; var
					underscore = el.id.indexOf('_'); var def = getIdFromElementId(el.id); var term =
					getIdFromElementId(parent.id); var hiddenelement = 'sel_matching_' + def;
					$(hiddenelement).value = term; } }); var dropzones = new Array(); var
					dragelements = new Array(); var dragelementspos = new Array(); <xsl:for-each
						select="//render_choice/response_label">
						<xsl:if test="@match_max">
							<xsl:text>dragelements.push('definition_</xsl:text>
							<xsl:value-of select="@ident"/>
							<xsl:text>');</xsl:text>
						</xsl:if>
					</xsl:for-each>
					<xsl:for-each select="//render_choice/response_label">
						<xsl:choose>
							<xsl:when test="@match_max"/>
							<xsl:otherwise>
								<xsl:text>dropzones.push('dropzone_</xsl:text>
								<xsl:value-of select="@ident"/>
								<xsl:text>');</xsl:text>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:for-each> for (var i = 0; i &lt; dragelements.length; i++) {
					dndMgr.registerDraggable(new CustomDraggable(dragelements[i], dragelements[i]));
					} for (var i = 0; i &lt; dropzones.length; i++) {
					dndMgr.registerDropZone(new Rico.Dropzone(dropzones[i])); } function
					setDragelementPositions() { for (var i = 0; i &lt; dragelements.length; i++)
					{ dragelementspos.push(RicoUtil.toDocumentPosition($(dragelements[i]))); } }
					function resetFast() { for (var i = 0; i &lt; dragelements.length; i++) {
					$(dragelements[i]).style.position = &quot;absolute&quot;; new
					Rico.Effect.Position(dragelements[i], $(dragelementspos[i]).x,
					$(dragelementspos[i]).y, 1, 1, true); } } function addSolution(dropzone,
					dragelement) { var dragname = 'definition_' + dragelement; var dropname =
					'dropzone_' + dropzone; $(dropname).appendChild($(dragname)); var hiddenname =
					'sel_matching_' + dragelement; $(hiddenname).value = dropzone; } function
					resetAnimated() { for (var i = 0; i &lt; dragelements.length; i++) {
					$(dragelements[i]).style.position = &quot;absolute&quot;; new
					Rico.Effect.Position(dragelements[i], $(dragelementspos[i]).x,
					$(dragelementspos[i]).y, 200, 20, true); } for (var i = 0; i &lt;
					dragelements.length; i++) { var id = getIdFromElementId(dragelements[i]);
					$('sel_matching_' + id).value = 'initial_value_' + id; } } </script>
			</xsl:when>
			<xsl:otherwise>
				<table class="nobackground">

					<!-- matching -->
					<xsl:for-each select="render_choice/response_label">
						<xsl:if test="@match_max">
							<tr>
								<td class="nobackground">
									<select>
										<xsl:attribute name="name"> sel_matching_<xsl:value-of
												select="@ident"/>
										</xsl:attribute>

										<xsl:variable name="mgrp">
											<xsl:value-of select="@match_group"/>
										</xsl:variable>
										<xsl:variable name="clabel">
											<xsl:value-of select="@ident"/>
										</xsl:variable>

										<option value="-1" selected="selected"> -- <xsl:value-of
												select="//LVs/LV[@name='please_select']/@value"/> -- </option>
										<xsl:for-each select="../response_label">
											<xsl:if
												test="contains($mgrp, concat(',',@ident,',')) or
								starts-with($mgrp, concat(@ident,',')) or
								$mgrp = @ident or
								substring($mgrp, string-length($mgrp) - string-length(@ident)) = concat(',',@ident)">
												<option>
												<xsl:attribute name="value">
												<xsl:value-of select="@ident"/>
												</xsl:attribute>
												<xsl:attribute name="dummy"> match<xsl:value-of
												select="$clabel"/>_<xsl:value-of
												select="@ident"/>
												</xsl:attribute>
												<xsl:value-of select="material/mattext"/>
												</option>
											</xsl:if>
										</xsl:for-each>
									</select>
								</td>
								<td class="nobackground">
									<xsl:value-of select="//LVs/LV[@name='matches']/@value"/>
								</td>
								<td class="nobackground">
									<xsl:if test="material/mattext">
										<b>
											<xsl:value-of select="material/mattext"/>
										</b>
									</xsl:if>
									<xsl:if test="material/matimage">
										<a target="_new">
											<xsl:attribute name="href">
												/assessment/<xsl:call-template
												name="replace-qtiident">
												<xsl:with-param name="original">
												<xsl:value-of
												select="//questestinterop/item/@ident"/>
												</xsl:with-param>
												<xsl:with-param name="substring"
												>qst_</xsl:with-param>
												</xsl:call-template>/images/<xsl:value-of
												select="material/matimage/@label"/>
											</xsl:attribute>
											<img border="0">
												<xsl:attribute name="src">
												/assessment/<xsl:call-template
												name="replace-qtiident">
												<xsl:with-param name="original">
												<xsl:value-of
												select="//questestinterop/item/@ident"
												/>
												</xsl:with-param>
												<xsl:with-param name="substring"
												>qst_</xsl:with-param>
												</xsl:call-template>/images/<xsl:value-of
												select="material/matimage/@label"
												/>.thumb.jpg </xsl:attribute>
											</img>
										</a>
									</xsl:if>
								</td>
							</tr>
						</xsl:if>
					</xsl:for-each>
				</table>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="termtaker">
		<xsl:param name="i"/>
		<xsl:for-each select="//render_choice/response_label">
			<xsl:if test="@match_max">
				<xsl:if test="position() = $i">
					<td align="right">
						<xsl:if test="material/mattext">
							<div class="textbox">
								<xsl:attribute name="id"> definition_<xsl:value-of select="@ident"/>
								</xsl:attribute>
								<xsl:value-of select="material/mattext"/>
							</div>
						</xsl:if>
						<xsl:if test="material/matimage">
							<div class="imagebox">
								<xsl:attribute name="id"> definition_<xsl:value-of select="@ident"/>
								</xsl:attribute>
								<table border="0">
									<tr>
										<td align="left">
											<img border="0">
												<xsl:attribute name="id"> thumb_<xsl:value-of
												select="@ident"/>
												</xsl:attribute>
												<xsl:attribute name="src">
												/assessment/<xsl:call-template
												name="replace-qtiident">
												<xsl:with-param name="original">
												<xsl:value-of
												select="//questestinterop/item/@ident"
												/>
												</xsl:with-param>
												<xsl:with-param name="substring"
												>qst_</xsl:with-param>
												</xsl:call-template>/images/<xsl:value-of
												select="material/matimage/@label"
												/>.thumb.jpg </xsl:attribute>
											</img>
										</td>
										<td valign="top">
											<a target="_new">
												<xsl:attribute name="id"> enlarge_<xsl:value-of
												select="@ident"/>
												</xsl:attribute>
												<xsl:attribute name="href">
												/assessment/<xsl:call-template
												name="replace-qtiident">
												<xsl:with-param name="original">
												<xsl:value-of
												select="//questestinterop/item/@ident"
												/>
												</xsl:with-param>
												<xsl:with-param name="substring"
												>qst_</xsl:with-param>
												</xsl:call-template>/images/<xsl:value-of
												select="material/matimage/@label"/>
												</xsl:attribute>
												<img border="0">
												<xsl:attribute name="src">
												<!--xsl:value-of select="$enlarge_path"/-->
												</xsl:attribute>
												</img>
											</a>
										</td>
									</tr>
								</table>
							</div>
						</xsl:if>
					</td>
				</xsl:if>
			</xsl:if>
		</xsl:for-each>
	</xsl:template>

	<xsl:template name="replace-qtiident">
		<xsl:param name="original"/>
		<xsl:param name="substring"/>
		<xsl:value-of select="substring-after($original, $substring)"/>
	</xsl:template>

	<!-- helper function to replace strings -->
	<xsl:template name="replace-substring">
		<xsl:param name="original"/>
		<xsl:param name="substring"/>
		<xsl:param name="replacement" select="''"/>
		<xsl:variable name="first">
			<xsl:choose>
				<xsl:when test="contains($original, $substring)">
					<xsl:value-of select="substring-before($original, $substring)"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select="$original"/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:variable name="middle">
			<xsl:choose>
				<xsl:when test="contains($original, $substring)">
					<xsl:value-of select="$replacement"/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:text/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:variable name="last">
			<xsl:choose>
				<xsl:when test="contains($original, $substring)">
					<xsl:choose>
						<xsl:when
							test="contains(substring-after($original, $substring), 
                                   $substring)">
							<xsl:call-template name="replace-substring">
								<xsl:with-param name="original">
									<xsl:value-of select="substring-after($original, $substring)"/>
								</xsl:with-param>
								<xsl:with-param name="substring">
									<xsl:value-of select="$substring"/>
								</xsl:with-param>
								<xsl:with-param name="replacement">
									<xsl:value-of select="$replacement"/>
								</xsl:with-param>
							</xsl:call-template>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select="substring-after($original, $substring)"/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:when>
				<xsl:otherwise>
					<xsl:text/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:value-of select="concat($first, $middle, $last)"/>
	</xsl:template>

	<!-- dump language variable data -->
	<xsl:template match="LV"/>
	<xsl:template match="LVs"/>

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
