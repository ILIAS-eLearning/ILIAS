/**
 * ILIAS Open Source
 * --------------------------------
 * Implementation of ADL SCORM 2004
 * 
 * This program is free software. The use and distribution terms for this software
 * are covered by the GNU General Public License Version 2
 * 	<http://opensource.org/licenses/gpl-license.php>.
 * By using this software in any fashion, you are agreeing to be bound by the terms 
 * of this license.
 * 
 * You must not remove this notice, or any other, from this software.
 * 
 * PRELIMINARY EDITION
 * This is work in progress and therefore incomplete and buggy ...
 * 
 * Derived from ADL Pseudocode
 *   
 * Content-Type: application/x-javascript; charset=ISO-8859-1
 * Modul: ADL SCORM 1.3 Sequencing Implementation
 *  
 * @author Alfred Kohnert <alfred.kohnert@bigfoot.com>
 * @version $Id$
 * @copyright: (c) 2007 Alfred Kohnert
 */ 


/* ######### SEQUENCER CORE : from pseudo code ################################ */


/**
 * @param string navigationRequest "Exit", "Continue", etc.
 * @return undefined
 */
 // noch eine Baustellen, da der Pseudocode hier unklar ist
function exec(navReq) // #166 
{
	var rn, rd, rs, rt, seqReq;

	//sclogclear();
	sclog("OverallSequencing [OP.1]", "ps");
	
	rn = navigationRequest(navReq);
	
	if (!rn.valid) 
	{
		return rn;
	}
	seqReq = rn.sequencingRequest;
	
	if (rn.terminationRequest && state>0)  
	{
		rt = terminationRequest(rn.terminationRequest);
		if (!rt.valid) 
		{
			return rt;
		}
		if (rt.sequencingRequest)
		{
			seqReq = rt.sequencingRequest; 
		} 
	}
	
	if (seqReq) 
	{
		rs = sequencingRequest(seqReq, activities[navReq.target]);
		if (!rs.valid) 
		{
			return rs;
		}
		if (rs.endSequencingSession) 
		{
			return endSequencingSession();
		} 
		if (rs.deliveryRequest) 
		{
			rd = deliveryRequest(rs.deliveryRequest);
			if (!rd.valid) 
			{
				return rd;
			}
			return contentDeliveryEnvironment(rs.deliveryRequest);
		}
	} 

}

/**
 * checks whether request is valid
 * if so it returns a sequencing request name and termination request name	 	
 * @param object e.g. {type: 'Choice', target: 'Isf34fd'}
 * @return object {valid, sequencingRequest, terminationRequest}
 */
function navigationRequest(navReq) // #168
{
	var returnValue = {valid: false, sequencingRequest: null, terminationRequest: null};
	
	sclog("NavigationRequest [NB.2.1]", "ps");
	
	NAVREQ_TYPE: // for jumping back, bad style taken from ADL specs
	switch (navReq.type) 
	{
	case 'Start':
		if (!currentAct) 
		{
			returnValue.valid = true;
			returnValue.sequencingRequest= 'Start';
		}
		else
		{
			returnValue.exception = 'NB.2.1-1';
		}
		break;
	case 'ResumeAll':
		if (!currentAct)
		{
			if (suspendedAct)
			{ 
				returnValue.valid = true;
				returnValue.sequencingRequest= 'ResumeAll';
			}
			else
			{
				returnValue.exception = 'NB.2.1-3';
			}
		}
		else
		{
			returnValue.exception = 'NB.2.1-1';
		}
		break;
	case 'Continue':
		if (!currentAct) 
		{
			returnValue.exception = 'NB.2.1-2';
		}
		else if (currentAct.parent && currentAct.parent.flow!=="false")
		{
			returnValue.valid = true;
			returnValue.sequencingRequest= 'Continue';
			if (currentAct.isActive)
			{
				returnValue.terminationRequest = 'Exit';
			}
		} 
		else
		{
			returnValue.exception = 'NB.2.1-4';
		} 
		break;
	case 'Previous':
		if (!currentAct) 
		{
			returnValue.exception = 'NB.2.1-2';
		}
		else if (currentAct.parent) 
		{
			if (currentAct.parent.flow!=="false" && !currentAct.parent.forwardOnly)
			{
				returnValue.valid = true;
				returnValue.sequencingRequest= 'Previous';
				if (currentAct.isActive)
				{
					returnValue.terminationRequest = 'Exit';
				}
			}
			else
			{
				returnValue.exception = 'NB.2.1-5';
			}
		}
		else
		{
			returnValue.exception = 'NB.2.1-6';
		}
		break;
	case 'Forward':
	case 'Backward':
		returnValue.exception = 'NB.2.1-7';
		break;
	case 'Choice':
		var target = activities[navReq.target]; 
		if (target) // 7.1
		{
			if (!target.parent || target.parent.choice) // 7.1.1
			{
				if (!currentAct) // 7.1.1.1
				{
					returnValue.valid = true;
					returnValue.sequencingRequest= 'Choice';
					returnValue.TargetActivity = target;
					break; 
				}
				if (!isSibling(currentAct, target)) // 7.1.1.2
				{						
					var activityPath = getCommonAncestorAndPath(target, currentAct).activityPath; 
					activityPath.push(currentAct); // always include currentAct
					if (activityPath.length) 
					{
						for (var i=0, ni=activityPath.length; i<ni; i+=1) 
						{
							var activity = activityPath[i];
							if (activity.isActive && activity.choiceExit==="false")
							{
								returnValue.exception = 'NB.2.1-8';
								break NAVREQ_TYPE;
							}
						}
					}
					else
					{
						returnValue.exception = 'NB.2.1-9';
						break;
					}
				}
				returnValue.valid = true;
				returnValue.sequencingRequest= 'Choice';
				returnValue.TargetActivity = target;
				if (currentAct.isActive)
				{
					returnValue.terminationRequest = 'Exit';
				}
				break;
			}
			else
			{
				returnValue.exception = 'NB.2.1-10';
				break;
			}
		}
		else
		{
			returnValue.exception = 'NB.2.1-11';
			break;
		}
		break;
	case 'Exit':
		if (currentAct) 
		{
			if (currentAct.isActive) 
			{
				returnValue.valid = true;
				returnValue.sequencingRequest= 'Exit';
				returnValue.terminationRequest = 'Exit';
			}
			else
			{
				returnValue.exception = 'NB.2.1-12';
			}			
		}
		else
		{
			returnValue.exception = 'NB.2.1-2';
		} 
		break;
	case 'ExitAll':
		if (currentAct) 
		{
			returnValue.valid = true;
			returnValue.sequencingRequest= 'Exit';
			returnValue.terminationRequest = 'ExitAll';
		}
		else
		{
			returnValue.exception = 'NB.2.1-2';
		}
		break;
	case 'Abandon':
		if (currentAct) 
		{
			if (currentAct.isActive) 
			{
				returnValue.valid = true;
				returnValue.sequencingRequest= 'Exit';
				returnValue.terminationRequest = 'Abandon';
			}
			else
			{
				returnValue.exception = 'NB.2.1-12';
			}
		}
		else
		{
			returnValue.exception = 'NB.2.1-2';
		} 
		break;
	case 'AbandonAll':
	case 'SuspendAll':
		if (currentAct) 
		{
			returnValue.valid = true;
			returnValue.sequencingRequest= 'Exit';
			returnValue.terminationRequest = navReq.type;
		}
		else
		{
			returnValue.exception = 'NB.2.1-2';
		} 
		break;
	}
	return returnValue;
} // end navigationRequest


