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

<xsl:template match="LearningObject">
  <xsl:choose>
		<xsl:when test="MetaData/General/@AggregationLevel=1">
			<xsl:choose>
				<xsl:when test="./MetaData/Technical/@Format='image-gif'">
					<table class="ImageTable" id="lo_view">
						<tr>
							<td class="ImageCell" id="lo_view">
								<img class="Image" id="lo_view">
									<xsl:attribute name="src">
										<xsl:value-of select="MetaData/Technical/Location"/>
									</xsl:attribute>	
									<xsl:attribute name="alt">
										<xsl:value-of select="MetaData/General/Description"/>
									</xsl:attribute>	
								</img>
							</td>
						</tr>
						<tr>
							<td class="ImageCaption" id="lo_view">
								<xsl:text>Abbildung</xsl:text>
								<xsl:number count="LearningObject"/>
								<xsl:text>: </xsl:text>
								<xsl:value-of select="MetaData/General/Title"/>
							</td>
						</tr>
						<tr>
							<td class="ImageText" id="lo_view">
								<xsl:apply-templates select="MetaData/General/Description"/>
							</td>
						</tr>
					</table>
				</xsl:when>
				<xsl:when test="./MetaData/Technical/@Format='image-jpeg'">
					<table class="ImageTable" id="lo_view">
						<tr>
							<td class="ImageCell" id="lo_view">
								<img class="Image" id="lo_view">
									<xsl:attribute name="src">
										<xsl:value-of select="MetaData/Technical/Location"/>
									</xsl:attribute>	
									<xsl:attribute name="alt">
										<xsl:value-of select="MetaData/General/Description"/>
									</xsl:attribute>	
								</img>
							</td>
						</tr>
						<tr>
							<td class="ImageCaption" id="lo_view">
								<xsl:text>Abbildung</xsl:text>
								<xsl:number count="LearningObject"/>
								<xsl:text>: </xsl:text>
								<xsl:value-of select="MetaData/General/Title"/>
							</td>
						</tr>
						<tr>
							<td class="ImageText" id="lo_view">
								<xsl:apply-templates select="MetaData/General/Description"/>
							</td>
						</tr>
					</table>
				</xsl:when>
				<xsl:when test="./MetaData/Technical/@Format='image-jpeg'">
					<table class="ImageTable" id="lo_view">
						<tr>
							<td class="ImageCell" id="lo_view">
								<img class="Image" id="lo_view">
									<xsl:attribute name="src">
										<xsl:value-of select="MetaData/Technical/Location"/>
									</xsl:attribute>	
									<xsl:attribute name="alt">
										<xsl:value-of select="MetaData/General/Description"/>
									</xsl:attribute>	
								</img>
							</td>
						</tr>
						<tr>
							<td class="ImageCaption" id="lo_view">
								<xsl:text>Abbildung</xsl:text>
								<xsl:number count="LearningObject"/>
								<xsl:text>: </xsl:text>
								<xsl:value-of select="MetaData/General/Title"/>
							</td>
						</tr>
						<tr>
							<td class="ImageText" id="lo_view">
								<xsl:apply-templates select="MetaData/General/Description"/>
							</td>
						</tr>
					</table>
				</xsl:when>
				<xsl:when test="MetaData/Technical/@Format='image/svg+xml'">
					<xsl:copy-of select="document(MetaData/Technical/Location)/*"/>
					<div class="ImageCaption" id="lo_view">
						<xsl:value-of select="MetaData/General/Title"/>
					</div>
					<div class="ImageText" id="lo_view">
						<xsl:apply-templates select="MetaData/General/Description"/>
					</div>
				</xsl:when>
				<xsl:when test="MetaData/Technical/@Format='text-xhtml'">
					<xsl:copy-of select="document(MetaData/Technical/Location)/xhtml:html/xhtml:body/*"/>
					<div class="ImageCaption" id="lo_view">
						<xsl:value-of select="MetaData/General/Title"/>
					</div>
					<div class="ImageText">
						<xsl:apply-templates select="MetaData/General/Description"/>
					</div>
				</xsl:when>
				<xsl:otherwise>
					<a class="Link" id="lo_view">
						<xsl:attribute name="href">
							<xsl:value-of select="MetaData/Technical/Location"/>
						</xsl:attribute>
						<xsl:value-of select="MetaData/General/Title"/>
					</a>
				</xsl:otherwise>
			</xsl:choose>	
		</xsl:when>
		<xsl:when test="MetaData/General/@AggregationLevel=2">
			<xsl:apply-templates/>
		</xsl:when>
		<xsl:when test="MetaData/General/@AggregationLevel=3">
			<xsl:apply-templates/>
		</xsl:when>
	</xsl:choose>	
</xsl:template>

