<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
								xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
								xmlns:xhtml="http://www.w3.org/1999/xhtml">
<!-- removed xmlns:str="http://exslt.org/strings" -->

<xsl:output method="xml" omit-xml-declaration="yes" />
<!-- <xsl:output method="html"/> -->

<!-- changing the default template to output all unknown tags -->
<xsl:template match="*">
  <xsl:copy-of select="."/>
</xsl:template>

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
	<xsl:if test = "count(./PageObject) = 0">
		<xsl:call-template name="outputImageMaps" />
	</xsl:if>
</xsl:template>

<!-- PageObject -->
<xsl:param name="mode"/>
<xsl:param name="media_mode"/>
<xsl:param name="pg_title"/>
<xsl:param name="pg_title_class"/>
<xsl:param name="pg_id"/>
<xsl:param name="ref_id"/>
<xsl:param name="parent_id"/>
<xsl:param name="link_params"/>
<xsl:param name="download_script"/>
<xsl:param name="pg_frame"/>
<xsl:param name="webspace_path"/>
<xsl:param name="enlarge_path"/>
<xsl:param name="img_add"/>
<xsl:param name="img_col"/>
<xsl:param name="img_row"/>
<xsl:param name="img_item"/>
<xsl:param name="img_path"/>
<xsl:param name="med_disabled_path"/>
<xsl:param name="bib_id" />
<xsl:param name="citation" />
<xsl:param name="map_item" />
<xsl:param name="map_edit_mode" />
<xsl:param name="javascript" />
<xsl:param name="image_map_link" />
<xsl:param name="file_download_link" />
<xsl:param name="encoded_download_script"/>
<xsl:param name="fullscreen_link" />
<xsl:param name="enable_split_new"/>
<xsl:param name="enable_split_next"/>
<xsl:param name="paragraph_plugins"/>
<xsl:param name="pagebreak"/>
<xsl:param name="page"/>
<xsl:param name="citate_from"/>
<xsl:param name="citate_to"/>
<xsl:param name="citate_page"/>
<xsl:param name="citate"/>
<xsl:param name="enable_rep_objects"/>
<xsl:param name="enable_map"/>
<xsl:param name="enable_tabs"/>
<xsl:param name="enable_file_list"/>

<xsl:template match="PageObject">
	<!-- <xsl:value-of select="@HierId"/> -->
	<xsl:if test="$pg_title != ''">
		<div class="ilc_PageTitle">
		<xsl:if test="$pg_title_class = ''">
			<xsl:attribute name="class">ilc_PageTitle</xsl:attribute>
		</xsl:if>
		<xsl:if test="$pg_title_class != ''">
			<xsl:attribute name="class"><xsl:value-of select="$pg_title_class" /></xsl:attribute>
		</xsl:if>
		<xsl:value-of select="$pg_title"/>
		</div>
	</xsl:if>
	<xsl:if test="$mode = 'edit'">
		<xsl:if test="$javascript = 'enable'">
			<div class="il_droparea">
				<xsl:attribute name="id">TARGET<xsl:value-of select="@HierId"/>:<xsl:value-of select="@PCID"/></xsl:attribute>
				<xsl:attribute name="onMouseOver">doMouseOver(this.id, 'il_droparea_active');</xsl:attribute>
				<xsl:attribute name="onMouseOut">doMouseOut(this.id, 'il_droparea');</xsl:attribute>
				<xsl:attribute name="onClick">doMouseClick(event, 'TARGET' + '<xsl:value-of select="@HierId"/>' + ':' + '<xsl:value-of select="@PCID"/>');</xsl:attribute>
			<img src="./templates/default/images/ed_plus.gif" border="0" width="30" height="12" />
			</div>
			<!-- insert menu for drop area -->
			<xsl:call-template name="EditMenu">
				<xsl:with-param name="hier_id" select="@HierId" />
				<xsl:with-param name="droparea">y</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="@HierId != 'pg' or $javascript != 'enable'">
			<xsl:call-template name="EditMenu">
				<xsl:with-param name="hier_id" select="@HierId" />
				<xsl:with-param name="edit">n</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<!-- <br/> -->
	</xsl:if>
	<xsl:if test="$citation = 1">
		<xsl:if test="count(//PageTurn) &gt; 0">
		<input type="checkbox" name="pgt_id[0]">
			<xsl:attribute name="value">
			<xsl:call-template name="getFirstPageNumber" />
			</xsl:attribute>
		</input>

		<xsl:call-template name="showCitationSelect">
			<xsl:with-param name="pos" select="0" />
		</xsl:call-template>
		<xsl:text> </xsl:text>
        <span class="ilc_Strong">[<xsl:value-of select="$page"/><xsl:text> </xsl:text><xsl:call-template name="getFirstPageNumber"/>]</span>
		</xsl:if>
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

	<!-- Pageturn List -->
	<xsl:if test="count(//PageTurn) > 0">
		<hr />
		<xsl:variable name="entry_two"><xsl:call-template name="get_bib_item" /></xsl:variable>
		<xsl:for-each select="//PageTurn">
			<xsl:variable name="entry_one"><xsl:value-of select="./BibItemIdentifier/@Entry" /></xsl:variable>
			<xsl:if test="contains($entry_two,$entry_one)">
			<div class="ilc_PageTurn">
				<a>
				<xsl:attribute name="name">pt<xsl:number count="PageTurn" level="multiple"/></xsl:attribute>
                <span class="ilc_Strong">[<xsl:value-of select="$pagebreak" /><xsl:text> </xsl:text><xsl:number count="PageTurn" level="multiple"/>] </span>
				</a>
				<xsl:call-template name="searchEdition">
				<xsl:with-param name="Entry">
					<xsl:value-of select="$entry_one" />
				</xsl:with-param>
				</xsl:call-template>
			</div>
			</xsl:if>
		</xsl:for-each>
		<xsl:if test="$citation = 1">
			<xsl:call-template name="showCitationSubmit" />
		</xsl:if>
	</xsl:if>

	<!-- image map data -->
	<xsl:call-template name="outputImageMaps" />

</xsl:template>

<!-- output image maps -->
<xsl:template name="outputImageMaps">
	<xsl:for-each select="//MediaItem">
		<xsl:variable name="corig"><xsl:value-of select="../@Id"/></xsl:variable>
		<xsl:variable name="corigp"><xsl:value-of select="@Purpose"/></xsl:variable>
		
		<xsl:choose>
			<!-- If we got a alias item map, take this -->
			<xsl:when test="//MediaAlias[@OriginId = $corig]/../MediaAliasItem[@Purpose = $corigp]/MapArea[1]">
				<xsl:for-each select="//MediaAlias[@OriginId = $corig]/../MediaAliasItem[@Purpose = $corigp]/MapArea[1]">
					<map>
						<xsl:attribute name="name">map_<xsl:value-of select="$corig"/>_<xsl:value-of select="$corigp"/></xsl:attribute>
						<xsl:call-template name="outputImageMapAreas" />
					</map>
				</xsl:for-each>
			</xsl:when>
			<xsl:otherwise>
				<!-- Otherwose, if we got an object item map, take this -->
				<xsl:for-each select="./MapArea[1]">
					<map>
						<xsl:attribute name="name">map_<xsl:value-of select="$corig"/>_<xsl:value-of select="$corigp"/></xsl:attribute>
						<xsl:call-template name="outputImageMapAreas" />
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
			<xsl:attribute name="shape"><xsl:value-of select="@Shape"/></xsl:attribute>
			<xsl:attribute name="coords"><xsl:value-of select="@Coords"/></xsl:attribute>
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
					<xsl:value-of select="//IntLinkInfos/IntLinkInfo[@Type=$type and @TargetFrame=$targetframe and @Target=$target]/@LinkHref"/>
				</xsl:variable>
				<xsl:variable name="link_target">
					<xsl:value-of select="//IntLinkInfos/IntLinkInfo[@Type=$type and @TargetFrame=$targetframe and @Target=$target]/@LinkTarget"/>
				</xsl:variable>

				<!-- set attributes -->
				<xsl:attribute name="href"><xsl:value-of select="$link_href"/></xsl:attribute>
				<xsl:if test="$link_target != ''">
					<xsl:attribute name="target"><xsl:value-of select="$link_target"/></xsl:attribute>
				</xsl:if>

				<xsl:attribute name="title"><xsl:value-of select="."/></xsl:attribute>
				<xsl:attribute name="alt"><xsl:value-of select="."/></xsl:attribute>
			</xsl:for-each>
			<xsl:for-each select="./ExtLink">
				<xsl:attribute name="href"><xsl:value-of select="@Href"/></xsl:attribute>
				<xsl:attribute name="title"><xsl:value-of select="."/></xsl:attribute>
				<xsl:attribute name="alt"><xsl:value-of select="."/></xsl:attribute>
				<xsl:attribute name="target">_blank</xsl:attribute>
			</xsl:for-each>
		</area>
	</xsl:for-each>
</xsl:template>

<!-- SHOW SELECTBOX OF CITATIONS -->
<xsl:template name="showCitationSelect">
	<xsl:param name="pos" />
	<xsl:text> </xsl:text>
	<select class="ilEditSelect">
		<xsl:attribute name="name">ct_option[<xsl:value-of select="$pos" />]</xsl:attribute>
        <option value="single"><xsl:value-of select="$citate_page"/></option>
		<option value="from"><xsl:value-of select="$citate_from"/></option>
		<option value="to"><xsl:value-of select="$citate_to"/></option>
		<option value="f">F</option>
		<option value="ff">FF</option>
	</select>
</xsl:template>

<!-- SHOW CITATION SUBMIT BUTTON -->
<xsl:template name="showCitationSubmit">
	<br />
    <input class="ilEditSubmit" type="submit" name="cmd[citation]">
    <xsl:attribute name="value">
      <xsl:value-of select="$citate"/>
    </xsl:attribute>
  </input>
</xsl:template>

<!-- GET BIB ITEM ENTRY BY BIB ID -->
<xsl:template name="get_bib_item">
	<xsl:for-each select="//Bibliography/BibItem">
		<xsl:if test="contains($bib_id,concat(',',position(),','))">
		<xsl:value-of select="./Identifier/@Entry" /><xsl:text>,</xsl:text>
		</xsl:if>
	</xsl:for-each>
</xsl:template>

<!-- GET PREDECESSOR OF FIRST PAGE NUMBER USED FOR CITATION -->
<xsl:template name="getFirstPageNumber">
	<xsl:variable name="entry_two"><xsl:call-template name="get_bib_item" /></xsl:variable>
	<xsl:for-each select="//PageTurn[contains($entry_two,./BibItemIdentifier/@Entry)]">
		<xsl:if test="position() = 1">
		<xsl:choose>
			<xsl:when test="@NumberingType = 'Roman'">
			<xsl:number format="i" value="@Number - 1" />
			</xsl:when>
			<xsl:when test="@NumberingType = 'Arabic'">
			<xsl:number format="1"  value="@Number - 1" />
			</xsl:when>
			<xsl:when test="@NumberingType = 'Alpanumeric'">
			<xsl:number format="A" value="@Number - 1" />
			</xsl:when>
		</xsl:choose>
		</xsl:if>
	</xsl:for-each>
</xsl:template>

<!-- Sucht zu den Pageturns die Edition und das Jahr raus -->
<xsl:template name="searchEdition">
	<xsl:param name="Entry"/>
	<xsl:variable name="act_number">
		<xsl:value-of select="./@Number" />
	</xsl:variable>
	<xsl:for-each select="//Bibliography/BibItem">
		<xsl:variable name="entry_cmp"><xsl:value-of select="./Identifier/@Entry" /></xsl:variable>
		<xsl:if test="$entry_cmp=$Entry">
          <xsl:value-of select="$page" /><xsl:text>: </xsl:text><xsl:value-of select="$act_number" /><xsl:text>, </xsl:text>
		</xsl:if>
		<xsl:if test="$entry_cmp=$Entry">
		<xsl:value-of select="./Edition/."/><xsl:text>, </xsl:text><xsl:value-of select="./Year/."/>
		</xsl:if>
	</xsl:for-each>
</xsl:template>

<!-- Bibliography-Tag nie ausgeben -->
<xsl:template match="Bibliography"/>