function sequencingExitActionRulesSub () // #174
{
	var activityPath = getActivityPath(currentAct);
	var activity, exitTarget;
	
	sclog("SequencingExitActionRulesSub [TB.2.1]", "ps");
	
	while (activityPath.length > 1)
	{
		activity = activityPath.pop();
		exitTarget = sequencingRulesCheck(activity, EXIT_ACTIONS);
		if (exitTarget)
		{
			break;
		}		
	}
	if (exitTarget) 
	{
		terminateDescendentAttempts(exitTarget);
		endAttempt(exitTarget);
		currentAct = exitTarget; 		
	}
}


function sequencingPostConditionRulesSub() // #175
{
	var returnValue = {terminationRequest : null, sequencingRequest: null};
	
	sclog("SequencingPostConditionRulesSub [TB.2.2]", "ps");
	
	if (currentAct.isSuspended)
	{
		return; 
		// ??? oder return returnValue???
	}
	var r = sequencingRulesCheck(currentAct, POST_ACTIONS);
	switch (r) 
	{
	case 'Retry':
	case 'Continue':
	case 'Previous':
		returnValue.sequencingRequest= r;
		break;
	case 'ExitParent':
	case 'ExitAll':
		returnValue.terminationRequest = r;
		break;
	case 'RetryAll':
		returnValue.sequencingRequest= 'Retry';
		returnValue.terminationRequest = 'ExitAll;';
		break;
	}
	return returnValue;
}


/**
 * @param string "Exit", "SuspendAll" etc.
 * @return {valid, sequencingRequest, exception}
 */	
function terminationRequest(termReq) // #176
{
	var returnValue = {valid : false, sequencingRequest: null, exception: null};
	var r, exit, activityPath, activity, seqRule = null;
	
	sclog("TerminationRequest [TB.2.3]", "ps");
	
	if (!currentAct) 
	{
		returnValue.exception = 'TB.2.3-1';
		return returnValue;
	}
	else if ((termReq=='Exit' || termReq=='Abandon') && !currentAct.isActive) 
	{
		returnValue.exception = 'TB.2.3-2';
		return returnValue;
	}
	switch (termReq) 
	{
	case 'Exit':
		endAttempt(currentAct);
		sequencingExitActionRulesSub(currentAct);
		do {
			exit = false;
			seqRule = sequencingPostConditionRulesSub();
			if (seqRule.terminationRequest == 'ExitAll')
			{
				returnValue.terminationRequest = 'ExitAll';
				break; // to the next case ??? oder return terminationRequest('ExitAll')
			}
			if (seqRule.terminationRequest=='ExitParent')
			{
				if (currentAct.parent)
				{
					currentAct = currentAct.parent;
					endAttempt(currentAct);
					exit = true;
				}
				else
				{
					returnValue.exception = 'TB.2.3-4';
					return returnValue;
				}
			}
		} while (exit);
		returnValue.valid = true;
		returnValue.sequencingRequest= seqRule.sequencingRequest;
		return returnValue;
	case 'ExitAll':
		if (currentAct.isActive)
		{
			r = endAttempt(currentAct);
			r = terminateDescendentAttempts(rootAct);
			r = endAttempt(rootAct);
			currentAct = rootAct;
			returnValue.valid = true;
			returnValue.sequencingRequest= seqRule; /// ?? nur bei sprung in case valide!!! 
			return returnValue;
		} 
		break;
	case 'SuspendAll':
		if (currentAct.isActive || currentAct.isSuspended)
		{
			setSuspendedActivity(currentAct);
		}
		else
		{
			if (currentAct.parent) 
			{
				setSuspendedActivity(currentAct.parent);
			}
			else // rootAct
			{
				returnValue.exception = 'TB.2.3-3';
				return returnValue;
			}
		}
		activityPath = getActivityPath(suspendedAct);
		if (activityPath.length===0)
		{
			returnValue.exception = 'TB.2.3-5';
			return returnValue;
		}
		while (activityPath.length) 
		{
			activity = activityPath.shift();
			activity.isActive = false;
			activity.isSuspended = true;			
		}
		currentAct = rootAct;
		returnValue.valid = true;
		returnValue.sequencingRequest= 'Exit';
		return returnValue;
	case 'Abandon':
		currentAct.isActive = false;
		returnValue.valid = true;
		return returnValue;
	case 'AbandonAll':
		activityPath = getActivityPath(currentAct);
		if (activityPath.length===0)
		{
			returnValue.exception = 'TB.2.3-6';
			return returnValue;
		}
		while (activityPath.length) 
		{
			activity = activityPath.shift();
			activity.isActive = false;
		}
		currentAct = rootAct;
		returnValue.valid = true;
		return returnValue;
	default: 
		returnValue.exception = 'TB.2.3-7';
		return returnValue;
	}
	
} // end terminationRequest


function measureRollup(activity) // #180
{
	var totalWeightedMeasure = 0;
	var countedMeasures = 0;
	var targetObjective = getTargetObjective(activity);
	
	sclog("MeasureRollup [RB.1.1]", "ps");
	
	if (targetObjective)
	{
		for (var activityId in activity.item)
		{
			var child = activity.item[activityId];
			if (child.tracked) 
			{
				var rolledUpObjective = undefined;
				var objectives = child.objective;
				for (var objectiveId in objectives) 
				{
					var objective = objectives[objectiveId];
					if (objective.ObjectiveContributesToRollup) 
					{
						rolledUpObjective = objective;
						break; 
					}
				}
				if (rolledUpObjective)
				{
					if (child.RollupObjectiveMeasureWeight >= 0)
					{
						countedMeasures += child.RollupObjectiveMeasureWeight;
						if (rolledUpObjective.ObjectiveMeasureStatus)
						{
							totalWeightedMeasure += rolledUpObjective.ObjectiveNormalizedMeasure * child.RollupObjectiveMeasureWeight;
						}
					}
					else
					{
						return;
					}
				}
			}
		}
		if (countedMeasures === 0)
		{
			targetObjective.ObjectiveMeasureStatus = false;
			return;
		}
		if (countedMeasures > 0)
		{
			targetObjective.ObjectiveMeasureStatus = true;
			targetObjective.ObjectiveNormalizedMeasure = totalWeightedMeasure / countedMeasures;
			return;
		}
	}
}


