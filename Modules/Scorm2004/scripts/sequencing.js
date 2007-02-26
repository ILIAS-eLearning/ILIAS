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


/**
 * @param {Object} manifest  A json compacted representation of the root manifest of the cp with only one organization and all sequencing information in sequencing collection
 * @param {Object} cmidata   A json compacted representation of the user data of all SCOs corresponding to the content aggregation
 * @param {Object} ondeliver A function taking an item (activity) object parameter 
 * 
 * isRequestValid(requestId) => Boolean
 * getActivityState(itemId) => {completion, progress, attemptcount, firstaccessed, lastaccessed, duration}
 */  
function OP_SCORM_SEQUENCING_1_3(manifest, cmidata, ondeliver, onundeliver, onend, ondebug) {

	this.execNavigation = exec;
	
	this.startOrResume = function ()
	{
		exec({type : SuspendedActivity ? 'ResumeAll' : 'Start'});
	}

	//var RootActivity = manifest.organizations[0]; // maps to selected organization
	var RootActivity = manifest.item; // maps to selected organization
	var CurrentActivity = null;
	var SuspendedActivity = null;
	var deliveryStarted = null;
	var activityCount = 0;
	// jede Activity hat children member als {activityId: activityObject}
	// jede Activity hat index Attribute, das ist die Position in der Gesamtliste alle activities, wobei root=0
	// jede Activity hat availableChildren Array mit referenzen zu Activities
	// activityIsLeaf(activity) = !!activity.href 
	// Attribute mit doppelter Activity Nennung sind gekürzt: activity.ActiivtyProgressStatus => activity.ProgressStatus
	var activityMap = {}; // assoziativer Array aller Activities nach activityId
	var activityIndex = []; // numerischer Array aller Activities im Manifest
	var seqMap = {};
	
	var SetOfExitActions = /^exit$/i;
	var SetOfPostActions = /^exitParent|exitAll|retry|retryAll|continue|previous$/i;
	var SetOfSkippedActions = /^skip$/i;
	var SetOfStopForwardTraversalActions = /^stopForwardTraversal$/i;
	var SetOfHiddenFromChoiceActions = /^hiddenFromChoice$/i;
	var SetOfDisabledActions = /^disabled$/i;
	
	function walk(a, f) 
	{
		f(a);
		if (a.item) 
		{
			for (var k in a.item) 
			{
				walk(a.item[k], f);
			}  
		} 
	}
	
	function SeqObject(data) 
	{
		if (data) 
		{
			for (var k in data) 
			{
				this[k] = data[k];
			}
			if (this.rule.length) 
			{
				for (var i=this.rule.length-1; i>-1; i--)
				{
					if (this.rule[i].type==="rollup")
					{
						if (!this.hasOwnProperty("rollupRule")) this.rollupRule = []; 
						this.rollupRule.unshift(this.rule[i]);
						delete this.rule[i];
					}
				}
			}
		}
	}
	
	SeqObject.prototype = {
		// controlMode
		choice : true,
		choiceExit : true,
		flow : true,
		forwardOnly : false,
		useCurrentAttemptObjectiveInfo : true,
		useCurrentAttemptProgressInfo : true,
		// limit
		activityAbsoluteDurationLimit : 0,
		activityExperiencedDurationLimit : 0,
		attemptAbsoluteDurationLimit : 0,
		attemptExperiencedDurationLimit : 0,
		attemptLimit : 0,
		beginTimeLimit : 0,
		endTimeLimit : 0,
		// misc
		rule : [],
		rollupRule : [],
		objective : [],
		// randomization
		randomizationTiming : 'never',
		selectCount : null,
		reorderChildren : false,
		selectionTiming : 'never',
		// delivery
		tracked : true,
		completionSetByContent : false,
		objectiveSetByContent : false,
		// adlseq
		preventActivation : false,
		constrainChoice : false
	}

	/*
		activityAbsoluteDurationLimit	activityExperiencedDurationLimit	attemptAbsoluteDurationLimit	
		attemptExperiencedDurationLimit	attemptLimit	beginTimeLimit	choice	choiceExit	
		completionSetByContent	constrainChoice	endTimeLimit	flow	forwardOnly	
		measureSatisfactionIfActive	objectiveMeasureWeight	objectiveSetByContent	preventActivation	
		randomizationTiming	reorderChildren	requiredForCompleted	requiredForIncomplete	
		requiredForNotSatisfied	requiredForSatisfied	rollupObjectiveSatisfied	rollupProgressCompletion	
		selectCount	selectionTiming	tracked	useCurrentAttemptObjectiveInfo	useCurrentAttemptProgressInfo
	*/
	
	if (manifest.sequencing)
	{
		for (var i=0, ni=manifest.sequencing.length; i<ni; i+=1)
		{
			seqMap[manifest.sequencing[i].id] = new SeqObject(manifest.sequencing[i]);
			delete manifest.sequencing[i];
		}
	}
	for (var k in seqMap) 
	{
		if (seqMap[k].sequencingId)
		{
			var basedata = seqMap[seqMap[k].sequencingId];
			for (var kk in basedata) 
			{
				if (!seqMap[k].hasOwnProperty(kk) && basedata.hasOwnProperty(kk)) 
				{
					seqMap[kk] = basedata[kk];
				}
			}
		}
	}
	  
	walk(RootActivity, function (a) {
		a.index = activityIndex.length; 
		a.sequencing = a.sequencingId in seqMap ? seqMap[a.sequencingId] : SeqObject.prototype;
		activityIndex[a.index] = a;
		activityMap[a.id] = a;
		if (a.item) {
			var r = [];
			for (var i=0, ni=a.item.length; i<ni; i+=1) {
				a.item[i].parentActivity = a; 
				r.push(a.item[i]);
			}
			a.availableChildren = r;
		}
	});
			
	// read last activity as location of organization node
	var id = cmidata.getValue(RootActivity.foreignId, 'location') 
	if (id) setSuspendedActivity(activityMap[id]);
	
	activityCount = activityIndex.length; 
	
	/*
		returnValue Typen dokumentieren
		Länge von activityPath muss überall geprüft werden, welchen Elemente sollen enthalten sein
	*/
	

	/**
	 * @param string NavigationRequest "Exit", "Continue", etc.
	 * @return undefined
	 */
	 // noch eine Baustellen, da der Pseudocode hier unklar ist
	function exec(NavigationRequest) // #166 
	{

		if (ondebug) ondebug("exec", exec.caller);
		var rn, rd, rs, SequencingRequest;

		rn = NavigationRequestProcess(NavigationRequest);
		
		if (!rn.valid) 
		{
			return adlException('navigation request exception');
		}
		SequencingRequest = rn.SequencingRequest;
		
		if (rn.TerminationRequest)  
		{
			rt = TerminationRequestProcess(rn.TerminationRequest);
			if (!rt.valid) 
			{
				return adlException('termination request exception');
			}
			if (rt.SequencingRequest)
			{
				SequencingRequest = rt.SequencingRequest; 
			} 
		}
		
		if (SequencingRequest) 
		{
			rs = SequencingRequestProcess(SequencingRequest, activityMap[NavigationRequest.target]);
			if (!rs.valid) 
			{
				return adlException('sequencing request exception');
			}
			if (rs.EndSequencingSession) 
			{
				return endSequencingSession();
			} 
			if (rs.DeliveryRequest) 
			{
				rd = DeliveryRequestProcess(rs.DeliveryRequest);
				if (!rd.valid) 
				{
					return adlException('delivery request exception');
				}
				ContentDeliveryEnvironmentProcess(rs.DeliveryRequest);
			}
		} 

	}
	
	/**
	 * checks whether request is valid
	 * if so it returns a sequencing request name and termination request name	 	
	 * @param object e.g. {type: 'Choice', target: 'Isf34fd'}
	 * @return object {valid, SequencingRequest, TerminationRequest}
	 */
	 // scheint bis auf "Choice" ok, aber komprimierbar
	function NavigationRequestProcess(navReq) // #168
	{
		if (ondebug) ondebug("NavigationRequestProcess", NavigationRequestProcess.caller); 
		var returnValue = {valid: false, SequencingRequest: null, TerminationRequest: null};
		switch (navReq.type) 
		{
		case 'Start':
			if (!CurrentActivity) 
			{
				returnValue.valid = true;
				returnValue.SequencingRequest = 'Start';
			}
			else
			{
				returnValue.Exception = 'NB.2.1-1';
			}
			break;
		case 'ResumeAll':
			if (!CurrentActivity)
			{
				if (SuspendedActivity)
				{ 
					returnValue.valid = true;
					returnValue.SequencingRequest = 'ResumeAll';
				}
				else
				{
					returnValue.Exception = 'NB.2.1-3';
				}
			}
			else
			{
				returnValue.Exception = 'NB.2.1-1';
			}
			break;
		case 'Continue':
			if (!CurrentActivity) 
			{
				returnValue.Exception = 'NB.2.1-2';
			}
			else if (CurrentActivity.parentActivity && CurrentActivity.parentActivity.sequencing.flow)
			{
				returnValue.valid = true;
				returnValue.SequencingRequest = 'Continue';
				if (CurrentActivity.isActive)
				{
					returnValue.TerminationRequest = 'Exit';
				}
			} 
			else
			{
				returnValue.Exception = 'NB.2.1-4';
			} 
			break;
		case 'Previous':
			if (!CurrentActivity) 
			{
				returnValue.Exception = 'NB.2.1-2';
			}
			else if (CurrentActivity.parentActivity) 
			{
				if (CurrentActivity.parentActivity.sequencing.flow && !CurrentActivity.parentActivity.sequencing.forwardOnly)
				{
					returnValue.valid = true;
					returnValue.SequencingRequest = 'Previous';
					if (CurrentActivity.isActive)
					{
						returnValue.TerminationRequest = 'Exit';
					}
				}
				else
				{
					returnValue.Exception = 'NB.2.1-5';
				}
			}
			else
			{
				returnValue.Exception = 'NB.2.1-6';
			}
			break;
		case 'Forward':
		case 'Backward':
			returnValue.Exception = 'NB.2.1-7';
			break;
		case 'Choice':
			var target = activityMap[navReq.target]; 
			if (target) 
			{
				if (!target.parentActivity || target.parentActivity.sequencing.choice)
				{
					if (!CurrentActivity)
					{
						returnValue.valid = true;
						returnValue.SequencingRequest = 'Choice';
						returnValue.TargetActivity = target;
						break;
					}
					if (!isSibling(CurrentActivity, target))
					{						
						var activityPath = getCommonAncestorAndPath(CurrentActivity, target).activityPath; 
						if (activityPath.length) 
						{
							for (var i=0, ni=activityPath.length; i<ni; i+=1) 
							{
								var activity = activityPath[i];
								if (activity.isActive && activity.sequencing.choiceExit==="false")
								{
									returnValue.Exception = 'NB.2.1-8';
									break;
								}
							}
						}
						else
						{
							returnValue.Exception = 'NB.2.1-9';
							break;
						}
					}
					returnValue.valid = true;
					returnValue.SequencingRequest = 'Choice';
					returnValue.TargetActivity = target;
					if (CurrentActivity.isActive)
					{
						returnValue.TerminationRequest = 'Exit';
					}
					break;
				}
				else
				{
					returnValue.Exception = 'NB.2.1-10';
					break;
				}
			}
			else
			{
				returnValue.Exception = 'NB.2.1-11';
				break;
			}
			break;
		case 'Exit':
			if (CurrentActivity) 
			{
				if (CurrentActivity.isActive) 
				{
					returnValue.valid = true;
					returnValue.SequencingRequest = 'Exit';
					returnValue.TerminationRequest = 'Exit';
				}
				else
				{
					returnValue.Exception = 'NB.2.1-12';
				}			
			}
			else
			{
				returnValue.Exception = 'NB.2.1-2';
			} 
			break;
		case 'ExitAll':
			if (CurrentActivity) 
			{
				returnValue.valid = true;
				returnValue.SequencingRequest = 'Exit';
				returnValue.TerminationRequest = 'ExitAll';
			}
			else
			{
				returnValue.Exception = 'NB.2.1-2';
			}
			break;
		case 'Abandon':
			if (CurrentActivity) 
			{
				if (CurrentActivity.isActive) 
				{
					returnValue.valid = true;
					returnValue.SequencingRequest = 'Exit';
					returnValue.TerminationRequest = 'Abandon';
				}
				else
				{
					returnValue.Exception = 'NB.2.1-12';
				}
			}
			else
			{
				returnValue.Exception = 'NB.2.1-2';
			} 
			break;
		case 'AbandonAll':
		case 'SuspendAll':
			if (CurrentActivity) 
			{
				returnValue.valid = true;
				returnValue.SequencingRequest = 'Exit';
				returnValue.TerminationRequest = navReq.type;
			}
			else
			{
				returnValue.Exception = 'NB.2.1-2';
			} 
			break;
		}
		return returnValue;
	} // end NavigationRequestProcess
	
	
	function SequencingExitActionRulesSubprocess () // #174
	{
		if (ondebug) ondebug("SequencingExitActionRulesSubprocess", SequencingExitActionRulesSubprocess.caller); 
		var activityPath = getActivityPath(CurrentActivity);
		var activity, exitTarget;
		while (activityPath.length > 1)
		{
			activity = activityPath.pop();
			exitTarget = SequencingRulesCheckProcess(activity, SetOfExitActions);
			if (exitTarget)
			{
				break;
			}		
		}
		if (exitTarget) 
		{
			TerminateDescendentAttemptsProcess(exitTarget);
			EndAttemptProcess(exitTarget);
			CurrentActivity = exitTarget; 		
		}
	}
	
	
	function SequencingPostConditionRulesSubprocess() // #175
	{
		if (ondebug) ondebug("SequencingPostConditionRulesSubprocess", SequencingPostConditionRulesSubprocess.caller); 
		var returnValue = {TerminationRequest : null, SequencingRequest : null};
		if (CurrentActivity.isSuspended)
		{
			return; 
			// ??? oder return returnValue???
		}
		var r = SequencingRulesCheckProcess(CurrentActivity, SetOfPostActions);
		switch (r) 
		{
		case 'Retry':
		case 'Continue':
		case 'Previous':
			returnValue.SequencingRequest = r;
			break;
		case 'ExitParent':
		case 'ExitAll':
			returnValue.TerminationRequest = r;
			break;
		case 'RetryAll':
			returnValue.SequencingRequest = 'Retry';
			returnValue.TerminationRequest = 'ExitAll;';
			break;
		}
		return returnValue;
	}
	

	/**
	 * @param string "Exit", "SuspendAll" etc.
	 * @return {valid, SequencingRequest, exception}
	 */	
	function TerminationRequestProcess(termReq) // #176
	{
		if (ondebug) ondebug("TerminationRequestProcess", TerminationRequestProcess.caller); 
		var returnValue = {valid : false, SequencingRequest : null, exception: null};
		var r, exit, activityPath, activity, seqRule = null;
		if (!CurrentActivity) 
		{
			returnValue.exception = 'TB.2.3-1';
			return returnValue;
		}
		else if ((termReq=='Exit' || termReq=='Abandon') && !CurrentActivity.isActive) 
		{
			returnValue.exception = 'TB.2.3-2';
			return returnValue;
		}
		switch (termReq) 
		{
		case 'Exit':
			EndAttemptProcess(CurrentActivity);
			SequencingExitActionRulesSubprocess(CurrentActivity);
			do {
				exit = false;
				seqRule = SequencingPostConditionRulesSubprocess();
				if (seqRule.TerminationRequest == 'ExitAll')
				{
					returnValue.TerminationRequest = 'ExitAll';
					break; // to the next case ??? oder return TerminationRequestProcess('ExitAll')
				}
				if (seqRule.TerminationRequest=='ExitParent')
				{
					if (CurrentActivity.parentActivity)
					{
						CurrentActivity = CurrentActivity.parentActivity;
						EndAttemptProcess(CurrentActivity);
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
			returnValue.SequencingRequest = seqRule.SequencingRequest;
			return returnValue;
		case 'ExitAll':
			if (CurrentActivity.isActive)
			{
				r = EndAttemptProcess(CurrentActivity);
				r = TerminateDescendentAttemptsProcess(RootActivity);
				r = EndAttemptProcess(RootActivity);
				CurrentActivity = RootActivity;
				returnValue.valid = true;
				returnValue.SequencingRequest = seqRule; /// ?? nur bei sprung in case valide!!! 
				return returnValue;
			} 
			break;
		case 'SuspendAll':
			if (CurrentActivity.isActive || CurrentActivity.isSuspended)
			{
				setSuspendedActivity(CurrentActivity);
			}
			else
			{
				if (CurrentActivity.parentActivity) 
				{
					setSuspendedActivity(CurrentActivity.parentActivity);
				}
				else // RootActivity
				{
					returnValue.exception = 'TB.2.3-3';
					return returnValue;
				}
			}
			activityPath = getActivityPath(SuspendedActivity);
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
			CurrentActivity = RootActivity;
			returnValue.valid = true;
			returnValue.SequencingRequest = 'Exit';
			return returnValue;
		case 'Abandon':
			CurrentActivity.isActive = false;
			returnValue.valid = true;
			return returnValue;
		case 'AbandonAll':
			activityPath = getActivityPath(CurrentActivity);
			if (activityPath.length===0)
			{
				returnValue.Exception = 'TB.2.3-6';
				return returnValue;
			}
			while (activityPath.length) 
			{
				activity = activityPath.shift();
				activity.isActive = false;
			}
			CurrentActivity = RootActivity;
			returnValue.valid = true;
			return returnValue;
		default: 
			returnValue.exception = 'TB.2.3-7';
			return returnValue;
		}
		
	} // end TerminationRequestProcess
	
	
	function MeasureRollupProcess(activity) // #180
	{
		if (ondebug) ondebug("MeasureRollupProcess", MeasureRollupProcess.caller); 
		var totalWeightedMeasure = 0;
		var countedMeasures = 0;
		var targetObjective = getTargetObjective(activity);
		if (targetObjective)
		{
			for (var activityId in activity.item)
			{
				var child = activity.item[activityId];
				if (child.sequencing.tracked) 
				{
					var rolledUpObjective = undefined;
					var objectives = child.sequencing.objective;
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
	
	
	function ObjectiveRollupUsingMeasureProcess(activity) // #182
	{
		if (ondebug) ondebug("ObjectiveRollupUsingMeasureProcess", ObjectiveRollupUsingMeasureProcess.caller); 
		var targetObjective = getTargetObjective(activity);
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
	
	function ObjectiveRollupUsingRulesProcess(activity) // #184
	{
		if (ondebug) ondebug("ObjectiveRollupUsingRulesProcess", ObjectiveRollupUsingRulesProcess.caller); 
		var targetObjective = getTargetObjective(activity);
		if (targetObjective) 
		{
			if (RollupRuleCheckSubprocess(activity, /^notSatisfied$/))
			{
				targetObjective.ObjectiveProgressStatus = true;
				targetObjective.ObjectiveSatisfiedStatus = false; 
			}
			if (RollupRuleCheckSubprocess(activity, /^satisfied$/))
			{
				targetObjective.ObjectiveProgressStatus = true;
				targetObjective.ObjectiveSatisfiedStatus = true; 
			}
		}
	}
	
	
	function ActivityProgressRollupProcess(activity) // #185
	{
		if (ondebug) ondebug("ActivityProgressRollupProcess", ActivityProgressRollupProcess.caller); 
		if (RollupRuleCheckSubprocess(activity, /^incomplete$/))
		{
			activity.AttemptProgressStatus = true;
			activity.AttemptCompletionStatus = false;
		}
		if (RollupRuleCheckSubprocess(activity, /^complete$/))
		{
			activity.AttemptProgressStatus = true;
			activity.AttemptCompletionStatus = true;
		}	
	}
	
	
	function RollupRuleCheckSubprocess(activity, RollupAction) // #186
	{
		if (ondebug) ondebug("RollupRuleCheckSubprocess", RollupRuleCheckSubprocess.caller); 
		var rules = getRulesByAction(activity.sequencing.rollupRule, RollupAction);
		var statusChange = false;
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
					if (child.sequencing.tracked)
					{
						if (CheckChildForRollupSubprocess(child, RollupAction))
						{
							switch (EvaluateRollupConditionsSubprocess(child, RollupAction))
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
	
	
	function EvaluateRollupConditionsSubprocess(activity, RollupConditions) // #188
	{
		if (ondebug) ondebug("EvaluateRollupConditionsSubprocess", EvaluateRollupConditionsSubprocess.caller); 
		var bag = {
			satisfied:0, objectiveStatusKnown:0, objectiveMeasureKnown:0, completed:0, 
			activityProgressKnown:0, attempted:0, attemptLimitExceeded:0, timeLimitExceeded:0, 
			outsideAvailableTimeRange:0
		};
		var bagcount = 0;
		for (var i=0, ni=RollupConditions.length; i<ni; i+=1)
		{
			var condition = RollupConditions[i];
			/// ??? Evaluate the rollup condition by applying the appropriate tracking
			/// ??? information for the activity to the Rollup Condition
			/// ??? pseudo code for evaluate rollup conditions subprocess
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
		/// ??? ConditionCombination 
		return ConditionCombination(bag);
	}
	
	
	/**
	 * @return {Boolean}
	 */
	function CheckChildForRollupSubprocess(activity, RollupAction) // #189
	{
		if (ondebug) ondebug("CheckChildForRollupSubprocess", CheckChildForRollupSubprocess.caller); 
		var included = false;
		if (RollupAction==='satisfied' || RollupAction==='notSatisfied')
		{
			if (activity.sequencing.objectiveSatisfied)
			{
				included = true;
				if ((RollupAction==='satisfied' && activity.requiredForSatisfied==='ifNotSuspended') ||
					(RollupAction==='notSatisfied' && activity.requiredForNotSatisfied==='ifNotSuspended') )
				{
					if (activity.AttemptCount > 0 && activity.isSuspended)
					{
						included = false;
					}
				}
				else
				{
					if ((RollupAction==='satisfied' && activity.requiredForSatisfied==='ifAttempted') ||
						(RollupAction==='notSatisfied' && activity.requiredForNotSatisfied==='ifAttempted') )
					{
						if (activity.AttemptCount > 0 && activity.isSuspended)
						{
							included = false;
						}					
					}
					else
					{
						if ((RollupAction==='satisfied' && activity.requiredForSatisfied==='ifNotSkipped') ||
							(RollupAction==='notSatisfied' && activity.requiredForNotSatisfied==='ifNotSkipped') )
						{
							if (activity.AttemptCount > 0 && activity.isSuspended)
							{
								if (SequencingRulesCheckProcess(activity, SetOfSkippedActions))
								{
									included = false;
								}
							}				
						}				
					}
				}
			}
		}
		if (RollupAction==='completed' || RollupAction==='incomplete')
		{
			if (activity.RollupProgressCompletion)
			{
				included = true;
				if ((RollupAction==='completed' && activity.requiredForCompleted==='ifNotSuspended') ||
					(RollupAction==='incomplete' && activity.requiredForIncomplete==='ifNotSuspended') )
				{
					if (activity.attemptCount>0 && activity.isSuspended)
					{
						included = false;					
					}
				}
				else
				{
					if ((RollupAction==='completed' && activity.requiredForCompleted==='ifAttempted') ||
						(RollupAction==='incomplete' && activity.requiredForIncomplete==='ifAttempted') )
					{
						if (activity.attemptCount>0 && activity.isSuspended)
						{
							included = false;					
						}
					}
					else
					{
						if ((RollupAction==='completed' && activity.requiredForCompleted==='ifNotSkipped') ||
							(RollupAction==='incomplete' && activity.requiredForIncomplete==='ifNotSkipped') )
						{
							if (SequencingRulesCheckProcess(activity, SetOfSkippedActions))
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
	
	
	function OverallRollupProcess(activity) // #191
	{
		if (ondebug) ondebug("OverallRollupProcess", OverallRollupProcess.caller); 
		var activityPath = getActivityPath(activity);
		//if (activityPath.length > 0) // kann gar nicht leer sein, da Root enthalten 
		//{
			for (var i=0, ni=activityPath.length; i<ni; i+=1)
			{
				var a = activityPath[i];
				MeasureRollupProcess(a);
				// ObjectiveRollupProcess(a); // Apply the appropriate Objective Rollup Process to the activity ????
				ObjectiveRollupUsingMeasureProcess(a); 
				ObjectiveRollupUsingRulesProcess(a); 				
				ActivityProgressRollupProcess(a);
			}
		//}
	} 
	
	
	function SelectChildrenProcess (activity) // #192
	{
		if (ondebug) ondebug("SelectChildrenProcess", SelectChildrenProcess.caller); 
		if (activity.item.length > 0 &&
			!activity.isSuspended && !activity.isActive &&
			activity.SelectionTiming==='once' &&
			!activity.ActivityProgressStatus && 
			activity.SelectionCountStatus) 
		{
			activity.availableChildren = selectRandomItems(activity.item, activity.SelectionCount);				
		} 
	}
	
	
	function RandomizeChildrenProcess (activity) // #193
	{
		if (ondebug) ondebug("RandomizeChildrenProcess", RandomizeChildrenProcess.caller); 
		if (activity.item.length>0 &&
			!activity.isSuspended && 
			!activity.isActive) 
		{
			if (((activity.RandomizationTiming==='once' && !activity.ProgressStatus) ||
				activity.RandomizationTiming==='onEachNewAttempt') && 
				activity.RandomizeChildren)
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
	function FlowTreeTraversalSubprocess(activity, traversalDirection, considerChildren, previousTraversalDirection) // #194
	{
		if (ondebug) ondebug("FlowTreeTraversalSubprocess", FlowTreeTraversalSubprocess.caller); 
		var returnValue = {identifiedActivity: null, traversalDirection : null, exception: null};
		var reversedDirection = false;
		if (previousTraversalDirection==='backward' && isLastOfAvailableChildren(activity)) 
		{
			traversalDirection = 'backward';
			////
			// ???? activity is the first activity in the activity’s parent’s list of Available Children
			//--activity = activity.parentActivity.availableChildren[0];
			////
			reversedDirection = true;
		}
		if (traversalDirection==='forward') 
		{
			if (activity.index === activityCount-1) //3,1
			{
				returnValue.exception = 'SB.2.1-1';
				return returnValue;
			} 
			if (!activity.item || activity.item.length===0 || !considerChildren) //3,2 ??????
			{
				// search for current item from back to front
				var siblings = activity.parentActivity.availableChildren;
				for (var i=siblings.length-1;i>-1; i--)
				{
					if (siblings[i]===activity) break;
				}
				if (i==siblings.length-1) // last one, one up
				{
					return FlowTreeTraversalSubprocess(activity.parentActivity, 'forward', false, null);
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
				if (reversedDirection===false && activity.parentActivity.sequencing.forwardOnly)
				{
					returnValue.exception = 'SB.2.1-4';
					return returnValue;
				}
				for (var i=activity.parentActivity.availableChildren.length-1;i>-1; i--)
				{
					if (activity.parentActivity.availableChildren[i]===activity) break;
				}
				if (i===0) // is first activity
				{
					return FlowTreeTraversalSubprocess(activity.parentActivity, 'backward', false, null);
				}
				else // non first activity
				{
					returnValue.identifiedActivity = activity.parentActivity.availableChildren[i-1];
					returnValue.traversalDirection = traversalDirection;
					return returnValue;
				}
			}
			else
			{
				if (activity.availableChildren && activity.availableChildren.length) 
				{
					if (activity.sequencing.forwardOnly)
					{
						returnValue.identifiedActivity = activity.parentActivity.availableChildren[0];
						returnValue.traversalDirection = 'forward';
						return returnValue;				
					}
					else
					{
						returnValue.identifiedActivity = activity.parentActivity.availableChildren[activity.parentActivity.availableChildren.length-1];
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
	Flow Activity Traversal Subprocess [SB.2.2]
	@param activity
	@param a traversal direction
	@param a previous traversal direction
	@return object the ‘next’ activity in a directed traversal of the activity tree ("identifiedActivity")
		and True if the activity can be delivered ("deliverable")
	*/
	function FlowActivityTraversalSubprocess(activity, traversalDirection, previousTraversalDirection) // #197
	{
		if (ondebug) ondebug("FlowActivityTraversalSubprocess", FlowActivityTraversalSubprocess.caller); 
		var r;
		if (!activity.sequencing.flow)
		{
			return {deliverable: false, identifiedActivity: activity, exception: 'SB.2.2-1'};
		}
		r = SequencingRulesCheckProcess(activity, SetOfSkippedActions);
		if (r) // skipped
		{
			r = FlowTreeTraversalSubprocess(activity, traversalDirection, false, previousTraversalDirection);
			if (!r.identifiedActivity)
			{
				return {deliverable: false, identifiedActivity: activity, exception: r.exception};
			}
			else
			{
				return FlowActivityTraversalSubprocess(
					r.identifiedActivity, 
					traversalDirection,
					(traversalDirection==='backward' && r.traversalDirection==='backward') ? null : r.previousTraversalDirection);
			}
		}
		if (CheckActivityProcess(activity))
		{
			return {deliverable: false, identifiedActivity: activity, exception: 'SB.2.2-2'};
		}
		if (!isLeaf(activity)) 
		{
			r = FlowTreeTraversalSubprocess(activity, traversalDirection, true, null);
			if (!r.identifiedActivity)
			{
				return {deliverable: false, identifiedActivity: activity, exception: r.exception};
			}
			else
			{
				if (traversalDirection==='backward' && r.traversalDirection==='forward')
				{
					return FlowActivityTraversalSubprocess(r.identifiedActivity, 'forward', 'backward');
				}
				else 
				{
					return FlowActivityTraversalSubprocess(r.identifiedActivity, traversalDirection, null);
				}
			}
		}
		return {deliverable: true, identifiedActivity: activity};
	}
	
	
	function FlowSubprocess(activity, traversalDirection, considerChildren) // #199
	{
		if (ondebug) ondebug("FlowSubprocess", FlowSubprocess.caller); 
		var returnValue = {identifiedActivity: null, deliverable: null, exception: null};
		var candidateActivity = activity;
		var r = FlowTreeTraversalSubprocess(candidateActivity, traversalDirection, considerChildren, null);
		if (!r.identifiedActivity) 
		{
			returnValue.identifiedActivity = candidateActivity;
			returnValue.deliverable = false;
		}
		else
		{
			candidateActivity = r.identifiedActivity;
			r = FlowActivityTraversalSubprocess(candidateActivity, traversalDirection, null);
			returnValue.identifiedActivity = r.identifiedActivity;
			returnValue.deliverable = r.deliverable;
		}
		returnValue.exception = r.exception;
		return returnValue;	
	}
	
	
	function ChoiceActivityTraversalSubprocess(activity, traversalDirection) // #200
	{
		if (ondebug) ondebug("ChoiceActivityTraversalSubprocess", ChoiceActivityTraversalSubprocess.caller); 
		var r, returnValue = {reachable: false, exception: null}; 
		if (traversalDirection=='forward')
		{
			r = SequencingRulesCheckProcess(activity, SetOfStopForwardTraversalActions);
			if (r) 
			{
				returnValue.exception = 'SB.2.4-1';
				return returnValue;
			}
		}
		else if (traversalDirection=='backward')
		{
			if (activity.parentActivity) 
			{
				if (activity.parentActivity.sequencing.forwardOnly)
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
	 * @return {Object} Defaults to {DeliveryRequest: undefined, exception: undefined}
	 */ 
	function StartSequencingRequestProcess() // #201
	{
		if (ondebug) ondebug("StartSequencingRequestProcess", StartSequencingRequestProcess.caller); 
		if (CurrentActivity)
		{
			return {exception : 'SB.2.5-1'};
		}
		if (RootActivity.item.length===0) 
		{
			return {DeliveryRequest:RootActivity};
		}
		else 
		{
			var r = FlowSubprocess(RootActivity, 'forward', true);
			if (r.exception)
			{
				return {exception: r.exception};
			}
			else
			{
				return {DeliveryRequest: r.identifiedActivity};
			}
		} 	
	}
	
	
	function ResumeAllSequencingRequestProcess() // #202
	{
		if (ondebug) ondebug("ResumeAllSequencingRequestProcess", ResumeAllSequencingRequestProcess.caller); 
		if (CurrentActivity)
		{
			return {exception : 'SB.2.6-1'};
		}
		else if (!SuspendedActivity)
		{
			return {exception : 'SB.2.6-2'};
		}
		return {DeliveryRequest: SuspendedActivity};
	}
	
	
	function ContinueSequencingRequestProcess() // #203
	{
		if (ondebug) ondebug("ContinueSequencingRequestProcess", ContinueSequencingRequestProcess.caller); 
		if (!CurrentActivity)
		{
			return {exception : 'SB.2.7-1'};
		}
		else if (CurrentActivity!=RootActivity)
		{
			if (!CurrentActivity.parentActivity.sequencing.flow) 
			{
				return {exception : 'SB.2.7-2'};
			}
		}
		var r = FlowSubprocess(CurrentActivity, 'forward', false);
		if (r.exception)
		{
			return {exception : r.exception};
		}
		else
		{
			return {DeliveryRequest: r.identifiedActivity};
		}
	}
	
	
	function PreviousSequencingRequestProcess()  // #204
	{
		if (ondebug) ondebug("PreviousSequencingRequestProcess", PreviousSequencingRequestProcess.caller); 
		if (!CurrentActivity)
		{
			return {exception : 'SB.2.8-1'};
		}
		else if (CurrentActivity!=RootActivity)
		{
			if (!CurrentActivity.parentActivity.sequencing.flow) 
			{
				return {exception : 'SB.2.8-2'};
			}
		}
		var r = FlowSubprocess(CurrentActivity, 'backward', false);
		if (r.exception)
		{
			return {exception : r.exception};
		}
		else
		{
			return {DeliveryRequest: r.identifiedActivity};
		}	
	}
	
	
	/**
	 * @return {Object} Defaults to {deliveryRequest: undefined, exception: undefined}
	 */ 
	function ChoiceSequencingRequestProcess(targetActivity)  // #205
	{
		if (ondebug) ondebug("ChoiceSequencingRequestProcess", ChoiceSequencingRequestProcess.caller); 
		var r, a, commonAncestor, commonPath, activityPath, traverse, activityList, consideredActivity ;
		if (!targetActivity) 
		{
			return {exception: 'SB.2.9-1'};
		}
		if (!isRoot(targetActivity)) 
		{
			if (indexOf(targetActivity.parentActivity.availableChildren, targetActivity)===-1) 
			{
				return {exception: 'SB.2.9-2'};
			}
		}
		activityPath = getActivityPath(targetActivity);
		for (var i=activityPath.length-1; i>-1; i--)
		{
			a = activityPath[i];
			r = SequencingRulesCheckProcess(a, SetOfHiddenFromChoiceActions);
			if (r)
			{
				return {exception: 'SB.2.9-3'};
			}
		}
		if (!isRoot(targetActivity)) 
		{
			if (!targetActivity.parentActivity.sequencing.choice) 
			{
				return {exception: 'SB.2.9-4'};
			}
		}
		if (CurrentActivity)
		{
			r = getCommonAncestorAndPath(CurrentActivity, targetActivity);
			commonAncestor = r.commonAncestor;
			activityPath = r.activityPath;
		}
		else
		{
			commonAncestor = RootActivity;
		}
		if (CurrentActivity==targetActivity) // 8
		{
		}
		else if (!CurrentActivity || CurrentActivity==commonAncestor) // 10 
		{
			if (activityPath.length===0)
			{
				return {exception: 'SB.2.9-5'};
			}
			while (activityPath.length)
			{
				a = activityPath.pop();
				r = ChoiceActivityTraversalSubprocess(a, 'forward');
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
		else if (isInArray(targetActivity.id, CurrentActivity.parentActivity.item, 'id')) // 9 (needs CurrentActivity, so better after case "10")
		{
			activityList = [];
			// ??? prüfen, ob das die richtigen Elemente sind
			for (var i = Math.min(targetActivity.index, CurrentActivity.index), ni = Math.max(targetActivity.index, CurrentActivity.index); i<ni; i+=1)
			{
				activityList.push(activityIndex[i]);
			}
			if (activityList.length===0) 
			{
				return {exception: 'SB.2.9-5'};
			}
			traverse = targetActivity.index > CurrentActivity.index ? 'forward' : 'backward';
			while (activityList.length)
			{
				r = ChoiceActivityTraversalSubprocess(activityList.pop(), traverse);
				if (r.exception)
				{
					return {exception: r.exception};
				} 
			}
		} 
		else if (targetActivity==commonAncestor) 
		{
			/// ?? prüfen ob der Pfad richtig ist
			if (activityPath.length===0)
			{
				return {exception: 'SB.2.9-5'};
			}
			// suche nach constrained activity
			a = activityPath.unshift(); // letzte wegwerfen
			var constrainedActivity = a.sequencing.constrainChoice ? a : null; ///??? letztes zuerst auswerten erlaubt?
			while (activityPath.length)
			{
				a = activityPath.pop();
				if (a.sequencing.choiceExit==="false")
				{
					return {exception: 'SB.2.9-7'};
				}
				if (!constrainedActivity && a.sequencing.constrainChoice)
				{
					constrainedActivity = a;
				}
			}
			if (constrainedActivity)
			{
				traverse = targetActivity.index > constrainedActivity.index ? 'forward' : 'backward';
				r = ChoiceFlowSubprocess(constrainedActivity, traverse);
				consideredActivity = r.identifiedActivity;
				if (!isAvailableDescendent(consideredActivity, targetActivity) && 
					(targetActivity!=constrainedActivity || targetActivity!=consideredActivity))
				{
					return {exception: 'SB.2.9-8'};
				}
			}
		}
		else if (targetActivity.index > commonAncestor.index) // 12 
		{
			activityPath.push(commonAncestor);
			if (activityPath.length===0)
			{
				return {exception: 'SB.2.9-5'};
			}
			constrainedActivity = null;
			for (var i=0, ni=activityPath.length-1; i<=ni; i++)  // 12.4
			{
				a = activityPath[i];
				if (i!==ni && a.sequencing.choiceExit === "false") 
				{
					return {exception: 'SB.2.9-7'};
				}
				if (!constrainedActivity && a.sequencing.constrainChoice) 
				{
					constrainedActivity = a;
				} 
			}
			if (constrainedActivity) // 12.5
			{
				traverse = targetActivity.index > constrainedActivity.index ? 'forward' : 'backward';
				r = ChoiceFlowSubprocess(constrainedActivity, traverse);
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
			if (targetActivity.index > CurrentActivity.index) 
			{
				while (activityPath.length)
				{
					a = activityPath.pop();
					r = ChoiceActivityTraversalSubprocess(a, 'forward');
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
			return {DeliveryRequest: targetActivity};
		}
		r = FlowSubprocess(targetActivity, 'forward', true);
		if (!r.deliverable)
		{
			TerminateDescendentAttemptsProcess(commonAncestor);
			EndAttemptProcess(commonAncestor);
			CurrentActivity = targetActivity;
			return {exception: 'SB.2.9-9'};
		}
		else
		{
			return {DeliveryRequest: r.identifiedActivity};
		} 
	}
	
	
	function ChoiceFlowSubprocess (activity, traversalDirection)  // #212
	{
		if (ondebug) ondebug("ChoiceFlowSubprocess", ChoiceFlowSubprocess.caller); 
		var identifiedActivity = ChoiceFlowTreeTraversalSubprocess(activity, traversalDirection);
		return identifiedActivity ? identifiedActivity : activity;
	}
	
	/**
	 * @return {String} activityId
	 */ 
	function ChoiceFlowTreeTraversalSubprocess(activity, traversalDirection) // #213
	{
		if (ondebug) ondebug("ChoiceFlowTreeTraversalSubprocess", ChoiceFlowTreeTraversalSubprocess.caller); 
		if (traversalDirection === 'forward')
		{
			if (activity.index===activityCount-1)
			{
				return;
			}
			if (isLastOfAvailableChildren(activity))
			{
				return ChoiceFlowTreeTraversalSubprocess(activity.parentActivity, 'forward');
			}
			else
			{
				///Traverse the tree, forward preorder, one activity to the next
				///activity, in the activity’s parent’s list of Available Children
				r = traverse(activity, 'forward');
				ChoiceFlowTreeTraversalSubprocess(r);
			}
		}
		if (traversalDirection === 'backward')
		{
			if (isRoot(activity))
			{
				return;
			}
			if (activity == activity.parentActivity.availableChildren[0])
			{
				return ChoiceFlowTreeTraversalSubprocess(activity.parentActivity, 'backward');
			}
			else
			{
				///Traverse the tree, reverse preorder, one activity to the previous
				///activity, from the activity’s parent’s list of Available Children
				r = traverse(activity, 'backward');
				ChoiceFlowTreeTraversalSubprocess(r);
			}
		}	
	}
		
		
	function RetrySequencingRequestProcess() // #214
	{
		if (ondebug) ondebug("RetrySequencingRequestProcess", RetrySequencingRequestProcess.caller); 
		if (!CurrentActivity)
		{
			return {exception: 'SB.2.10-1'};
		}
		if (CurrentActivity.isActive || CurrentActivity.isSuspended)
		{
			return {exception: 'SB.2.10-2'};
		}
		if (!isLeaf(CurrentActivity))
		{
			var r = FlowSubprocess(CurrentActivity, 'forward', true);
			if (r.exception)
			{
				return {exception: 'SB.2.10-3'};
			}
			else
			{
				return {DeliveryRequest: r.identifiedActivity};
			}
		}
		else
		{
			return {DeliveryRequest: CurrentActivity};
		}
	}
		
		
	function ExitSequencingRequestProcess() // #215
	{
		if (ondebug) ondebug("ExitSequencingRequestProcess", ExitSequencingRequestProcess.caller); 
		if (!CurrentActivity)
		{
			return {EndSequencingSession: false, exception: 'SB.2.11-1'};
		}
		else if (CurrentActivity.isActive)
		{
			return {EndSequencingSession: false, exception: 'SB.2.11-2'};
		}
		else if (CurrentActivity == RootActivity)
		{
			return {EndSequencingSession: true};
		}
		return {EndSequencingSession: false};
	}
	
		
	function SequencingRequestProcess(sequencingRequest, target) // #216
	{
		if (ondebug) ondebug("SequencingRequestProcess", SequencingRequestProcess.caller); 
		var r;
		switch (sequencingRequest)
		{
		case 'Start':
			r = StartSequencingRequestProcess();
			break;
		case 'ResumeAll':
			r = ResumeAllSequencingRequestProcess();
			break;
		case 'Exit':
			r = ExitSequencingRequestProcess();
			break;
		case 'Retry':
			r = RetrySequencingRequestProcess();
			break;
		case 'Continue':
			r = ContinueSequencingRequestProcess();
			break;
		case 'Previous':
			r = PreviousSequencingRequestProcess();
			break;
		case 'Choice':
			r = ChoiceSequencingRequestProcess(target);
			break;
		}
		if (!r || r.exception)
		{
			return {valid: false, exception: r ?	r.exception : 'SB.2.12-1'};
		}
		else
		{
			return {valid: true, DeliveryRequest: r.DeliveryRequest, EndSequencingSession: r.EndSequencingSession};
		}
	}
	
	
	function DeliveryRequestProcess(deliveryRequest) // #219
	{
		if (ondebug) ondebug("DeliveryRequestProcess", DeliveryRequestProcess.caller); 
		if (!isLeaf(deliveryRequest)) 
		{
			return {DeliveryRequest: false, exception: 'DB.1.1-1'};
		} 
		var activityPath = getActivityPath(deliveryRequest); 
		if (activityPath.length===0)
		{
			return {DeliveryRequest: false, exception: 'DB.1.1-2'};
		}
		for (var i=activityPath.length-1; i>-1; i-=1)
		{
			if (CheckActivityProcess(activityPath[i]))
			{
				return {DeliveryRequest: false, exception: 'DB.1.1-3'};
			}
		}
		return {valid: true};
	}
		
	
	function ContentDeliveryEnvironmentProcess(deliveryRequest) // #220
	{
		if (ondebug) ondebug("ContentDeliveryEnvironmentProcess", ContentDeliveryEnvironmentProcess.caller); 
		if (CurrentActivity && CurrentActivity.isActive)
		{
			return {exception: 'DB.2-1'};
		}
		if (deliveryRequest != SuspendedActivity)
		{
			ClearSuspendedActivitySubprocess(deliveryRequest);
		}
		TerminateDescendentAttemptsProcess(deliveryRequest);
		//var activityPath = deliveryRequest; /// ???
		var activityPath = getActivityPath(deliveryRequest); /// ???
		while (activityPath.length) 
		{
			var a = activityPath.pop();
			if (!a.isActive)
			{
				if (a.sequencing.tracked)
				{ 
					if (a.isSuspended)
					{
						a.isSuspended = false;
					}
					else
					{
						a.AttemptCount+=1;
						if (a.AttemptCount===1)
						{
							a.ProgressStatus = true;
						}
						///???Initialize Objective Progress Information and Attempt
						///???Progress Information required for the new attempt
					}
				}
				a.isActive = true;
			}
		}
		CurrentActivity = deliveryRequest;
		
		///???Once the delivery of the activity’s content resources and auxiliary resources begins

		/// ??? The Objective and Attempt Progress information for the activity should not be recorded during delivery
		ondeliver(CurrentActivity);
		/// ??? The delivery environment begins tracking the Attempt Absolute Duration and the Attempt Experienced Duration
		deliveryStarted = currentTimePoint();
		if (CurrentActivity.sequencing.tracked)
		{
			// ??
		}
		return {};
	}
		
	
	function ClearSuspendedActivitySubprocess(activity) // #222
	{
		if (ondebug) ondebug("ClearSuspendedActivitySubprocess", ClearSuspendedActivitySubprocess.caller); 
		if (SuspendedActivity)
		{
			var activityPath = getCommonAncestorAndPath(activity, SuspendedActivity).activityPath;
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
	 * @return {Boolean} True if any of the activity’s limit conditions have been violated
	 */ 
	function LimitConditionsCheckProcess(activity) // #223
	{
		if (ondebug) ondebug("LimitConditionsCheckProcess", LimitConditionsCheckProcess.caller);
		var limit; 
		if (!activity.sequencing.tracked)
		{
			return false;
		}
		if (cmidata.getValue(activity.foreignId, 'activityProgressState'))
		{
			if (limit = Number(activity.sequencing.attemptLimit))
			{
				if (Number(cmidata.getValue(activity.foreignId, 'attemptCount')) >= limit)
				{
					return true;
				}
			}
			if (limit = Number(activity.sequencing.activityAbsoluteDurationLimit))
			{
				if (Number(cmidata.getValue(activity.foreignId, 'activityAbsoluteDuration')) >= limit)
				{
					return true;
				}
			}
			if (limit = Number(activity.sequencing.activityExperiencedDurationLimit))
			{
				if (Number(cmidata.getValue(activity.foreignId, 'activityExperiencedDuration')) >= limit)
				{
					return true;
				}
			}
			if (cmidata.getValue(activity.foreignId, 'attemptProgressState'))
			{
				if (limit = Number(activity.sequencing.attemptAbsoluteDurationLimit))
				{
					if (Number(cmidata.getValue(activity.foreignId, 'attemptAbsoluteDuration')) >= limit)
					{
						return true;
					}
				}
				if (limit = Number(activity.sequencing.attemptExperiencedDurationLimit))
				{
					if (Number(cmidata.getValue(activity.foreignId, 'attemptExperiencedDuration')) >= limit)
					{
						return true;
					}
				}
			}
		}
		/*
		// At this time, the SCORM does not require an LMS to implement
		// time related sequencing decisions based on this value.
		if (activity.sequencing.beginTimeLimit)
		{
			if (currentTimePoint() < activity.sequencing.beginTimeLimit)
			{
				return true;
			}
		}
		if (activity.sequencing.endTimeLimit)
		{
			if (currentTimePoint() > activity.sequencing.endTimeLimit)
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
	function SequencingRulesCheckProcess(activity, SetOfRuleActions) // #225
	{
		if (ondebug) ondebug("SequencingRulesCheckProcess", SequencingRulesCheckProcess.caller); 
		var rules = activity.sequencing.rule;
		for (var i=0; i<rules.length; i+=1)
		{
			var rule = rules[i];
			if (rule && SetOfRuleActions.test(rule.action))
			{
				if (SequencingRuleCheckSubprocess(activity, rule))
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
	function SequencingRuleCheckSubprocess(activity, SequencingRule) // #226
	{
		if (ondebug) ondebug("SequencingRuleCheckSubprocess", SequencingRuleCheckSubprocess.caller); 
		var bag = [], value;
		var conditions = SequencingRule.condition;
		for (var i=0, ni=conditions.length; i<ni; i+=1)
		{
			var condition = conditions[i];
				/*
				o satisfied
				o objectiveStatusKnown
				o objectiveMeasureKnown
				o objectiveMeasureGreaterThan
				o objectiveMeasureLessThan
				o completed
				o activityProgressKnown
				o attempted
				o attempLimitExceeded
				o timeLimitExceeded
				o outsideAvailableTimeRange
				o always

Objective Measure Greater
=> cmi.objectives.n.score.scaled (if defined)
=> cmi.score.scaled (for prim objective)

				*/
			switch (condition.condition)
			{
				case 'objectiveMeasureGreaterThan':
					var v = cmidata.getObjectiveValue(condition.referencedObjective, 'scaled');
					value = v!==undefined && Number(v) > Number(condition.measureThreshold);
					break;
				case 'objectiveMeasureLessThan':
					var v = cmidata.getObjectiveValue(condition.referencedObjective, 'scaled');
					value = v!==undefined && Number(v) < Number(condition.measureThreshold);
					break;
				case 'always':
					value = true;
					break;
				default:
					break;
			}
			bag.push(condition.operator === 'not' ? !value : value);
		}
		return RuleCombination(SequencingRule, bag);
	}
		
	function TerminateDescendentAttemptsProcess(activity) // #227
	{
		if (ondebug) ondebug("TerminateDescendentAttemptsProcess", TerminateDescendentAttemptsProcess.caller); 
		var activityPath = getCommonAncestorAndPath(CurrentActivity, activity).activityPath;
		while (activityPath.length)
		{
			EndAttemptProcess(activityPath.shift());		
		}	
	}
		
	
	function EndAttemptProcess(activity) // #228
	{
		if (ondebug) ondebug("EndAttemptProcess", EndAttemptProcess.caller);
		if (isLeaf(activity)) 
		{
			//enforceUndeliver in GUI
			// only do if navigation succeeds in new delivery???
			onundeliver(); 
		} 
		if (isLeaf(activity) && activity.sequencing.tracked && !activity.isSuspended) 
		{
			if (!activity.sequencing.completionSetByContent)
			{
				if (cmidata.getValue(activity.foreignId, "attemptProgressStatus")!=1) 
				{
					cmidata.setValue(activity.foreignId, "attemptProgressStatus", 1);
					cmidata.setValue(activity.foreignId, "attemptCompletionStatus", 1);
				}
			}
			if (!activity.sequencing.objectiveSetByContent)
			{
				var objectives = activity.sequencing.objective;
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
		OverallRollupProcess(activity);
	}
	
		
	function CheckActivityProcess(activity) // #230
	{
		if (ondebug) ondebug("CheckActivityProcess", CheckActivityProcess.caller); 
		return SequencingRulesCheckProcess(activity, SetOfDisabledActions) || LimitConditionsCheckProcess(activity); 
	}
	
	
	var errorCodes = { // #233-234
		'NB.2.1-1'  : 'Current Activity is already defined / Sequencing session has already begun',
		'NB.2.1-2'  : 'Current Activity is not defined / Sequencing session has not begun',
		'NB.2.1-3'  : 'Suspended Activity is not defined',
		'NB.2.1-4'  : 'Flow Sequencing Control Mode violation',
		'NB.2.1-5'  : 'Flow or Forward Only Sequencing Control Mode violation',
		'NB.2.1-6'  : 'No activity is ‘previous’ to the root',
		'NB.2.1-7'  : 'Unsupported navigation request',
		'NB.2.1-8'  : 'Choice Exit Sequencing Control Mode violation',
		'NB.2.1-9'  : 'No activities to consider',
		'NB.2.1-10' : 'Choice Sequencing Control Mode violation',
		'NB.2.1-11' : 'Target activity does not exist',
		'NB.2.1-12' : 'Current Activity already terminated',
		'NB.2.1-13' : 'Undefined navigation request',
		'TB.2.3-1'  : 'Current Activity is not defined / Sequencing session has not begun',
		'TB.2.3-2'  : 'Current Activity already terminated',
		'TB.2.3-3'  : 'Cannot suspend an inactive root',
		'TB.2.3-4'  : 'Activity tree root has no parent',
		'TB.2.3-5'  : 'Nothing to suspend; No active activities',
		'TB.2.3-6'  : 'Nothing to abandon; No active activities',
		'TB.2.3-7'  : 'Undefined termination request',
		'SB.2.1-1'  : 'Last activity in the tree',
		'SB.2.1-2'  : 'Cluster has no available children',
		'SB.2.1-3'  : 'No activity is ‘previous’ to the root',
		'SB.2.1-4'  : 'Forward Only Sequencing Control Mode violation',
		'SB.2.2-1'  : 'Flow Sequencing Control Mode violation',
		'SB.2.2-2'  : 'Activity unavailable',
		'SB.2.4-1'  : 'Forward Traversal Blocked',
		'SB.2.4-2'  : 'Forward Only Sequencing Control Mode violation',
		'SB.2.4-3'  : 'No activity is ‘previous’ to the root',
		'SB.2.5-1'  : 'Current Activity is defined / Sequencing session already begun',
		'SB.2.6-1'  : 'Current Activity is defined / Sequencing session already begun',
		'SB.2.6-2'  : 'No Suspended Activity defined',
		'SB.2.7-1'  : 'Current Activity is not defined / Sequencing session has not begun',
		'SB.2.7-2'  : 'Flow Sequencing Control Mode violation',
		'SB.2.8-1'  : 'Current Activity is not defined / Sequencing session has not begun',
		'SB.2.8-2'  : 'Flow Sequencing Control Mode violation',
		'SB.2.9-1'  : 'No target for Choice',
		'SB.2.9-2'  : 'Target activity does not exist or is unavailable',
		'SB.2.9-3'  : 'Target activity hidden from choice',
		'SB.2.9-4'  : 'Choice Sequencing Control Mode violation',
		'SB.2.9-5'  : 'No activities to consider',
		'SB.2.9-6'  : 'Unable to activate target; target is not a child of the Current Activity',
		'SB.2.9-7'  : 'Choice Exit Sequencing Control Mode violation',
		'SB.2.9-8'  : 'Unable to choice target activity – constrained choice',
		'SB.2.9-9'  : 'Choice request prevented by Flow-only activity',
		'SB.2.10-1' : 'Current Activity is not defined / Sequencing session has not begun',
		'SB.2.10-2' : 'Current Activity is active or suspended',
		'SB.2.10-3' : 'Flow Sequencing Control Mode violation',
		'SB.2.11-1' : 'Current Activity is not defined / Sequencing session has not begun',
		'SB.2.11-2' : 'Current Activity has not been terminated',
		'SB.2.12-1' : 'Undefined sequencing request',
		'DB.1.1-1'  : 'Cannot deliver a non-leaf activity',
		'DB.1.1-2'  : 'Nothing to deliver',
		'DB.1.1-3'  : 'Activity unavailable',
		'DB.2-1'    : 'Identified activity is already active'
	};
	
	
	function adlException(msg) 
	{
		return msg;
	}
	
	function endSequencingSession() 
	{
		onend();	
	}
	
	function isSibling(activity1, activity2) 
	{
		var p = activity1.parentActivity;
		for (var k in p.item)
		{
			if (k == activity2.id) 
			{
				return true;
			}
		}
		return false;
	}
	function isSibling(activity1, activity2) 
	{
		return isInArray(activity1.id, activity1.parentActivity.item, 'id');
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
		while ((a = a.parentActivity)) 
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
			while ((a = a.parentActivity) && !(a.id in p)) 
			{
				returnValue.activityPath.push(a);
			}
			returnValue.commonAncestor = a;
		} 
		return returnValue;
	}

	function getTargetObjective(activity) /// ???
	{
		var objectives = activity.sequencing.objective;
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
		return activity.parentActivity.availableChildren[activity.parentActivity.availableChildren.length-1]==activity;
	}
	
	function negateRuleCondition (condition) 
	{
	} 
	
	function RuleCombination(SequencingRule, bag)
	{
		var sum = 0;
		for (var i=bag.length; i>-1; i--) 
		{
			if (bag[i]) sum++;
		}
		return (sum && SequencingRule.conditionCombination=="any") 
			|| (sum && sum==bag.length);
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
	
	function currentTimePoint()
	{
		return (new Date()).getTime();
	}
	
	function isRoot(activity)
	{
		return activity==RootActivity;
	}
	
	function isLeaf(activity)
	{
		return !!activity.href;
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
	
	function ConditionCombination() 
	{
	}
	
	function setSuspendedActivity(activity)
	{
		SuspendedActivity = activity;
		cmidata.setValue(RootActivity.foreignId, 'location', activity ? activity.id : null);
	}
		
}