<!-- PageContent -->
<xsl:template match="PageContent">
	<xsl:if test="$mode = 'edit'">
		<xsl:variable name="content_type" select="name(./*[1])"/>
		<div>
			<xsl:if test="(./MediaObject/MediaAliasItem[@Purpose = 'Standard']/Layout/@HorizontalAlign = 'RightFloat') or
				(./Map/Layout/@HorizontalAlign = 'RightFloat') or
				(./Table/@HorizontalAlign = 'RightFloat')">
				<xsl:attribute name="style"><xsl:if test="./Table/@Width">width:<xsl:value-of select="./Table/@Width"/>;</xsl:if> float:right; clear:both; background-color:#FFFFFF;</xsl:attribute>
			</xsl:if>
			<xsl:if test="(./MediaObject/MediaAliasItem[@Purpose = 'Standard']/Layout/@HorizontalAlign = 'LeftFloat') or
				(./Map/Layout/@HorizontalAlign = 'LeftFloat') or
				(./Table/@HorizontalAlign = 'LeftFloat')">
				<xsl:attribute name="style"><xsl:if test="./Table/@Width">width:<xsl:value-of select="./Table/@Width"/>;</xsl:if> float:left; clear:both; background-color:#FFFFFF;</xsl:attribute>
			</xsl:if>
			<div>
				<xsl:if test="not(../../../@DataTable) or (../../../@DataTable = 'n')">
					<xsl:if test="$javascript='enable'">
						<xsl:attribute name="class">il_editarea</xsl:attribute>
					</xsl:if>
					<xsl:if test="$javascript!='enable'">
						<xsl:attribute name="class">il_editarea_nojs</xsl:attribute>
					</xsl:if>
					<xsl:if test="@Enabled='False'">
						<xsl:attribute name="class">il_editarea_disabled</xsl:attribute>
					</xsl:if>
					<xsl:if test="$javascript = 'enable'">
						<xsl:attribute name="onMouseOver">doMouseOver(this.id, 'il_editarea_active');</xsl:attribute>
						<xsl:attribute name="onMouseOut">doMouseOut(this.id, 'il_editarea');</xsl:attribute>
						<xsl:attribute name="onMouseDown">doMouseDown(this.id);</xsl:attribute>
						<xsl:attribute name="onMouseUp">doMouseUp(this.id);</xsl:attribute>
						<xsl:attribute name="onClick">doMouseClick(event,this.id,'<xsl:value-of select="$content_type"/>');</xsl:attribute>
						<xsl:attribute name="onDblClick">doMouseDblClick(event,this.id,'<xsl:value-of select="$content_type"/>');</xsl:attribute>
					</xsl:if>
				</xsl:if>
				<xsl:attribute name="id">CONTENT<xsl:value-of select="@HierId"/>:<xsl:value-of select="@PCID"/></xsl:attribute>
		
				<xsl:apply-templates>
					<xsl:with-param name="par_counter" select ="position()" />
				</xsl:apply-templates>
			</div>
			
			<!-- drop area -->
			<xsl:if test="(not(../../../@DataTable) or (../../../@DataTable = 'n')) and ($javascript != 'disable')">
				<div class="il_droparea">
					<xsl:attribute name="onMouseOver">doMouseOver(this.id, 'il_droparea_active');</xsl:attribute>
					<xsl:attribute name="onMouseOut">doMouseOut(this.id, 'il_droparea');</xsl:attribute>
					<xsl:attribute name="onClick">doMouseClick(event, 'TARGET' + '<xsl:value-of select="@HierId"/>' + ':' + '<xsl:value-of select="@PCID"/>');</xsl:attribute>
					<xsl:attribute name="id">TARGET<xsl:value-of select="@HierId"/>:<xsl:value-of select="@PCID"/></xsl:attribute><img src="./templates/default/images/ed_plus.gif" border="0" width="30" height="12" />
				</div>
			</xsl:if>
	
			<!-- insert menu for drop area -->
			<xsl:if test="$mode = 'edit'">
				<xsl:if test="$javascript='enable'">
					<xsl:call-template name="EditMenu">
						<xsl:with-param name="hier_id" select="@HierId" />
						<xsl:with-param name="droparea">y</xsl:with-param>
					</xsl:call-template>
				</xsl:if>
			</xsl:if>
		
		</div>
	</xsl:if>
	<xsl:if test="$mode != 'edit' and (not(@Enabled) or @Enabled='True')">
		<xsl:if test="//PageObject/DivClass/@HierId = ./@HierId">
			<div>
				<xsl:attribute name="class"><xsl:value-of select="//PageObject/DivClass[@HierId = ./@HierId]/@Class" /></xsl:attribute>
				<xsl:apply-templates>
					<xsl:with-param name="par_counter" select ="position()" />
				</xsl:apply-templates>
			</div>
		</xsl:if>
		<xsl:if test="not(//PageObject/DivClass/@HierId = ./@HierId)">
			<xsl:apply-templates>
				<xsl:with-param name="par_counter" select ="position()" />
			</xsl:apply-templates>
		</xsl:if>
	</xsl:if>
</xsl:template>

<!-- edit return anchors-->
<xsl:template name="EditReturnAnchors">
	<xsl:if test="$mode = 'edit'">
		<a>
		<xsl:choose>
			<xsl:when test="@HierId">
				<xsl:attribute name="name">jump<xsl:value-of select="@HierId"/>
				</xsl:attribute>
			</xsl:when>
			<xsl:when test="../@HierId">
				<xsl:attribute name="name">jump<xsl:value-of select="../@HierId"/>
				</xsl:attribute>
			</xsl:when>
			<xsl:otherwise>
				<xsl:attribute name="name">jump<xsl:value-of select="../../@HierId"/>
				</xsl:attribute>
			</xsl:otherwise>
		</xsl:choose>
		</a>
	</xsl:if>
</xsl:template>

<!-- Edit Menu -->
<xsl:template name="EditMenu">
	<xsl:param name="hier_id"/>
	<xsl:param name="pc_id"/>
	<xsl:param name="edit"/>
	<xsl:param name="droparea">n</xsl:param>
	
	<xsl:if test = "$javascript = 'enable'">
	<div style="position:absolute;left:0;top:0;visibility:hidden;">
		<xsl:if test = "$droparea = 'n'">
			<xsl:attribute name="id">contextmenu_<xsl:value-of select="$hier_id"/></xsl:attribute>
		</xsl:if>
		<xsl:if test = "$droparea = 'y'">
			<xsl:attribute name="id">dropareamenu_<xsl:value-of select="$hier_id"/></xsl:attribute>
		</xsl:if>
		<table class="il_editmenu" cellspacing="0" cellpadding="3">
			<xsl:if test = "$droparea = 'n'">
				<xsl:call-template name="EditMenuItems">
					<xsl:with-param name="edit"><xsl:value-of select="$edit"/></xsl:with-param>
					<xsl:with-param name="hier_id"><xsl:value-of select="$hier_id"/></xsl:with-param>
				</xsl:call-template>
			</xsl:if>
			<xsl:if test = "$droparea = 'y'">
				<xsl:call-template name="EditMenuInsertItems"/>
			</xsl:if>
		</table>
	</div>
	</xsl:if>
	
	<xsl:if test="$javascript = 'disable'">
		<select size="1" class="ilEditSelect">
			<xsl:attribute name="name">command<xsl:value-of select="$hier_id"/></xsl:attribute>
			<xsl:call-template name="EditMenuItems">
				<xsl:with-param name="edit"><xsl:value-of select="$edit"/></xsl:with-param>
				<xsl:with-param name="hier_id"><xsl:value-of select="$hier_id"/></xsl:with-param>
			</xsl:call-template>
		</select>
		<input class="ilEditSubmit" type="submit">
			<xsl:attribute name="value"><xsl:value-of select="//LVs/LV[@name='ed_go']/@value"/></xsl:attribute>
			<xsl:attribute name="name">cmd[exec_<xsl:value-of select="$hier_id"/>:<xsl:value-of select="$pc_id"/>]</xsl:attribute>
		</input>
	</xsl:if>
	
</xsl:template>

<!-- Edit Menu Items -->
<xsl:template name="EditMenuItems">
	<xsl:param name="edit"/>
	<xsl:param name="hier_id"/>

	<xsl:if test="$edit = 'y'">
		<xsl:call-template name="EditMenuItem">
			<xsl:with-param name="command">edit</xsl:with-param>
			<xsl:with-param name="langvar">ed_edit</xsl:with-param>
		</xsl:call-template>
	</xsl:if>
	<xsl:if test="$edit = 'p'">
		<xsl:call-template name="EditMenuItem">
			<xsl:with-param name="command">edit</xsl:with-param>
			<xsl:with-param name="langvar">ed_edit_prop</xsl:with-param>
		</xsl:call-template>
	</xsl:if>

	<xsl:if test = "$javascript = 'disable'">
		<xsl:call-template name="EditMenuInsertItems"/>
	</xsl:if>
	
	<xsl:if test="$edit = 'y' or $edit = 'p'">
	
		<!-- delete -->
		<xsl:call-template name="EditMenuItem">
			<xsl:with-param name="command">delete</xsl:with-param>
			<xsl:with-param name="langvar">ed_delete</xsl:with-param>
		</xsl:call-template>
		
		<xsl:if test = "$javascript = 'enable'">
			<xsl:call-template name="EditMenuItem">
				<xsl:with-param name="command">deactivate</xsl:with-param>
				<xsl:with-param name="langvar">de_activate</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		
		<xsl:if test="$javascript = 'disable'">
			<!-- move after -->
			<xsl:call-template name="EditMenuItem">
				<xsl:with-param name="command">moveAfter</xsl:with-param>
				<xsl:with-param name="langvar">ed_moveafter</xsl:with-param>
			</xsl:call-template>
			
			<!-- move before -->
			<xsl:call-template name="EditMenuItem">
				<xsl:with-param name="command">moveBefore</xsl:with-param>
				<xsl:with-param name="langvar">ed_movebefore</xsl:with-param>
			</xsl:call-template>
		</xsl:if>

		<!-- split page to new page -->
		<xsl:if test = "substring-after($hier_id,'_') = '' and $hier_id != '1' and $enable_split_new = 'y'">
			<xsl:call-template name="EditMenuItem">
				<xsl:with-param name="command">splitPage</xsl:with-param>
				<xsl:with-param name="langvar">ed_split_page</xsl:with-param>
			</xsl:call-template>
		</xsl:if>

		<!-- split page to next page -->
		<xsl:if test = "substring-after($hier_id,'_') = '' and $hier_id != '1' and $enable_split_next = 'y'">
			<xsl:call-template name="EditMenuItem">
				<xsl:with-param name="command">splitPageNext</xsl:with-param>
				<xsl:with-param name="langvar">ed_split_page_next</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		
	</xsl:if>
		
</xsl:template>

<!-- Insert Menu Items -->
<xsl:template name="EditMenuInsertItems">

	<!-- insert paragraph -->
	<xsl:call-template name="EditMenuItem">
		<xsl:with-param name="command">insert_par</xsl:with-param>
		<xsl:with-param name="langvar">ed_insert_par</xsl:with-param>
	</xsl:call-template>
	
	<!-- insert repository objects -->
	<xsl:if test = "$enable_rep_objects = 'y'">
		<xsl:call-template name="EditMenuItem">
			<xsl:with-param name="command">insert_repobj</xsl:with-param>
			<xsl:with-param name="langvar">ed_insert_repobj</xsl:with-param>
		</xsl:call-template>
	</xsl:if>

	<!-- insert media object -->
	<xsl:call-template name="EditMenuItem">
		<xsl:with-param name="command">insert_mob</xsl:with-param>
		<xsl:with-param name="langvar">ed_insert_media</xsl:with-param>
	</xsl:call-template>

	<!-- insert file list -->
	<xsl:if test = "$enable_file_list = 'y'">
		<xsl:call-template name="EditMenuItem">
			<xsl:with-param name="command">insert_flst</xsl:with-param>
			<xsl:with-param name="langvar">ed_insert_filelist</xsl:with-param>
		</xsl:call-template>
	</xsl:if>

	<!-- insert data table -->
	<xsl:call-template name="EditMenuItem">
		<xsl:with-param name="command">insert_dtab</xsl:with-param>
		<xsl:with-param name="langvar">ed_insert_dtable</xsl:with-param>
	</xsl:call-template>

	<!-- insert advanced table -->
	<xsl:call-template name="EditMenuItem">
		<xsl:with-param name="command">insert_tab</xsl:with-param>
		<xsl:with-param name="langvar">ed_insert_atable</xsl:with-param>
	</xsl:call-template>
	
	<!-- insert list -->
	<xsl:call-template name="EditMenuItem">
		<xsl:with-param name="command">insert_list</xsl:with-param>
		<xsl:with-param name="langvar">ed_insert_list</xsl:with-param>
	</xsl:call-template>
	
	<!-- insert section -->
	<xsl:call-template name="EditMenuItem">
		<xsl:with-param name="command">insert_sec</xsl:with-param>
		<xsl:with-param name="langvar">ed_insert_section</xsl:with-param>
	</xsl:call-template>
	
	<!-- insert map (geographical) -->
	<xsl:if test = "$enable_map = 'y'">
		<xsl:call-template name="EditMenuItem">
			<xsl:with-param name="command">insert_map</xsl:with-param>
			<xsl:with-param name="langvar">ed_insert_map</xsl:with-param>
		</xsl:call-template>
	</xsl:if>

	<!-- insert code -->
	<xsl:call-template name="EditMenuItem">
		<xsl:with-param name="command">insert_src</xsl:with-param>
		<xsl:with-param name="langvar">ed_insert_code</xsl:with-param>
	</xsl:call-template>

	<!-- insert tabbed content -->
	<xsl:if test = "$enable_tabs = 'y'">
		<xsl:call-template name="EditMenuItem">
			<xsl:with-param name="command">insert_tabs</xsl:with-param>
			<xsl:with-param name="langvar">ed_insert_tabs</xsl:with-param>
		</xsl:call-template>
	</xsl:if>
	
	<!-- insert plugged component -->
	<xsl:for-each select="//ComponentPlugins/ComponentPlugin">
		<xsl:call-template name="EditMenuItem">
			<xsl:with-param name="command">insert_plug_<xsl:value-of select="@Name" /></xsl:with-param>
			<xsl:with-param name="text"><xsl:value-of select="@InsertText" /></xsl:with-param>
		</xsl:call-template>
	</xsl:for-each>
	
	<!-- paste from clipboard -->
	<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">pasteFromClipboard</xsl:with-param>
	<xsl:with-param name="langvar">ed_paste_clip</xsl:with-param></xsl:call-template>

</xsl:template>

<!-- Align Menu Items -->
<xsl:template name="EditMenuAlignItems">

	<!-- left align -->
	<xsl:call-template name="EditMenuItem">
		<xsl:with-param name="command">leftAlign</xsl:with-param>
		<xsl:with-param name="langvar">ed_align_left</xsl:with-param>
	</xsl:call-template>
	
	<!-- right align -->
	<xsl:call-template name="EditMenuItem">
		<xsl:with-param name="command">rightAlign</xsl:with-param>
		<xsl:with-param name="langvar">ed_align_right</xsl:with-param>
	</xsl:call-template>
	
	<!-- center align -->
	<xsl:call-template name="EditMenuItem">
		<xsl:with-param name="command">centerAlign</xsl:with-param>
		<xsl:with-param name="langvar">ed_align_center</xsl:with-param>
	</xsl:call-template>
	
	<!-- left float align -->
	<xsl:call-template name="EditMenuItem">
		<xsl:with-param name="command">leftFloatAlign</xsl:with-param>
		<xsl:with-param name="langvar">ed_align_left_float</xsl:with-param>
	</xsl:call-template>

	<!-- right float align -->
	<xsl:call-template name="EditMenuItem">
		<xsl:with-param name="command">rightFloatAlign</xsl:with-param>
		<xsl:with-param name="langvar">ed_align_right_float</xsl:with-param>
	</xsl:call-template>