function objectiveRollupUsingMeasure(activity) // #182
{
	var targetObjective = getTargetObjective(activity);
	
	sclog("ObjectiveRollupUsingMeasure [RB.1.2a]", "ps");
	
	if (targetObjective)
	{
		if (targetObjective.ObjectiveSatisfiedByMeasure)
		{
			if (!targetObjective.ObjectiveMeasureStatus)
			{
				targetObjective.ObjectiveProgressStatus = false;
			}
			else
			{
				if (!activity.isActive || (activity.isActive && activity.measureSatisfactionIfActive))
				{
					targetObjective.ObjectiveProgressStatus = true;
					targetObjective.ObjectiveSatisfiedStatus = targetObjective.ObjectiveNormalizedMeasure >= targetObjective.ObjectiveMinimumSatisfiedNormalizedMeasure; 
				}
				else
				{
					targetObjective.ObjectiveProgressStatus = false;					
				}
			}
		}
		return;
	}
	else
	{
		return;
	}
}

function objectiveRollupUsingRules(activity) // #184
{
	var targetObjective = getTargetObjective(activity);
	
	sclog("ObjectiveRollupUsingRules [RB.1.2b]", "ps");
	
	if (targetObjective) 
	{
		if (rollupRuleCheckSub(activity, /^notSatisfied$/))
		{
			targetObjective.ObjectiveProgressStatus = true;
			targetObjective.ObjectiveSatisfiedStatus = false; 
		}
		if (rollupRuleCheckSub(activity, /^satisfied$/))
		{
			targetObjective.ObjectiveProgressStatus = true;
			targetObjective.ObjectiveSatisfiedStatus = true; 
		}
	}
}


function activityProgressRollup(activity) // #185
{
	sclog("ActivityProgressRollup [RB.1.3]", "ps");
	
	if (rollupRuleCheckSub(activity, /^incomplete$/))
	{
		activity.activityProgressStatus = true;
		activity.activity.attemptCompletionStatus = false;
	}
	if (rollupRuleCheckSub(activity, /^complete$/))
	{
		activity.activityProgressStatus = true;
		activity.activity.attemptCompletionStatus = true;
	}	
}


function rollupRuleCheckSub(activity, rollupAction) // #186
{
	var rules = getRulesByAction(activity.rollupRule, rollupAction);
	var statusChange = false;
	
	sclog("RollupRuleCheckSub [RB.1.4]", "ps");
	
	if (rules) 
	{
		for (var i=0, ni=rules.length; i<ni; i+=1)
		{
			var rule = rules[i]; 
			var bag = {'unknown' : 0, 'true': 0, 'false': 0};
			var bagcount = 0;
			for (var childId in activity.item)
			{
				var child = activity.item[childId];
				if (child.tracked)
				{
					if (checkChildForRollupSub(child, rollupAction))
					{
						switch (evaluateRollupConditionsSub(child, rollupAction))
						{
						case 'unknown':
							bag['unknown']+=1;
							break;
						case true:
							bag['true']+=1;
							break;
						default:
							bag['false']+=1;
							break;
						}
						bagcount += 1;
				}
				}
			}
			switch (rule.childActivitySet)
			{
			case 'all':
				if ((bag['false'] + bag['unknown']) === 0) 
				{
					statusChange = true;
				} 
				break;
			case 'any':
				if (bag['true'] > 0) 
				{
					statusChange = true;
				} 
				break;
			case 'none':
				if ((bag['true'] + bag['unknown']) === 0) 
				{
					statusChange = true;
				} 
				break;
			case 'atLeastCount':
				if (bag['true'] >= rule.minimumCount) 
				{
					statusChange = true;
				} 
				break;
			case 'atLeastPercent':
				if (bag['true'] / bagcount >= rule.minimumPercent / 100) 
				{
					statusChange = true;
				} 
				break;
			}
		}
	}
	return statusChange;
}


function evaluateRollupConditionsSub(activity, rollupConditions) // #188
{
	var bag = {
		satisfied:0, objectiveStatusKnown:0, objectiveMeasureKnown:0, completed:0, 
		activityProgressKnown:0, attempted:0, attemptLimitExceeded:0, timeLimitExceeded:0, 
		outsideAvailableTimeRange:0
	};
	var bagcount = 0;
	
	sclog("EvaluateRollupConditionsSub [RB.1.4.1]", "ps");
	
	for (var i=0, ni=rollupConditions.length; i<ni; i+=1)
	{
		var condition = rollupConditions[i];
		/// ??? Evaluate the rollup condition by applying the appropriate tracking
		/// ??? information for the activity to the Rollup Condition
		/// ??? pseudo code for evaluate rollup conditions sub
		if (condition.operator === 'not') 
		{
			/// ??? negate condition
		}
		bag[condition.value]+=1; /// ? 
	}	
	if (bagcount===0)
	{
		return 'unknown';
	}
	/// ??? conditionCombination 
	return conditionCombination(bag);
}


/**
 * @return {Boolean}
 */
function checkChildForRollupSub(activity, rollupAction) // #189
{
	var included = false;
	
	sclog("CheckChildForRollupSub [RB.1.4.2]", "ps");
	
	if (rollupAction==='satisfied' || rollupAction==='notSatisfied')
	{
		if (activity.objectiveSatisfied)
		{
			included = true;
			if ((rollupAction==='satisfied' && activity.requiredForSatisfied==='ifNotSuspended') ||
				(rollupAction==='notSatisfied' && activity.requiredForNotSatisfied==='ifNotSuspended') )
			{
				if (activity.activityAttemptCount > 0 && activity.isSuspended)
				{
					included = false;
				}
			}
			else
			{
				if ((rollupAction==='satisfied' && activity.requiredForSatisfied==='ifAttempted') ||
					(rollupAction==='notSatisfied' && activity.requiredForNotSatisfied==='ifAttempted') )
				{
					if (activity.activityAttemptCount > 0 && activity.isSuspended)
					{
						included = false;
					}					
				}
				else
				{
					if ((rollupAction==='satisfied' && activity.requiredForSatisfied==='ifNotSkipped') ||
						(rollupAction==='notSatisfied' && activity.requiredForNotSatisfied==='ifNotSkipped') )
					{
						if (activity.activityAttemptCount > 0 && activity.isSuspended)
						{
							if (sequencingRulesCheck(activity, SKIPPED_ACTIONS))
							{
								included = false;
							}
						}				
					}				
				}
			}
		}
	}
	if (rollupAction==='completed' || rollupAction==='incomplete')
	{
		if (activity.RollupProgressCompletion)
		{
			included = true;
			if ((rollupAction==='completed' && activity.requiredForCompleted==='ifNotSuspended') ||
				(rollupAction==='incomplete' && activity.requiredForIncomplete==='ifNotSuspended') )
			{
				if (activity.activityAttemptCount>0 && activity.isSuspended)
				{
					included = false;					
				}
			}
			else
			{
				if ((rollupAction==='completed' && activity.requiredForCompleted==='ifAttempted') ||
					(rollupAction==='incomplete' && activity.requiredForIncomplete==='ifAttempted') )
				{
					if (activity.activityAttemptCount>0 && activity.isSuspended)
					{
						included = false;					
					}
				}
				else
				{
					if ((rollupAction==='completed' && activity.requiredForCompleted==='ifNotSkipped') ||
						(rollupAction==='incomplete' && activity.requiredForIncomplete==='ifNotSkipped') )
					{
						if (sequencingRulesCheck(activity, SKIPPED_ACTIONS))
						{
							included = false;					
						}
					}					
				}
			}			
		}
	}
	return included;	
}