<xsl:template match="Glossary">
	<div>
		<xsl:attribute name="class">Glossary</xsl:attribute>
		<xsl:attribute name="id">gl_view</xsl:attribute>
		<xsl:variable name="GlossaryID" select="@Id"/>
		<span>
			<xsl:attribute name="class">GlossaryHeading</xsl:attribute>
			<xsl:attribute name="id">gl_view</xsl:attribute>
			Glossary:	
		</span>
		<p>
			<xsl:for-each select="GlossaryItem">
 				<a>
					<xsl:attribute name="href">
						<xsl:text>lo_glossary.php?id=</xsl:text>
						<xsl:value-of select="$GlossaryID"/>
						<xsl:text>&amp;glossary=</xsl:text>
						<xsl:value-of select="GlossaryTerm/@Definition"/>
					</xsl:attribute>
					<xsl:attribute name="target">new</xsl:attribute>
					<xsl:value-of select="GlossaryTerm"/>
				</a><br/>
			</xsl:for-each>
		</p>	
	</div>
</xsl:template>

<!-- we dump the MetaData and Bibliography -->
<xsl:template match="MetaData"/>
<xsl:template match="Examples"/>
<xsl:template match="Reference"/>
<xsl:template match="Bibliography"/>

<!-- start of explicit template declaration -->

<xsl:template match="LO">
	<a>
		<xsl:attribute name="href">lo_view.php?lm_id=<xsl:value-of select="@lm"/>&amp;lo_id=<xsl:value-of select="@id"/></xsl:attribute>	
		<xsl:value-of select="@title"/>
	</a>
	<br/>
</xsl:template>

<xsl:template match="Headline">
	<h2 class="Headline" id="lo_view"><xsl:apply-templates/></h2>
</xsl:template>	

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

<xsl:template match="List">
	<xsl:choose>
		<xsl:when test="@Type='ordered'">
			<xsl:for-each select="Title">
				<br/>
				<xsl:apply-templates select="."/>
			</xsl:for-each>
			<ol class="OrderedList" id="lo_view">
				<xsl:apply-templates select="Item"/>
			</ol>
		</xsl:when>
		<xsl:otherwise>
			<xsl:for-each select="Title">
				<br/>
				<xsl:apply-templates select="."/>
			</xsl:for-each>
			<ul class="List" id="lo_view">
				<xsl:apply-templates select="Item"/>
			</ul>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="Item">
	<li class="ListItem" id="lo_view"><xsl:apply-templates/></li>
</xsl:template>	

<xsl:template match="Emph|Strong">
	<xsl:variable name="Reason" select="@Reason"/>
	<xsl:variable name="Tagname" select="name()"/>
	<span class="{$Tagname}{$Reason}" id="lo_view"><xsl:value-of select="current()"/></span>
</xsl:template>	

<xsl:template match="Comment">
	<xsl:text><!--</xsl:text>
		<xsl:value-of select="."/> 
	<xsl:text>--></xsl:text>
</xsl:template>

<xsl:template match="Entity">
	<xsl:choose>
		<xsl:when test="vCard">
			<xsl:value-of select="cCard/FN"/>
		</xsl:when>
		<xsl:otherwise>
			<xsl:apply-templates/>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="Quotation">
	<xsl:choose>
		<xsl:when test="contains(@Type,'Block')">
			<blockquote class="QuotationBlock" id="lo_view">
				&quot;<xsl:value-of select="current()"/>&quot;
				<xsl:if test="@Reference_to">
					<xsl:choose>
						<xsl:when test="id(@Reference_to)/author">
							<xsl:for-each select="id(@Reference_to)/author">
								<xsl:apply-templates select="lastname"/>
								<xsl:if test="position()!=last()">
									<xsl:text>, </xsl:text>
								</xsl:if>
							</xsl:for-each>
						</xsl:when>
						<xsl:when test="id(@Reference_to)/editor">
							<xsl:for-each select="id(@Reference_to)/editor">
								<xsl:apply-templates select="lastname"/>
								<xsl:if test="position()!=last()">
									<xsl:text>, </xsl:text>
								</xsl:if>
							</xsl:for-each>
						</xsl:when>
					</xsl:choose>
					<xsl:text>, </xsl:text>
					<xsl:apply-templates select="id(@Reference_to)/year"/>
					<xsl:if test="Page">
						<xsl:text>, S. </xsl:text>
						<xsl:apply-templates select="@Page"/>
					</xsl:if>
				</xsl:if>
			</blockquote>	
		</xsl:when>
		<xsl:otherwise>
			<span class="Quotation" id="lo_view">&quot;<xsl:apply-templates/>&quot;</span>
			<xsl:if test="@Reference_to">
				<div class="Reference" id="lo_view">
				<xsl:text>(</xsl:text>
				<xsl:choose>
					<xsl:when test="id(@Reference_to)/author">
						<xsl:for-each select="id(@Reference_to)/author">
							<xsl:apply-templates select="lastname"/>
							<xsl:if test="position()!=last()">
								<xsl:text>, </xsl:text>
							</xsl:if>
						</xsl:for-each>
					</xsl:when>
					<xsl:when test="id(@Reference_to)/editor">
						<xsl:for-each select="id(@Reference_to)/editor">
							<xsl:apply-templates select="lastname"/>
							<xsl:if test="position()!=last()">
								<xsl:text>, </xsl:text>
							</xsl:if>
						</xsl:for-each>
					</xsl:when>
				</xsl:choose>
				<xsl:text>, </xsl:text>
				<xsl:apply-templates select="id(@Reference_to)/year"/>
				<xsl:if test="Page">
					<xsl:text>, S. </xsl:text>
					<xsl:apply-templates select="@Page"/>
				</xsl:if>
				<xsl:text>)</xsl:text>
				</div>
			</xsl:if>
		</xsl:otherwise>