</xsl:template>


<!-- Edit Menu Item-->
<xsl:template name="EditMenuItem">
	<xsl:param name="command"/>
	<xsl:param name="langvar"/>
	<xsl:param name="text"/>
	
	<xsl:if test = "$javascript = 'disable'">
		<option>
			<xsl:attribute name="value"><xsl:value-of select="$command"/></xsl:attribute>
			<xsl:if test="$text = ''">
				<xsl:value-of select="//LVs/LV[@name=$langvar]/@value"/>
			</xsl:if>
			<xsl:if test="$text != ''">
				<xsl:value-of select="$text"/>
			</xsl:if>
		</option>
	</xsl:if>
	<xsl:if test = "$javascript = 'enable'">
		<tr>
			<td class="small" style="white-space:nowrap;" onMouseOver="M_in(this);" onMouseOut="M_out(this);">
			<xsl:attribute name="onClick">doActionForm('cmd[exec]', 'command', '<xsl:value-of select="$command"/>', '');</xsl:attribute>
			<xsl:if test="$text = ''">
				<xsl:value-of select="//LVs/LV[@name=$langvar]/@value"/>
			</xsl:if>
			<xsl:if test="$text != ''">
				<xsl:value-of select="$text"/>
			</xsl:if>
			</td>
		</tr>
	</xsl:if>
</xsl:template>

<!-- Icon -->
<xsl:template name="Icon">
	<xsl:param name="img_src"/>
	<xsl:param name="img_id"/>
	<xsl:param name="float">n</xsl:param>

	<img border="0">
		<xsl:if test="$float = 'y'">
			<xsl:attribute name="style"></xsl:attribute>
		</xsl:if>
		<xsl:attribute name="onMouseOver">doMouseOver(this.id);</xsl:attribute>
		<xsl:attribute name="onMouseOut">doMouseOut(this.id,false);</xsl:attribute>
		<xsl:attribute name="onMouseDown">doMouseDown(this.id);</xsl:attribute>
		<xsl:attribute name="onMouseUp">doMouseUp(this.id);</xsl:attribute>
		<xsl:attribute name="onClick">doMouseClick(event,this.id,'PageObject');</xsl:attribute>
		<xsl:attribute name="id"><xsl:value-of select="$img_id"/></xsl:attribute>
		<xsl:attribute name="src"><xsl:value-of select="$img_src"/></xsl:attribute>
	</img>
</xsl:template>

<!-- Drop Area for Adding -->
<xsl:template name="DropArea">
	<xsl:param name="hier_id"/>
	<xsl:param name="pc_id"/>
<!-- <xsl:value-of select="$hier_id"/> -->
	<!-- Drop area -->
	<xsl:if test="$javascript != 'disable'">
		<div class="il_droparea">
			<xsl:attribute name="id">TARGET<xsl:value-of select="$hier_id"/>:<xsl:value-of select="$pc_id"/></xsl:attribute>
			<xsl:attribute name="onMouseOver">doMouseOver(this.id, 'il_droparea_active');</xsl:attribute>
			<xsl:attribute name="onMouseOut">doMouseOut(this.id, 'il_droparea');</xsl:attribute>
			<xsl:attribute name="onClick">doMouseClick(event, 'TARGET' + '<xsl:value-of select="@HierId"/>' + ':' + '<xsl:value-of select="@PCID"/>');</xsl:attribute>
		<img src="./templates/default/images/ed_plus.gif" border="0" width="30" height="12" />
		</div>
	</xsl:if>
	<!-- insert menu for drop area -->
	<xsl:call-template name="EditMenu">
		<xsl:with-param name="hier_id" select="$hier_id" />
		<xsl:with-param name="droparea">y</xsl:with-param>
	</xsl:call-template>

</xsl:template>

<!-- Paragraph -->
<xsl:template match="Paragraph">
	<xsl:param name="par_counter" select="-1" />

	<xsl:choose>
		<xsl:when test="not (@Characteristic) or @Characteristic != 'Code'">
		<div>
			<xsl:call-template name="ShowParagraph"/>
		</div>
		</xsl:when>
		<xsl:otherwise>
			<xsl:call-template name="ShowParagraph">
				<xsl:with-param name="p_id" select="$par_counter" />
			</xsl:call-template>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template name="ShowParagraph">
	<xsl:param name="p_id" select = "-1"/>
	<xsl:if test="not(@Characteristic)">
	<xsl:attribute name="class">ilc_Standard</xsl:attribute>
	</xsl:if>
	<xsl:if test="@Characteristic and not (@Characteristic = 'Code')">
	<xsl:attribute name="class">ilc_<xsl:value-of select="@Characteristic"/></xsl:attribute>
	</xsl:if>
	<xsl:call-template name="EditReturnAnchors"/>
	<!-- content -->
	<xsl:choose>
		<xsl:when test="@Characteristic = 'Code'">
			<xsl:call-template name='Sourcecode'>
				<!-- <xsl:with-param name="p_id" select="$p_id" /> -->
				<xsl:with-param name="p_id"><xsl:number count="Paragraph" level="any"/></xsl:with-param>
			</xsl:call-template>
		</xsl:when>
		<xsl:otherwise>
		<xsl:apply-templates/>
		</xsl:otherwise>
	</xsl:choose>

	<!-- command selectbox -->
	<xsl:if test="$mode = 'edit'">
		<br />
		<!-- <xsl:value-of select="../@HierId"/> -->
		<xsl:if test="((../../../../@DataTable != 'y' or not(../../../../@DataTable)))">
			<xsl:if test="$javascript='disable'">
			<br />
			<input type="checkbox" name="target[]">
				<xsl:attribute name="value"><xsl:value-of select="../@HierId"/>:<xsl:value-of select="../@PCID"/>
				</xsl:attribute>
			</input>
			</xsl:if>
			<xsl:call-template name="EditMenu">
				<xsl:with-param name="hier_id" select="../@HierId" />
				<xsl:with-param name="pc_id" select="../@PCID" />
				<xsl:with-param name="edit">y</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
	</xsl:if>
</xsl:template>

<xsl:template name="Sourcecode">
	<xsl:param name="p_id" select="-1"/>
	<p class="ilc_Code"><table class="ilc_Sourcecode" cellpadding="0" cellspacing="0" border="0">
		<!--- <xsl:value-of select="." /> -->
		[[[[[Code;<xsl:number count="Paragraph" level="any"/>]]]]]
		<xsl:if test="@DownloadTitle != ''" >
				<xsl:variable name="downloadtitle" select="@DownloadTitle"/>
				<xsl:choose>
					<xsl:when test="$mode = 'offline'" >
							<xsl:variable name="href" select="concat($webspace_path,'/codefiles/',$pg_id,'/',$p_id,'/',$downloadtitle)"/>
							<xsl:call-template name="DownloadLink">
								<xsl:with-param name="p_id" select="$p_id"/>
								<xsl:with-param name="downloadtitle" select="$downloadtitle"/>
								<xsl:with-param name="href" select="$href"/>
								<xsl:with-param name="subchar" select="-1"/>
							</xsl:call-template>
					</xsl:when>
					<xsl:when test="$mode = '' or $mode = 'presentation' or $mode = 'preview' or $mode = 'edit'" >
						<xsl:variable name="href" select="concat($download_script,'&amp;cmd=download_paragraph&amp;downloadtitle=',$downloadtitle,'&amp;pg_id=',$pg_id,'&amp;par_id=',$p_id)"/>
						<xsl:call-template name="DownloadLink">
							<xsl:with-param name="p_id" select="$p_id"/>
							<xsl:with-param name="downloadtitle" select="$downloadtitle"/>
							<xsl:with-param name="href" select="$href"/>
							<xsl:with-param name="subchar" select="@SubCharacteristic"/>
						</xsl:call-template>					
					</xsl:when >
				</xsl:choose>
		</xsl:if>
	</table></p>
</xsl:template>

<xsl:template name="DownloadLink">
	<xsl:param name="p_id" select="-1"/>
	<xsl:param name="downloadtitle" select="-1"/>
	<xsl:param name="href" select="'-1'"/>
	<xsl:param name="subchar" select="'-1'"/>
	
	<xsl:if test="$href != '-1'">
		<tr><td colspan="2"><div>
		<a href="{$href}"><img src="{$img_path}/download.gif" align="middle" alt="{$downloadtitle}" border="0" /></a>

		<xsl:if test="$paragraph_plugins != '-1' and $subchar != '-1'">		
			<xsl:call-template name="plugins">
				<xsl:with-param name="pluginsString" select="$paragraph_plugins"/>
				<xsl:with-param name="subchar" select="@SubCharacteristic"/>
				<xsl:with-param name="par_vars" select="concat('&amp;download=',$encoded_download_script,'&amp;downloadtitle=',$downloadtitle,'&amp;pg_id=',$pg_id,'&amp;par_id=',$p_id)"/>
			</xsl:call-template>
		</xsl:if>
		
		</div></td></tr>		
	</xsl:if>
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
					<xsl:with-param name="pluginString" select="substring-before($pluginsString,'|')"/>								
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
	
		<xsl:variable name="linkNode" >
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

		
<!--		<filetype><xsl:value-of select="$filetype"/></filetype>
		<title><xsl:value-of select="$title"/></title>
		<link><xsl:value-of select="$link"/></link>
		 <image><xsl:value-of select="$image"/></image>
		<subchar><xsl:value-of select="$subchar"/></subchar> -->

		 <xsl:if test="$subchar = $filetype or $filetype='-1'">
			 <span style="margin-left: 5px"><a href="{$link}" ><img src="{$image}" align="middle" alt="{$title}" border="0" /></a></span>
		 </xsl:if>
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

<!-- PageTurn (Links) -->
<xsl:template match="PageTurn">
	<xsl:variable name="entry_one"><xsl:value-of select="./BibItemIdentifier/@Entry" /></xsl:variable>
	<xsl:variable name="entry_two"><xsl:call-template name="get_bib_item" /></xsl:variable>
	<xsl:if test="contains($entry_two,$entry_one)">
		<xsl:if test="$citation = 1">
			<br />
			<input type="checkbox">
				<xsl:attribute name="name">
				<xsl:text>pgt_id[</xsl:text><xsl:number count="PageTurn" level="multiple" /><xsl:text>]</xsl:text>
				</xsl:attribute>
				<xsl:attribute name="value">
				<xsl:value-of select="./@Number" />
				</xsl:attribute>
			</input>
			<xsl:call-template name="showCitationSelect">
			<xsl:with-param name="pos">
			<xsl:number level="multiple" count="PageTurn" />
			</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<a class="ilc_PageTurnLink">
		<xsl:attribute name="href">#pt<xsl:number count="PageTurn" level="any"/></xsl:attribute>[<xsl:value-of select="$pagebreak" /><xsl:text> </xsl:text><xsl:number count="PageTurn" level="multiple"/>]</a>
	</xsl:if>
</xsl:template>

<!-- IntLink -->
<xsl:template match="IntLink">
	<xsl:choose>
		<!-- internal link to external resource (other installation) -->
		<xsl:when test="substring-after(@Target,'__') = ''">
			[could not resolve link target: <xsl:value-of select="@Target"/>]
		</xsl:when>
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
				<xsl:value-of select="//IntLinkInfos/IntLinkInfo[@Type=$type and @TargetFrame=$targetframe and @Target=$target]/@LinkHref"/>
			</xsl:variable>
			<xsl:variable name="link_target">
				<xsl:value-of select="//IntLinkInfos/IntLinkInfo[@Type=$type and @TargetFrame=$targetframe and @Target=$target]/@LinkTarget"/>
			</xsl:variable>

			<xsl:if test="$mode != 'print'">
				<a class="ilc_IntLink">
					<xsl:attribute name="href"><xsl:value-of select="$link_href"/></xsl:attribute>
					<xsl:if test="$link_target != ''">
						<xsl:attribute name="target"><xsl:value-of select="$link_target"/></xsl:attribute>
					</xsl:if>
					<xsl:apply-templates/>
				</a>
			</xsl:if>
			<xsl:if test="$mode = 'print'">
				<span class="ilc_Print_IntLink">
					<xsl:apply-templates/>
				</span>
			</xsl:if>

		</xsl:when>
		<!-- inline mob vri -->
		<xsl:when test="@Type = 'MediaObject' and not(@TargetFrame)">
			<xsl:variable name="cmobid" select="@Target"/>

			<!-- determine location type (LocalFile, Reference) -->
			<xsl:variable name="curType">
				<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = 'Standard']/Location/@Type"/>
			</xsl:variable>

			<!-- determine format (mime type) -->
			<xsl:variable name="type">
				<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = 'Standard']/Format"/>
			</xsl:variable>

			<!-- determine location -->
			<xsl:variable name="data">
				<xsl:if test="$curType = 'LocalFile'">
					<xsl:value-of select="$webspace_path"/>/mobs/mm_<xsl:value-of select="substring-after($cmobid,'mob_')"/>/<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = 'Standard']/Location"/>
				</xsl:if>
				<xsl:if test="$curType = 'Reference'">
					<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = 'Standard']/Location"/>
				</xsl:if>
			</xsl:variable>

			<!-- determine size mode (alias, mob or none) -->
			<xsl:variable name="sizemode">mob</xsl:variable>

			<!-- determine width -->
			<xsl:variable name="width">
				<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose='Standard']/Layout[1]/@Width"/>
			</xsl:variable>

			<!-- determine height -->
			<xsl:variable name="height">
				<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose='Standard']/Layout[1]/@Height"/>
			</xsl:variable>

			<xsl:call-template name="MOBTag">
				<xsl:with-param name="data" select="$data" />
				<xsl:with-param name="type" select="$type" />
				<xsl:with-param name="width" select="$width" />
				<xsl:with-param name="height" select="$height" />
				<xsl:with-param name="curPurpose" >Standard</xsl:with-param>
				<xsl:with-param name="cmobid" select="$cmobid" />
				<xsl:with-param name="location_mode">standard</xsl:with-param>
				<xsl:with-param name="curType" select="$curType" />
				<xsl:with-param name="inline">y</xsl:with-param>
			</xsl:call-template>

		</xsl:when>
	</xsl:choose>