function overallRollup(activity) // #191
{
	var activityPath = getActivityPath(activity);
	
	sclog("OverallRollup [RB.1.5]", "ps");
	
	for (var i=0, ni=activityPath.length; i<ni; i+=1)
	{
		var a = activityPath[i];
		measureRollup(a);
		// ObjectiveRollup(a); // Apply the appropriate Objective Rollup  to the activity ????
		objectiveRollupUsingMeasure(a); 
		objectiveRollupUsingRules(a); 				
		activityProgressRollup(a);
	}
} 


function selectChildren (activity) // #192
{
	sclog("SelectChildren [SR.1]", "ps");
	
	if (activity.item.length > 0 &&
		!activity.isSuspended && !activity.isActive &&
		activity.SelectionTiming==='once' &&
		!activity.activityProgressStatus && 
		activity.SelectionCount) 
	{
		activity.availableChildren = selectRandomItems(activity.item, activity.SelectionCount);				
	} 
}


function randomizeChildren (activity) // #193
{
	sclog("RandomizeChildren [SR.2]", "ps");
	
	if (activity.item.length>0 &&
		!activity.isSuspended && 
		!activity.isActive) 
	{
		if (((activity.RandomizationTiming==='once' && !activity.ProgressStatus) ||
			activity.RandomizationTiming==='onEachNewAttempt') && 
			activity.randomizeChildren)
		{
				activity.availableChildren = selectRandomItems(activity.availableChildren, activity.availableChildren.length);
		}
	}
}

/**
 * @param activity
 * @param traversalDirection 
 * @param considerChildren
 * @param previousTraversalDirection
 * @return
 */	
function flowTreeTraversalSub(activity, traversalDirection, considerChildren, previousTraversalDirection) // #194
{
	var returnValue = {identifiedActivity: null, traversalDirection : null, exception: null};
	var reversedDirection = false;
	var i;
	
	sclog("FlowTreeTraversalSub [SR.2.1]", "ps");
	
	if (previousTraversalDirection==='backward' && isLastOfAvailableChildren(activity)) 
	{
		traversalDirection = 'backward';
		////
		// ???? activity is the first activity in the activity�s parent�s list of Available Children
		//--activity = activity.parent.availableChildren[0];
		////
		reversedDirection = true;
	}
	if (traversalDirection==='forward') 
	{
		if (activity.index === activity.length-1) //3,1
		{
			//returnValue.exception = 'SB.2.1-1';
			terminateDescendentAttempts(rootAct);
			returnValue.endSequencingSession = true;
			return returnValue;
		} 
		if (!activity.item || activity.item.length===0 || !considerChildren) //3,2 ??????
		{
			// search for current item from back to front
			var siblings = activity.parent.availableChildren;
			for (i=siblings.length-1;i>-1; i--)
			{
				if (siblings[i]===activity) {break;}
			}
			if (i==siblings.length-1) // last one, one up
			{
				return flowTreeTraversalSub(activity.parent, 'forward', false, null);
			}
			else // go for next sibling
			{
				returnValue.identifiedActivity = siblings[i+1]; // next one
				returnValue.traversalDirection = traversalDirection;
				return returnValue;
			}
		}
		else // 3,3
		{
			if (activity.availableChildren && activity.availableChildren.length>0)
			{
				returnValue.identifiedActivity = activity.availableChildren[0];
				returnValue.traversalDirection = traversalDirection;
				return returnValue;
			}
			else
			{
				returnValue.exception = 'SB.2.1-2';
				return returnValue;
			}
		}
	}
	if (traversalDirection==='backward') // 4
	{
		if (isRoot(activity)) // 4.1
		{
			returnValue.exception = 'SB.2.1-3';
			return returnValue;
		}
		if (!activity.item || activity.item.length===0 || !considerChildren) // 4.2 
		{
			if (reversedDirection===false && activity.parent.forwardOnly)
			{
				returnValue.exception = 'SB.2.1-4';
				return returnValue;
			}
			for (i=activity.parent.availableChildren.length-1;i>-1; i--)
			{
				if (activity.parent.availableChildren[i]===activity) {break;}
			}
			if (i===0) // is first activity
			{
				return flowTreeTraversalSub(activity.parent, 'backward', false, null);
			}
			else // non first activity
			{
				returnValue.identifiedActivity = activity.parent.availableChildren[i-1];
				returnValue.traversalDirection = traversalDirection;
				return returnValue;
			}			}
		else
		{
			if (activity.availableChildren && activity.availableChildren.length) 
			{
				if (activity.forwardOnly)
				{
					returnValue.identifiedActivity = activity.parent.availableChildren[0];
					returnValue.traversalDirection = 'forward';
					return returnValue;				
				}
				else
				{
					returnValue.identifiedActivity = activity.parent.availableChildren[activity.parent.availableChildren.length-1];
					returnValue.traversalDirection = 'backward';
					return returnValue;				
				}
			}
			else
			{
				returnValue.exception = 'SB.2.1-2';
				return returnValue;
			}		
		}
	}	
	return returnValue;
}


/*
Flow Activity Traversal Sub [SB.2.2]
@param activity
@param a traversal direction
@param a previous traversal direction
@return object the �next� activity in a directed traversal of the activity tree ("identifiedActivity")
	and True if the activity can be delivered ("deliverable")
*/
function flowActivityTraversalSub(activity, traversalDirection, previousTraversalDirection) // #197
{
	var r;
	
	sclog("FlowActivityTraversalSub [SR.2.2]", "ps");
	
	if (activity.flow==="false")
	{
		return {deliverable: false, identifiedActivity: activity, exception: 'SB.2.2-1'};
	}
	r = sequencingRulesCheck(activity, SKIPPED_ACTIONS);
	if (r) // skipped
	{
		r = flowTreeTraversalSub(activity, traversalDirection, false, previousTraversalDirection);
		if (!r.identifiedActivity)
		{
			return {
				deliverable: false, 
				endSequencingSession: r.endSequencingSession,
				identifiedActivity: activity, 
				exception: r.exception};
		}
		else
		{
			return flowActivityTraversalSub(
				r.identifiedActivity, 
				traversalDirection,
				(traversalDirection==='backward' && r.traversalDirection==='backward') ? null : r.previousTraversalDirection);
		}
	}
	if (checkActivity(activity))
	{
		return {deliverable: false, identifiedActivity: activity, exception: 'SB.2.2-2'};
	}
	if (!isLeaf(activity)) 
	{
		r = flowTreeTraversalSub(activity, traversalDirection, true, null);
		if (!r.identifiedActivity)
		{
			return {
				deliverable: false, 
				endSequencingSession: r.endSequencingSession,
				identifiedActivity: activity, 
				exception: r.exception};
		}
		else
		{
			if (traversalDirection==='backward' && r.traversalDirection==='forward')
			{
				return flowActivityTraversalSub(r.identifiedActivity, 'forward', 'backward');
			}
			else 
			{
				return flowActivityTraversalSub(r.identifiedActivity, traversalDirection, null);
			}
		}
	}
	return {deliverable: true, identifiedActivity: activity};
}


