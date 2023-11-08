<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  version="1.0">
  <xsl:output method="xml" indent="yes" />

  <!-- LearningProgress -->
  <xsl:template match="LearningProgress">
    <fo:root>
      <fo:layout-master-set>
        <fo:simple-page-master master-name="A4"
          page-height="29.7cm" 
          page-width="21cm" 
          margin-top="1cm" 
          margin-bottom="1cm"
          margin-left="1.5cm"
          margin-right="1.5cm">
          <fo:region-body
            margin-top="3cm" 
            margin-bottom="2.5cm"/>
          <fo:region-before
            extent="2cm" />
          <fo:region-after 
            extent="1.5cm" />
        </fo:simple-page-master>
      </fo:layout-master-set>
      <fo:page-sequence master-reference="A4">
        <xsl:apply-templates select="./Title" />
        <xsl:apply-templates select="./Header" />
        <xsl:apply-templates select="./Footer" />
        <fo:flow flow-name="xsl-region-body">
          <xsl:apply-templates select="Info" />
          <xsl:apply-templates select="Items" />
        </fo:flow>
      </fo:page-sequence>
    </fo:root>
  </xsl:template>

  <!-- Title -->
  <xsl:template match="Title">
    <fo:title>
      <xsl:value-of select="." />
    </fo:title>
  </xsl:template>

  <!-- Header -->
  <xsl:template match="Header">
    <fo:static-content flow-name="xsl-region-before">
      <fo:block
        text-align="center"
        font-size="1.5em"
        padding="1cm">
        <xsl:value-of select="." />
      </fo:block>
    </fo:static-content>
  </xsl:template>

  <!-- Footer -->
  <xsl:template match="Footer">
    <fo:static-content flow-name="xsl-region-after">
      <fo:block
        text-align="center"
        font-size="0.75em"
        padding="1cm">
        <xsl:value-of select="." />
      </fo:block>
    </fo:static-content>
  </xsl:template>

  <!-- Info -->
  <xsl:template match="Info">
    <fo:table width="180mm" table-layout="fixed">
      <fo:table-column column-width="60mm" />
      <fo:table-column column-width="120mm" />
      <xsl:apply-templates select="InfoHeader" />
      <xsl:apply-templates select="InfoBody" />
    </fo:table>
  </xsl:template>

  <!-- InfoHeader -->
  <xsl:template match="InfoHeader">
    <fo:table-header>
      <fo:table-row>
        <fo:table-cell border-style="solid" border-width="0.5mm" padding="1mm" number-columns-spanned="2" background-color="#ccdbf2">
          <fo:block font-weight="bold">
            <xsl:value-of select="." />
          </fo:block>
        </fo:table-cell>
      </fo:table-row>
    </fo:table-header>
  </xsl:template>

  <!-- InfoBody -->
  <xsl:template match="InfoBody">
    <fo:table-body>
      <xsl:apply-templates select="InfoRow" />
    </fo:table-body>
  </xsl:template>

  <!-- InfoRow -->
  <xsl:template match="InfoRow">
    <fo:table-row>
      <xsl:apply-templates select="InfoColumn" />
    </fo:table-row>
  </xsl:template>

  <!-- InfoColumn -->
  <xsl:template match="InfoColumn">
    <xsl:if test="./@Style = 'option'">
      <fo:table-cell border-style="solid" border-width="0.5mm" padding="1mm" background-color="#e2e2e2">
        <fo:block font-weight="bold" text-align="right">
          <xsl:value-of select="." />
        </fo:block>
      </fo:table-cell>
    </xsl:if>
    <xsl:if test="./@Style = 'option_value'">
      <fo:table-cell border-style="solid" border-width="0.5mm" padding="1mm" background-color="#f7f7f7">
        <fo:block>
          <xsl:value-of select="." />
        </fo:block>
      </fo:table-cell>
    </xsl:if>
    <xsl:if test="./@Style = 'title'">
      <fo:table-cell border-style="solid" border-width="0.5mm" number-columns-spanned="2" padding="1mm" background-color="#ccdbf2">
        <fo:block 
          font-weight="bold">
          <xsl:value-of select="." />
        </fo:block>
      </fo:table-cell>
    </xsl:if>
  </xsl:template>  

  <!-- Items -->
  <xsl:template match="Items">
    <fo:block text-align="justify" padding-top="1cm">
      <fo:list-block
        provisional-label-separation="0.5cm"
        provisional-distance-between-starts="0.5cm">
        <xsl:apply-templates select="Item" />
      </fo:list-block>
    </fo:block>
  </xsl:template>

  <!-- Item -->
  <xsl:template match="Item">
      <fo:list-item 
        space-after="0.2cm">
        <fo:list-item-label end-indent="label-end()">
          <fo:block font-weight="bold">
            <xsl:number level="multiple" format="1.1" count="Item" />
          </fo:block>
        </fo:list-item-label>
        <fo:list-item-body start-indent="body-start()">
          <xsl:apply-templates select="./ItemText" />
          <xsl:apply-templates select="./ItemInfo" />
          <xsl:if test="./Item">
            <fo:block
              padding-top="0.5cm">
            <fo:list-block
              provisional-label-separation="0.2cm"
              provisional-distance-between-starts="1.4cm">
              <xsl:apply-templates select="Item" />
            </fo:list-block>    
          </fo:block>
          </xsl:if>
        </fo:list-item-body>
      </fo:list-item>
  </xsl:template>

  <!-- ItemText -->
  <xsl:template match="ItemText">
    <fo:block>
      <xsl:choose>
        <xsl:when test="./@Style = 'title'">
          <fo:block font-weight="bold" font-size="1.1em" padding-bottom="0.1cm">
            <xsl:value-of select="." />
          </fo:block>            
        </xsl:when>
        <xsl:when test="./@Style = 'description'">
          <fo:block font-size="1em" font-style="italic">
            <xsl:value-of select="." />
          </fo:block>            
        </xsl:when>
      </xsl:choose>
    </fo:block>
  </xsl:template>    

  <!-- ItemInfo -->
  <xsl:template match="ItemInfo">
    <fo:block>
      <xsl:choose>
        <xsl:when test="./@Style = 'text'">
          <fo:block>
            <xsl:value-of select="." />
          </fo:block>            
        </xsl:when>
        <xsl:when test="./@Style = 'alert'">
          <fo:block color="red">
            <xsl:apply-templates select="ItemInfoName" />
            <xsl:apply-templates select="ItemInfoValue" />
          </fo:block>            
        </xsl:when>
      </xsl:choose>
    </fo:block>
  </xsl:template>

  <!-- ItemInfoName -->
  <xsl:template match="ItemInfoName">
    <xsl:value-of select="." />
  </xsl:template>

  
  <!-- ItemInfoValue -->
  <xsl:template match="ItemInfoValue">
    <xsl:value-of select="." />
  </xsl:template>

</xsl:stylesheet>