</xsl:template>

<xsl:template match="IntLinkInfos">
</xsl:template>

<!-- ExtLink -->
<xsl:template match="ExtLink">
	<a class="ilc_ExtLink" target="_blank">
		<xsl:attribute name="href"><xsl:value-of select="@Href"/></xsl:attribute>
		<xsl:apply-templates/>
	</a>
</xsl:template>


<!-- Tables -->
<xsl:template match="Table">
	<!-- <xsl:value-of select="@HierId"/> -->
	<xsl:if test="$mode = 'edit' and $javascript='disable'">
		<br/>
	</xsl:if>
	<xsl:call-template name="EditReturnAnchors"/>
	<xsl:choose>
		<xsl:when test="@HorizontalAlign = 'Left'">
			<div align="left"><xsl:call-template name="TableTag" /></div>
		</xsl:when>
		<xsl:when test="@HorizontalAlign = 'Right'">
			<div align="right"><xsl:call-template name="TableTag" /></div>
		</xsl:when>
		<xsl:otherwise>
			<xsl:call-template name="TableTag" />
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<!-- Table Tag -->
<xsl:template name="TableTag">
	<table class="ilc_Table">
	<xsl:if test="$mode != 'edit' or not(@HorizontalAlign) or (@HorizontalAlign != 'RightFloat' and @HorizontalAlign != 'LeftFloat')">
		<xsl:attribute name="width"><xsl:value-of select="@Width"/></xsl:attribute>
	</xsl:if>
	<xsl:if test="$mode = 'edit' and (@HorizontalAlign = 'RightFloat' or @HorizontalAlign = 'LeftFloat')">
		<xsl:attribute name="width">100%</xsl:attribute>
	</xsl:if>
	<xsl:attribute name="border"><xsl:value-of select="@Border"/></xsl:attribute>
	<xsl:attribute name="cellspacing"><xsl:value-of select="@CellSpacing"/></xsl:attribute>
	<xsl:attribute name="cellpadding"><xsl:value-of select="@CellPadding"/></xsl:attribute>
	<xsl:if test="$mode = 'edit'">
		<xsl:attribute name="style">
			<xsl:choose>
				<xsl:when test="@HorizontalAlign = 'RightFloat'">margin-right: 0px;</xsl:when>
				<xsl:when test="@HorizontalAlign = 'LeftFloat'">margin-left: 0px;</xsl:when>
				<xsl:when test="@HorizontalAlign = 'Center'">margin-left: auto; margin-right: auto;</xsl:when>
			</xsl:choose>
		</xsl:attribute>
	</xsl:if>
	<xsl:if test="$mode != 'edit'">
		<xsl:attribute name="style">
			<xsl:choose>
				<xsl:when test="@HorizontalAlign = 'RightFloat'">float:right; margin-right: 0px;</xsl:when>
				<xsl:when test="@HorizontalAlign = 'LeftFloat'">float:left; margin-left: 0px;</xsl:when>
				<xsl:when test="@HorizontalAlign = 'Center'">margin-left: auto; margin-right: auto;</xsl:when>
			</xsl:choose>
		</xsl:attribute>
	</xsl:if>
	<xsl:for-each select="Caption">
		<caption>
		<xsl:attribute name="align"><xsl:value-of select="@Align"/></xsl:attribute>
		<xsl:value-of select="."/>
		</caption>
	</xsl:for-each>
	<xsl:for-each select = "TableRow">
		<xsl:variable name = "rowpos" select = "position()"/>
		<tr valign="top">
			<xsl:for-each select = "TableData">
				<td>
					<xsl:attribute name = "class"><xsl:value-of select = "@Class"/></xsl:attribute>
					<xsl:attribute name = "width"><xsl:value-of select = "@Width"/></xsl:attribute>
					<!-- insert commands -->
					<!-- <xsl:value-of select="@HierId"/> -->
					<xsl:call-template name="EditReturnAnchors"/>
					<xsl:if test="($mode = 'edit' and ((../../@DataTable != 'y' or not(../../@DataTable))) or $mode = 'table_edit')">
						<!-- checkbox -->
						<xsl:if test="$javascript = 'disable'">
							<input type="checkbox" name="target[]">
								<xsl:attribute name="value"><xsl:value-of select="@HierId"/>:<xsl:value-of select="@PCID"/>
								</xsl:attribute>
							</input>
						</xsl:if>
						<!-- insert select list -->
						<xsl:if test="$mode = 'edit'">
							<xsl:if test= "$javascript = 'disable'">
								<select size="1" class="ilEditSelect">
									<xsl:attribute name="name">command<xsl:value-of select="@HierId"/>
									</xsl:attribute>
									<xsl:call-template name="TableDataMenu"/>
								</select>
								<input class="ilEditSubmit" type="submit">
									<xsl:attribute name="value"><xsl:value-of select="//LVs/LV[@name='ed_go']/@value"/></xsl:attribute>
									<xsl:attribute name="name">cmd[exec_<xsl:value-of select="@HierId"/>:<xsl:value-of select="@PCID"/>]</xsl:attribute>
								</input>
								<br/>
							</xsl:if>
							<xsl:if test= "$javascript = 'enable'">
								<xsl:if test = "position() = 1">
									<xsl:call-template name="Icon">
										<xsl:with-param name="img_src"><xsl:value-of select="$img_row"/></xsl:with-param>
										<xsl:with-param name="img_id">CONTENTr<xsl:value-of select="@HierId"/>:<xsl:value-of select="@PCID"/></xsl:with-param>
									</xsl:call-template>
									<div style="position:absolute;left:0;top:0;visibility:hidden;">
										<xsl:attribute name="id">contextmenu_r<xsl:value-of select="@HierId"/></xsl:attribute>
										<table class="il_editmenu" cellspacing="0" cellpadding="3">
											<xsl:call-template name="TableRowMenu"/>
										</table>
									</div>
								</xsl:if>
								<xsl:if test = "$rowpos = 1">
									<xsl:call-template name="Icon">
										<xsl:with-param name="img_src"><xsl:value-of select="$img_col"/></xsl:with-param>
										<xsl:with-param name="img_id">CONTENTc<xsl:value-of select="@HierId"/>:<xsl:value-of select="@PCID"/></xsl:with-param>
									</xsl:call-template>
									<div style="position:absolute;left:0;top:0;visibility:hidden;">
										<xsl:attribute name="id">contextmenu_c<xsl:value-of select="@HierId"/></xsl:attribute>
										<table class="il_editmenu" cellspacing="0" cellpadding="3">
											<xsl:call-template name="TableColMenu"/>
										</table>
									</div>
								</xsl:if>
								<xsl:call-template name="DropArea">
									<xsl:with-param name="hier_id"><xsl:value-of select="@HierId"/></xsl:with-param>
									<xsl:with-param name="pc_id"><xsl:value-of select="@PCID"/></xsl:with-param>
								</xsl:call-template>
							</xsl:if>
						</xsl:if>
					</xsl:if>
					<!-- class and width output for table edit -->
					<xsl:if test="$mode = 'table_edit'">
						<div class="small" style="white-space:nowrap; padding:2px; margin:2px; background-color:#FFFFFF;">
							<xsl:value-of select="//LVs/LV[@name='ed_class']/@value"/>:
							<xsl:if test="@Class">
								<xsl:value-of select="substring(@Class, 5)"/>
							</xsl:if>
							<xsl:if test="not(@Class)">None</xsl:if>
							<input type="checkbox" value="1">
								<xsl:attribute name="name">target[<xsl:value-of select="@HierId"/>:<xsl:value-of select="@PCID"/>]</xsl:attribute>
							</input>
							<br />
							<xsl:value-of select="//LVs/LV[@name='ed_width']/@value"/>:
							<input class="small" type="text" size="5" maxlength="10">
								<xsl:attribute name="name">width[<xsl:value-of select="@HierId"/>:<xsl:value-of select="@PCID"/>]</xsl:attribute>
								<xsl:attribute name="value"><xsl:value-of select="@Width"/></xsl:attribute>
							</input>
						</div>
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
		<xsl:if test="$javascript = 'disable'">
			<!-- <xsl:value-of select="../@HierId"/> -->
			<input type="checkbox" name="target[]">
				<xsl:attribute name="value"><xsl:value-of select="../@HierId"/>:<xsl:value-of select="../@PCID"/>
				</xsl:attribute>
			</input>
			<select size="1" class="ilEditSelect">
				<xsl:attribute name="name">command<xsl:value-of select="../@HierId"/>
				</xsl:attribute>
				<xsl:call-template name="TableMenu">
					<xsl:with-param name="hier_id" select="../@HierId"/>
				</xsl:call-template>
			</select>
			<input class="ilEditSubmit" type="submit">
				<xsl:attribute name="value"><xsl:value-of select="//LVs/LV[@name='ed_go']/@value"/></xsl:attribute>
				<xsl:attribute name="name">cmd[exec_<xsl:value-of select="../@HierId"/>:<xsl:value-of select="../@PCID"/>]</xsl:attribute>
			</input>
			<br/>
		</xsl:if>
		<xsl:if test="$javascript = 'enable'">
			<div style="position:absolute;left:0;top:0;visibility:hidden;">
				<xsl:attribute name="id">contextmenu_<xsl:value-of select="../@HierId"/></xsl:attribute>
				<table class="il_editmenu" cellspacing="0" cellpadding="3">
					<xsl:call-template name="TableMenu">
						<xsl:with-param name="hier_id" select="../@HierId"/>
					</xsl:call-template>
				</table>
			</div>
		</xsl:if>
	</xsl:if>
</xsl:template>

<!-- Table Data Menu -->
<xsl:template name="TableDataMenu">

	<xsl:call-template name="EditMenuInsertItems"/>
	
	<xsl:call-template name="TableRowMenu"/>
	
	<xsl:call-template name="TableColMenu"/>
		
</xsl:template>

<!-- Table Row Menu -->
<xsl:template name="TableRowMenu">
	<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">newRowAfter</xsl:with-param>
	<xsl:with-param name="langvar">ed_new_row_after</xsl:with-param></xsl:call-template>

	<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">newRowBefore</xsl:with-param>
	<xsl:with-param name="langvar">ed_new_row_before</xsl:with-param></xsl:call-template>
	
	<xsl:variable name="ni"><xsl:number from="PageContent" level="single" count="TableRow"/></xsl:variable>

	<xsl:if test= "$ni != 1">
		<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">moveRowUp</xsl:with-param>
		<xsl:with-param name="langvar">ed_row_up</xsl:with-param></xsl:call-template>
	</xsl:if>
	
	<xsl:if test= "../../TableRow[number($ni + 1)]">
		<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">moveRowDown</xsl:with-param>
		<xsl:with-param name="langvar">ed_row_down</xsl:with-param></xsl:call-template>
	</xsl:if>

	<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">deleteRow</xsl:with-param>
	<xsl:with-param name="langvar">ed_delete_row</xsl:with-param></xsl:call-template>
</xsl:template>

<!-- Table Col Menu -->
<xsl:template name="TableColMenu">
	<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">newColAfter</xsl:with-param>
	<xsl:with-param name="langvar">ed_new_col_after</xsl:with-param></xsl:call-template>

	<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">newColBefore</xsl:with-param>
	<xsl:with-param name="langvar">ed_new_col_before</xsl:with-param></xsl:call-template>

	<xsl:variable name="ni"><xsl:number from="TableRow" level="single" count="TableData"/></xsl:variable>
	
	<xsl:if test= "$ni != 1">
		<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">moveColLeft</xsl:with-param>
		<xsl:with-param name="langvar">ed_col_left</xsl:with-param></xsl:call-template>
	</xsl:if>
	
	<xsl:if test= "../TableData[number($ni + 1)]">
		<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">moveColRight</xsl:with-param>
		<xsl:with-param name="langvar">ed_col_right</xsl:with-param></xsl:call-template>
	</xsl:if>
	
	<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">deleteCol</xsl:with-param>
	<xsl:with-param name="langvar">ed_delete_col</xsl:with-param></xsl:call-template>
</xsl:template>

<!-- Table Menu -->
<xsl:template name="TableMenu">
	<xsl:param name="hier_id"/>

	<xsl:if test="@DataTable = 'y'">
		<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">editData</xsl:with-param>
		<xsl:with-param name="langvar">ed_edit_data</xsl:with-param></xsl:call-template>
	</xsl:if>

	<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">edit</xsl:with-param>
	<xsl:with-param name="langvar">ed_edit_prop</xsl:with-param></xsl:call-template>
	
	<!--
	<xsl:if test="$hier_id != 'pg'">
		<xsl:call-template name="EditMenuItem">
			<xsl:with-param name="command">switchEnable</xsl:with-param>
			<xsl:with-param name="langvar">ed_enable</xsl:with-param>
		</xsl:call-template>
	</xsl:if>-->

	<xsl:if test = "$javascript = 'disable'">
		<xsl:call-template name="EditMenuInsertItems"/>
	</xsl:if>
	
	<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">delete</xsl:with-param>
	<xsl:with-param name="langvar">ed_delete</xsl:with-param></xsl:call-template>	

	<xsl:if test="$javascript = 'disable'">
		<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">moveAfter</xsl:with-param>
		<xsl:with-param name="langvar">ed_moveafter</xsl:with-param></xsl:call-template>	
	
		<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">moveBefore</xsl:with-param>
		<xsl:with-param name="langvar">ed_movebefore</xsl:with-param></xsl:call-template>
	</xsl:if>

	<!-- split page -->
	<xsl:if test = "substring-after($hier_id,'_') = '' and $hier_id != '1' and $enable_split_new = 'y'">
		<xsl:call-template name="EditMenuItem">
			<xsl:with-param name="command">splitPage</xsl:with-param>
			<xsl:with-param name="langvar">ed_split_page</xsl:with-param>
		</xsl:call-template>
	</xsl:if>
	
	<!-- split page to next page -->
	<xsl:if test = "substring-after($hier_id,'_') = '' and $hier_id != '1' and $enable_split_next = 'y'">
		<xsl:call-template name="EditMenuItem">
			<xsl:with-param name="command">splitPageNext</xsl:with-param>
			<xsl:with-param name="langvar">ed_split_page_next</xsl:with-param>
		</xsl:call-template>
	</xsl:if>


	<xsl:call-template name="EditMenuAlignItems"/>
		