function flowSub(activity, traversalDirection, considerChildren) // #199
{
	var returnValue = {identifiedActivity: null, deliverable: null, exception: null};
	var candidateActivity = activity;
	var r = flowTreeTraversalSub(candidateActivity, traversalDirection, considerChildren, null);
	
	sclog("FlowSub [SR.2.3]", "ps");
	
	if (!r.identifiedActivity) 
	{
		returnValue.identifiedActivity = candidateActivity;
		returnValue.endSequencingSession = r.endSequencingSession;
		returnValue.deliverable = false;
	}
	else
	{
		candidateActivity = r.identifiedActivity;
		r = flowActivityTraversalSub(candidateActivity, traversalDirection, null);
		returnValue.endSequencingSession = r.endSequencingSession;
		returnValue.identifiedActivity = r.identifiedActivity;
		returnValue.deliverable = r.deliverable;
	}
	returnValue.exception = r.exception;
	return returnValue;	
}


function choiceActivityTraversalSub(activity, traversalDirection) // #200
{
	var r, returnValue = {reachable: false, exception: null};
	
	sclog("ChoiceActivityTraversalSub [SB.2.4]", "ps");
	
	if (traversalDirection=='forward')
	{
		r = sequencingRulesCheck(activity, STOP_FORWARD_TRAVERSAL_ACTIONS);
		if (r) 
		{
			returnValue.exception = 'SB.2.4-1';
			return returnValue;
		}
	}
	else if (traversalDirection=='backward')
	{
		if (activity.parent) 
		{
			if (activity.parent.forwardOnly)
			{
				returnValue.exception = 'SB.2.4-2';
				return returnValue;
			}
		}
		else
		{
			returnValue.exception = 'SB.2.4-3';
			return returnValue;
		}
	}
	returnValue.reachable = true;
	return returnValue;
}


/**
 * @return {Object} Defaults to {deliveryRequest: undefined, exception: undefined}
 */ 
function startSequencingRequest() // #201
{
	sclog("StartSequencingRequest [SB.2.5]", "ps");
	
	if (currentAct)
	{
		return {exception : 'SB.2.5-1'};
	}
	if (rootAct.item.length===0) 
	{
		return {deliveryRequest:rootAct};
	}
	else 
	{
		var r = flowSub(rootAct, 'forward', true);
		if (r.exception)
		{
			return {endSequencingSession: r.endSequencingSession, exception: r.exception};
		}
		else
		{
			return {deliveryRequest: r.identifiedActivity};
		}
	} 	
}


function resumeAllSequencingRequest() // #202
{
	sclog("ResumeAllSequencingRequest [SB.2.6]", "ps");
	
	if (currentAct)
	{
		return {exception : 'SB.2.6-1'};
	}
	else if (!suspendedAct)
	{
		return {exception : 'SB.2.6-2'};
	}
	return {deliveryRequest: suspendedAct};
}


function continueSequencingRequest() // #203
{
	sclog("ContinueSequencingRequest [SB.2.7]", "ps");
	
	if (!currentAct)
	{
		return {exception : 'SB.2.7-1'};
	}
	else if (currentAct!=rootAct)
	{
		if (currentAct.parent.flow==="false") 
		{
			return {exception : 'SB.2.7-2'};
		}
	}
	var r = flowSub(currentAct, 'forward', false);
	if (r.endSequencingSession || r.exception)
	{
		return {endSequencingSession: r.endSequencingSession, exception : r.exception};
	}
	else
	{
		return {deliveryRequest: r.identifiedActivity};
	}
}


function previousSequencingRequest()  // #204
{
	sclog("PreviousSequencingRequest [SB.2.8]", "ps");
	
	if (!currentAct)
	{
		return {exception : 'SB.2.8-1'};
	}
	else if (currentAct!=rootAct)
	{
		if (currentAct.parent.flow==="false") 
		{
			return {exception : 'SB.2.8-2'};
		}
	}
	var r = flowSub(currentAct, 'backward', false);
	if (r.endSequencingSession || r.exception)
	{
		return {exception : r.exception};
	}
	else
	{
		return {deliveryRequest: r.identifiedActivity};
	}	
}


/**
 * @return {Object} Defaults to {deliveryRequest: undefined, exception: undefined}
 */ 
