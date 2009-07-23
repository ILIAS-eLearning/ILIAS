<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://ltsc.ieee.org/xsd/LOM" >
	<xsl:output method="xml"/>

	<!-- strip white spaces between tags -->
	<xsl:strip-space elements="*"/>

	<xsl:template match="/">
		<lom xmlns="http://ltsc.ieee.org/xsd/LOM">
			<xsl:apply-templates/>
		</lom>
	</xsl:template>
	
	<xsl:template match="/MetaData/General">
		<general>
			<identifier>
		   		<catalog><xsl:value-of select="Identifier/@Catalog" /></catalog>
   				<entry><xsl:value-of select="Identifier/@Entry" /></entry>
			</identifier>
			<title>
				<string>
					<xsl:if test="Title/@Language" >
						<xsl:attribute name="language">
							<xsl:value-of select="Title/@Language"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:value-of select="Title" />
				</string>
			</title>
			<language><xsl:value-of select="Language/@Language" /></language>
			<description>
				<string>
					<xsl:if test="Description/@Language" >
						<xsl:attribute name="language">
							<xsl:value-of select="Description/@Language"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:value-of select="Description" />
				</string>
			</description>
			<keyword>
				<string>
					<xsl:if test="Keyword/@Language" >
						<xsl:attribute name="language">
							<xsl:value-of select="Keyword/@Language"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:value-of select="Keyword" />
				</string>
			</keyword>
			<xsl:if test="Coverage" >
			<coverage>
				<string>
					<xsl:if test="Coverage/@Language" >
						<xsl:attribute name="language">
							<xsl:value-of select="Coverage/@Language"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:value-of select="Coverage" />
				</string>
			</coverage>
			</xsl:if>
		</general>
	</xsl:template>
	<xsl:template match="/MetaData/Lifecycle">
		<lifeCycle>
			<version>
			   <string>
			   		<xsl:if test="Version/@Language" >
				   		<xsl:attribute name="language">
							<xsl:value-of select="Version/@Language"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:value-of select="Version" />
				</string>
			</version>
			<status>
			   <source>LOMv1.0</source>
			   <value>
				   <xsl:choose>
					   <xsl:when test="@Status='Draft'">draft</xsl:when>
					   <xsl:when test="@Status='Final'">final</xsl:when>
					   <xsl:when test="@Status='Revised'">revised</xsl:when>
					   <xsl:when test="@Status='Unavailable'">unavailable</xsl:when>
				   </xsl:choose>
				</value>
			</status>
			<xsl:for-each select="Contribute/Entity">
				<contribute>
			    	<role>
			        	<source>LOMv1.0</source>
			            <value>
							<xsl:choose>
								<xsl:when test="../@Role='Author'">author</xsl:when>
								<xsl:when test="../@Role='Publisher'">publisher</xsl:when>
								<xsl:when test="../@Role='Unknown'">unknown</xsl:when>
								<xsl:when test="../@Role='Initiator'">initiator</xsl:when>
								<xsl:when test="../@Role='Terminator'">termintator</xsl:when>
								<xsl:when test="../@Role='Validator'">validator</xsl:when>
								<xsl:when test="../@Role='Editor'">editor</xsl:when>
								<xsl:when test="../@Role='GraphicalDesigner'">graphical designer</xsl:when>
								<xsl:when test="../@Role='TechnicalImplementer'">technical implementer</xsl:when>
								<xsl:when test="../@Role='ContentProvider'">content provider</xsl:when>
								<xsl:when test="../@Role='TechnicalValidator'">technical validator</xsl:when>
								<xsl:when test="../@Role='EducationalValidator'">educational validator</xsl:when>
								<xsl:when test="../@Role='ScriptWriter'">script writer</xsl:when>
								<xsl:when test="../@Role='InstructionalDesigner'">instructional designer</xsl:when>
								<xsl:when test="../@Role='SubjectMatterExpert'">subject matter expert</xsl:when>
							</xsl:choose>
						</value>
			        </role>
			        <entity><xsl:value-of select="."/></entity>
			        <xsl:if test="../Date!=''">
				        <date>
				        	<dateTime><xsl:value-of select="../Date"/></dateTime>
				        </date>
			        </xsl:if>
			      </contribute>
			</xsl:for-each>
		</lifeCycle>
	</xsl:template>
	<xsl:template match="/MetaData/Meta-Metadata">
	</xsl:template>
	<xsl:template match="/MetaData/Technical">
	</xsl:template>
	<xsl:template match="/MetaData/Educational">
	</xsl:template>
	<xsl:template match="/MetaData/Rights">
	</xsl:template>
	<xsl:template match="/MetaData/Annotation">
	</xsl:template>
	<xsl:template match="/MetaData/Classification">
	</xsl:template>
	<xsl:template match="/MetaData/Relation">
	</xsl:template>
</xsl:stylesheet>