</xsl:template>


<!-- Lists -->
<xsl:template match="List">
	<!-- <xsl:value-of select="..@HierId"/> -->
	<xsl:call-template name="EditReturnAnchors"/>
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
		<xsl:if test = "$javascript='disable'">
		<input type="checkbox" name="target[]">
			<xsl:attribute name="value"><xsl:value-of select="../@HierId"/>:<xsl:value-of select="../@PCID"/>
			</xsl:attribute>
		</input>
		</xsl:if>
		<xsl:call-template name="EditMenu">
			<xsl:with-param name="hier_id" select="../@HierId" />
			<xsl:with-param name="pc_id" select="../@PCID" />
			<xsl:with-param name="edit">p</xsl:with-param>
		</xsl:call-template>
		<xsl:if test = "$javascript='disable'">
			<br/>
		</xsl:if>
	</xsl:if>
</xsl:template>

<!-- List Item -->
<xsl:template match="ListItem">
	<li>
	<xsl:call-template name="EditReturnAnchors"/>
	<!-- insert commands -->
	<!-- <xsl:value-of select="@HierId"/> -->
	<xsl:if test="$mode = 'edit'">
		<xsl:if test="$javascript='disable'">
			<!-- checkbox -->
			<input type="checkbox" name="target[]">
				<xsl:attribute name="value"><xsl:value-of select="@HierId"/>:<xsl:value-of select="@PCID"/>
				</xsl:attribute>
			</input>
			<select size="1" class="ilEditSelect">
				<xsl:attribute name="name">command<xsl:value-of select="@HierId"/>
				</xsl:attribute>
				<xsl:if test = "$javascript = 'disable'">
					<xsl:call-template name="EditMenuInsertItems"/>
				</xsl:if>
				<xsl:call-template name="ListItemMenu"/>
			</select>
			<input class="ilEditSubmit" type="submit">
				<xsl:attribute name="value"><xsl:value-of select="//LVs/LV[@name='ed_go']/@value"/></xsl:attribute>
				<xsl:attribute name="name">cmd[exec_<xsl:value-of select="@HierId"/>:<xsl:value-of select="@PCID"/>]</xsl:attribute>
			</input>
			<br/>
		</xsl:if>
		<xsl:if test="$javascript = 'enable'">
			<xsl:call-template name="Icon">
				<xsl:with-param name="img_id">CONTENTi<xsl:value-of select="@HierId"/>:<xsl:value-of select="@PCID"/></xsl:with-param>
				<xsl:with-param name="img_src"><xsl:value-of select="$img_item"/></xsl:with-param>
				<xsl:with-param name="float">y</xsl:with-param>
			</xsl:call-template>
			<xsl:call-template name="DropArea">
				<xsl:with-param name="hier_id"><xsl:value-of select="@HierId"/></xsl:with-param>
				<xsl:with-param name="pc_id"><xsl:value-of select="@PCID"/></xsl:with-param>
			</xsl:call-template>
			<div style="position:absolute;left:0;top:0;visibility:hidden;">
				<xsl:attribute name="id">contextmenu_i<xsl:value-of select="@HierId"/></xsl:attribute>
				<table class="il_editmenu" cellspacing="0" cellpadding="3">
					<xsl:call-template name="ListItemMenu"/>
				</table>
			</div>
		</xsl:if>
	</xsl:if>

	<xsl:apply-templates/>
	</li>
</xsl:template>

<!-- List Item Menu -->
<xsl:template name="ListItemMenu">

	<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">newItemAfter</xsl:with-param>
	<xsl:with-param name="langvar">ed_new_item_after</xsl:with-param></xsl:call-template>

	<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">newItemBefore</xsl:with-param>
	<xsl:with-param name="langvar">ed_new_item_before</xsl:with-param></xsl:call-template>

	<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">deleteItem</xsl:with-param>
	<xsl:with-param name="langvar">ed_delete_item</xsl:with-param></xsl:call-template>
	
	<xsl:variable name="ni"><xsl:number level="single" count="ListItem|FileItem"/></xsl:variable>
	<xsl:if test= "$ni != 1">
		<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">moveItemUp</xsl:with-param>
		<xsl:with-param name="langvar">ed_item_up</xsl:with-param></xsl:call-template>
	</xsl:if>

	<xsl:if test= "../ListItem[number($ni + 1)] or ../FileItem[number($ni + 1)]">
		<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">moveItemDown</xsl:with-param>
		<xsl:with-param name="langvar">ed_item_down</xsl:with-param></xsl:call-template>
	</xsl:if>
	
</xsl:template>

<!-- SimpleBulletList -->
<xsl:template match="SimpleBulletList">
	<ul><xsl:apply-templates/></ul>
</xsl:template>
<xsl:template match="SimpleNumberedList">
	<ol><xsl:apply-templates/></ol>
</xsl:template>
<xsl:template match="SimpleListItem">
	<li><xsl:apply-templates/></li>
</xsl:template>

<!-- FileList -->
<xsl:template match="FileList">
	<xsl:call-template name="EditReturnAnchors"/>
	<table class="ilc_FileList">
		<tr><th class="ilc_FileList">
		<xsl:value-of select="./Title"/>
		</th></tr>
		<xsl:apply-templates/>
		<!-- <xsl:apply-templates select="FileItem"/> -->
	</table>
	<!-- command selectbox -->
	<xsl:if test="$mode = 'edit'">
		<!-- <xsl:value-of select="../@HierId"/> -->
		<xsl:if test = "$javascript='disable'">
		<input type="checkbox" name="target[]">
			<xsl:attribute name="value"><xsl:value-of select="../@HierId"/>:<xsl:value-of select="../@PCID"/>
			</xsl:attribute>
		</input>
		</xsl:if>
		<xsl:call-template name="EditMenu">
			<xsl:with-param name="hier_id" select="../@HierId" />
			<xsl:with-param name="pc_id" select="../@PCID" />
			<xsl:with-param name="edit">p</xsl:with-param>
		</xsl:call-template>
		<xsl:if test = "$javascript='disable'">
			<br/>
		</xsl:if>
	</xsl:if>
</xsl:template>

<!-- FileItem -->
<xsl:template match="FileItem">
	<tr class="ilc_FileItem">
		<td class="ilc_FileItem">
		<xsl:call-template name="EditReturnAnchors"/>
		<!-- <xsl:value-of select="@HierId"/> -->
		<xsl:if test="$mode = 'edit'">
			<xsl:if test="$javascript='disable'">
				<!-- checkbox -->
				<input type="checkbox" name="target[]">
					<xsl:attribute name="value"><xsl:value-of select="@HierId"/>:<xsl:value-of select="@PCID"/>
					</xsl:attribute>
				</input>
				<select size="1" class="ilEditSelect">
					<xsl:attribute name="name">command<xsl:value-of select="@HierId"/>
					</xsl:attribute>
					<xsl:call-template name="ListItemMenu"/>
				</select>
				<input class="ilEditSubmit" type="submit">
					<xsl:attribute name="value"><xsl:value-of select="//LVs/LV[@name='ed_go']/@value"/></xsl:attribute>
					<xsl:attribute name="name">cmd[exec_<xsl:value-of select="@HierId"/>:<xsl:value-of select="@PCID"/>]</xsl:attribute>
				</input>
				<br/>
			</xsl:if>
			<xsl:if test="$javascript = 'enable'">
				<xsl:call-template name="Icon">
					<xsl:with-param name="img_id">CONTENTi<xsl:value-of select="@HierId"/>:<xsl:value-of select="@PCID"/></xsl:with-param>
					<xsl:with-param name="img_src"><xsl:value-of select="$img_item"/></xsl:with-param>
				</xsl:call-template>
				&amp;nbsp;
				<div style="position:absolute;left:0;top:0;visibility:hidden;">
					<xsl:attribute name="id">contextmenu_i<xsl:value-of select="@HierId"/></xsl:attribute>
					<table class="il_editmenu" cellspacing="0" cellpadding="3">
						<xsl:call-template name="ListItemMenu"/>
					</table>
				</div>
			</xsl:if>
		</xsl:if>
		<xsl:if test="$mode != 'print'">
			<xsl:if test="$mode != 'offline'">
				<a>
					<xsl:attribute name="href"><xsl:value-of select="$file_download_link"/>&amp;file_id=<xsl:value-of select="./Identifier/@Entry"/></xsl:attribute>
					<xsl:call-template name="FileItemText"/>
				</a>
			</xsl:if>
			<xsl:if test="$mode = 'offline'">
				<a>
					<xsl:attribute name="href">./files/file_<xsl:value-of select="substring-after(./Identifier/@Entry,'file_')"/>/<xsl:value-of select="./Location"/></xsl:attribute>
					<xsl:call-template name="FileItemText"/>
				</a>
			</xsl:if>
		</xsl:if>
		<xsl:if test="$mode = 'print'">
			<span class="ilc_Print_FileItem">
				<xsl:call-template name="FileItemText"/>
			</span>
		</xsl:if>
		</td>
	</tr>
</xsl:template>


<!-- FileItemText -->
<xsl:template name="FileItemText">
	<xsl:value-of select="./Location"/>
	<xsl:if test="./Size">
		<xsl:choose>
			<xsl:when test="./Size > 1000000">
				(<xsl:value-of select="round(./Size div 10000) div 100"/> MB)
			</xsl:when>
			<xsl:when test="./Size > 1000">
				(<xsl:value-of select="round(./Size div 10) div 100"/> KB)
			</xsl:when>
			<xsl:otherwise>
				(<xsl:value-of select="./Size"/> B)
			</xsl:otherwise>
		</xsl:choose>
	</xsl:if>
</xsl:template>