function choiceSequencingRequest(targetActivity)  // #205
{
	var i, ni, r, a, commonAncestor, commonPath, activityPath, traverse, activityList, consideredActivity;
	
	sclog("ChoiceSequencingRequest [SB.2.9]", "ps");
	
	if (!targetActivity) 
	{
		return {exception: 'SB.2.9-1'};
	}
	if (!isRoot(targetActivity)) 
	{
		if (indexOf(targetActivity.parent.availableChildren, targetActivity)===-1) 
		{
			return {exception: 'SB.2.9-2'};
		}
	}
	activityPath = getActivityPath(targetActivity);
	for (i=activityPath.length-1; i>-1; i--)
	{
		a = activityPath[i];
		r = sequencingRulesCheck(a, HIDDEN_FROM_CHOICE_ACTIONS);
		if (r)
		{
			return {exception: 'SB.2.9-3'};
		}
	}
	if (!isRoot(targetActivity)) 
	{
		if (!targetActivity.parent.choice) 
		{
			return {exception: 'SB.2.9-4'};
		}
	}
	if (currentAct)
	{
		r = getCommonAncestorAndPath(currentAct, targetActivity);
		commonAncestor = r.commonAncestor;
		activityPath = r.activityPath;
	}
	if (!commonAncestor) // deviation from pseudo code
	{
		commonAncestor = rootAct;
	}
	if (currentAct==targetActivity) // 8
	{
	}
	else if (!currentAct || currentAct==commonAncestor) // 10 
	{
		if (activityPath.length===0)
		{
			return {exception: 'SB.2.9-5'};
		}
		while (activityPath.length)
		{
			a = activityPath.pop();
			r = choiceActivityTraversalSub(a, 'forward');
			if (r.exception)
			{
				return {exception: r.exception};
			}
			if (!a.isActive && a!=commonAncestor && a.preventActivation)
			{
				return {exception: 'SB.2.9-6'};
			}
		}
	}
	else if (isInArray(targetActivity.id, currentAct.parent ? currentAct.parent.item : null, 'id')) // 9 (needs currentAct, so better after case "10")
	{
		activityList = [];
		// ??? pr�fen, ob das die richtigen Elemente sind
		for (i = Math.min(targetActivity.index, currentAct.index), ni = Math.max(targetActivity.index, currentAct.index); i<ni; i+=1)
		{
			activityList.push(activitiesByNo[i]);
		}
		if (activityList.length===0) 
		{
			return {exception: 'SB.2.9-5'};
		}
		traverse = targetActivity.index > currentAct.index ? 'forward' : 'backward';
		while (activityList.length)
		{
			r = choiceActivityTraversalSub(activityList.pop(), traverse);
			if (r.exception)
			{
				return {exception: r.exception};
			} 
		}
	} 
	else if (targetActivity==commonAncestor) 
	{
		/// ?? pr�fen ob der Pfad richtig ist
		if (activityPath.length===0)
		{
			return {exception: 'SB.2.9-5'};
		}
		// suche nach constrained activity
		a = activityPath.unshift(); // letzte wegwerfen
		var constrainedActivity = a.constrainChoice ? a : null; ///??? letztes zuerst auswerten erlaubt?
		while (activityPath.length)
		{
			a = activityPath.pop();
			if (a.choiceExit==="false")
			{
				return {exception: 'SB.2.9-7'};
			}
			if (!constrainedActivity && a.constrainChoice)
			{
				constrainedActivity = a;
			}
		}
		if (constrainedActivity)
		{
			traverse = targetActivity.index > constrainedActivity.index ? 'forward' : 'backward';
			r = choiceFlowSub(constrainedActivity, traverse);
			consideredActivity = r.identifiedActivity;
			if (!isAvailableDescendent(consideredActivity, targetActivity) && 
				(targetActivity!=constrainedActivity || targetActivity!=consideredActivity))
			{
				return {exception: 'SB.2.9-8'};
			}
		}
	}
	else if (commonAncestor && targetActivity.index > commonAncestor.index) // 12 
	{
		activityPath.push(commonAncestor);
		if (activityPath.length===0)
		{
			return {exception: 'SB.2.9-5'};
		}
		constrainedActivity = null;
		for (i=0, ni=activityPath.length-1; i<=ni; i++)  // 12.4
		{
			a = activityPath[i];
			if (i!==ni && a.choiceExit === "false") 
			{
				return {exception: 'SB.2.9-7'};
			}
			if (!constrainedActivity && a.constrainChoice) 
			{
				constrainedActivity = a;
			} 
		}
		if (constrainedActivity) // 12.5
		{
			traverse = targetActivity.index > constrainedActivity.index ? 'forward' : 'backward';
			r = choiceFlowSub(constrainedActivity, traverse);
			consideredActivity = r.identifiedActivity;
			if (!isAvailableDescendent(consideredActivity, targetActivity) && 
				(targetActivity!=constrainedActivity || targetActivity!=consideredActivity))
			{
				return {exception: 'SB.2.9-8'};
			}
		}
		if (activityPath.length===0)
		{
			return {exception: 'SB.2.9-5'};
		}
		if (targetActivity.index > currentAct.index) 
		{
			while (activityPath.length)
			{
				a = activityPath.pop();
				r = choiceActivityTraversalSub(a, 'forward');
				if (r.exception)
				{
					return {exception: r.exception};
				}
				if (!a.isActive && (a!=commonAncestor && a.preventActivation))
				{
					return {exception: 'SB.2.9-6'};
				}
			}
		}
		else
		{
			while (activityPath.length)
			{
				a = activityPath.pop();
				if (!a.isActive && (a!=commonAncestor && a.preventActivation))
				{
					return {exception: 'SB.2.9-6'};
				}
			}
		}
	}	
	if (isLeaf(targetActivity)) 
	{
		return {deliveryRequest: targetActivity};
	}
	r = flowSub(targetActivity, 'forward', true);
	if (!r.deliverable)
	{
		terminateDescendentAttempts(commonAncestor);
		endAttempt(commonAncestor);
		currentAct = targetActivity;
		return {exception: 'SB.2.9-9'};
	}
	else
	{
		return {deliveryRequest: r.identifiedActivity};
	} 
}


function choiceFlowSub (activity, traversalDirection)  // #212
{
	sclog("ChoiceFlowSub [SB.2.9.1]", "ps");
	
	var identifiedActivity = choiceFlowTreeTraversalSub(activity, traversalDirection);
	return identifiedActivity ? identifiedActivity : activity;
}

/**
 * @return {String} activityId
 */ 
function choiceFlowTreeTraversalSub(activity, traversalDirection) // #213
{
	var r;
	
	sclog("choiceFlowTreeTraversalSub [SB.2.9.2]", "ps");
	
	if (traversalDirection === 'forward')
	{
		if (activity.index===activitiesByNo.length-1)
		{
			return;
		}
		if (isLastOfAvailableChildren(activity))
		{
			return choiceFlowTreeTraversalSub(activity.parent, 'forward');
		}
		else
		{
			///Traverse the tree, forward preorder, one activity to the next
			///activity, in the activity�s parent�s list of Available Children
			r = traverse(activity, 'forward');
			choiceFlowTreeTraversalSub(r);
		}
	}
	if (traversalDirection === 'backward')
	{
		if (isRoot(activity))
		{
			return;
		}
		if (activity == activity.parent.availableChildren[0])
		{
			return choiceFlowTreeTraversalSub(activity.parent, 'backward');
		}
		else
		{
			///Traverse the tree, reverse preorder, one activity to the previous
			///activity, from the activity�s parent�s list of Available Children
			r = traverse(activity, 'backward');
			choiceFlowTreeTraversalSub(r);
		}
	}	
}
	
	
function retrySequencingRequest() // #214
{
	sclog("RetrySequencingRequest [SB.2.10]", "ps");
	
	if (!currentAct)
	{
		return {exception: 'SB.2.10-1'};
	}
	if (currentAct.isActive || currentAct.isSuspended)
	{
		return {exception: 'SB.2.10-2'};
	}
	if (!isLeaf(currentAct))
	{
		var r = flowSub(currentAct, 'forward', true);
		if (r.exception)
		{
			return {exception: 'SB.2.10-3'};
		}
		else
		{
			return {deliveryRequest: r.identifiedActivity};
		}
	}
	else
	{
		return {deliveryRequest: currentAct};
	}
}
	
	
function exitSequencingRequest() // #215
{
	sclog("ExitSequencingRequest [SR.2.11]", "ps");
	
	if (!currentAct)
	{
		return {endSequencingSession: false, exception: 'SB.2.11-1'};
	}
	else if (currentAct.isActive)
	{
		return {endSequencingSession: false, exception: 'SB.2.11-2'};
	}
	else if (currentAct == rootAct)
	{
		return {endSequencingSession: true};
	}
	return {endSequencingSession: false};
}

	
function sequencingRequest(seqReq, target) // #216
{
	var r;
	
	sclog("SequencingRequest [SR.2.12]", "ps");
	
	switch (seqReq)
	{
	case 'Start':
		r = startSequencingRequest();
		break;
	case 'ResumeAll':
		r = resumeAllSequencingRequest();
		break;
	case 'Exit':
		r = exitSequencingRequest();
		break;
	case 'Retry':
		r = retrySequencingRequest();
		break;
	case 'Continue':
		r = continueSequencingRequest();
		break;
	case 'Previous':
		r = previousSequencingRequest();
		break;
	case 'Choice':
		r = choiceSequencingRequest(target);
		break;
	}
	if (!r || r.exception)
	{
		return {valid: false, exception: r ?	r.exception : 'SB.2.12-1'};
	}
	else
	{
		return {valid: true, deliveryRequest: r.deliveryRequest, endSequencingSession: r.endSequencingSession};
	}
}


