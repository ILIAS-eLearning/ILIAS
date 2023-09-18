<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  version="1.0">
  
  <!-- ContentObject -->
  <xsl:template match="ContentObject">
      <fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
        <fo:layout-master-set>
          <fo:simple-page-master master-name="LearningProgress">
            <fo:region-body margin="10mm" />
          </fo:simple-page-master>
        </fo:layout-master-set>
        <xsl:apply-templates />
      </fo:root>
    </xsl:template>

  <!-- PageObject -->
  <xsl:template match="PageObject">
    <fo:page-sequence master-reference="LearningProgress">
      <fo:flow flow-name="xsl-region-body">
        <xsl:apply-templates />
      </fo:flow>
    </fo:page-sequence>
  </xsl:template>

  <!-- PageContent -->
  <xsl:template match="PageContent">
    <xsl:apply-templates />
  </xsl:template>
  
  <!-- Paragraph -->
  <xsl:template match="Paragraph">
    <xsl:choose>
      <xsl:when test="@Characteristic = 'Headline1'">
        <fo:block 
          font-size="1.5em"
          font-family="any"
          space-before="5mm"
          space-after="5mm">
          <xsl:apply-templates />
        </fo:block>
      </xsl:when>
      <xsl:when test="@Characteristic = 'TableContent'">
        <fo:block 
          font-size="0.9em"
          font-family="any">
          <xsl:apply-templates />
        </fo:block>
      </xsl:when>
      <xsl:otherwise>
        <fo:block 
          font-size="1.1em"
          font-family="any">
          <xsl:apply-templates />
        </fo:block>          
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <!-- Table -->
  <xsl:template match="Table">
    <fo:table>
      <fo:table-column column-width="60mm" />
      <fo:table-column column-width="120mm" />
      <fo:table-body>
        <xsl:apply-templates />
      </fo:table-body>
    </fo:table>
  </xsl:template>

  <!-- TableRow -->
  <xsl:template match="TableRow">
    <fo:table-row>
      <xsl:apply-templates />
    </fo:table-row>
  </xsl:template>
</xsl:stylesheet>