<!-- MediaAlias -->
<xsl:template match="MediaAlias">
	<xsl:call-template name="EditReturnAnchors"/>

	<!-- Alignment Part 1 (Left, Center, Right)-->
	<xsl:if test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'Left'
		and $mode != 'fullscreen' and $mode != 'media'">
		<div align="left" style="clear:both;">
		<xsl:call-template name="MOBTable"/>
		</div>
	</xsl:if>
	<xsl:if test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'Center'
		or $mode = 'fullscreen' or $mode = 'media'">
		<div align="center" style="clear:both;">
		<xsl:call-template name="MOBTable"/>
		</div>
	</xsl:if>
	<xsl:if test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'Right'
		and $mode != 'fullscreen' and $mode != 'media'">
		<div align="right" style="clear:both;">
		<xsl:call-template name="MOBTable"/>
		</div>
	</xsl:if>
	<xsl:if test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'RightFloat'
		and $mode != 'fullscreen' and $mode != 'media'">
		<xsl:call-template name="MOBTable"/>
	</xsl:if>
	<xsl:if test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'LeftFloat'
		and $mode != 'fullscreen' and $mode != 'media'">
		<xsl:call-template name="MOBTable"/>
	</xsl:if>
	<xsl:if test="count(../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign) = 0
		and $mode != 'fullscreen' and $mode != 'media'">
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
		<xsl:if test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'LeftFloat'
			and $mode != 'fullscreen' and $mode != 'media'">
			<xsl:attribute name="style">margin-left: 0px;</xsl:attribute>
			<xsl:if test="$mode != 'edit'">
				<xsl:attribute name="style">float:left; clear:both; margin-left: 0px;</xsl:attribute>
			</xsl:if>
		</xsl:if>
		<xsl:if test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'RightFloat'
			and $mode != 'fullscreen' and $mode != 'media'">
			<xsl:attribute name="style">margin-right: 0px;</xsl:attribute>
			<xsl:if test="$mode != 'edit'">
				<xsl:attribute name="style">float:right; clear:both; margin-right: 0px;</xsl:attribute>
			</xsl:if>
		</xsl:if>

		<!-- make object fit to left/right border -->
		<xsl:if test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'Left'
			and $mode != 'fullscreen' and $mode != 'media'">
			<xsl:attribute name="style">margin-left: 0px;</xsl:attribute>
		</xsl:if>
		<xsl:if test="../MediaAliasItem[@Purpose='Standard']/Layout[1]/@HorizontalAlign = 'Right'
			and $mode != 'fullscreen' and $mode != 'media'">
			<xsl:attribute name="style">margin-right: 0px;</xsl:attribute>
		</xsl:if>

		<!-- determine purpose -->
		<xsl:variable name="curPurpose"><xsl:choose>
			<xsl:when test="$mode = 'fullscreen'">Fullscreen</xsl:when>
			<xsl:otherwise>Standard</xsl:otherwise>
		</xsl:choose></xsl:variable>

		<!-- build object tag -->
		<tr><td class="ilc_Mob">
			<xsl:for-each select="../MediaAliasItem[@Purpose = $curPurpose]">

				<!-- data / Location -->
				<xsl:variable name="curItemNr"><xsl:number count="MediaItem" from="MediaAlias"/></xsl:variable>

				<!-- determine location mode (curpurpose, standard) -->
				<xsl:variable name="location_mode">
					<xsl:choose>
						<xsl:when test="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = $curPurpose]/Location != ''">curpurpose</xsl:when>
						<xsl:otherwise>standard</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>

				<!-- determine location type (LocalFile, Reference) -->
				<xsl:variable name="curType">
					<xsl:choose>
						<xsl:when test="$location_mode = 'curpurpose'">
							<xsl:value-of select="//MediaObject[@Id=$cmobid]//MediaItem[@Purpose = $curPurpose]/Location/@Type"/>
						</xsl:when>
						<xsl:when test="$location_mode = 'standard'">
							<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = 'Standard']/Location/@Type"/>
						</xsl:when>
					</xsl:choose>
				</xsl:variable>

				<!-- determine format (mime type) -->
				<xsl:variable name="type">
					<xsl:choose>
						<xsl:when test="$location_mode = 'curpurpose'">
							<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = $curPurpose]/Format"/>
						</xsl:when>
						<xsl:when test="$location_mode = 'standard'">
							<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = 'Standard']/Format"/>
						</xsl:when>
					</xsl:choose>
				</xsl:variable>

				<!-- determine location -->
				<xsl:variable name="data">
					<xsl:choose>
						<xsl:when test="$location_mode = 'curpurpose'">
							<xsl:if test="$curType = 'LocalFile'">
								<xsl:value-of select="$webspace_path"/>/mobs/mm_<xsl:value-of select="substring-after($cmobid,'mob_')"/>/<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = $curPurpose]/Location"/>
							</xsl:if>
							<xsl:if test="$curType = 'Reference'">
								<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = $curPurpose]/Location"/>
							</xsl:if>
						</xsl:when>
						<xsl:when test="$location_mode = 'standard'">
							<xsl:if test="$curType = 'LocalFile'">
								<xsl:value-of select="$webspace_path"/>/mobs/mm_<xsl:value-of select="substring-after($cmobid,'mob_')"/>/<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = 'Standard']/Location"/>
							</xsl:if>
							<xsl:if test="$curType = 'Reference'">
								<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = 'Standard']/Location"/>
							</xsl:if>
						</xsl:when>
					</xsl:choose>
				</xsl:variable>

				<!-- determine size mode (alias, mob or none) -->
				<xsl:variable name="sizemode">
					<xsl:choose>
						<xsl:when test="../MediaAliasItem[@Purpose=$curPurpose]/Layout[1]/@Width != '' or
							../MediaAliasItem[@Purpose=$curPurpose]/Layout[1]/@Height != ''">alias</xsl:when>
						<xsl:when test="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Layout[1]/@Width != '' or
							//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Layout[1]/@Height != ''">mob</xsl:when>
						<xsl:otherwise>none</xsl:otherwise>
					</xsl:choose>
				</xsl:variable>

				<!-- determine width -->
				<xsl:variable name="width">
					<xsl:choose>
						<xsl:when test="$sizemode = 'alias'"><xsl:value-of select="../MediaAliasItem[@Purpose=$curPurpose]/Layout[1]/@Width"/></xsl:when>
						<xsl:when test="$sizemode = 'mob'"><xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Layout[1]/@Width"/></xsl:when>
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>

				<!-- determine height -->
				<xsl:variable name="height">
					<xsl:choose>
						<xsl:when test="$sizemode = 'alias'"><xsl:value-of select="../MediaAliasItem[@Purpose=$curPurpose]/Layout[1]/@Height"/></xsl:when>
						<xsl:when test="$sizemode = 'mob'"><xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Layout[1]/@Height"/></xsl:when>
						<xsl:otherwise></xsl:otherwise>
					</xsl:choose>
				</xsl:variable>

				<xsl:call-template name="MOBTag">
					<xsl:with-param name="data" select="$data" />
					<xsl:with-param name="type" select="$type" />
					<xsl:with-param name="width" select="$width" />
					<xsl:with-param name="height" select="$height" />
					<xsl:with-param name="curPurpose" select="$curPurpose" />
					<xsl:with-param name="cmobid" select="$cmobid" />
					<xsl:with-param name="location_mode" select="$location_mode" />
					<xsl:with-param name="curType" select="$curType" />
				</xsl:call-template>

				<!-- parameter -->
				<!--
				<xsl:for-each select="../MediaAliasItem[@Purpose = $curPurpose]/Parameter">
					<param>
					<xsl:attribute name="name"><xsl:value-of select="@Name"/></xsl:attribute>
					<xsl:attribute name="value"><xsl:value-of select="@Value"/></xsl:attribute>
					</param>
				</xsl:for-each>-->

			</xsl:for-each></td></tr>

		<!-- mob caption -->
		<xsl:choose>			<!-- derive -->
			<xsl:when test="count(../MediaAliasItem[@Purpose=$curPurpose]/Caption[1]) != 0">
				<tr><td class="ilc_MediaCaption">
				<xsl:call-template name="FullscreenLink">
					<xsl:with-param name="cmobid" select="$cmobid"/>
				</xsl:call-template>
				<xsl:value-of select="../MediaAliasItem[@Purpose=$curPurpose]/Caption[1]"/>
				</td></tr>
			</xsl:when>
			<xsl:when test="count(//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Caption[1]) != 0">
				<tr><td class="ilc_MediaCaption">
				<xsl:call-template name="FullscreenLink">
					<xsl:with-param name="cmobid" select="$cmobid"/>
				</xsl:call-template>
				<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Caption[1]"/>
				</td></tr>
			</xsl:when>
			<xsl:otherwise>
				<xsl:if test="count(../MediaAliasItem[@Purpose='Fullscreen']) = 1">
					<tr><td class="ilc_MediaCaption">
					<xsl:call-template name="FullscreenLink">
						<xsl:with-param name="cmobid" select="$cmobid"/>
					</xsl:call-template>
					</td></tr>
				</xsl:if>
			</xsl:otherwise>
		</xsl:choose>

		<!-- command selectbox -->
		<xsl:if test="$mode = 'edit' and $javascript='disable'">
			<tr><td>
				<!-- <xsl:value-of select="../../@HierId"/> -->
				<input type="checkbox" name="target[]">
					<xsl:attribute name="value"><xsl:value-of select="../../@HierId"/>:<xsl:value-of select="../../@PCID"/>
					</xsl:attribute>
				</input>
				<select size="1" class="ilEditSelect">
					<xsl:attribute name="name">command<xsl:value-of select="../../@HierId"/>
					</xsl:attribute>
					<xsl:call-template name="MOBEditMenu">
						<xsl:with-param name="hier_id" select="../../@HierId"/>
					</xsl:call-template>
				</select>
				<input class="ilEditSubmit" type="submit">
					<xsl:attribute name="value"><xsl:value-of select="//LVs/LV[@name='ed_go']/@value"/></xsl:attribute>
					<xsl:attribute name="name">cmd[exec_<xsl:value-of select="../../@HierId"/>:<xsl:value-of select="../../@PCID"/>]</xsl:attribute>
				</input>
			</td></tr>
		</xsl:if>
	</table>
	<!-- menu -->
	<xsl:if test="$mode = 'edit' and $javascript='enable'">
		<div style="position:absolute; left:0; top:0; visibility:hidden; z-index:10;">
			<xsl:attribute name="id">contextmenu_<xsl:value-of select="../../@HierId"/></xsl:attribute>
			<table class="il_editmenu" cellspacing="0" cellpadding="3">
				<xsl:call-template name="MOBEditMenu">
					<xsl:with-param name="hier_id" select="../../@HierId"/>
				</xsl:call-template>
			</table>
		</div>
	</xsl:if>
</xsl:template>


<!-- MOB edit menu -->
<xsl:template name="MOBEditMenu">
	<xsl:param name="hier_id"/>

	<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">editAlias</xsl:with-param>
	<xsl:with-param name="langvar">ed_edit_prop</xsl:with-param></xsl:call-template>
	
	<!--
	<xsl:if test="$hier_id != 'pg'">
		<xsl:call-template name="EditMenuItem">
			<xsl:with-param name="command">switchEnable</xsl:with-param>
			<xsl:with-param name="langvar">ed_enable</xsl:with-param>
		</xsl:call-template>
	</xsl:if>-->


	<xsl:if test = "$javascript = 'disable'">
		<xsl:call-template name="EditMenuInsertItems"/>
	</xsl:if>
	
	<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">delete</xsl:with-param>
	<xsl:with-param name="langvar">ed_delete</xsl:with-param></xsl:call-template>

	<xsl:if test="$javascript = 'disable'">
		<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">moveAfter</xsl:with-param>
		<xsl:with-param name="langvar">ed_moveafter</xsl:with-param></xsl:call-template>
	
		<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">moveBefore</xsl:with-param>
		<xsl:with-param name="langvar">ed_movebefore</xsl:with-param></xsl:call-template>
	</xsl:if>
	
	<!-- split page -->
	<xsl:if test = "substring-after($hier_id,'_') = '' and $hier_id != '1' and $enable_split_new = 'y'">
		<xsl:call-template name="EditMenuItem">
			<xsl:with-param name="command">splitPage</xsl:with-param>
			<xsl:with-param name="langvar">ed_split_page</xsl:with-param>
		</xsl:call-template>
	</xsl:if>
	
	<!-- split page to next page -->
	<xsl:if test = "substring-after($hier_id,'_') = '' and $hier_id != '1' and $enable_split_next = 'y'">
		<xsl:call-template name="EditMenuItem">
			<xsl:with-param name="command">splitPageNext</xsl:with-param>
			<xsl:with-param name="langvar">ed_split_page_next</xsl:with-param>
		</xsl:call-template>
	</xsl:if>

	<xsl:call-template name="EditMenuAlignItems"/>
	
	<xsl:call-template name="EditMenuItem"><xsl:with-param name="command">copyToClipboard</xsl:with-param>
	<xsl:with-param name="langvar">ed_copy_clip</xsl:with-param></xsl:call-template>
	
</xsl:template>