function deliveryRequest(delReq) // #219
{
	sclog("DeliveryRequest [DB.1.1]", "ps");
	
	if (!isLeaf(delReq)) 
	{
		return {delReq: false, exception: 'DB.1.1-1'};
	} 
	var activityPath = getActivityPath(delReq); 
	if (activityPath.length===0)
	{
		return {deliveryRequest: false, exception: 'DB.1.1-2'};
	}
	for (var i=activityPath.length-1; i>-1; i-=1)
	{
		if (checkActivity(activityPath[i]))
		{
			return {deliveryRequest: false, exception: 'DB.1.1-3'};
		}
	}
	return {valid: true};
}
	

function contentDeliveryEnvironment(delReq) // #220
{
	sclog("ContentDeliveryEnvironment [DB.2]", "ps");
	
	if (currentAct && currentAct.isActive)
	{
		return {exception: 'DB.2-1'};
	}
	if (state<0) 
	{
		return delReq;
	}
	if (delReq != suspendedAct)
	{
		clearSuspendedActivitySub(delReq);
	}
	terminateDescendentAttempts(delReq);
	//var activityPath = deliveryRequest; /// ???
	var activityPath = getActivityPath(delReq); /// ???
	while (activityPath.length) 
	{
		var a = activityPath.pop();
		if (!a.isActive)
		{
			if (a.tracked)
			{ 
				if (a.isSuspended)
				{
					a.isSuspended = false;
				}
				else
				{
					var count = a.activityAttemptCount + 1;
					a.activityAttemptCount = count;
					if (count===1)
					{
						a.activityProgressStatus = true;
					}
					///???Initialize Objective Progress Information and Attempt
					///???Progress Information required for the new attempt
				}
			}
			a.isActive = true;
		}
	}
	currentAct = delReq;
	onItemDeliver(currentAct);
	return {};
}

function clearSuspendedActivitySub(activity) // #222
{
	sclog("ClearSuspendedActivitySub [DB.2.1]", "ps");
	
	if (suspendedAct)
	{
		var activityPath = getCommonAncestorAndPath(activity, suspendedAct).activityPath;
		while (activityPath.length)
		{
			var a = activityPath.shift();
			if (isLeaf(a))
			{
				a.isSuspended = false;
			}
			else
			{
				if (hasAnySuspendedChildren(a))
				{
					a.isSuspended = false;
				}
			}
		}
		setSuspendedActivity(null);
	}
}
	
/**
 * @return {Boolean} True if any of the activity�s limit conditions have been violated
 */ 
function limitConditionsCheck(activity) // #223
{
	sclog("LimitConditionsCheck [UP.1]", "ps");
	
	var limit, value; 
	if (!activity.tracked)
	{
		return false;
	}
	value = activity.activityProgressStatus;
	if (value)
	{
		limit = activity.attemptLimit;
		if (limit)
		{
			value = activity.activityAttemptCount;
			if (value >= limit)
			{
				return true;
			}
		}
		limit = activity.activityAbsoluteDurationLimit;
		if (limit)
		{
			value = activity.activityAbsoluteDuration;
			if (value >= limit)
			{
				return true;
			}
		}
		limit = activity.activityExperiencedDurationLimit;
		if ((limit = activity.activityExperiencedDurationLimit))
		{
			value = activity.activityExperiencedDuration;
			if (value >= limit)
			{
				return true;
			}
		}
		value = activity.attemptProgressState;
		if (value)
		{
			limit = activity.attemptAbsoluteDurationLimit;
			if (limit)
			{
				value = activity.attemptAbsoluteDuration;
				if (value >= limit)
				{
					return true;
				}
			}
			limit = activity.attemptExperiencedDurationLimit;
			if (limit) {
				value = activity.attemptExperiencedDuration;
				if (value >= limit)
				{
					return true;
				}
			}
		}
	}
	/*
	// At this time, the SCORM does not require an LMS to implement
	// time related sequencing decisions based on this value.
	if (activity.beginTimeLimit)
	{
		if (Scorm2004.currentTime() < activity.beginTimeLimit)
		{
			return true;
		}
	}
	if (activity.endTimeLimit)
	{
		if (Scorm2004.currentTime() > activity.endTimeLimit)
		{
			return true;
		}
	}
	*/
	return false;
}
	

/**
 * @param {Array} rules Set of rules actions as RegExp of action names
 * @return {String} returns the action to apply or Nil
 */ 
function sequencingRulesCheck(activity, ruleActions) // #225
{
	sclog("SequencingRulesCheck [UP.2]", "ps");
	
	for (var i=0; i<activity.rules.length; i+=1)
	{
		var rule = activity.rules[i];
		if (rule && ruleActions.test(rule.action))
		{
			if (sequencingRuleCheckSub(activity, rule))
			{
				return rule.action.substr(0, 1).toUpperCase() + rule.action.substr(1);
			}
		}
	}
	return;
}


/**
 * @return {Boolean} True if the rule applies
 */ 
