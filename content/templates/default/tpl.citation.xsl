<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:output method="html"/>
  <xsl:param name="target_id" />
  
  <xsl:template match="/">
    <h1>{CITATIONS}:</h1>
    <!-- BEGIN citation_row -->
    <xsl:for-each select="//BibItem">
      <xsl:if test="contains($target_id,position())">
        {CITATION}
        <xsl:call-template name="showAuthors" />
        <xsl:call-template name="showYear" />
        <xsl:call-template name="showTitle" />
        <xsl:call-template name="showEdition" />
        <xsl:text>, </xsl:text><strong><xsl:text>{PAGES_ROW}</xsl:text></strong>
        <xsl:call-template name="showURL" />
        <xsl:call-template name="showISSN" />
        <xsl:call-template name="showISBN" />
      </xsl:if>
    </xsl:for-each>
    <br />
    <br />
    <!-- END citation_row -->
  </xsl:template>

  <xsl:template name="showAuthors">
    <xsl:for-each select="Author">
      <xsl:for-each select="FirstName">
        <xsl:text> </xsl:text>
        <xsl:value-of select="text() " />
      </xsl:for-each>
      <xsl:for-each select="MiddleName">
        <xsl:text> </xsl:text>
        <xsl:value-of select="text()" />
      </xsl:for-each>
      <xsl:for-each select="LastName">
        <xsl:text> </xsl:text>
        <xsl:value-of select="text()" />
      </xsl:for-each>
      <xsl:if test="position() != last()" >
        <xsl:text>,</xsl:text>                  
      </xsl:if>
    </xsl:for-each>              
  </xsl:template>

  <xsl:template name="showYear">
    <xsl:text> (</xsl:text><xsl:value-of select="./Year" /><xsl:text>).</xsl:text>
  </xsl:template>

  <xsl:template name="showTitle">
    <xsl:text> </xsl:text><i><xsl:value-of select="./Booktitle" /></i>
  </xsl:template>

  <xsl:template name="showEdition">
    <xsl:text>, </xsl:text><xsl:value-of select="./Edition" />
  </xsl:template>

  <xsl:template name="showURL">
    <xsl:if test="count(./URL) &gt; 0">
      <xsl:text>, </xsl:text>
      <a target="_blank">
        <xsl:attribute name="href"><xsl:text>http://</xsl:text>
          <xsl:value-of select="./URL" />
        </xsl:attribute>
        <xsl:value-of select="./URL" /></a>
      </xsl:if>
  </xsl:template>

  <xsl:template name="showISSN">
    <xsl:if test="count(./ISSN) &gt; 0">
      <xsl:text> ISSN: </xsl:text><xsl:value-of select="./ISSN" />
    </xsl:if>
  </xsl:template>

  <xsl:template name="showISBN">
    <xsl:if test="count(./ISBN) &gt; 0">
      <xsl:text> ISBN: </xsl:text><xsl:value-of select="./ISBN" />
    </xsl:if>
  </xsl:template>
</xsl:stylesheet>
