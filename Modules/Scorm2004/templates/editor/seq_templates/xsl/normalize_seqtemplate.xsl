<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns="http://www.ilias.de/scorm/scorm13"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
	<xsl:output method="xml" indent="yes" encoding="UTF-8" media-type="text/xml"/>
	<xsl:key name="id" match="//*[@identifier]" use="@identifier"/>
	<xsl:template match="/">
		<xsl:apply-templates select="*[local-name()='seqTemplate']"/>
	</xsl:template>
	<xsl:template match="*[local-name()='seqTemplate']">
		<seqTemplate id="{@identifier}">
		<xsl:apply-templates select="*[local-name()='item']"/>
		<xsl:apply-templates select="//*[local-name()='sequencing' and node()]" mode="data"/>
		</seqTemplate>
	</xsl:template>
	<xsl:template match="*[local-name()='item']">
		<item title="{*[local-name()='title']/text()}" type="{@type}" nocopy="{@nocopy}" nodelete="{@nodelete}" nomove="{@nomove}">
			<xsl:apply-templates select="*[local-name()='sequencing']" mode="reference"/>
			<xsl:apply-templates select="*[local-name()='item']"/>
		</item>
	</xsl:template>
	<xsl:template name="base">
		<xsl:if test="@xml:base">
			<xsl:attribute name="base">
				<xsl:value-of select="@xml:base"/>
			</xsl:attribute>
		</xsl:if>
	</xsl:template>
	<xsl:template match="*[local-name()='sequencing']" mode="reference">
		<xsl:choose>
			<xsl:when test="@IDRef and node() and @ID">
				<xsl:attribute name="sequencingId">
					<xsl:value-of select="@ID"/>
				</xsl:attribute>
			</xsl:when>
			<xsl:when test="@IDRef and node()">
				<xsl:attribute name="sequencingId">
					<xsl:value-of select="generate-id()"/>
				</xsl:attribute>
			</xsl:when>
			<xsl:when test="@IDRef">
				<xsl:attribute name="sequencingId">
					<xsl:value-of select="@IDRef"/>
				</xsl:attribute>
			</xsl:when>
			<xsl:when test="node()">
				<xsl:attribute name="sequencingId">
					<xsl:value-of select="generate-id()"/>
				</xsl:attribute>
			</xsl:when>
		</xsl:choose>
	</xsl:template>
	<xsl:template match="*[local-name()='sequencing']" mode="data">
		<sequencing>
			<xsl:attribute name="id">
				<xsl:choose>
					<xsl:when test="@ID">
						<xsl:value-of select="@ID"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:value-of select="generate-id()"/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:attribute>
			<xsl:if test="@IDRef and not(local-name(./..)='sequencingCollection') ">
				<xsl:attribute name="sequencingId">
					<xsl:value-of select="@IDRef"/>
				</xsl:attribute>
			</xsl:if>
			<!-- attributes -->
			<xsl:apply-templates select="*[local-name()='controlMode']"/>
			<xsl:apply-templates select="*[local-name()='limitConditions']"/>
			<xsl:apply-templates select="*[local-name()='randomizationControls']"/>
			<xsl:apply-templates select="*[local-name()='deliveryControls']"/>
			<xsl:apply-templates select="*[local-name()='constrainedChoiceConsiderations']"/>
			<xsl:apply-templates select="*[local-name()='rollupConsiderations']"/>
			<xsl:apply-templates select="*[local-name()='rollupRules']" mode="attributes"/>
			<!-- elements -->
			<xsl:apply-templates select="*[local-name()='sequencingRules']"/>
			<xsl:apply-templates select="*[local-name()='rollupRules']" mode="elements"/>
			<xsl:apply-templates select="*[local-name()='auxiliaryResources']"/>
			<xsl:apply-templates select="*[local-name()='objectives']"/>
		</sequencing>
	</xsl:template>
	<xsl:template match="*[local-name()='controlMode']">
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
	</xsl:template>
	<xsl:template match="*[local-name()='constrainedChoiceConsiderations']">
		<xsl:if test="@preventActivation and not(@preventActivation='false')">
			<xsl:attribute name="preventActivation">true</xsl:attribute>
		</xsl:if>
		<xsl:if test="@constrainChoice and not(@constrainChoice='false')">
			<xsl:attribute name="constrainChoice">true</xsl:attribute>
		</xsl:if>
	</xsl:template>
	<xsl:template match="*[local-name()='rollupConsiderations']">
		<xsl:if test="@requiredForSatisfied and not(@requiredForSatisfied='always')">
			<xsl:attribute name="requiredForSatisfied">
				<xsl:value-of select="@requiredForSatisfied"/>
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@requiredForNotSatisfied and not(@requiredForNotSatisfied='always')">
			<xsl:attribute name="requiredForNotSatisfied">
				<xsl:value-of select="@requiredForNotSatisfied"/>
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@requiredForCompleted and not(@requiredForCompleted='always')">
			<xsl:attribute name="requiredForCompleted">
				<xsl:value-of select="@requiredForCompleted"/>
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@requiredForIncomplete and not(@requiredForIncomplete='always')">
			<xsl:attribute name="requiredForIncomplete">
				<xsl:value-of select="@requiredForIncomplete"/>
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@measureSatisfactionIfActive and not(@measureSatisfactionIfActive='true')">
			<xsl:attribute name="measureSatisfactionIfActive">false</xsl:attribute>
		</xsl:if>
	</xsl:template>
	<xsl:template match="*[local-name()='limitConditions']">
		<xsl:if test="@attemptLimit">
			<xsl:attribute name="attemptLimit">
				<xsl:value-of select="@attemptLimit"/>
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@attemptAbsoluteDurationLimit">
			<xsl:attribute name="attemptAbsoluteDurationLimit">
				<xsl:value-of select="@attemptAbsoluteDurationLimit"/>
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@attemptExperiencedDurationLimit">
			<xsl:attribute name="attemptExperiencedDurationLimit">
				<xsl:value-of select="@attemptExperiencedDurationLimit"/>
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@activityAbsoluteDurationLimit">
			<xsl:attribute name="activityAbsoluteDurationLimit">
				<xsl:value-of select="@activityAbsoluteDurationLimit"/>
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@activityExperiencedDurationLimit">
			<xsl:attribute name="activityExperiencedDurationLimit">
				<xsl:value-of select="@activityExperiencedDurationLimit"/>
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@beginTimeLimit">
			<xsl:attribute name="beginTimeLimit">
				<xsl:value-of select="@beginTimeLimit"/>
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@endTimeLimit">
			<xsl:attribute name="endTimeLimit">
				<xsl:value-of select="@endTimeLimit"/>
			</xsl:attribute>
		</xsl:if>
	</xsl:template>
	<xsl:template match="*[local-name()='randomizationControls']">
		<xsl:if test="@randomizationTiming and not(@randomizationTiming='never')">
			<xsl:attribute name="randomizationTiming">
				<xsl:value-of select="@randomizationTiming"/>
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@selectCount">
			<xsl:attribute name="selectCount">
				<xsl:value-of select="@selectCount"/>
			</xsl:attribute>
		</xsl:if>
		<xsl:if test="@reorderChildren and not(@reorderChildren='false')">
			<xsl:attribute name="reorderChildren">true</xsl:attribute>
		</xsl:if>
		<xsl:if test="@selectionTiming and not(@selectionTiming='never')">
			<xsl:attribute name="selectionTiming">
				<xsl:value-of select="@selectionTiming"/>
			</xsl:attribute>
		</xsl:if>
	</xsl:template>
	<xsl:template match="*[local-name()='deliveryControls']">
		<xsl:if test="@tracked and not(@tracked='true')">
			<xsl:attribute name="tracked">false</xsl:attribute>
		</xsl:if>
		<xsl:if test="@completionSetByContent and not(@completionSetByContent='false')">
			<xsl:attribute name="completionSetByContent">true</xsl:attribute>
		</xsl:if>
		<xsl:if test="@objectiveSetByContent and not(@objectiveSetByContent='false')">
			<xsl:attribute name="objectiveSetByContent">true</xsl:attribute>
		</xsl:if>
	</xsl:template>
	<!-- elements -->
	<xsl:template match="*[local-name()='sequencingRules']">
		<xsl:apply-templates select="*[local-name()='preConditionRule']"/>
		<xsl:apply-templates select="*[local-name()='exitConditionRule']"/>
		<xsl:apply-templates select="*[local-name()='postConditionRule']"/>
	</xsl:template>
	<xsl:template match="*[local-name()='preConditionRule']">
		<rule type="pre">
			<xsl:apply-templates select="*[local-name()='ruleConditions']" mode="attributes"/>
			<xsl:apply-templates select="*[local-name()='ruleAction']"/>
			<xsl:apply-templates select="*[local-name()='ruleConditions']" mode="elements"/>
		</rule>
	</xsl:template>
	<xsl:template match="*[local-name()='exitConditionRule']">
		<rule type="exit">
			<xsl:apply-templates select="*[local-name()='ruleConditions']" mode="attributes"/>
			<xsl:apply-templates select="*[local-name()='ruleAction']"/>
			<xsl:apply-templates select="*[local-name()='ruleConditions']" mode="elements"/>
		</rule>
	</xsl:template>
	<xsl:template match="*[local-name()='postConditionRule']">
		<rule type="post">
			<xsl:apply-templates select="*[local-name()='ruleConditions']" mode="attributes"/>
			<xsl:apply-templates select="*[local-name()='ruleAction']"/>
			<xsl:apply-templates select="*[local-name()='ruleConditions']" mode="elements"/>
		</rule>
	</xsl:template>
	<xsl:template match="*[local-name()='ruleAction']">
		<xsl:attribute name="action">
			<xsl:value-of select="@action"/>
		</xsl:attribute>
	</xsl:template>
	<xsl:template match="*[local-name()='ruleConditions']" mode="attributes">
		<xsl:if test="@conditionCombination and not(@conditionCombination='all')">
			<xsl:attribute name="conditionCombination">
				<xsl:value-of select="@conditionCombination"/>
			</xsl:attribute>
		</xsl:if>
	</xsl:template>
	<xsl:template match="*[local-name()='ruleConditions']" mode="elements">
		<xsl:apply-templates select="*[local-name()='ruleCondition']"/>
	</xsl:template>
	<xsl:template match="*[local-name()='ruleCondition']">
		<condition>
			<xsl:if test="@referencedObjective">
				<xsl:attribute name="referencedObjective">
					<xsl:value-of select="@referencedObjective"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="@measureThreshold">
				<xsl:attribute name="measureThreshold">
					<xsl:value-of select="@measureThreshold"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="@operator and not(@operator='noOp')">
				<xsl:attribute name="operator">
					<xsl:value-of select="@operator"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:attribute name="condition">
				<xsl:value-of select="@condition"/>
			</xsl:attribute>
		</condition>
	</xsl:template>
	<xsl:template match="*[local-name()='rollupRules']" mode="attributes">
		<xsl:if test="@rollupObjectiveSatisfied and not(@rollupObjectiveSatisfied='true')">
			<xsl:attribute name="rollupObjectiveSatisfied">false</xsl:attribute>
		</xsl:if>
		<xsl:if test="@rollupProgressCompletion and not(@rollupProgressCompletion='true')">
			<xsl:attribute name="rollupProgressCompletion">false</xsl:attribute>
		</xsl:if>
		<xsl:if test="@objectiveMeasureWeight and not(number(@objectiveMeasureWeight)=1.0)">
			<xsl:attribute name="objectiveMeasureWeight">
				<xsl:value-of select="@objectiveMeasureWeight"/>
			</xsl:attribute>
		</xsl:if>
	</xsl:template>
	<xsl:template match="*[local-name()='rollupRules']" mode="elements">
		<xsl:apply-templates select="*[local-name()='rollupRule']"/>
	</xsl:template>
	<xsl:template match="*[local-name()='rollupRule']">
		<rule type="rollup">
			<xsl:if test="@childActivitySet and not(@childActivitySet='all')">
				<xsl:attribute name="childActivitySet">
					<xsl:value-of select="@childActivitySet"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="@minimumCount and not(number(@minimumCount)=0)">
				<xsl:attribute name="minimumCount">
					<xsl:value-of select="@minimumCount"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test="@minimumPercent and not(number(@minimumPercent)=0)">
				<xsl:attribute name="minimumPercent">
					<xsl:value-of select="@minimumPercent"/>
				</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select="*[local-name()='rollupAction']"/>
			<xsl:apply-templates select="*[local-name()='rollupConditions']" mode="attributes"/>
			<xsl:apply-templates select="*[local-name()='rollupConditions']" mode="elements"/>
		</rule>
	</xsl:template>
	<xsl:template match="*[local-name()='rollupConditions']" mode="attributes">
		<xsl:if test="@conditionCombination and not(@conditionCombination='any')">
			<xsl:attribute name="conditionCombination">
				<xsl:value-of select="@conditionCombination"/>
			</xsl:attribute>
		</xsl:if>
	</xsl:template>
	<xsl:template match="*[local-name()='rollupConditions']" mode="elements">
		<xsl:apply-templates select="*[local-name()='rollupCondition']"/>
	</xsl:template>
	<xsl:template match="*[local-name()='rollupAction']">
		<xsl:attribute name="action">
			<xsl:value-of select="@action"/>
		</xsl:attribute>
	</xsl:template>
	<xsl:template match="*[local-name()='rollupCondition']">
		<condition condition="{@condition}">
			<xsl:if test="@operator and not(@operator='noOp')">
				<xsl:attribute name="operator">
					<xsl:value-of select="@operator"/>
				</xsl:attribute>
			</xsl:if>
		</condition>
	</xsl:template>
	<xsl:template match="*[local-name()='auxiliaryResources']">
		<xsl:apply-templates select="*[local-name()='auxiliaryResource']"/>
	</xsl:template>
	<xsl:template match="*[local-name()='auxiliaryResource']">
		<auxiliaryResource>
			<xsl:attribute name="auxiliaryResourceID">
				<xsl:value-of select="@auxiliaryResourceID"/>
			</xsl:attribute>
			<xsl:attribute name="purpose">
				<xsl:value-of select="@purpose"/>
			</xsl:attribute>
		</auxiliaryResource>
	</xsl:template>
	<xsl:template match="*[local-name()='objectives']">
		<xsl:apply-templates select="*[local-name()='primaryObjective'][1]"/>
		<xsl:if test="*[local-name()='primaryObjective'][1]">
			<xsl:apply-templates select="*[local-name()='objective']"/>
		</xsl:if>
	</xsl:template>
	<xsl:template match="*[local-name()='primaryObjective']">
		<objective primary="true" objectiveID="{@objectiveID}">
			<xsl:if test="@satisfiedByMeasure and not(@satisfiedByMeasure='false')">
				<xsl:attribute name="satisfiedByMeasure">true</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select="*[local-name()='minNormalizedMeasure']" mode="attributes"/>
			<xsl:apply-templates select="*[local-name()='mapInfo']"/>
		</objective>
	</xsl:template>
	<xsl:template match="*[local-name()='objective']">
		<objective primary="false" objectiveID="{@objectiveID}">
			<xsl:if test="@satisfiedByMeasure and not(@satisfiedByMeasure='false')">
				<xsl:attribute name="satisfiedByMeasure">true</xsl:attribute>
			</xsl:if>
			<xsl:apply-templates select="*[local-name()='minNormalizedMeasure']" mode="attributes"/>
			<xsl:apply-templates select="*[local-name()='mapInfo']"/>
		</objective>
	</xsl:template>
	<xsl:template match="*[local-name()='minNormalizedMeasure']" mode="attributes">
		<xsl:if test="text()">
			<xsl:attribute name="minNormalizedMeasure">
				<xsl:value-of select="text()"/>
			</xsl:attribute>
		</xsl:if>
	</xsl:template>
	<xsl:template match="*[local-name()='mapInfo']">
		<mapInfo>
			<xsl:attribute name="targetObjectiveID">
				<xsl:value-of select="@targetObjectiveID"/>
			</xsl:attribute>
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