function sequencingRuleCheckSub(activity, seqRule) // UP.2
{
	var bag = [], value, v;
	var conditions = seqRule.condition;
	
	sclog("SequencingRuleCheckSub [UP.2.1]", "ps");
	
	for (var i=0, ni=conditions.length; i<ni; i+=1)
	{
		var condition = conditions[i];
		
		var objective = Tracker.getObjectiveValues(activity.foreignId, condition.referencedObjective);
		
		switch (condition.condition)
		{
			case 'satisfied':
				value = objective.success_status==="passed";
				break;
			case 'objectiveStatusKnown':
				value = objective.completion_status!=="unknown";
				break;
			case 'objectiveMeasureKnown':
				// check this
				value = typeof(objective.scaled)==="string";
				break;
			case 'completed':
				value = objective.completion_status==="completed";
				break;
			case 'activityProgressKnown':
				value = objective.completion_status!=="unknown"; // TODO  "&& activity progress status #SN 237
				break;
			case 'attempted':
				value = activity.activityProgressStatus && activity.activityAttemptCount;
				break;
			case 'attemptLimitExceeded':
				//
				break;
			case 'timeLimitExceeded':
				break;
			case 'outsideAvailableTimeRange':
				// ?????? 
				break;
			case 'objectiveMeasureGreaterThan':
				value = v!==undefined && Number(objective.scaled) > Number(condition.measureThreshold);
				break;
			case 'objectiveMeasureLessThan':
				value = v!==undefined && Number(objective.scaled) < Number(condition.measureThreshold);
				break;
			case 'always':
				value = true;
				break;
			case 'never': 
				value = false;
				break;
			default:
				break;
		}
		bag.push(condition.operator === 'not' ? !value : value);
	}
	return ruleCombination(seqRule, bag);
}

function terminateDescendentAttempts(activity) // #227
{
	sclog("TerminateDescendentAttempts [UP.3]", "ps");
	
	var activityPath = getCommonAncestorAndPath(currentAct, activity).activityPath;
	while (activityPath.length)
	{
		endAttempt(activityPath.shift());		
	}	
}
	

function endAttempt(activity) // #228
{
	sclog("EndAttempt [UP.4]", "ps");
	
	var isleaf = isLeaf(activity);
	if (isleaf) 
	{
		// may result in a new nav request while sequencer is busy
		onItemUndeliver(); 
	} 
	if (isleaf && activity.tracked && !activity.isSuspended) 
	{
		if (!activity.completionSetByContent)
		{
			if (!activity.attemptProgressStatus) 
			{
				activity.attemptProgressStatus = true;
				activity.attemptCompletionStatus = true;
			}
		}
		if (!activity.objectiveSetByContent)
		{
			var objectives = activity.objective;
			for (var objectiveId in objectives)
			{
				var objective = objectives[objectiveId];
				if (objective.ObjectiveContributesToRollup)
				{
					if (!objective.ObjectiveProgressStatus)
					{
						objective.ObjectiveProgressStatus = true;
						objective.ObjectiveSatisfiedStatus = true;								
					}
				}
			}
		}
	}
	else
	{
		activity.isSuspended = false;
		if (hasAnySuspendedChildren(activity))
		{
			activity.isSuspended = true;				
		}
	}
	activity.isActive = false;
	overallRollup(activity);
}

	
function checkActivity(activity) // #230
{
	sclog("CheckActivity [UP.5]", "ps");
	
	return sequencingRulesCheck(activity, DISABLED_ACTIONS) || 
		limitConditionsCheck(activity); 
}

function endSequencingSession() 
{
	currentAct = null;
	onNavigationEnd();	
}

function isSibling(activity1, activity2) 
{
	return activity2 && activity1 && activity1.parent && 
		isInArray(activity2.id, activity1.parent.item, 'id');
}

function isInArray(value, array, prop) 
{
	for (var i=0, ni=array.length; i<ni; i++)
	{
		if (value===(prop ? array[i][prop] : array[i])) 
		{
			return true;
		} 
	}
	return false;
}

/**
 *	Form the activity path as the ordered series of activities from an
 *	activity to the root of the activity tree
 *	@param {object} activity to start bubble 
 *	@param {boolean} as default starting activity will be included as first element
 *	@param {boolean} as root activity will be included as last element
 *	@return {array} bottom up list of references to activity objects with id-associated positions
 */	  
function getActivityPath(activity, excludeSource, excludeTop)
{
	var r = [];
	var a = activity;
	if (!excludeSource)
	{
		r[a.id] = r.length;
		r.push(a);
	}
	while ((a = a.parent)) 
	{
		r[a.id] = r.length;
		r.push(a);
	}
	if (excludeTop)
	{
		delete r[r.pop(a).id];
	}
	return r;
}

/**
 * Form the activity path as the ordered series of activities from the Current
 * Activity to the common ancestor, exclusive of the Current Activity and the
 * common ancestor
 * @param {object} first activity
 * @param {object} second activity
 * @return {object} containing commonAncestor property and bottom-up activityPath
*/
function getCommonAncestorAndPath(commonActivity, otherActivity)
{
	var returnValue = {commonAncestor: null, activityPath: []};
	if (commonActivity && otherActivity)
	{
		var p = getActivityPath(commonActivity, true);
		var a = otherActivity;
		while ((a = a.parent) && !(a.id in p)) 
		{
			returnValue.activityPath.push(a);
		}
		returnValue.commonAncestor = a;
	} 
	return returnValue;
}

function getTargetObjective(activity) /// ???
{
	var objectives = activity.objective;
	for (var objectiveId in objectives) 
	{
		var objective = objectives[objectiveId];
		if (objective.objectiveSetByContent) 
		{
			return objective;
		}
	}
	return null;
}

function getRulesByAction(rules, action) 
{
	var r = new Array();
	if (rules && rules.length) 
	{
		for (var i=0, ni=rules.length; i<ni; i+=1)
		{
			if (rules[i].action==action) 
			{
				r.push(rules[i]);
			}
		}
	}
	return r;
}

function selectRandomItems(array, count) {
	var i, a = [], b = [];
	for (i=array.length-1; i>-1; i-=1)
	{
		a.push([Math.random(), i]);
	}
	a = a.sort();
	for (i=0; i<count; i+=1)
	{
		b.push(a[i][1]);
	}
	b = b.sort();
	for (i=0; i<count; i+=1)
	{
		b[i] = array[b[i]];
	}
	return b;
}

function isLastOfAvailableChildren(activity)
{
	return activity.parent.availableChildren[activity.parent.availableChildren.length-1]==activity;
}

function negateRuleCondition (condition) 
{
} 

function ruleCombination(seqRule, bag)
{
	var sum = 0;
	for (var i=bag.length; i>-1; i--) 
	{
		if (bag[i]) 
		{
			sum++;
		}
	}
	return (sum && seqRule.conditionCombination=="any") || (sum && sum==bag.length);
}

function hasAnySuspendedChildren(activity) 
{
	var children = activity.item;
	for (var id in children)
	{
		if (children[id].isSuspended)
		{
			return true;
		}
	}
	return false;
}

function isRoot(activity)
{
	return activity && activity==rootAct;
}

function isLeaf(activity)
{
	return activity && activity.href;
}

function traverse(activity, dir)
{
	
}

function indexOf(array, item)
{
	for (var i=array.length-1; i>-1; i-=1)
	{
		if (array[i]==item)
		{
			break;
		}
	}
	return i;
}

function isAvailableDescendent() 
{
}

function conditionCombination() 
{
}

function setSuspendedActivity(activity)
{
	suspendedAct = activity;
	rootAct.location = activity ? activity.id : '';
}	

