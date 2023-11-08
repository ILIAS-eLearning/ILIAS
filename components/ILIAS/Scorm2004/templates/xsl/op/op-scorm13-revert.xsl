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

PRELIMINARY EDITION 
This is work in progress and therefore incomplete and buggy ... 

TODO
add namespace prefixes

@author Alfred Kohnert <alfred.kohnert@bigfoot.com>
@version $Id$
@copyright: (c) 2007 Alfred Kohnert
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" xmlns="http://www.imsglobal.org/xsd/imscp_v1p1" xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_v1p3" xmlns:adlseq="http://www.adlnet.org/xsd/adlseq_v1p3" xmlns:adlnav="http://www.adlnet.org/xsd/adlnav_v1p3">
	<xsl:output method="xml" indent="yes" encoding="UTF-8" media-type="text/xml"/>
	<xsl:template match="/">
		<xsl:apply-templates select="*[local-name()='manifest']"/>
	</xsl:template>
	<xsl:template match="*[local-name()='manifest']">
		<manifest version="{@version}" identifier="{@id}" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.imsglobal.org/xsd/imscp_v1p1 imscp_v1p1.xsd http://www.adlnet.org/xsd/adlcp_v1p3 adlcp_v1p3.xsd http://www.adlnet.org/xsd/adlseq_v1p3 adlseq_v1p3.xsd http://www.imsglobal.org/xsd/imsss imsss_v1p0.xsd http://www.adlnet.org/xsd/adlnav_v1p3 adlnav_v1p3.xsd http://ltsc.ieee.org/xsd/LOM lom.xsd">
			<metadata>
				<schema>ADL SCORM</schema>
				<schemaversion>CAM 1.3</schemaversion>
				<lom xmlns="http://ltsc.ieee.org/xsd/LOM">
					<general>
						<identifier>
							<catalog>URI</catalog>
							<entry>
								<xsl:value-of select="@uri"/>
							</entry>
						</identifier>
					</general>
				</lom>
			</metadata>
			<organizations default="{@defaultOrganization}">
				<xsl:for-each select="*[local-name()='organization']">
					<organization identifier="{@id}">
						<xsl:if test="@structure and not(@structure='hierarchical')">
							<xsl:attribute name="structure"><xsl:value-of select="@structure"/></xsl:attribute>
						</xsl:if>
						<xsl:if test="@objectivesGlobalToSystem and not(@objectivesGlobalToSystem='true')">
							<xsl:attribute name="adlcp:objectivesGlobalToSystem">false</xsl:attribute>
						</xsl:if>
						<title>
							<xsl:value-of select="@title"/>
						</title>
						<xsl:apply-templates select="*[local-name()='item']"/>
						<xsl:if test="@sequencingId">
							<sequencing IDRef="{@sequencingId}" xmlns="http://www.imsglobal.org/xsd/imsss"/>
						</xsl:if>
					</organization>
				</xsl:for-each>
			</organizations>
			<resources>
				<xsl:call-template name="base"/>
				<xsl:for-each select="*[local-name()='resource']">
					<resource identifier="{@id}" adlcp:scormType="{@*[local-name()='scormType']}" type="{@type}">
						<xsl:call-template name="base"/>
						<xsl:if test="@href">
							<xsl:attribute name="href"><xsl:value-of select="@href"/></xsl:attribute>
						</xsl:if>
						<xsl:for-each select="*[local-name()='file']">
							<file href="{@href}"/>
						</xsl:for-each>
						<xsl:for-each select="*[local-name()='dependency']">
							<dependency identifierref="{@resourceId}"/>
						</xsl:for-each>
					</resource>
				</xsl:for-each>
			</resources>
			<xsl:call-template name="sequencingCollection"/>
		</manifest>
	</xsl:template>
	<xsl:template match="*[local-name()='item']">
		<item identifier="{@id}">
			<xsl:if test="@resourceId">
				<xsl:attribute name="identifierref"><xsl:value-of select="@resourceId"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="@isvisible and not(@isvisible='true')">
				<xsl:attribute name="isvisible">false</xsl:attribute>
			</xsl:if>
			<xsl:if test="@parameters">
				<xsl:attribute name="parameters"><xsl:value-of select="@parameters"/></xsl:attribute>
			</xsl:if>
			<title>
				<xsl:value-of select="@title"/>
			</title>
			<xsl:if test="@timeLimitAction">
				<xsl:element name="timeLimitAction">
					<xsl:value-of select="@timeLimitAction"/>
				</xsl:element>
			</xsl:if>
			<xsl:if test="@dataFromLMS">
				<xsl:element name="dataFromLMS">
					<xsl:value-of select="@dataFromLMS"/>
				</xsl:element>
			</xsl:if>
			<xsl:if test="@completionThreshold">
				<xsl:element name="completionThreshold">
					<xsl:value-of select="@completionThreshold"/>
				</xsl:element>
			</xsl:if>
			<xsl:apply-templates select="*[local-name()='item']"/>
			<xsl:if test="@sequencingId">
				<sequencing IDRef="{@sequencingId}" xmlns="http://www.imsglobal.org/xsd/imsss"/>
			</xsl:if>
			<xsl:if test="*[local-name()='hideLMSUI']">
				<navigation xmlns="http://www.adlnet.org/xsd/adlnav_v1p3">
					<navigationInterface>
						<xsl:for-each select="*[local-name()='hideLMSUI']">
							<hideLMSUI>
								<xsl:value-of select="."/>
							</hideLMSUI>
						</xsl:for-each>
					</navigationInterface>
				</navigation>
			</xsl:if>
		</item>
	</xsl:template>
	<!-- -->
	<xsl:template name="base">
		<xsl:if test="@base">
			<xsl:attribute name="xml:base"><xsl:value-of select="@base"/></xsl:attribute>
		</xsl:if>
	</xsl:template>
	<!-- -->
	<xsl:template name="sequencingCollection" xmlns="http://www.imsglobal.org/xsd/imsss">
		<sequencingCollection>
			<xsl:for-each select="*[local-name()='sequencing']">
				<sequencing ID="{@id}">
					<controlMode>
						<xsl:if test="@choice='false'">
							<xsl:attribute name="choice">false</xsl:attribute>
						</xsl:if>
						<xsl:if test="@choiceExit='false'">
							<xsl:attribute name="choiceExit">false</xsl:attribute>
						</xsl:if>
						<xsl:if test="@flow='true'">
							<xsl:attribute name="flow">true</xsl:attribute>
						</xsl:if>
						<xsl:if test="@forwardOnly='false'">
							<xsl:attribute name="forwardOnly">false</xsl:attribute>
						</xsl:if>
						<xsl:if test="@useCurrentAttemptObjectiveInfo='false'">
							<xsl:attribute name="useCurrentAttemptObjectiveInfo">false</xsl:attribute>
						</xsl:if>
						<xsl:if test="@useCurrentAttemptProgressInfo='false'">
							<xsl:attribute name="useCurrentAttemptProgressInfo">false</xsl:attribute>
						</xsl:if>
					</controlMode>
					<sequencingRules>
						<xsl:apply-templates select="*[local-name()='rule' and not(@type='rollup')]"/>
					</sequencingRules>
					<limitConditions>
						<xsl:if test="@attemptLimit">
							<xsl:attribute name="attemptLimit"><xsl:value-of select="@attemptLimit"/></xsl:attribute>
						</xsl:if>
						<xsl:if test="@attemptAbsoluteDurationLimit">
							<xsl:attribute name="attemptAbsoluteDurationLimit"><xsl:value-of select="@attemptAbsoluteDurationLimit"/></xsl:attribute>
						</xsl:if>
						<xsl:if test="@attemptExperiencedDurationLimit">
							<xsl:attribute name="attemptExperiencedDurationLimit"><xsl:value-of select="@attemptExperiencedDurationLimit"/></xsl:attribute>
						</xsl:if>
						<xsl:if test="@activityAbsoluteDurationLimit">
							<xsl:attribute name="activityAbsoluteDurationLimit"><xsl:value-of select="@activityAbsoluteDurationLimit"/></xsl:attribute>
						</xsl:if>
						<xsl:if test="@activityExperiencedDurationLimit">
							<xsl:attribute name="activityExperiencedDurationLimit"><xsl:value-of select="@activityExperiencedDurationLimit"/></xsl:attribute>
						</xsl:if>
						<xsl:if test="@beginTimeLimit">
							<xsl:attribute name="beginTimeLimit"><xsl:value-of select="@beginTimeLimit"/></xsl:attribute>
						</xsl:if>
						<xsl:if test="@endTimeLimit">
							<xsl:attribute name="endTimeLimit"><xsl:value-of select="@endTimeLimit"/></xsl:attribute>
						</xsl:if>
					</limitConditions>
					<xsl:if test="*[local-name()='auxiliaryResource']">
						<auxiliaryResources>
							<xsl:for-each select="*[local-name()='auxiliaryResource']">
								<auxiliaryResource>
									<xsl:attribute name="auxiliaryResourceID"><xsl:value-of select="@auxiliaryResourceID"/></xsl:attribute>
									<xsl:attribute name="purpose"><xsl:value-of select="@purpose"/></xsl:attribute>
								</auxiliaryResource>
							</xsl:for-each>
						</auxiliaryResources>
					</xsl:if>
					<rollupRules>
						<xsl:if test="@rollupObjectiveSatisfied and not(@rollupObjectiveSatisfied='true')">
							<xsl:attribute name="rollupObjectiveSatisfied">false</xsl:attribute>
						</xsl:if>
						<xsl:if test="@rollupProgressCompletion and not(@rollupProgressCompletion='true')">
							<xsl:attribute name="rollupProgressCompletion">false</xsl:attribute>
						</xsl:if>
						<xsl:if test="@objectiveMeasureWeight and not(number(@objectiveMeasureWeight)=1.0)">
							<xsl:attribute name="objectiveMeasureWeight"><xsl:value-of select="@objectiveMeasureWeight"/></xsl:attribute>
						</xsl:if>
						<xsl:apply-templates select="*[local-name()='rule' and @type='rollup']"/>
					</rollupRules>
					<xsl:if test="*[local-name()='objective']">
						<objectives>
							<xsl:apply-templates select="*[local-name()='objective']">
								<xsl:sort select="not(@primary='true')"/>
							</xsl:apply-templates>
						</objectives>
					</xsl:if>
					<randomizationControls>
						<xsl:if test="@randomizationTiming  and not(@randomizationTiming='never')">
							<xsl:attribute name="randomizationTiming"><xsl:value-of select="@randomizationTiming"/></xsl:attribute>
						</xsl:if>
						<xsl:if test="@selectCount">
							<xsl:attribute name="selectCount"><xsl:value-of select="@selectCount"/></xsl:attribute>
						</xsl:if>
						<xsl:if test="@reorderChildren and not(@reorderChildren='false')">
							<xsl:attribute name="reorderChildren">true</xsl:attribute>
						</xsl:if>
						<xsl:if test="@selectionTiming and not(@selectionTiming='never')">
							<xsl:attribute name="selectionTiming"><xsl:value-of select="@selectionTiming"/></xsl:attribute>
						</xsl:if>
					</randomizationControls>
					<deliveryControls>
						<xsl:if test="@tracked and not(@tracked='true')">
							<xsl:attribute name="tracked">false</xsl:attribute>
						</xsl:if>
						<xsl:if test="@completionSetByContent and not(@completionSetByContent='false')">
							<xsl:attribute name="completionSetByContent">true</xsl:attribute>
						</xsl:if>
						<xsl:if test="@objectiveSetByContent and not(@objectiveSetByContent='false')">
							<xsl:attribute name="objectiveSetByContent">true</xsl:attribute>
						</xsl:if>
					</deliveryControls>
					<xsl:if test="@preventActivation or @constrainChoice">
						<constrainedChoiceConsiderations>
							<xsl:if test="@preventActivation and not(@preventActivation='false')">
								<xsl:attribute name="preventActivation">true</xsl:attribute>
							</xsl:if>
							<xsl:if test="@constrainChoice and not(@constrainChoice='false')">
								<xsl:attribute name="constrainChoice">true</xsl:attribute>
							</xsl:if>
						</constrainedChoiceConsiderations>
					</xsl:if>
					<xsl:if test="@requiredForSatisfied or @requiredForNotSatisfied or @requiredForCompleted or @requiredForIncomplete or @measureSatisfactionIfActive ">
						<rollupConsiderations xmlns="http://www.adlnet.org/xsd/adlseq_v1p3">
							<xsl:if test="@requiredForSatisfied and not(@requiredForSatisfied='always')">
								<xsl:attribute name="requiredForSatisfied"><xsl:value-of select="@requiredForSatisfied"/></xsl:attribute>
							</xsl:if>
							<xsl:if test="@requiredForNotSatisfied and not(@requiredForNotSatisfied='always')">
								<xsl:attribute name="requiredForNotSatisfied"><xsl:value-of select="@requiredForNotSatisfied"/></xsl:attribute>
							</xsl:if>
							<xsl:if test="@requiredForCompleted and not(@requiredForCompleted='always')">
								<xsl:attribute name="requiredForCompleted"><xsl:value-of select="@requiredForCompleted"/></xsl:attribute>
							</xsl:if>
							<xsl:if test="@requiredForIncomplete and not(@requiredForIncomplete='always')">
								<xsl:attribute name="requiredForIncomplete"><xsl:value-of select="@requiredForIncomplete"/></xsl:attribute>
							</xsl:if>
							<xsl:if test="@measureSatisfactionIfActive and not(@measureSatisfactionIfActive='true')">
								<xsl:attribute name="measureSatisfactionIfActive">false</xsl:attribute>
							</xsl:if>
						</rollupConsiderations>
					</xsl:if>
				</sequencing>
			</xsl:for-each>
		</sequencingCollection>
	</xsl:template>
	<xsl:template match="*[local-name()='rule' and not(@type='rollup')]" xmlns="http://www.imsglobal.org/xsd/imsss">
		<xsl:element name="{@type}ConditionRule">
			<ruleConditions>
				<xsl:if test="@conditionCombination and not(@conditionCombination='all')">
					<xsl:attribute name="conditionCombination"><xsl:value-of select="@conditionCombination"/></xsl:attribute>
				</xsl:if>
				<xsl:for-each select="*[local-name()='condition']">
					<ruleCondition>
						<xsl:if test="@referencedObjective">
							<xsl:attribute name="referencedObjective"><xsl:value-of select="@referencedObjective"/></xsl:attribute>
						</xsl:if>
						<xsl:if test="@measureThreshold">
							<xsl:attribute name="measureThreshold"><xsl:value-of select="@measureThreshold"/></xsl:attribute>
						</xsl:if>
						<xsl:if test="@operator and not(@operator='noOp')">
							<xsl:attribute name="operator"><xsl:value-of select="@operator"/></xsl:attribute>
						</xsl:if>
						<xsl:attribute name="condition"><xsl:value-of select="@condition"/></xsl:attribute>
					</ruleCondition>
				</xsl:for-each>
			</ruleConditions>
			<ruleAction action="{@action}"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match="*[local-name()='rule' and @type='rollup']" xmlns="http://www.imsglobal.org/xsd/imsss">
		<rollupRule>
			<xsl:if test="@childActivitySet and not(@childActivitySet='all')">
				<xsl:attribute name="childActivitySet"><xsl:value-of select="@childActivitySet"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="@minimumCount and not(number(@minimumCount)=0)">
				<xsl:attribute name="minimumCount"><xsl:value-of select="@minimumCount"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="@minimumPercent and not(number(@minimumPercent)=0)">
				<xsl:attribute name="minimumPercent"><xsl:value-of select="@minimumPercent"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="*[local-name()='condition']">
				<rollupConditions>
					<xsl:if test="@conditionCombination and not(@conditionCombination='any')">
						<xsl:attribute name="conditionCombination"><xsl:value-of select="@conditionCombination"/></xsl:attribute>
					</xsl:if>
					<xsl:apply-templates select="*[local-name()='condition']" mode="rollup"/>
				</rollupConditions>
			</xsl:if>
			<rollupAction action="{@action}"/>
		</rollupRule>
	</xsl:template>
	<xsl:template match="*[local-name()='condition']" mode="rollup" xmlns="http://www.imsglobal.org/xsd/imsss">
		<rollupCondition condition="@condition">
			<xsl:if test="@operator and not(@operator='noOp')">
				<xsl:attribute name="operator"><xsl:value-of select="@operator"/></xsl:attribute>
			</xsl:if>
		</rollupCondition>
	</xsl:template>
	<xsl:template match="*[local-name()='objective']" xmlns="http://www.imsglobal.org/xsd/imsss">
		<xsl:choose>
			<xsl:when test="@primary='true'">
				<primaryObjective>
					<xsl:call-template name="objective"/>
				</primaryObjective>
			</xsl:when>
			<xsl:otherwise>
				<objective>
					<xsl:call-template name="objective"/>
				</objective>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<xsl:template name="objective" xmlns="http://www.imsglobal.org/xsd/imsss">
		<xsl:attribute name="objectiveID"><xsl:value-of select="@objectiveID"/></xsl:attribute>
		<xsl:if test="@satisfiedByMeasure and not(@satisfiedByMeasure='false')">
			<xsl:attribute name="satisfiedByMeasure">true</xsl:attribute>
		</xsl:if>
		<xsl:if test="@minNormalizedMeasure">
			<minNormalizedMeasure>
				<xsl:value-of select="@minNormalizedMeasure"/>
			</minNormalizedMeasure>
		</xsl:if>
		<xsl:apply-templates select="*[local-name()='mapInfo']"/>
	</xsl:template>
	<xsl:template match="*[local-name()='mapInfo']" xmlns="http://www.imsglobal.org/xsd/imsss">
		<mapInfo>
			<xsl:attribute name="targetObjectiveID"><xsl:value-of select="@targetObjectiveID"/></xsl:attribute>
			<xsl:if test="@readSatisfiedStatus and not(@readSatisfiedStatus='true')">
				<xsl:attribute name="readSatisfiedStatus">false</xsl:attribute>
			</xsl:if>
			<xsl:if test="@readNormalizedMeasure and not(@readNormalizedMeasure='true')">
				<xsl:attribute name="readNormalizedMeasure">false</xsl:attribute>
			</xsl:if>
			<xsl:if test="@writeSatisfiedStatus and not(@writeSatisfiedStatus='false')">
				<xsl:attribute name="writeSatisfiedStatus">true</xsl:attribute>
			</xsl:if>
			<xsl:if test="@writeNormalizedMeasure and not(@writeNormalizedMeasure='false')">
				<xsl:attribute name="writeNormalizedMeasure">true</xsl:attribute>
			</xsl:if>
		</mapInfo>
	</xsl:template>
</xsl:stylesheet>
