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
		<metadata>
			<identifier>
				<catalog><xsl:value-of select="Identifier/@Catalog" /></catalog>
   				<entry><xsl:value-of select="Identifier/@Entry" /></entry>
			</identifier>
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
			<metadataSchema>LOM v.1</metadataSchema>
			<language><xsl:value-of select="Language/@Language" /></language>					
		</metadata>
	</xsl:template>
	<xsl:template match="/MetaData/Technical">
	<technical>
		<format><xsl:value-of select="Format"></xsl:value-of></format>
		<size><xsl:value-of select="Size"></xsl:value-of></size>
		<xsl:for-each select="Location">
			<location><xsl:value-of select="."></xsl:value-of></location>
		</xsl:for-each>
		<xsl:for-each select="Requirement">
			<requirement>
				<xsl:for-each select="Type/*">	
					<orComposite>
						<type>
							<source>LOMv1.0</source>
							<value>
								<xsl:choose>
									<xsl:when test="name()='OperatingSystem'">operating system</xsl:when>
									<xsl:when test="name()='Browser'">browser</xsl:when>
								</xsl:choose>
							</value>
						</type>
						<name>
							<source>LOMv1.0</source>
							<value>
								<xsl:choose>
									<xsl:when test="@Name='PC-DOS'">pc-dos</xsl:when>
									<xsl:when test="@Name='MS-Windows'">ms-windows</xsl:when>
									<xsl:when test="@Name='MacOS'">macos</xsl:when>
									<xsl:when test="@Name='Unix'">unix</xsl:when>
									<xsl:when test="@Name='Multi-OS'">multi-os</xsl:when>
									<xsl:when test="@Name='None'">none</xsl:when>
									<xsl:when test="@Name='Any'">any</xsl:when>
									<xsl:when test="@Name='NetscapeCommunicator'">netscape commincator</xsl:when>
									<xsl:when test="@Name='MS-InternetExplorer'">ms-internet explorer</xsl:when>
									<xsl:when test="@Name='Opera'">opera</xsl:when>
									<xsl:when test="@Name='Amaya'">amaya</xsl:when>
								</xsl:choose>
							</value>
						</name>
						<minimumVersion><xsl:value-of select="@MinimumVersion"></xsl:value-of></minimumVersion>
						<maximumVersion><xsl:value-of select="@MaximumVersion"></xsl:value-of></maximumVersion>
					</orComposite>
				</xsl:for-each>
			</requirement>
		</xsl:for-each>
		<xsl:if test="InstallationRemarks">
			<installationRemarks>
				<string>
					<xsl:if test="InstallationRemarks/@Language" >
						<xsl:attribute name="language">
							<xsl:value-of select="InstallationRemarks/@Language"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:value-of select="InstallationRemarks" />
				</string>
			</installationRemarks>
		</xsl:if>
		<xsl:if test="OtherPlatformRequirements">
			<otherPlatformRequirements>
				<string>
					<xsl:if test="OtherPlatformRequirements/@Language" >
						<xsl:attribute name="language">
							<xsl:value-of select="OtherPlatformRequirements/@Language"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:value-of select="OtherPlatformRequirements" />
				</string>
			</otherPlatformRequirements>
		</xsl:if>
		<xsl:if test="Duration">
			<duration>
				<duration><xsl:value-of select="Duration"></xsl:value-of></duration>
			</duration>
		</xsl:if>
		</technical>
	</xsl:template>
	<xsl:template match="/MetaData/Educational">
		<educational>
			<interactivityType>
				<source>LOMv1.0</source>
				<value><xsl:value-of select="@InteractivityType"></xsl:value-of></value>
			</interactivityType>
			<learningResourceType>
				<source>LOMv1.0</source>
				<value><xsl:value-of select="@LearningResourceType"></xsl:value-of></value>
			</learningResourceType>
			<interactivityLevel>
				<source>LOMv1.0</source>
				<value><xsl:value-of select="@InteractivityLevel"></xsl:value-of></value>
			</interactivityLevel>
			<semanticDensity>
				<source>LOMv1.0</source>
				<value><xsl:value-of select="@SemanticDensity"></xsl:value-of></value>
			</semanticDensity>
			<intendedEndUserRole>
				<source>LOMv1.0</source>
				<value><xsl:value-of select="@IntendedEndUserRole"></xsl:value-of></value>
			</intendedEndUserRole>
			<context>
				<source>LOMv1.0</source>
				<value><xsl:value-of select="@Context"></xsl:value-of></value>
			</context>
			<difficulty>
				<source>LOMv1.0</source>
				<value><xsl:value-of select="@Difficulty"></xsl:value-of></value>
			</difficulty>
			<xsl:for-each select="TypicalAgeRange">
				<typicalAgeRange>
					<xsl:if test="@Language" >
						<xsl:attribute name="language">
							<xsl:value-of select="@Language"/>
						</xsl:attribute>
					</xsl:if>
					<string><xsl:value-of select="."></xsl:value-of></string>
				</typicalAgeRange>
			</xsl:for-each>	
			<xsl:for-each select="TypicalLearningTime">
				<typicalLearningTime>
					<duration><xsl:value-of select="."></xsl:value-of></duration>
				</typicalLearningTime>
			</xsl:for-each>
			<xsl:for-each select="Description">
				<description>
					<xsl:if test="@Language" >
						<xsl:attribute name="language">
							<xsl:value-of select="@Language"/>
						</xsl:attribute>
					</xsl:if>
					<string><xsl:value-of select="."></xsl:value-of></string>
				</description>
			</xsl:for-each>
			<xsl:for-each select="Language">
				<language>
					<xsl:value-of select="@Language"></xsl:value-of>
				</language>
			</xsl:for-each>
		</educational>
	</xsl:template>
	<xsl:template match="/MetaData/Rights">
		<rights>
			<xsl:if test="@Righst" >
				<cost>
					<source>LOMv1.0</source>
					<value><xsl:value-of select="@Righst"></xsl:value-of></value>
				</cost>
			</xsl:if>
			<xsl:if test="@CopyrightAndOtherRestrictions" >
				<copyrightAndOtherRestrictions>
					<source>LOMv1.0</source>
					<value><xsl:value-of select="@CopyrightAndOtherRestrictions"></xsl:value-of></value>
				</copyrightAndOtherRestrictions>
			</xsl:if>
			<xsl:for-each select="Description">
				<description>
					<xsl:if test="@Language" >
						<xsl:attribute name="language">
							<xsl:value-of select="@Language"/>
						</xsl:attribute>
					</xsl:if>
					<string><xsl:value-of select="."></xsl:value-of></string>
				</description>
			</xsl:for-each>
		</rights>
	</xsl:template>
	<xsl:template match="/MetaData/Annotation">
		<annotation>
			<xsl:if test="Entity">
				<entity><xsl:value-of select="Entity"></xsl:value-of></entity>
			</xsl:if>
			<xsl:if test="Date">
				<date>
					<dateTime><xsl:value-of select="Date"></xsl:value-of></dateTime>
				</date>	
			</xsl:if>
			<xsl:for-each select="Description">
				<description>
					<xsl:if test="@Language" >
						<xsl:attribute name="language">
							<xsl:value-of select="@Language"/>
						</xsl:attribute>
					</xsl:if>
					<string><xsl:value-of select="."></xsl:value-of></string>
				</description>
			</xsl:for-each>
		</annotation>
	</xsl:template>
	<xsl:template match="/MetaData/Classification">
		<classification>
			<purpose>
				<source>LOMv1.0</source>
				<value><xsl:value-of select="@Purpose"></xsl:value-of></value>
			</purpose>
			<xsl:for-each select="TaxonPath">
				<taxonPath>
					<xsl:if test="Source" >
						<xsl:if test="Source/@Language" >
							<xsl:attribute name="language">
								<xsl:value-of select="Source/@Language"/>
							</xsl:attribute>
						</xsl:if>
						<string><xsl:value-of select="."></xsl:value-of></string>	
					</xsl:if>
					<xsl:for-each select="Taxon">
						<id><xsl:value-of select="@Id"></xsl:value-of></id>
						<entry>
							<xsl:if test="@Language" >
								<xsl:attribute name="language">
									<xsl:value-of select="@Language"/>
								</xsl:attribute>
							</xsl:if>
							<string><xsl:value-of select="."></xsl:value-of></string>
						</entry>
					</xsl:for-each>		
				</taxonPath>
			</xsl:for-each>
			<xsl:for-each select="Description">
				<description>
					<xsl:if test="@Language" >
						<xsl:attribute name="language">
							<xsl:value-of select="@Language"/>
						</xsl:attribute>
					</xsl:if>
					<string><xsl:value-of select="."></xsl:value-of></string>
				</description>
			</xsl:for-each>
			<xsl:for-each select="Keyword">
				<keyword>
					<xsl:if test="@Language" >
						<xsl:attribute name="language">
							<xsl:value-of select="@Language"/>
						</xsl:attribute>
					</xsl:if>
					<string><xsl:value-of select="."></xsl:value-of></string>
				</keyword>
			</xsl:for-each>
		</classification>
	</xsl:template>
	<xsl:template match="/MetaData/Relation">
		<relation>
			<xsl:if test="@Kind" >
				<kind>
					<source>LOMv1.0</source>
					<value><xsl:value-of select="@Kind"></xsl:value-of></value>
				</kind>
			</xsl:if>
			<xsl:for-each select="Resourse">
				<resourse>
					<identifier>
						<catalog><xsl:value-of select="Identifier_/@Catalog" /></catalog>
		   				<entry><xsl:value-of select="Identifier_/@Entry" /></entry>
					</identifier>
					<xsl:for-each select="Description">
						<description>
							<xsl:if test="@Language" >
								<xsl:attribute name="language">
									<xsl:value-of select="@Language"/>
								</xsl:attribute>
							</xsl:if>
							<string><xsl:value-of select="."></xsl:value-of></string>
						</description>
					</xsl:for-each>
				</resourse>
			</xsl:for-each>
		</relation>
	</xsl:template>
</xsl:stylesheet>
