<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:fo="http://www.w3.org/1999/XSL/Format"
  version="1.0">
  
  <!-- ContentObject -->
  <xsl:template match="ContentObject">
      <fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
        <fo:layout-master-set>
          <fo:simple-page-master master-name="LearningProgress">
            <fo:region-body/>
          </fo:simple-page-master>
        </fo:layout-master-set>
        <xsl:apply-templates select="//PageObject" />
      </fo:root>
    </xsl:template>

  <!-- PageObject -->
  <xsl:template match="PageObject">
      <fo:page-sequence master-reference="LearningProgress">
        <fo:flow flow-name="xsl-region-body">
          <fo:block>Hi</fo:block>
        </fo:flow>
      </fo:page-sequence>
    </xsl:template>
</xsl:stylesheet>