<!-- MOBTag: display media object tag -->
<xsl:template name="MOBTag">
	<xsl:param name="data"/>
	<xsl:param name="type"/>
	<xsl:param name="width"/>
	<xsl:param name="height"/>
	<xsl:param name="cmobid"/>
	<xsl:param name="curPurpose"/>
	<xsl:param name="location_mode"/>
	<xsl:param name="curType"/>
	<xsl:param name="inline">n</xsl:param>
	<xsl:choose>
		<xsl:when test="($media_mode = 'disable' and $mode='edit') or $mode='table_edit'">
			<img border="0">
				<xsl:if test="$width != ''">
				<xsl:attribute name="width"><xsl:value-of select="$width"/></xsl:attribute>
				</xsl:if>
				<xsl:if test="$height != ''">
				<xsl:attribute name="height"><xsl:value-of select="$height"/></xsl:attribute>
				</xsl:if>
				<xsl:attribute name="src"><xsl:value-of select="$med_disabled_path"/></xsl:attribute>
			</img>
		</xsl:when>

		<!-- all image mime types, except svg -->
		<xsl:when test="substring($type, 1, 5) = 'image' and not(substring($type, 1, 9) = 'image/svg')">
			<xsl:if test="$map_edit_mode != 'get_coords'">
				<img border="0">
					<xsl:if test = "$map_item = ''">
						<xsl:attribute name="src"><xsl:value-of select="$data"/></xsl:attribute>
					</xsl:if>
					<xsl:if test = "$map_item != ''">
						<xsl:attribute name="src"><xsl:value-of select="$image_map_link"/>&amp;item_id=<xsl:value-of select="$map_item"/>&amp;<xsl:value-of select="$link_params"/></xsl:attribute>
					</xsl:if>
					<xsl:if test="$width != ''">
					<xsl:attribute name="width"><xsl:value-of select="$width"/></xsl:attribute>
					</xsl:if>
					<xsl:if test="$height != ''">
					<xsl:attribute name="height"><xsl:value-of select="$height"/></xsl:attribute>
					</xsl:if>
					<xsl:if test = "//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = $curPurpose]/MapArea[1] or ./MapArea[1]">
						<xsl:attribute name="usemap">#map_<xsl:value-of select="$cmobid"/>_<xsl:value-of select="$curPurpose"/></xsl:attribute>
					</xsl:if>
					<xsl:if test = "$inline = 'y'">
						<xsl:attribute name="align">middle</xsl:attribute>
					</xsl:if>
				</img>
			</xsl:if>
			<xsl:if test = "$map_edit_mode = 'get_coords'">
				<input type="image" name="editImagemapForward" value="editImagemapForward">
					<xsl:attribute name="src"><xsl:value-of select="$image_map_link"/>&amp;item_id=<xsl:value-of select="$map_item"/>&amp;<xsl:value-of select="$link_params"/></xsl:attribute>
					<xsl:attribute name="width"><xsl:value-of select="$width"/></xsl:attribute>
					<xsl:attribute name="height"><xsl:value-of select="$height"/></xsl:attribute>
				</input>
			</xsl:if>
		</xsl:when>

		<!-- flash -->
		<xsl:when test="$type = 'application/x-shockwave-flash'">
			<object>
				<xsl:attribute name="classid">clsid:D27CDB6E-AE6D-11cf-96B8-444553540000</xsl:attribute>
				<xsl:attribute name="codebase">http://active.macromedia.com/flash2/cabs/swflash.cab#version=4,0,0,0</xsl:attribute>
				<xsl:attribute name="ID"><xsl:value-of select="$data"/></xsl:attribute>
				<xsl:attribute name="width"><xsl:value-of select="$width"/></xsl:attribute>
				<xsl:attribute name="height"><xsl:value-of select="$height"/></xsl:attribute>
				<param>
					<xsl:attribute name = "name">movie</xsl:attribute>
					<xsl:attribute name = "value"><xsl:value-of select="$data"/></xsl:attribute>
				</param>
				<xsl:call-template name="MOBParams">
					<xsl:with-param name="curPurpose" select="$curPurpose" />
					<xsl:with-param name="mode">elements</xsl:with-param>
					<xsl:with-param name="cmobid" select="$cmobid" />
				</xsl:call-template>
				<embed>
					<xsl:attribute name="src"><xsl:value-of select="$data"/></xsl:attribute>
					<xsl:attribute name="width"><xsl:value-of select="$width"/></xsl:attribute>
					<xsl:attribute name="height"><xsl:value-of select="$height"/></xsl:attribute>
					<xsl:attribute name="type">application/x-shockwave-flash</xsl:attribute>
					<xsl:attribute name="pluginspage">http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash</xsl:attribute>
					<xsl:call-template name="MOBParams">
						<xsl:with-param name="curPurpose" select="$curPurpose" />
						<xsl:with-param name="mode">attributes</xsl:with-param>
						<xsl:with-param name="cmobid" select="$cmobid" />
					</xsl:call-template>
				</embed>
			</object>
		</xsl:when>

		<!-- java -->
		<xsl:when test="$type = 'application/x-java-applet'">
			<xsl:variable name="upper-case" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÜ'" />
			<xsl:variable name="lower-case" select="'abcdefghijklmnopqrstuvwxyzäöü'" />

			<!-- filename normalisieren: trim, toLowerCase -->
			<xsl:variable name="_filename" select="normalize-space(translate(substring-after($data,'/'), $upper-case, $lower-case))" />

			<applet width="{$width}" height="{$height}" >

				<xsl:choose>
					<!-- if is single class file: filename ends-with (class) -->
      					<xsl:when test="'class' = substring($_filename, string-length($_filename) - string-length('class') + 1)">
				<xsl:choose>
					<xsl:when test="$location_mode = 'curpurpose'">
						<xsl:if test="$curType = 'LocalFile'">
							<xsl:attribute name="code"><xsl:value-of select="substring-before(//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = $curPurpose]/Location,'.')"/></xsl:attribute>
							<xsl:attribute name="codebase"><xsl:value-of select="$webspace_path"/>/mobs/mm_<xsl:value-of select="substring-after($cmobid,'mob_')"/>/</xsl:attribute>
						</xsl:if>
						<xsl:if test="$curType = 'Reference'">
							<xsl:attribute name="code"><xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = $curPurpose]/Location"/></xsl:attribute>
						</xsl:if>
					</xsl:when>
					<xsl:when test="$location_mode = 'standard'">
						<xsl:if test="$curType = 'LocalFile'">
							<xsl:attribute name="code"><xsl:value-of select="substring-before(//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = 'Standard']/Location,'.')"/></xsl:attribute>
							<xsl:attribute name="codebase"><xsl:value-of select="$webspace_path"/>/mobs/mm_<xsl:value-of select="substring-after($cmobid,'mob_')"/>/</xsl:attribute>
						</xsl:if>
						<xsl:if test="$curType = 'Reference'">
							<xsl:attribute name="code"><xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = 'Standard']/Location"/></xsl:attribute>
						</xsl:if>
					</xsl:when>
				</xsl:choose>
				<xsl:call-template name="MOBParams">
					<xsl:with-param name="curPurpose" select="$curPurpose" />
					<xsl:with-param name="mode">elements</xsl:with-param>
					<xsl:with-param name="cmobid" select="$cmobid" />
				</xsl:call-template>
					</xsl:when>

					<!-- assuming is applet archive: filename ends-with something else -->
					<xsl:otherwise>
						<xsl:choose>
							<xsl:when test="$location_mode = 'curpurpose'">
              							<xsl:if test="$curType = 'LocalFile'">
                							<xsl:attribute name="archive"><xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = $curPurpose]/Location"/></xsl:attribute>
                							<xsl:attribute name="codebase"><xsl:value-of select="$webspace_path"/>/mobs/mm_<xsl:value-of select="substring-after($cmobid,'mob_')"/>/</xsl:attribute>
              							</xsl:if>
              							<xsl:if test="$curType = 'Reference'">
                							<xsl:attribute name="archive"><xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = $curPurpose]/Location"/></xsl:attribute>
              							</xsl:if>
            						</xsl:when>
            						<xsl:when test="$location_mode = 'standard'">
              							<xsl:if test="$curType = 'LocalFile'">
                							<xsl:attribute name="archive"><xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = 'Standard']/Location"/></xsl:attribute>
                							<xsl:attribute name="codebase"><xsl:value-of select="$webspace_path"/>/mobs/mm_<xsl:value-of select="substring-after($cmobid,'mob_')"/>/</xsl:attribute>
                						</xsl:if>
              							<xsl:if test="$curType = 'Reference'">
                							<xsl:attribute name="archive"><xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose = 'Standard']/Location"/></xsl:attribute>
              							</xsl:if>
            						</xsl:when>
          					</xsl:choose>
						<!-- object or instance parameters -->
						<!-- nescessary because attribute code is part of applet-tag and others are sub elements -->
						<!-- code attribute -->
						<xsl:choose>
							<xsl:when test="../MediaAliasItem[@Purpose=$curPurpose]/Parameter[@Name = 'code']">
								<xsl:attribute name="code"><xsl:value-of select="../MediaAliasItem[@Purpose=$curPurpose]/Parameter[@Name = 'code']/@Value" /></xsl:attribute>
							</xsl:when>
							<xsl:otherwise>
								<xsl:attribute name="code"><xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Parameter[@Name = 'code']/@Value" /></xsl:attribute>
							</xsl:otherwise>
						</xsl:choose>

						<xsl:choose>

							<xsl:when test="../MediaAliasItem[@Purpose=$curPurpose]/Parameter">
							<!-- alias parameters -->
		          					<xsl:for-each select="../MediaAliasItem[@Purpose = $curPurpose]/Parameter">
            								<xsl:if test="@Name != 'code'">
          									<param>
  	      							  			<xsl:attribute name="name"><xsl:value-of select="@Name"/></xsl:attribute>
											<xsl:attribute name="value"><xsl:value-of select="@Value"/></xsl:attribute>
       										</param>
	            							</xsl:if>
        		  					</xsl:for-each>
							</xsl:when>
							<!-- object parameters -->
							<xsl:otherwise>
		          					<xsl:for-each select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Parameter">
            								<xsl:if test="@Name != 'code'">
             									<param>
       	      							  			<xsl:attribute name="name"><xsl:value-of select="@Name"/></xsl:attribute>
      								  			<xsl:attribute name="value"><xsl:value-of select="@Value"/></xsl:attribute>
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
				<xsl:attribute name="src"><xsl:value-of select="$data"/></xsl:attribute>
				<xsl:attribute name="width"><xsl:value-of select="$width"/></xsl:attribute>
				<xsl:attribute name="height"><xsl:value-of select="$height"/></xsl:attribute>
				<xsl:call-template name="MOBParams">
					<xsl:with-param name="curPurpose" select="$curPurpose" />
					<xsl:with-param name="mode">attributes</xsl:with-param>
					<xsl:with-param name="cmobid" select="$cmobid" />
				</xsl:call-template>
			</iframe>
		</xsl:when>
		
		<!-- application/pdf -->
		<xsl:when test="$type = 'application/pdf'">
			<iframe frameborder="0">
				<xsl:attribute name="src"><xsl:value-of select="$data"/></xsl:attribute>
				<xsl:attribute name="width"><xsl:value-of select="$width"/></xsl:attribute>
				<xsl:attribute name="height"><xsl:value-of select="$height"/></xsl:attribute>
				<xsl:call-template name="MOBParams">
					<xsl:with-param name="curPurpose" select="$curPurpose" />
					<xsl:with-param name="mode">attributes</xsl:with-param>
					<xsl:with-param name="cmobid" select="$cmobid" />
				</xsl:call-template>
			</iframe>
		</xsl:when>

		<!-- mp4 -->
		<xsl:when test="$type = 'video/mp4'">
			<embed pluginspage="http://www.apple.com/quicktime/download/">
				<xsl:attribute name="src"><xsl:value-of select="$data"/></xsl:attribute>
				<xsl:attribute name="type"><xsl:value-of select="$type"/></xsl:attribute>
				<xsl:attribute name="width"><xsl:value-of select="$width"/></xsl:attribute>
				<xsl:attribute name="height"><xsl:value-of select="$height"/></xsl:attribute>
				<xsl:call-template name="MOBParams">
					<xsl:with-param name="curPurpose" select="$curPurpose" />
					<xsl:with-param name="mode">attributes</xsl:with-param>
					<xsl:with-param name="cmobid" select="$cmobid" />
				</xsl:call-template>
			</embed>
		</xsl:when>

		<!-- YouTube -->
		<xsl:when test = "substring-after($data,'youtube.com') != ''">
			<object>
				<xsl:attribute name="width"><xsl:value-of select="$width"/></xsl:attribute>
				<xsl:attribute name="height"><xsl:value-of select="$height"/></xsl:attribute>
				<param name="movie">
					<xsl:attribute name="value">http://www.youtube.com/v/<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Parameter[@Name='v']/@Value" />&amp;hl=en&amp;fs=1&amp;rel=0</xsl:attribute>
				</param>
				<param name="allowFullScreen" value="true"></param>
				<embed type="application/x-shockwave-flash"
					allowfullscreen="true">
					<xsl:attribute name="width"><xsl:value-of select="$width"/></xsl:attribute>
					<xsl:attribute name="height"><xsl:value-of select="$height"/></xsl:attribute>
					<xsl:attribute name="src">http://www.youtube.com/v/<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Parameter[@Name='v']/@Value" />&amp;hl=en&amp;fs=1&amp;rel=0</xsl:attribute>
				</embed>
			</object>
		</xsl:when>
		
		<!-- Flickr -->
		<xsl:when test = "substring-after($data,'flickr.com') != ''">
			<xsl:variable name="flickr_tags"><xsl:if test = "//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Parameter[@Name='tags']/@Value != ''">&amp;tags=<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Parameter[@Name='tags']/@Value"/></xsl:if></xsl:variable>
			<xsl:variable name="flickr_sets"><xsl:if test = "//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Parameter[@Name='sets']/@Value != ''">&amp;set_id=<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Parameter[@Name='sets']/@Value"/></xsl:if></xsl:variable>
			<xsl:variable name="flickr_user_id">user_id=<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Parameter[@Name='user_id']/@Value"/></xsl:variable>
			<iframe frameBorder="0" scrolling="no">
				<xsl:attribute name="width"><xsl:value-of select="$width"/></xsl:attribute>
				<xsl:attribute name="height"><xsl:value-of select="$height"/></xsl:attribute>
				<xsl:attribute name="src">http://www.flickr.com/slideShow/index.gne?<xsl:value-of select="$flickr_user_id" /><xsl:value-of select="$flickr_tags" /><xsl:value-of select="$flickr_sets" /></xsl:attribute>
			</iframe>
		</xsl:when>

		<!-- GoogleVideo -->
		<xsl:when test = "substring-after($data,'video.google') != ''">
			<embed id="VideoPlayback" allowFullScreen="true"  type="application/x-shockwave-flash">
				<xsl:attribute name="src">http://video.google.com/googleplayer.swf?docid=<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Parameter[@Name='docid']/@Value" />&amp;fs=true</xsl:attribute>
				<xsl:attribute name="width"><xsl:value-of select="$width"/></xsl:attribute>
				<xsl:attribute name="height"><xsl:value-of select="$height"/></xsl:attribute>
			</embed>
		</xsl:when>
		
		<!-- GoogleDoc -->
		<xsl:when test = "substring-after($data,'docs.google') != ''">
			<xsl:variable name="googledoc_action"><xsl:if test = "//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Parameter[@Name='type']/@Value = 'Presentation'">EmbedSlideshow</xsl:if><xsl:if test = "//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Parameter[@Name='type']/@Value = 'Document'">View</xsl:if></xsl:variable>
			<iframe frameborder='0'>
				<xsl:attribute name="src">http://docs.google.com/<xsl:value-of select="$googledoc_action"/>?docid=<xsl:value-of select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Parameter[@Name='docid']/@Value" /></xsl:attribute>
				<xsl:attribute name="width"><xsl:value-of select="$width"/></xsl:attribute>
				<xsl:attribute name="height"><xsl:value-of select="$height"/></xsl:attribute>
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
					<xsl:with-param name="curPurpose" select="$curPurpose" />
					<xsl:with-param name="mode">elements</xsl:with-param>
					<xsl:with-param name="cmobid" select="$cmobid" />
				</xsl:call-template>-->
				<embed>
					<xsl:attribute name="src"><xsl:value-of select="$data"/></xsl:attribute>
					<xsl:attribute name="type"><xsl:value-of select="$type"/></xsl:attribute>
					<xsl:attribute name="width"><xsl:value-of select="$width"/></xsl:attribute>
					<xsl:attribute name="height"><xsl:value-of select="$height"/></xsl:attribute>
					<xsl:call-template name="MOBParams">
						<xsl:with-param name="curPurpose" select="$curPurpose" />
						<xsl:with-param name="mode">attributes</xsl:with-param>
						<xsl:with-param name="cmobid" select="$cmobid" />
					</xsl:call-template>
				</embed>
			<!--</object>-->
		</xsl:otherwise>

	</xsl:choose>
</xsl:template>

<!-- MOB Parameters -->
<xsl:template name="MOBParams">
	<xsl:param name="curPurpose"/>
	<xsl:param name="cmobid"/>
	<xsl:param name="mode"/>		<!-- 'attributes' | 'elements' -->

	<xsl:choose>
		<!-- output parameters as attributes -->
		<xsl:when test="$mode = 'attributes'">
			<xsl:choose>
				<!-- take parameters from alias -->
				<xsl:when test = "../MediaAliasItem[@Purpose = $curPurpose]/Parameter">
					<xsl:for-each select="../MediaAliasItem[@Purpose = $curPurpose]/Parameter">
						<xsl:attribute name="{@Name}"><xsl:value-of select="@Value"/></xsl:attribute>
					</xsl:for-each>
				</xsl:when>
				<!-- take parameters from object -->
				<xsl:otherwise>
					<xsl:for-each select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Parameter">
						<xsl:attribute name="{@Name}"><xsl:value-of select="@Value"/></xsl:attribute>
					</xsl:for-each>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:when>
		<!-- output parameters as param elements -->
		<xsl:otherwise>
			<xsl:choose>
				<!-- take parameters from alias -->
				<xsl:when test = "../MediaAliasItem[@Purpose = $curPurpose]/Parameter">
					<xsl:for-each select="../MediaAliasItem[@Purpose = $curPurpose]/Parameter">
						<param>
						<xsl:attribute name="name"><xsl:value-of select="@Name"/></xsl:attribute>
						<xsl:attribute name="value"><xsl:value-of select="@Value"/></xsl:attribute>
						</param>
					</xsl:for-each>
				</xsl:when>
				<!-- take parameters from object -->
				<xsl:otherwise>
					<xsl:for-each select="//MediaObject[@Id=$cmobid]/MediaItem[@Purpose=$curPurpose]/Parameter">
						<param>
						<xsl:attribute name="name"><xsl:value-of select="@Name"/></xsl:attribute>
						<xsl:attribute name="value"><xsl:value-of select="@Value"/></xsl:attribute>
						</param>
					</xsl:for-each>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:otherwise>
	</xsl:choose>

