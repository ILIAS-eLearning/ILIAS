<?xml version="1.0" encoding="UTF-8"?>
<!--
ILIAS Open Source
================================
Implementation of ADL SCORM 2004

This program is free software. The use and distribution terms for this software
are covered by the GNU General Public License Version 2
	<http://opensource.org/licenses/gpl-license.php>.
By using this software in any fashion, you are agreeing to be bound by the terms 
of this license.

You must not remove this notice, or any other, from this software.

@author Hendrik Holtmann
@version $Id$
@copyright: (c) 2007 Hendrik Holtmann

-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="text"  encoding="iso-8859-1" indent="yes"/>
  <xsl:template match="//table">
		<xsl:text>CREATE TABLE </xsl:text> <xsl:value-of select="@name"/><xsl:text>(</xsl:text>
		    <xsl:apply-templates select="field"/><xsl:text> );</xsl:text>
			<xsl:apply-templates select="key"><xsl:with-param  name="table" select="@name"/></xsl:apply-templates>
			<xsl:apply-templates select="index"><xsl:with-param  name="table" select="@name"/></xsl:apply-templates>
		</xsl:template>
		
		
  <xsl:template match="field">
		<xsl:text>`</xsl:text><xsl:value-of select="@name"/><xsl:text>` </xsl:text>
			<xsl:choose> 
				
				<xsl:when test="@type = 'integer'">
					<xsl:text>INTEGER</xsl:text>
				</xsl:when>
				
				<xsl:when test="@type = 'string' and @size>0">
						<xsl:text>VARCHAR(</xsl:text><xsl:value-of select="@size"/><xsl:text>)</xsl:text>
				</xsl:when>
				
				<xsl:when test="@type = 'string' and not(@size)">
						<xsl:text>TEXT</xsl:text>
				</xsl:when>
				
				<xsl:when test="@type = 'boolean'">
						<xsl:text>TINYINT</xsl:text>
				</xsl:when>
				
				<xsl:when test="@type = 'float'">
						<xsl:text>REAL</xsl:text>
				</xsl:when>
				<xsl:when test="@type = 'date'">
						<xsl:text>VARCHAR(20)</xsl:text>
				</xsl:when>				
				
				<xsl:when test="@type = 'binary'">
						<xsl:text>BLOB</xsl:text>
				</xsl:when>
			</xsl:choose>	
			<xsl:if test="@autoincrement = 'true'">
					<xsl:text> PRIMARY KEY AUTO_INCREMENT</xsl:text>
			</xsl:if>
			<xsl:if test="@default and @autoincrement != 'true'">
					<xsl:text> DEFAULT </xsl:text> <xsl:if test="@type='string' or @type='boolean'"><xsl:text>'</xsl:text></xsl:if><xsl:value-of select="@default"/><xsl:if test="@type='string' or @type='boolean'"><xsl:text>'</xsl:text></xsl:if>
			</xsl:if>
		<!--		
			<xsl:if test="@required = 'true'">
				<xsl:text> NOT NULL</xsl:text>
			</xsl:if>
		-->	
			 <xsl:if test="position()!=last()">
			    <xsl:text>, </xsl:text>
			  </xsl:if>
		
  </xsl:template>

  <xsl:template match="key">
	<xsl:if test="@type = 'primary' and  not(../field/@autoincrement) "> 
		<xsl:text>
	ALTER TABLE </xsl:text><xsl:value-of select="$table"/> <xsl:text> ADD PRIMARY KEY(</xsl:text><xsl:value-of select="reference/@name"/><xsl:text>)</xsl:text>;
	</xsl:if>
  </xsl:template>

<xsl:template match="index">
	<xsl:text>
	CREATE INDEX </xsl:text><xsl:value-of select="@name"/> <xsl:text> ON </xsl:text><xsl:value-of select="$table"/> <xsl:text>(</xsl:text> <xsl:value-of select="reference/@name"/> <xsl:text>)</xsl:text>;
</xsl:template>

</xsl:stylesheet>
