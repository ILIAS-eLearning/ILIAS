<?xml version="1.0"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  xmlns:fox="http://xml.apache.org/fop/extensions">

	<xsl:output method="xml" omit-xml-declaration="yes" />

	<xsl:template match="*">
		<xsl:copy-of select="." />
	</xsl:template>

	<!-- strip white spaces between tags -->
	<xsl:strip-space elements="*" />
	
	<xsl:param name="target_dir"/>
	
	<!-- ContentObject -->
	<xsl:template match="ContentObject[@Type='SCORM2004SCO']">
        <fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format"  xmlns:fox="http://xml.apache.org/fop/extensions">
			<fo:layout-master-set>
				<fo:simple-page-master master-name="Master">
					<fo:region-body margin="10mm" />
				</fo:simple-page-master>
			</fo:layout-master-set>
            <fo:declarations>
            </fo:declarations>
			<fo:page-sequence master-reference="Master">
                <xsl:attribute name="id"><xsl:value-of select="MetaData/General/Identifier/@Entry" /></xsl:attribute>
                <fo:title>
                    <xsl:value-of select="MetaData/General/Title"></xsl:value-of>
                </fo:title>
                <fo:flow flow-name="xsl-region-body">
                    <fo:block text-align="center" font-weight="bold" font-size="24pt" line-height="27pt" space-after="18pt">
                        <xsl:value-of select="MetaData/General/Title"></xsl:value-of>
                    </fo:block>
                    <fo:block font-size="14pt" line-height="17pt" space-after="12pt">
                        <xsl:value-of select="MetaData/General/Description"></xsl:value-of>
                    </fo:block>
                </fo:flow>
            </fo:page-sequence>
			<xsl:apply-templates />
		</fo:root>
	</xsl:template>

    <xsl:template match="ContentObject[@Type='Glossary']">
        <fo:page-sequence master-reference="Master">
            <xsl:attribute name="id"><xsl:value-of select="MetaData/General/Identifier/@Entry" /></xsl:attribute>
            <fo:title>
                <xsl:value-of select="MetaData/General/Title"></xsl:value-of>
            </fo:title>
            <fo:flow flow-name="xsl-region-body">
                <fo:block font-size="1.5em" space-before="5mm" space-after="5mm" font-weight="bold">
                    <xsl:value-of select="MetaData/General/Title"></xsl:value-of>
                </fo:block>
                <xsl:for-each select="Glossary/GlossaryItem">
                    <xsl:call-template name="GlossaryItemTpl"></xsl:call-template>
                </xsl:for-each>
            </fo:flow>
        </fo:page-sequence>
    </xsl:template>
    
	<xsl:template match="MetaData" />
	<xsl:template match="PlaceHolder" />

	<!-- PageObject -->
	<xsl:template match="PageObject">
		<fo:page-sequence master-reference="Master">
            <xsl:attribute name="id"><xsl:value-of select="MetaData/General/Identifier/@Entry" /></xsl:attribute>
			<fo:title>
				<xsl:value-of select="MetaData/General/Title"></xsl:value-of>
			</fo:title>
			<fo:flow flow-name="xsl-region-body">
                <fo:block font-size="1.5em" space-before="5mm" space-after="5mm" font-weight="bold">
                    <xsl:value-of select="MetaData/General/Title"></xsl:value-of>
                </fo:block>
				<xsl:apply-templates />
			</fo:flow>
		</fo:page-sequence>
	</xsl:template>


	<!-- PageContent -->
	<xsl:template match="PageContent">
		<fo:block>
		<xsl:apply-templates />
		</fo:block>
	</xsl:template>

	<!-- Paragraph -->
	<xsl:template match="Paragraph|Section">
		<xsl:choose>
			<xsl:when test="@Characteristic = 'Headline1'">
				<fo:block font-size="1.5em" space-before="5mm" space-after="5mm">
					<xsl:apply-templates />
				</fo:block>
			</xsl:when>
			<xsl:when test="@Characteristic = 'Headline2'">
				<fo:block font-size="1.4em" space-before="5mm" space-after="5mm">
					<xsl:apply-templates />
				</fo:block>
			</xsl:when>
			<xsl:when test="@Characteristic = 'Headline3'">
				<fo:block font-size="1.3em" space-before="5mm" space-after="5mm">
					<xsl:apply-templates />
				</fo:block>
			</xsl:when>
			<xsl:when test="@Characteristic = 'Hint'">
				<fo:block border="1px solid #FFFFD0" background-color="#FFFFF4"
					space-before="5mm" space-after="5mm" padding-start="3pt"
					padding-end="3pt" padding-before="3pt" padding-after="3pt">
					<xsl:apply-templates />
				</fo:block>
			</xsl:when>
			<xsl:when test="@Characteristic = 'Citation'">
				<fo:block space-before="5mm" space-after="5mm"
					padding-start="3pt" padding-end="3pt" padding-before="3pt"
					padding-after="3pt">
					<xsl:apply-templates />
				</fo:block>
			</xsl:when>
			<xsl:when test="@Characteristic = 'Mnemonic'">
				<fo:block space-before="5mm" space-after="5mm"
					padding-start="3pt" padding-end="3pt" padding-before="3pt"
					padding-after="3pt" border="2pt solid red">
					<xsl:apply-templates />
				</fo:block>
			</xsl:when>
			<xsl:when test="@Characteristic = 'Ressources'">
				<fo:block space-before="5mm" space-after="5mm"
					background-color="#F9F9F9">
					<xsl:apply-templates />
				</fo:block>
			</xsl:when>
			<xsl:when test="@Characteristic = 'Reading'">
				<fo:block space-before="5mm" space-after="5mm"
					padding-start="3pt" padding-end="3pt" padding-before="3pt"
					padding-after="3pt" background-color="#F9F9F9">
					<xsl:apply-templates />
				</fo:block>
			</xsl:when>
			<xsl:when test="@Characteristic = 'Example'">
				<fo:block space-before="5mm" space-after="5mm"
					padding-start="3pt" padding-end="3pt" padding-before="3pt"
					padding-after="3pt" border-left="2pt solid blue">
					<xsl:apply-templates />
				</fo:block>
			</xsl:when>
			<xsl:when test="@Characteristic = 'Additional'">
				<fo:block space-before="5mm" space-after="5mm"
					padding-start="3pt" padding-end="3pt" padding-before="3pt"
					padding-after="3pt" border="1pt solid blue">
					<xsl:apply-templates />
				</fo:block>
			</xsl:when>
			<xsl:when test="@Characteristic = 'Remark'">
				<fo:block space-before="5mm" space-after="5mm"
					padding-start="3pt" padding-end="3pt" padding-before="3pt"
					padding-after="3pt" border="1pt solid #909090" background-color="#F0F0F0">
					<xsl:apply-templates />
				</fo:block>
			</xsl:when>
			<xsl:otherwise>
				<fo:block>
					<xsl:apply-templates />
				</fo:block>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!-- Basic Text Formatting -->
	<xsl:template match="Strong">
		<fo:inline font-weight="bold">
			<xsl:apply-templates />
		</fo:inline>
	</xsl:template>
	<xsl:template match="Emph">
		<fo:inline font-style="italic">
			<xsl:apply-templates />
		</fo:inline>
	</xsl:template>
	<xsl:template match="Comment">
		<fo:inline color="green">
			<xsl:apply-templates />
		</fo:inline>
	</xsl:template>
	<xsl:template match="Quotation">
		<fo:inline font-style="italic" color="brown">
			<xsl:apply-templates />
		</fo:inline>
	</xsl:template>
	<xsl:template match="Accent">
		<fo:inline color="#E000E0">
			<xsl:apply-templates />
		</fo:inline>
	</xsl:template>
	<xsl:template match="Code">
		<fo:inline color="#222222" font-family="monospace">
			<xsl:apply-templates />
		</fo:inline>
	</xsl:template>
	<xsl:template match="Important">
		<fo:inline text-decoration="underline">
			<xsl:apply-templates />
		</fo:inline>
	</xsl:template>

	<!-- Line break -->
	<xsl:template match="br">
		<fo:block>
		</fo:block>
	</xsl:template>

	<!-- Tables -->
	<xsl:template match="Table">
		<fo:table table-layout="fixed">
			<xsl:attribute name="width"><xsl:value-of select="@Width" /></xsl:attribute>
			<xsl:attribute name="border-width"><xsl:value-of select="@Border" /></xsl:attribute>
			<fo:table-body>
				<xsl:apply-templates select="TableRow" />
			</fo:table-body>
		</fo:table>
	</xsl:template>
	<xsl:template match="TableRow">
		<fo:table-row>
			<xsl:apply-templates select="TableData" />
		</fo:table-row>
	</xsl:template>
	<xsl:template match="TableData">
		<fo:table-cell>
			<fo:block>
				<xsl:apply-templates />
			</fo:block>
		</fo:table-cell>
	</xsl:template>

	<xsl:template match="List|FileList|SimpleBulletList|SimpleNumberedList">
		<fo:list-block>
			<xsl:apply-templates />
		</fo:list-block>
	</xsl:template>

	
	<xsl:template match="ListItem|SimpleListItem">
		<fo:list-item>
			<fo:list-item-label end-indent="label-end()">
				<fo:block>
					<xsl:variable name="value-attr">
						<xsl:choose>
							<xsl:when test="../@StartValue">
								<xsl:number value="position() + ../@StartValue - 1" />
							</xsl:when>
							<xsl:otherwise>
								<xsl:number value="position()" />
							</xsl:otherwise>
						</xsl:choose>
					</xsl:variable>
					<xsl:choose>
						<xsl:when test="(../@Type='Ordered') or (name(parent::node())='SimpleNumberedList')">
							<xsl:choose>
								<xsl:when test="../@NumberingType='Roman'">
									<xsl:number value="$value-attr" format="I. " />
								</xsl:when>
								<xsl:when test="../@NumberingType='roman'">
									<xsl:number value="$value-attr" format="i. " />
								</xsl:when>
								<xsl:when test="../@NumberingType='Alphabetic'">
									<xsl:number value="$value-attr" format="A. " />
								</xsl:when>
								<xsl:when test="../@NumberingType='alphabetic'">
									<xsl:number value="$value-attr" format="a. " />
								</xsl:when>
								<xsl:when test="../@NumberingType='Decimal'">
									<xsl:number value="$value-attr" format="1. " />
								</xsl:when>
								<xsl:otherwise>
									<xsl:number value="$value-attr" format="1. " />
								</xsl:otherwise>
							</xsl:choose>
						</xsl:when>
						<xsl:otherwise>
							&#x2022;
						</xsl:otherwise>
					</xsl:choose>
				</fo:block>
			</fo:list-item-label>
			<fo:list-item-body start-indent="body-start()">
				<fo:block>
					<xsl:apply-templates />
				</fo:block>
			</fo:list-item-body>
		</fo:list-item>
	</xsl:template>

	<xsl:template match="FileItem">
		<fo:list-item>
			<fo:list-item-label end-indent="label-end()">
				<fo:block>&#x2022;</fo:block>
			</fo:list-item-label>
			<fo:list-item-body start-indent="body-start()">
				<fo:block>
					<xsl:value-of select="Location"></xsl:value-of>
				</fo:block>
			</fo:list-item-body>
		</fo:list-item>
	</xsl:template>

	<xsl:template match="Tabs">
		<xsl:apply-templates />
	</xsl:template>

	<xsl:template match="Tab">
		<fo:block border="1px solid #9EADBA">
			<fo:block font-size="1.3em" background-color="#E2EAF4">
				<xsl:value-of select="TabCaption"></xsl:value-of>
			</fo:block>
			<xsl:apply-templates />
		</fo:block>
	</xsl:template>

	<xsl:template match="ExtLink">
		<fo:basic-link color="blue">
			<xsl:attribute name="external-destination">
	              <xsl:value-of select="@Href"/>
	        </xsl:attribute>
		 	<xsl:value-of select="."/>
		</fo:basic-link>
	</xsl:template>
	
    <xsl:template match="IntLink">
    	<xsl:if test="@Type='File'">
    	</xsl:if>
        <xsl:value-of select="."/>
        <xsl:if test="@Type='GlossaryItem'">
            <fo:inline font-size="8pt" vertical-align="super">
                <fo:basic-link color="black"><xsl:attribute name="internal-destination"><xsl:value-of select="@Target"/></xsl:attribute>*</fo:basic-link>
            </fo:inline>
        </xsl:if>
    </xsl:template>
    	
	<xsl:template match="MediaObject">
		<xsl:if test="name(parent::node())!='ContentObject'">
				<xsl:variable name="cmobid" select="MediaAlias/@OriginId" />
				<xsl:variable name="title"><xsl:value-of select="//MediaObject[MetaData/General/Identifier/@Entry=$cmobid]/MetaData/General/Title"/></xsl:variable>
				<!-- determine location type (LocalFile, Reference) -->
				<xsl:variable name="curType"><xsl:value-of select="//MediaObject[MetaData/General/Identifier/@Entry=$cmobid]/MediaItem[@Purpose = 'Standard']/Location/@Type"/></xsl:variable>
				<!-- determine format (mime type) -->
				<xsl:variable name="type"><xsl:value-of select="//MediaObject[MetaData/General/Identifier/@Entry=$cmobid]/MediaItem[@Purpose = 'Standard']/Format"/></xsl:variable>
				<!-- determine location -->
				<xsl:variable name="data">
					<xsl:if test="$curType = 'LocalFile'"><xsl:value-of select="$target_dir"/>/objects/<xsl:value-of select="$cmobid"/>/<xsl:value-of select="normalize-space(//MediaObject[MetaData/General/Identifier/@Entry=$cmobid]/MediaItem[@Purpose = 'Standard']/Location)"/></xsl:if>
					<xsl:if test="$curType = 'Reference'"><xsl:value-of select="normalize-space(//MediaObject[MetaData/General/Identifier/@Entry=$cmobid]/MediaItem[@Purpose = 'Standard']/Location)"/></xsl:if></xsl:variable>
				<!-- determine width -->
				<xsl:variable name="width"><xsl:value-of select="//MediaObject[MetaData/General/Identifier/@Entry=$cmobid]/MediaItem[@Purpose='Standard']/Layout[1]/@Width"/></xsl:variable>
				<!-- determine height -->
				<xsl:variable name="height"><xsl:value-of select="//MediaObject[MetaData/General/Identifier/@Entry=$cmobid]/MediaItem[@Purpose='Standard']/Layout[1]/@Height"/></xsl:variable>
				<xsl:choose>
					<xsl:when test="$curType='Reference'">
						<fo:basic-link color="blue">
							<xsl:attribute name="external-destination">
					              <xsl:value-of select="$data"/>
					        </xsl:attribute>
						 	<xsl:value-of select="$title"/>
						</fo:basic-link>
					</xsl:when>
					<xsl:when test="($curType='LocalFile')"> <!-- and (substring($type, 1, 5) = 'image') and not(substring($type, 1, 9) = 'image/svg')">  -->
						<fo:external-graphic width="100%" content-width="scale-to-fit" content-height="100%">
							<xsl:attribute name="src"><xsl:value-of select="$data"/></xsl:attribute>
					      <!-- <xsl:if test="$width">
					        <xsl:attribute name="width">
					              <xsl:value-of select="$width"/>
					        </xsl:attribute>
					      </xsl:if>
					      <xsl:if test="$height">
					        <xsl:attribute name="height">
					              <xsl:value-of select="$height"/>
					        </xsl:attribute>
					      </xsl:if> -->
					    </fo:external-graphic>
					</xsl:when>
				</xsl:choose>
		</xsl:if>
	</xsl:template>
	
	<xsl:template match="Question">
		<xsl:variable name="qref" select="@QRef" />
		<xsl:for-each select="//questestinterop[item/@ident=$qref]">
		<xsl:call-template name="QuestionTpl"></xsl:call-template>
		</xsl:for-each>
	</xsl:template>
	
	<xsl:template name="QuestionTpl">
		<fo:block>
			<xsl:for-each select="item/presentation/flow">
				<xsl:apply-templates /> 
			</xsl:for-each>
		</fo:block>
	</xsl:template>
	<xsl:template match="questestinterop" />
	<xsl:template match="material">
		<xsl:apply-templates />
	</xsl:template>
	<xsl:template match="response_xy">
	</xsl:template>
	<xsl:template match="material/mattext">
		<xsl:value-of select="text()"/>
	</xsl:template>
	<xsl:template match="response_str">
		   <fo:inline width="10px" />
	</xsl:template>
	<xsl:template match="response_lid|response_grp">
		<fo:list-block>
			<xsl:for-each select="render_choice/response_label">
				<fo:list-item>
					<fo:list-item-label end-indent="label-end()">
						<fo:block>&#x2022;</fo:block>
					</fo:list-item-label>
					<fo:list-item-body start-indent="body-start()">
						<fo:block>
							<xsl:apply-templates />
						</fo:block>
					</fo:list-item-body>
				</fo:list-item>
			</xsl:for-each>
		</fo:list-block>
	</xsl:template>

    <xsl:template name="GlossaryItemTpl">   
           <fo:block>
                <xsl:attribute name="id">
                      <xsl:value-of select="@Id"/>
                </xsl:attribute> 
                <fo:inline font-weight="bold">
                    <xsl:value-of select="GlossaryTerm"/>
                </fo:inline>            
                <fo:block start-indent="15pt">
                    <xsl:for-each select="Definition">
                    <xsl:apply-templates />   
                </xsl:for-each>
                </fo:block> 
           </fo:block> 
    </xsl:template>
</xsl:stylesheet>