</xsl:template>

<!-- Fullscreen Link -->
<xsl:template name="FullscreenLink">
	<xsl:param name="cmobid"/>
	<xsl:if test="count(../MediaAliasItem[@Purpose='Fullscreen']) = 1 and
		count(//MediaObject[@Id=$cmobid]/MediaItem[@Purpose='Fullscreen']) = 1 and
		$mode != 'fullscreen' and $mode != 'print'">

		<xsl:choose>
		<xsl:when test="$fullscreen_link = 'fullscreen.html'">
			<a target="_blank">
			<xsl:attribute name="href">fullscreen_<xsl:value-of select="substring-after($cmobid,'mob_')"/>.html</xsl:attribute>
			<img border="0" align="right">
			<xsl:attribute name="src"><xsl:value-of select="$enlarge_path"/></xsl:attribute>
			</img>
			</a>
		</xsl:when>
		<xsl:otherwise>
			<a target="_blank">
			<xsl:attribute name="href"><xsl:value-of select="$fullscreen_link"/>&amp;mob_id=<xsl:value-of select="substring-after($cmobid,'mob_')"/>&amp;pg_id=<xsl:value-of select="$pg_id"/></xsl:attribute>
			<img border="0" align="right">
			<xsl:attribute name="src"><xsl:value-of select="$enlarge_path"/></xsl:attribute>
			</img>
			</a>
		</xsl:otherwise>
		</xsl:choose>
	</xsl:if>
</xsl:template>


<!-- MediaObject -->
<xsl:template match="MediaObject">
	<xsl:apply-templates select="MediaAlias"/>
</xsl:template>

<!-- Section -->
<xsl:template match="Section">
	<div>
		<xsl:if test="@Characteristic">
			<xsl:attribute name="class"><xsl:value-of select="@Characteristic"/></xsl:attribute>
		</xsl:if>
		<xsl:if test="$mode = 'edit'">
			<xsl:attribute name="style">min-height: 60px; height: auto !important; height: 60px;</xsl:attribute>
		</xsl:if>
		<xsl:call-template name="EditReturnAnchors"/>
		<!-- command selectbox -->
		<xsl:if test="$mode = 'edit'">
			<xsl:call-template name="DropArea">
				<xsl:with-param name="hier_id"><xsl:value-of select="@HierId"/></xsl:with-param>
				<xsl:with-param name="pc_id"><xsl:value-of select="@PCID"/></xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<xsl:apply-templates/>
		<xsl:if test="$mode = 'edit'">
			<!-- <xsl:value-of select="../@HierId"/> -->
			<xsl:if test="$javascript='disable'">
				<br />
				<input type="checkbox" name="target[]">
					<xsl:attribute name="value"><xsl:value-of select="../@HierId"/>:<xsl:value-of select="../@PCID"/>
					</xsl:attribute>
				</input>
			</xsl:if>
			<xsl:call-template name="EditMenu">
				<xsl:with-param name="hier_id" select="../@HierId" />
				<xsl:with-param name="pc_id" select="../@PCID" />
				<xsl:with-param name="edit">y</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="$mode = 'edit'">
			<br />
		</xsl:if>
	</div>
</xsl:template>

<!-- Resources -->
<xsl:template match="Resources">
	<div>
		[list-<xsl:value-of select="./ResourceList/@Type"/>]
		<xsl:call-template name="EditReturnAnchors"/>
		<xsl:if test="$mode = 'edit'">
			<!-- <xsl:value-of select="../@HierId"/> -->
			<xsl:if test="$javascript='disable'">
				<br />
				<input type="checkbox" name="target[]">
					<xsl:attribute name="value"><xsl:value-of select="../@HierId"/>:<xsl:value-of select="../@PCID"/>
					</xsl:attribute>
				</input>
			</xsl:if>
			<xsl:call-template name="EditMenu">
				<xsl:with-param name="hier_id" select="../@HierId" />
				<xsl:with-param name="pc_id" select="../@PCID" />
				<xsl:with-param name="edit">y</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
	</div>
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
			<xsl:if test="$mode != 'edit'">
				<xsl:attribute name="style">clear:both; float:left;</xsl:attribute>
			</xsl:if>
		</xsl:if>
		<xsl:if test="./Layout[1]/@HorizontalAlign = 'RightFloat'">
			<xsl:if test="$mode != 'edit'">
				<xsl:attribute name="style">clear:both; float:right;</xsl:attribute>
			</xsl:if>
		</xsl:if>
		<table class="ilc_Media" width="1">
			<xsl:if test="(./Layout[1]/@HorizontalAlign = 'LeftFloat')">
				<xsl:attribute name="style">margin-left: 0px; style="float:left;"</xsl:attribute>
			</xsl:if>
			<xsl:if test="./Layout[1]/@HorizontalAlign = 'RightFloat'">
				<xsl:attribute name="style">margin-right: 0px; style="float:right;</xsl:attribute>
			</xsl:if>
			<tr><td class="ilc_Mob">
				<div>
					[[[[[Map;<xsl:value-of select="@Latitude"/>;<xsl:value-of select="@Longitude"/>;<xsl:value-of select="@Zoom"/>;<xsl:value-of select="./Layout[1]/@Width"/>;<xsl:value-of select="./Layout[1]/@Height"/>]]]]]
					<xsl:call-template name="EditReturnAnchors"/>
				</div>
			</td></tr>
			<xsl:if test="count(./MapCaption[1]) != 0">
				<tr><td class="ilc_MediaCaption">
				<xsl:value-of select="./MapCaption[1]"/>
				</td></tr>
			</xsl:if>
		</table>
		<xsl:if test="$mode = 'edit'">
			<!-- <xsl:value-of select="../@HierId"/> -->
			<xsl:if test="$javascript='disable'">
				<br />
				<input type="checkbox" name="target[]">
					<xsl:attribute name="value"><xsl:value-of select="../@HierId"/>:<xsl:value-of select="../@PCID"/>
					</xsl:attribute>
				</input>
			</xsl:if>
			<xsl:call-template name="EditMenu">
				<xsl:with-param name="hier_id" select="../@HierId" />
				<xsl:with-param name="pc_id" select="../@PCID" />
				<xsl:with-param name="edit">y</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
	</div>
</xsl:template>

<!-- Tabs -->
<xsl:template match="Tabs">
	<xsl:call-template name="EditReturnAnchors"/>
	<xsl:if test="@Type = 'HorizontalTabs'">
		<div class="yui-skin-sam">
		<div>
		<xsl:if test="$mode != 'edit'">
			<xsl:attribute name="id">tabs<xsl:number count="Tabs" level="any" /></xsl:attribute>
			<xsl:attribute name="class">yui-navset</xsl:attribute>
		</xsl:if>
			<xsl:if test="$mode != 'edit'">
				<ul class="yui-nav">
					<xsl:for-each select="./Tab">
						<li>
							<xsl:if test="position() = 1">
								<xsl:attribute name="class">selected</xsl:attribute>
							</xsl:if>
						<a>
						<xsl:attribute name="href">#tab<xsl:number count="Tab" level="any" /></xsl:attribute>
						&amp;nbsp;<xsl:value-of select="./TabCaption" />&amp;nbsp;
						</a></li>
					</xsl:for-each>
				</ul>
				<div class="yui-content">
					<xsl:apply-templates/>
				</div>
			</xsl:if>
		<xsl:if test="$mode != 'edit'">
			<script type="text/javascript"> 
				var tabView<xsl:number count="Tabs" level="any" /> = new YAHOO.widget.TabView('tabs<xsl:number count="Tabs" level="any" />'); 
			</script>
		</xsl:if>
			<xsl:if test="$mode = 'edit'">
				<xsl:apply-templates/>
			</xsl:if>
		</div>
		</div>
	</xsl:if>
	<xsl:if test="@Type = 'Accordion'">
		<ul>
		<xsl:apply-templates/>
		</ul>
	</xsl:if>
	<!-- command selectbox -->
	<xsl:if test="$mode = 'edit'">
		<!-- <xsl:value-of select="../@HierId"/> -->
		<xsl:if test = "$javascript='disable'">
		<input type="checkbox" name="target[]">
			<xsl:attribute name="value"><xsl:value-of select="../@HierId"/>:<xsl:value-of select="../@PCID"/>
			</xsl:attribute>
		</input>
		</xsl:if>
		<xsl:call-template name="EditMenu">
			<xsl:with-param name="hier_id" select="../@HierId" />
			<xsl:with-param name="pc_id" select="../@PCID" />
			<xsl:with-param name="edit">p</xsl:with-param>
		</xsl:call-template>
		<xsl:if test = "$javascript='disable'">
			<br/>
		</xsl:if>
	</xsl:if>
</xsl:template>

<!-- Tab -->
<xsl:template match="Tab">
	<xsl:if test="$mode = 'edit'">
		<div class="il_edit_pc_tab_head">
		<xsl:if test="$javascript='disable'">
			<!-- checkbox -->
			<input type="checkbox" name="target[]">
				<xsl:attribute name="value"><xsl:value-of select="@HierId"/>:<xsl:value-of select="@PCID"/>
				</xsl:attribute>
			</input>
			<select size="1" class="ilEditSelect">
				<xsl:attribute name="name">command<xsl:value-of select="@HierId"/>
				</xsl:attribute>
				<xsl:if test = "$javascript = 'disable'">
					<xsl:call-template name="EditMenuInsertItems"/>
				</xsl:if>
				<xsl:call-template name="ListItemMenu"/>
			</select>
			<input class="ilEditSubmit" type="submit">
				<xsl:attribute name="value"><xsl:value-of select="//LVs/LV[@name='ed_go']/@value"/></xsl:attribute>
				<xsl:attribute name="name">cmd[exec_<xsl:value-of select="@HierId"/>:<xsl:value-of select="@PCID"/>]</xsl:attribute>
			</input>
			<br/>
		</xsl:if>
		<xsl:if test="$javascript = 'enable'">
			<xsl:call-template name="Icon">
				<xsl:with-param name="img_id">CONTENTi<xsl:value-of select="@HierId"/>:<xsl:value-of select="@PCID"/></xsl:with-param>
				<xsl:with-param name="img_src"><xsl:value-of select="$img_item"/></xsl:with-param>
				<xsl:with-param name="float">y</xsl:with-param>
			</xsl:call-template>
			<div style="position:absolute;left:0;top:0;visibility:hidden;">
				<xsl:attribute name="id">contextmenu_i<xsl:value-of select="@HierId"/></xsl:attribute>
				<table class="il_editmenu" cellspacing="0" cellpadding="3">
					<xsl:call-template name="ListItemMenu"/>
				</table>
			</div>
		</xsl:if>
		&amp;nbsp;<xsl:value-of select="./TabCaption" />
		</div>
	</xsl:if>
	<div>
	<xsl:if test="$mode != 'edit'">
		<xsl:attribute name="id">tab<xsl:number count="Tab" level="any"/></xsl:attribute>
	</xsl:if>
	<xsl:if test="$mode = 'edit'">
		<xsl:attribute name="class">il_edit_pc_tab</xsl:attribute>
	</xsl:if>
	<xsl:call-template name="EditReturnAnchors"/>
	<!-- insert commands -->
	<!-- <xsl:value-of select="@HierId"/> -->
	<xsl:if test="$mode = 'edit'">
		<xsl:if test="$javascript = 'enable'">
			<xsl:call-template name="DropArea">
				<xsl:with-param name="hier_id"><xsl:value-of select="@HierId"/></xsl:with-param>
				<xsl:with-param name="pc_id"><xsl:value-of select="@PCID"/></xsl:with-param>
			</xsl:call-template>
		</xsl:if>
	</xsl:if>
	<xsl:apply-templates select="PageContent"/>
	</div>
</xsl:template>

<!-- Plugged -->
<xsl:template match="Plugged">
	<div>
		{{{{{Plugged;<xsl:value-of select="@PluginID"/>;<xsl:value-of select="@PluginVersion"/>}}}}}
		<xsl:call-template name="EditReturnAnchors"/>
		<xsl:if test="$mode = 'edit'">
			<!-- <xsl:value-of select="../@HierId"/> -->
			<xsl:if test="$javascript='disable'">
				<br />
				<input type="checkbox" name="target[]">
					<xsl:attribute name="value"><xsl:value-of select="../@HierId"/>:<xsl:value-of select="../@PCID"/>
					</xsl:attribute>
				</input>
			</xsl:if>
			<xsl:call-template name="EditMenu">
				<xsl:with-param name="hier_id" select="../@HierId" />
				<xsl:with-param name="pc_id" select="../@PCID" />
				<xsl:with-param name="edit">y</xsl:with-param>
			</xsl:call-template>
		</xsl:if>
	</div>
</xsl:template>

<!-- Question -->
<xsl:template match="Question">
	<div class="ilc_Question">
	[[[[[Question;]]]]]
	<xsl:call-template name="EditReturnAnchors"/>

	<!-- command selectbox -->
	<xsl:if test="$mode = 'edit'">
		<br />
		<!-- <xsl:value-of select="../@HierId"/> -->
		<xsl:if test = "$javascript='disable'">
		<input type="checkbox" name="target[]">
			<xsl:attribute name="value"><xsl:value-of select="../@HierId"/>:<xsl:value-of select="../@PCID"/>
			</xsl:attribute>
		</input>
		</xsl:if>
		<xsl:call-template name="EditMenu">
			<xsl:with-param name="hier_id" select="../@HierId" />
			<xsl:with-param name="pc_id" select="../@PCID" />
			<xsl:with-param name="edit">y</xsl:with-param>
		</xsl:call-template>
	</xsl:if>

	</div>
</xsl:template>

<!-- dump language variable data -->
<xsl:template match="LV"/>
<xsl:template match="LVs"/>

</xsl:stylesheet>