</xsl:choose>		
</xsl:template>	

<xsl:template match="Text">
	<xsl:apply-templates/>
</xsl:template>

<xsl:template match="Link">
	<xsl:variable name="Href" select="@Href"/>
	<a href="{$Href}" class="Link" id="lo_view"><xsl:value-of select="current()"/></a>
</xsl:template>

<xsl:template match="Code">
	<xsl:choose>
		<xsl:when test="@Display='Block'">
			<div class="CodeBlock" id="lo_view">
				<xsl:apply-templates/>
			</div>
		</xsl:when>
		<xsl:otherwise>
			<xsl:choose>
				<xsl:when test="CodeElem">
					<xsl:apply-templates/>
				</xsl:when>
				<xsl:otherwise>
					<span class="Code" id="lo_view">
						<xsl:apply-templates/>
					</span>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="CodeElem">
	<span class="CodeElem" id="lo_view"><xsl:apply-templates/></span>
	<br/>
</xsl:template>	

<!-- in lack of testing data Formula and Table are -->
<!-- simply copied from the milca stylesheet -->
<xsl:template match="Formula">
	<xsl:choose>
		<xsl:when test="@Display='Block'">
			<div class="FormulaBlock" id="lo_view">
				<xsl:if test="@Id">
					<xsl:attribute name="id">
						<xsl:value-of select="@Id"/>
					</xsl:attribute>
				</xsl:if>
				<xsl:copy-of select="node()"/>
				<xsl:if test="@Id">
					<span class="Label">
						<xsl:text>(</xsl:text>
						<xsl:choose>
							<xsl:when test="@Information_Link">
								<a target="_blank">
									<xsl:attribute name="href">
										<xsl:value-of select="@Information_Link"/>
									</xsl:attribute>
									<xsl:value-of select="@Id"/>
								</a>
							</xsl:when>
							<xsl:when test="@Information_Ref">
								<a>
									<xsl:attribute name="href">
										<xsl:value-of select="@Information_Ref"/>
									</xsl:attribute>
									<xsl:value-of select="@Id"/>
								</a>
							</xsl:when>
							<xsl:otherwise>
								<xsl:value-of select="@Id"/>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:text>)</xsl:text>
					</span>
				</xsl:if>
			</div>
		</xsl:when>
		<xsl:otherwise>
			<span class="Formula">
				<xsl:if test="@Id">
					<xsl:attribute name="id">
						<xsl:value-of select="@Id"/>
					</xsl:attribute>
				</xsl:if>
				<xsl:copy-of select="node()"/>
			</span>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

<xsl:template match="Table">
	<xsl:for-each select="Title">
		<xsl:value-of select="."/>
	<br/>
	</xsl:for-each>
	<table class="Table" id="lo_view">
	<xsl:for-each select="@Width">
		<xsl:attribute name="width">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:for-each>
	<xsl:for-each select="@Height">
		<xsl:attribute name="height">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:for-each>
	<xsl:for-each select="@Border">
		<xsl:attribute name="border">
			<xsl:value-of select="."/>
		</xsl:attribute>
	</xsl:for-each>
	<xsl:if test="Summary">
		<xsl:attribute name="summary">
			<xsl:value-of select="Summary"/>
		</xsl:attribute>
	</xsl:if>
	<xsl:if test="HeaderCaption">
		<caption align="top">
		<xsl:text>Tabelle </xsl:text>
		<xsl:number count="Table"/><xsl:text>: </xsl:text>
		<xsl:value-of select="HeaderCaption"/>
		</caption>
	</xsl:if>
	<xsl:if test="FooterCaption">
		<caption align="bottom">
		<xsl:value-of select="FooterCaption"/>
		</caption>
	</xsl:if>
	<xsl:for-each select="TableRow">
		<tr class="TableRow" id="lo_view">
			<xsl:for-each select="TableData">
				<td class="TableData" id="lo_view">
					<xsl:value-of select="."/>
				</td>
			</xsl:for-each>
		</tr>
	</xsl:for-each>
	</table>
</xsl:template>

<xsl:template match="Content">
	<div class="lo" id="lo_view">
		<span class="Title" id="lo_view">
			<xsl:value-of select="../MetaData/General/Title"/>
		</span>
		<xsl:apply-templates/>
	</div>
</xsl:template>
</xsl:stylesheet>
