<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:output method="html"/>
  <xsl:param name="mode"/>
  <xsl:param name="action"/>
  <xsl:param name="target_id"/>
  
  <xsl:template match="/">
    <xsl:for-each select="/Bibliography/Abstract">
      {MESSAGE}
      <h1><xsl:value-of select="text()"/></h1>        
    </xsl:for-each>
    <!-- VIEW SIMPLE -->
    <xsl:if test="$mode = 'view_simple'">
      <xsl:if test="count(//Bibliography/BibItem) &gt; 0">
        <form method="post">
          <xsl:attribute name="action">
            <xsl:value-of select="$action" />
          </xsl:attribute>
          <!-- BEGIN TRANSLATION -->
          <table class="fullwidth">
            <tr class="tblheader">
              <td class="std" colspan="2">
                {TRANSLATION_HEADER}
              </td>
            </tr>
            <!-- BEGIN TRANSLATION_ROW -->
            <tr class="std">
              <td class="option_value" width="5%"><input type="checkbox" name="tr_id[]" value="{ROW_ID}" /></td>
              <td class="option_value">{ROW_TITLE}</td>
            </tr>
            <!-- END TRANSLATION_ROW -->
          </table>
          <br />
          <!-- END TRANSLATION -->
          <table class="std" width="100%">
            <tr class="tblheader">
              <td width="5%"></td>
              <td class="std">{TITLE}</td>
              <td class="std">{EDITION}</td>
              <td class="std">{AUTHORS}</td>
            </tr>
            <xsl:for-each select="/Bibliography/BibItem">
              <tr>
                <td class="option_value">
                  <input type="checkbox" name="target[]">
                    <xsl:attribute name="value">
                      <xsl:value-of select="position()" />
                    </xsl:attribute>
                    <xsl:if test="position() = 1">
                      <xsl:attribute name="checked">
                        <xsl:text>checked</xsl:text>
                      </xsl:attribute>
                    </xsl:if>
                  </input>
                </td>
                <td class="option_value">
                  <xsl:call-template name="book_title" />
                </td>                
                <td class="option_value">
                  <xsl:call-template name="edition" />
                </td>                
                <td class="option_value">
                  <xsl:call-template name="authors" />
                </td>                
              </tr>
            </xsl:for-each>
            <tr class="tblfooter">
              <td colspan="4" align="left">
                <select name="action" class="ilEditSelect">
                  <option value="details">{DETAILS}</option>
                  <option value="show">{SHOW}</option>
                  <option value="show_citation">{SHOW_CITATION}</option>
                </select>
                <input class="ilEditSubmit" type="submit" value="Go"></input>
              </td>
            </tr>              
          </table>
        </form>
      </xsl:if>      
    </xsl:if>
    <!-- VIEW FULL -->
    <xsl:if test="$mode = 'view_full'">
      <table class="std" width="75%">
        <xsl:for-each select="Bibliography/BibItem">
          <xsl:if test="position() = $target_id">
            <xsl:if test="count(Author) &gt; 0">
              <tr>
                <td class="option" colspan="2" align="left"><div align="left">{AUTHORS}</div></td>
                <td class="option_value"><xsl:call-template name="authors" /></td>
              </tr>
            </xsl:if>
            <xsl:if test="count(Booktitle) &gt; 0">
              <tr>
                <td align="left" class="option" colspan="2"><div align="left">{BOOKTITLE}</div></td>
                <td class="option_value"><xsl:call-template name="book_title" /></td>
              </tr>
            </xsl:if>
            <xsl:if test="count(CrossRef) &gt; 0">
              <tr>
                <td class="option" colspan="2" align="left"><div align="left">{CROSS_REFERENCE}</div></td>
                <td class="option_value"><xsl:call-template name="cross_reference" /></td>
              </tr>                
            </xsl:if>
            <xsl:if test="count(Edition) &gt; 0">
              <tr>
                <td class="option" colspan="2" align="left"><div align="left">{EDITION}</div></td>
                <td class="option_value"><xsl:call-template name="edition" /></td>
              </tr>                
            </xsl:if>
            <xsl:if test="count(Editor) &gt; 0">
              <tr>
                <td class="option" colspan="2" align="left"><div align="left">{EDITOR}</div></td>
                <td class="option_value"><xsl:call-template name="editor" /></td>
              </tr>                
            </xsl:if>
            <xsl:if test="count(HowPublished) &gt; 0">
              <tr>
                <td class="option" colspan="2" align="left"><div align="left">{HOW_PUBLISHED}</div></td>
                <td class="option_value"><xsl:call-template name="how_published" /></td>
              </tr>                
            </xsl:if>
            <xsl:if test="count(WherePublished) &gt; 0">
              <tr>
                <td class="option" colspan="2" align="left"><div align="left">{WHERE_PUBLISHED}</div></td>
                <td class="option_value"><xsl:call-template name="where_published" /></td>
              </tr>                
            </xsl:if>
            <xsl:if test="count(Institution) &gt; 0">
              <tr>
                <td class="option" colspan="2" align="left"><div align="left">{INSTITUTION}</div></td>
                <td class="option_value"><xsl:call-template name="institution" /></td>
              </tr>                
            </xsl:if>
            <xsl:if test="count(Journal) &gt; 0">
              <tr>
                <td class="option" colspan="2" align="left"><div align="left">{JOURNAL}</div></td>
                <td class="option_value"><xsl:call-template name="journal_value" /></td>
              </tr>
              <xsl:for-each select="Journal/attribute::*">
                <tr>
                  <td class="option">
                    <xsl:text> </xsl:text>
                  </td>
                  <td class="sub_option"><div align="left"><xsl:value-of select="name()" /></div></td>
                  <td class="option_value"><xsl:value-of select="." /></td>
                </tr>
              </xsl:for-each>
            </xsl:if>
            <xsl:if test="count(Keyword) &gt; 0">
              <xsl:for-each select="Keyword">
                <tr>
                  <td class="option" colspan="2" align="left"><div align="left">{KEYWORD}</div></td>
                  <td class="option_value"><xsl:call-template name="keyword_value" /></td>
                </tr>
                <xsl:for-each select="attribute::*">
                  <tr>
                    <td class="option">
                      <xsl:text> </xsl:text>
                    </td>
                    <td class="sub_option"><div align="left"><xsl:value-of select="name()" /></div></td>
                    <td class="option_value"><xsl:value-of select="." /></td>
                  </tr>
                </xsl:for-each>
              </xsl:for-each>
            </xsl:if>
            <xsl:if test="count(Pages) &gt; 0">
              <tr>
                <td class="option" colspan="2" align="left"><div align="left">{PAGES}</div></td>
                <td class="option_value"><xsl:call-template name="pages" /></td>
              </tr>                
            </xsl:if>
            <xsl:if test="count(School) &gt; 0">
              <tr>
                <td class="option" colspan="2" align="left"><div align="left">{SCHOOL}</div></td>
                <td class="option_value"><xsl:call-template name="school" /></td>
              </tr>                
            </xsl:if>
            <xsl:if test="count(Month) &gt; 0">
              <tr>
                <td class="option" colspan="2" align="left"><div align="left">{MONTH}</div></td>
                <td class="option_value"><xsl:call-template name="month" /></td>
              </tr>                
            </xsl:if>
            <xsl:if test="count(Publisher) &gt; 0">
              <tr>
                <td class="option" colspan="2" align="left"><div align="left">{PUBLISHER}</div></td>
                <td class="option_value"><xsl:call-template name="publisher" /></td>
              </tr>
            </xsl:if>
            <xsl:if test="count(Series) &gt; 0">
              <tr>
                <td class="option" colspan="2"><div align="left">{SERIES}</div></td>
                <td class="option_value"><xsl:text> </xsl:text></td>
              </tr>
              <xsl:if test="count(Series/SeriesTitle) &gt; 0">
                <tr>
                  <td class="option">
                    <xsl:text> </xsl:text>
                  </td>
                  <td class="sub_option"><div align="left">{SERIES_TITLE}</div></td>
                  <td class="option_value"><xsl:value-of select="Series/SeriesTitle" /></td>
                </tr>                
              </xsl:if>              
              <xsl:if test="count(Series/SeriesEditor) &gt; 0">
                <tr>
                  <td class="option">
                    <xsl:text> </xsl:text>
                  </td>
                  <td class="sub_option"><div align="left">{SERIES_EDITOR}</div></td>
                  <td class="option_value"><xsl:call-template name="series_editor" /></td>
                </tr>                
              </xsl:if>              
              <xsl:if test="count(Series/SeriesVolume) &gt; 0">
                <tr>
                  <td class="option">
                    <xsl:text> </xsl:text>
                  </td>
                  <td class="sub_option"><div align="left">{SERIES_VOLUME}</div></td>
                  <td class="option_value"><xsl:call-template name="series_volume" /></td>
                </tr>                
              </xsl:if>
            </xsl:if>
            <xsl:if test="count(Year) &gt; 0">
              <tr>
                <td class="option" colspan="2" align="left"><div align="left">{YEAR}</div></td>
                <td class="option_value"><xsl:call-template name="year" /></td>
              </tr>                
            </xsl:if>
            <xsl:if test="count(ISBN) &gt; 0">
              <tr>
                <td class="option" colspan="2" align="left"><div align="left">{ISBN}</div></td>
                <td class="option_value"><xsl:call-template name="isbn" /></td>
              </tr>                
            </xsl:if>
            <xsl:if test="count(ISSN) &gt; 0">
              <tr>
                <td class="option" colspan="2" align="left"><div align="left">{ISSN}</div></td>
                <td class="option_value"><xsl:call-template name="month" /></td>
              </tr>                
            </xsl:if>
            <xsl:if test="count(URL) &gt; 0">
              <tr>
                <td class="option" colspan="2" align="left"><div align="left">{URL}</div></td>
                <td class="option_value"><xsl:call-template name="url" /></td>
              </tr>                
            </xsl:if>
          </xsl:if>
        </xsl:for-each>          
      </table>              
    </xsl:if>
  </xsl:template>


  <xsl:template name="book_title">
    <xsl:value-of select="Booktitle" />
  </xsl:template>

  <xsl:template name="authors">
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

  <xsl:template name="editor">
    <xsl:for-each select="Editor">
      <xsl:value-of select="text()" />
      <xsl:if test="position() != last()" >
        <xsl:text>,</xsl:text>
      </xsl:if>
    </xsl:for-each>
  </xsl:template>

  <xsl:template name="edition">
    <xsl:value-of select="Edition" />
  </xsl:template>

  <xsl:template name="cross_reference">
    <xsl:for-each select="CrossRef">
      <xsl:value-of select="text()" />
      <xsl:if test="position() != last()" >
        <xsl:text>,</xsl:text>
      </xsl:if>
    </xsl:for-each>
  </xsl:template>

  <xsl:template name="how_published">
    <xsl:value-of select="@Type" />
  </xsl:template>

  <xsl:template name="where_published">
    <xsl:for-each select="WherePublished">
      <xsl:value-of select="text()" />
      <xsl:if test="position() != last()" >
        <xsl:text>,</xsl:text>
      </xsl:if>
    </xsl:for-each>
  </xsl:template>

  <xsl:template name="institution">
    <xsl:for-each select="Institution">
      <xsl:value-of select="text()" />
      <xsl:if test="position() != last()" >
        <xsl:text>,</xsl:text>
      </xsl:if>
    </xsl:for-each>
  </xsl:template>

  <xsl:template name="journal_value">
    <xsl:value-of select="Journal" />
  </xsl:template>

  <xsl:template name="keyword_value">
    <xsl:value-of select="text()" />
  </xsl:template>

  <xsl:template name="month">
    <xsl:value-of select="Month" />
  </xsl:template>

  <xsl:template name="pages">
    <xsl:value-of select="Pages" />
  </xsl:template>

  <xsl:template name="school">
    <xsl:for-each select="School">
      <xsl:value-of select="text()" />
      <xsl:if test="position() != last()" >
        <xsl:text>,</xsl:text>
      </xsl:if>      
    </xsl:for-each>
  </xsl:template>

  <xsl:template name="publisher">
    <xsl:value-of select="Publisher" />
  </xsl:template>

  <xsl:template name="series_editor">
    <xsl:for-each select="Series/SeriesEditor">
      <xsl:value-of select="text()" />
      <xsl:if test="position() != last()" >
        <xsl:text>,</xsl:text>
      </xsl:if>      
    </xsl:for-each>
  </xsl:template>

  <xsl:template name="series_volume">
    <xsl:for-each select="Series/SeriesVolume">
      <xsl:value-of select="text()" />
      <xsl:if test="position() != last()" >
        <xsl:text>,</xsl:text>
      </xsl:if>      
    </xsl:for-each>
  </xsl:template>

  <xsl:template name="year">
    <xsl:value-of select="Year" />
  </xsl:template>

  <xsl:template name="isbn">
    <xsl:value-of select="ISBN" />
  </xsl:template>

  <xsl:template name="issn">
    <xsl:value-of select="ISSN" />
  </xsl:template>

  <xsl:template name="url">
    <xsl:value-of select="URL" />
  </xsl:template>
</xsl:stylesheet>
