/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/
/*
	JS port of ADL SeqActivity.java
	@author Alex Killing <alex.killing@gmx.de>, Hendrik Holtmann <holtmann@mac.com>
	
	This .js file is GPL licensed (see above) but based on
	SeqActivity.java by ADL Co-Lab, which is licensed as:
	
	ADL SCORM 2004 4th Edition Sample Run-Time Environment

The ADL SCORM 2004 4th Ed. Sample Run-Time Environment is licensed under
Creative Commons Attribution-Noncommercial-Share Alike 3.0 United States.

The Advanced Distributed Learning Initiative allows you to:
  *  Share - to copy, distribute and transmit the work.
  *  Remix - to adapt the work. 

Under the following conditions:
  *  Attribution. You must attribute the work in the manner specified by the author or
     licensor (but not in any way that suggests that they endorse you or your use
     of the work).
  *  Noncommercial. You may not use this work for commercial purposes. 
  *  Share Alike. If you alter, transform, or build upon this work, you may distribute
     the resulting work only under the same or similar license to this one. 

For any reuse or distribution, you must make clear to others the license terms of this work. 

Any of the above conditions can be waived if you get permission from the ADL Initiative. 
Nothing in this license impairs or restricts the author's moral rights.
*/

var TIMING_NEVER = "never";
var TIMING_ONCE = "once";
var TIMING_EACHNEW = "onEachNewAttempt";
var TER_EXITALL = "_EXITALL_";

function SeqActivity()  
{
}
//this.SeqActivity = SeqActivity;
SeqActivity.prototype = 
{
	mPreConditionRules: null,
	mPostConditionRules: null,
	mExitActionRules: null,
	mXML: null,
	mDepth: 0,
	mCount: -1,
	mLearnerID: "_NULL_",
	mScopeID: null,
	mActivityID: null,
	mResourceID: null,
	mStateID: null,
	mTitle: null,
	mIsVisible: true,
	mOrder: -1,
	mActiveOrder: -1,
	mSelected: true,
	mParent: null,
	mIsActive: false,
	mIsSuspended: false,
	mChildren: null,
	mActiveChildren: null,
	mDeliveryMode: "normal",
	mControl_choice: true,
	mControl_choiceExit: true,
	mControl_flow: false,
	mControl_forwardOnly: false,
	mConstrainChoice: false,
	mPreventActivation: false,
	mUseCurObj: true,
	mUseCurPro: true,
	mMaxAttemptControl: false,
	mMaxAttempt: 0,
	mAttemptAbDurControl: false,
	mAttemptAbDur: null,
	mAttemptExDurControl: false,
	mAttemptExDur: null,
	mActivityAbDurControl: false,
	mActivityAbDur: null,
	mActivityExDurControl: false,
	mActivityExDur: null,
	mBeginTimeControl: false,
	mBeginTime: null,
	mEndTimeControl: false,
	mEndTime: null,
	mAuxResources: null,
	mRollupRules: null,
	mActiveMeasure: true,
	mRequiredForSatisfied: ROLLUP_CONSIDER_ALWAYS,
	mRequiredForNotSatisfied: ROLLUP_CONSIDER_ALWAYS,
	mRequiredForCompleted: ROLLUP_CONSIDER_ALWAYS,
	mRequiredForIncomplete: ROLLUP_CONSIDER_ALWAYS,
	mObjectives: null,
	mObjMaps: null,
	mIsObjectiveRolledUp: true,
	mObjMeasureWeight: 1.0,
	mIsProgressRolledUp: true,
	mSelectTiming: "never",
	mSelectStatus: false,
	mSelectCount: 0,
	mSelection: false,
	mRandomTiming: "never",
	mReorder: false,
	mRandomized: false,
	mIsTracked: true,
	mContentSetsCompletion: false,
	mContentSetsObj: false,
	mCurTracking: null,
	mTracking: null,
	mNumAttempt: 0,
	mNumSCOAttempt: 0,
	mActivityAbDur_track: null,
	mActivityExDur_track: null,
	mPrimaryStatusSetBySCO: false,
	mProgressDeterminedByMeasure: false,
	mProgressThreshold: 1.0,
	mProgressWeight: 1.0,
	mPrimaryProgressSetBySCO: false,

	
	
	// getter/setter
	isPrimaryStatusSetBySCO: function () { return this.mPrimaryStatusSetBySCO; },
	primaryStatusSetBySCO: function (val) { this.mPrimaryStatusSetBySCO = val; },
	isPrimaryProgressSetBySCO: function () { return this.mPrimaryProgressSetBySCO; },
	primaryProgressSetBySCO: function (val) { this.mPrimaryProgressSetBySCO = val; },
	getControlModeChoice: function () { return this.mControl_choice; },
	setControlModeChoice: function (iChoice) { this.mControl_choice = iChoice; },
	getControlModeChoiceExit: function () { return this.mControl_choiceExit; },
	setControlModeChoiceExit: function (val) { this.mControl_choiceExit = val; },
	getControlModeFlow: function () { return this.mControl_flow; },
	setControlModeFlow: function (val) { this.mControl_flow = val; },
	getControlForwardOnly: function () { return this.mControl_forwardOnly; },
	setControlForwardOnly: function (val) { this.mControl_forwardOnly = val; },
	getConstrainChoice: function () { return this.mConstrainChoice; },
	setConstrainChoice: function (val) { this.mConstrainChoice = val; },
	getPreventActivation: function () { return this.mPreventActivation; },
	setPreventActivation: function (val) { this.mPreventActivation = val; },
	getUseCurObjective: function () { return this.mUseCurObj; },
	setUseCurObjective: function (val) { this.mUseCurObj = val; },
	getUseCurProgress: function () { return this.mUseCurPro; },
	setUseCurProgress: function (val) { this.mUseCurPro = val; },
	getPreSeqRules: function () { return this.mPreConditionRules; },
	setPreSeqRules: function (val) { this.mPreConditionRules = val; },
	getExitSeqRules: function () { return this.mExitActionRules; },
	setExitSeqRules: function (val) { this.mExitActionRules = val; },
	getPostSeqRules: function () { return this.mPostConditionRules; },
	setPostSeqRules: function (val) { this.mPostConditionRules = val; },
	getAttemptLimitControl: function () { return this.mMaxAttemptControl; },
	getAttemptLimit: function () { return this.mMaxAttempt; },
	getAttemptAbDurControl: function () { return this.mAttemptAbDurControl; },
	getAttemptExDurControl: function () { return this.mAttemptExDurControl; },
	getActivityAbDurControl: function () { return this.mActivityAbDurControl; },
	getActivityExDurControl: function () { return this.mActivityExDurControl; },
	getBeginTimeLimitControl: function () { return this.mBeginTimeControl; },
	getBeginTimeLimit: function () { return this.mBeginTime; },
	getEndTimeLimitControl: function () { return this.mEndTimeControl; },
	getEndTimeLimit: function () { return this.mEndTime; },
	getAuxResources: function () { return this.mAuxResources; },
	setAuxResources: function (val) { this.mAuxResources = val; },
	getRollupRules: function () { return this.mRollupRules; },
	setRollupRules: function (val) { this.mRollupRules = val; },
	getSatisfactionIfActive: function () { return this.mActiveMeasure; },
	setSatisfactionIfActive: function (val) { this.mActiveMeasure = val; },
	getRequiredForSatisfied: function () { return this.mRequiredForSatisfied; },
	setRequiredForSatisfied: function (val) { this.mRequiredForSatisfied = val; },
	getRequiredForNotSatisfied: function () { return this.mRequiredForNotSatisfied; },
	setRequiredForNotSatisfied: function (val) { this.mRequiredForNotSatisfied = val; },
	getRequiredForCompleted: function () { return this.mRequiredForCompleted; },
	setRequiredForCompleted: function (val) { this.mRequiredForCompleted = val; },
	getRequiredForIncomplete: function () { return this.mRequiredForIncomplete; },
	setRequiredForIncomplete: function (val) { this.mRequiredForIncomplete = val; },
	getObjectives: function () { return this.mObjectives; },
	getIsObjRolledUp: function () { return this.mIsObjectiveRolledUp; },
	setIsObjRolledUp: function (val) { this.mIsObjectiveRolledUp = val; },
	getObjMeasureWeight: function () { return this.mObjMeasureWeight; },
	setObjMeasureWeight: function (val) { this.mObjMeasureWeight = val; },
	getIsProgressRolledUp: function () { return this.mIsProgressRolledUp; },
	setIsProgressRolledUp: function (val) { this.mIsProgressRolledUp = val; },
	getSelectionTiming: function () { return this.mSelectTiming; },
	setSelectionTiming: function (val) { this.mSelectTiming = val; },
	getSelectStatus: function () { return this.mSelectStatus; },
	getRandomTiming: function () { return this.mRandomTiming; },
	getReorderChildren: function () { return this.mReorder; },
	setReorderChildren: function (val) { this.mReorder = val; },
	getIsTracked: function () { return this.mIsTracked; },
	setIsTracked: function (val) { this.mIsTracked = val; },
	getSetCompletion: function () { return this.mContentSetsCompletion; },
	setSetCompletion: function (val) { this.mContentSetsCompletion = val; },
	getSetObjective: function () { return this.mContentSetsObj; },
	setSetObjective: function (val) { this.mContentSetsObj = val; },
	getResourceID: function () { return this.mResourceID; },
	setResourceID: function (val) { this.mResourceID = val; },
	getDeliveryMode: function () { return this.mDeliveryMode; },
	getStateID: function () { return this.mStateID; },
	setStateID: function (val) { this.mStateID = val; },
	getID: function () { return this.mActivityID; },
	setID: function (val) { this.mActivityID = val; },
	getTitle: function () { return this.mTitle; },
	setTitle: function (val) { this.mTitle = val; },
	getXMLFragment: function () { return this.mXML; },
	setXMLFragment: function (val) { this.mXML = val; },
	getLearnerID: function () { return this.mLearnerID; },
	setLearnerID: function (val) { this.mLearnerID = val; },
	getIsSelected: function () { return this.mSelected; },
	setIsSelected: function (val) { this.mSelected = val; },
	getScopeID: function () { return this.mScopeID; },
	setScopeID: function (val) { this.mScopeID = val; },
	getIsVisible: function () { return this.mIsVisible; },
	setIsVisible: function (val) { this.mIsVisible = val; },
	getIsActive: function () { return this.mIsActive; },
	setIsActive: function (val) { this.mIsActive = val; },
	getIsSuspended: function () { return this.mIsSuspended; },
	setIsSuspended: function (val) { this.mIsSuspended = val; },
	getNumSCOAttempt: function () { return this.mNumSCOAttempt; },
	getParent: function () { return this.mParent; },
	setParent: function (val) { this.mParent = val; },
	getActiveOrder: function () { return this.mActiveOrder; },
	setActiveOrder: function (val) { this.mActiveOrder = val; },
	getDepth: function () { return this.mDepth; },
	setDepth: function (val) { this.mDepth = val; },
	getCount: function () { return this.mCount; },
	setCount: function (val) { this.mCount = val; },
	getSelection: function () { return this.mSelection; },
	setSelection: function (val) { this.mSelection = val; },
	getRandomized: function () { return this.mRandomized; },
	setRandomized: function (val) { this.mRandomized = val; },
	setOrder: function (val) { this.mOrder = val; },
	//: function () { return this.; },
	//: function (val) { this. = val; },
	
	setAttemptLimit: function (iMaxAttempt)
	{
		if (iMaxAttempt != null)
		{
			var value = iMaxAttempt;
			if (value >= 0)
			{
				this.mMaxAttemptControl = true;
				this.mMaxAttempt = value;
			}
			else
			{
				this.mMaxAttemptControl = false;
				this.mMaxAttempt = -1;
			}
		}
	},
	
	getAttemptAbDur: function ()
	{
		var dur = null;
	
		if (this.mAttemptAbDur != null)
		{
			dur = this.mAttemptAbDur.format(FORMAT_SCHEMA);
		}
		return dur;
	},
	
	setAttemptAbDur: function (iDur)
	{
		if (iDur != null)
		{
			this.mAttemptAbDurControl = true;
			this.mAttemptAbDur = new ADLDuration(
				{iFormat: FORMAT_SCHEMA, iValue: iDur});
		}
		else
		{
			this.mAttemptAbDurControl = false;
			this.mAttemptAbDur = null;
		}
	},
	
	getAttemptExDur: function ()
	{
		var dur = null;
		if (this.mAttemptExDur != null)
		{
			dur = this.mAttemptExDur.format(FORMAT_SCHEMA);
		}
		return dur;
	},
	
	setAttemptExDur: function (iDur)
	{
		if ( iDur != null )
		{
			this.mAttemptExDurControl = true;
			this.mAttemptExDur = new ADLDuration(
				{iFormat: FORMAT_SCHEMA, iValue: iDur});
		}
		else
		{
			this.mAttemptExDurControl = false;
		}
	},
	
	getActivityAbDur: function ()
	{
		var dur = null;
		if (this.mActivityAbDur != null)
		{
			dur = this.mActivityAbDur.format(FORMAT_SCHEMA);
		}
		return dur;
	},
   
	setActivityAbDur: function (iDur)
	{
		if ( iDur != null )
		{
			this.mActivityAbDurControl = true;
			this.mActivityAbDur = new ADLDuration(
				{iFormat: FORMAT_SCHEMA, iValue: iDur});
		}
		else
		{
			this.mActivityAbDurControl = false;
		}
	},
	
	getActivityExDur: function ()
	{
		var dur = null;
		if (this.mActivityExDur != null)
		{
			dur = this.mActivityExDur.format(FORMAT_SCHEMA);
		}
		return dur;
	},
	
	setActivityExDur: function (iDur)
	{
		if ( iDur != null )
		{
			this.mActivityExDurControl = true;
			this.mActivityExDur = new ADLDuration(
				{iFormat: FORMAT_SCHEMA, iValue: iDur});
		}
		else
		{
			this.mActivityExDurControl = false;
		}
	},
	
	setBeginTimeLimit: function (iTime)
	{
		if (iTime != null)
		{
			this.mBeginTimeControl = true;
			this.mBeginTime = iTime;
		}
		else
		{
			this.mBeginTimeControl = false;
		}
	},
	
	setEndTimeLimit: function (iTime)
	{
		if (iTime != null)
		{
			this.mEndTimeControl = true;
			this.mEndTime = iTime;
		}
		else
		{
			this.mEndTimeControl = false;
		}
	},
	
	setObjectives: function (iObjs)
	{

		if (mObjectives != null)
		{
			if (this.mOjbectives.length > 0 )
				{
					if ( iObjs != null)
						{
							for ( var i = 0; i < iObjs.length; i++)
								{
									var toadd = iObjs[i];
									var contained = false;
									
									for ( var j = 0; j < this.mObjectives.length; j++)
									{			
											if (this.mObjectives[j] == toadd)
											{
												contained = true;
												this.mObjectives[j].merge(toadd);
											}
											if ( !contained)
											{
												this.mObjectives[this.mObjectives.length] = iObjs[i];
											}
									}		
								}
						}
				}
		}
		
		else
		{
			this.mObjectives = iObjs;
		}
		
			if ( this.mObjectives != null)
			{
				for (var i = 0; i < this.mObjectives.length; i++)
				{
					obj = iObjs[i];
				
					if (obj.mMaps != null)
					{
						if (this.mObjMaps == null)
						{
							this.mObjMaps = new Object();	// Hashtable
						}
						this.mObjMaps[obj.mObjID] = obj.mMaps;
				}
			}
		}
	},
	
	setSelectionTiming: function (iTiming)
	{
		// Validate vocabulary
		if (!(iTiming == TIMING_NEVER || 
			iTiming == TIMING_ONCE ||
			iTiming == TIMING_EACHNEW))
		{
			this.mSelectTiming = TIMING_NEVER;
		}
		else
		{
			this.mSelectTiming = iTiming;
		}
   },
   
	getSelectCount: function ()
	{
		// If the number to be randomized is greater than the number of children
		// available, no  selection is required
		if (this.mChildren != null)
		{
			if (this.mSelectCount >= this.mChildren.length)
			{
				this.mSelectTiming = "never";
				this.mSelectCount = mChildren.length;
			}
		}
		else
		{
			// No children to select from; can't select
			this.mSelectStatus = false;
			this.mSelectCount = 0;
		}
		return this.mSelectCount;
	},
	
	setSelectCount: function (iCount)
	{
		if (iCount >= 0)
		{
			this.mSelectStatus = true;
			this.mSelectCount = iCount;
		}
		else
		{
			this.mSelectStatus = false;
		}
	},
	
	setRandomTiming: function (iTiming)
	{
		// Validate vocabulary
		if (!(iTiming == TIMING_NEVER || 
			iTiming == TIMING_ONCE ||
			iTiming == TIMING_EACHNEW ))
		{
			this.mSelectTiming = TIMING_NEVER;
		}
		else
		{
			this.mRandomTiming = iTiming;
		}
	},
	
	setDeliveryMode: function (iDeliveryMode)
	{
		// Test vocabulary
		if (iDeliveryMode == "browse" || iDeliveryMode == "review" ||
			iDeliveryMode == "normal")
		{
			this.mDeliveryMode = iDeliveryMode;
		}
		else
		{
			this.mDeliveryMode = "normal";
		}
	},
	
	getActivityAttempted: function ()
	{
		return(this.mNumAttempt != 0);
	},
	
	getAttemptCompleted: function (iIsRetry)
	{
		var progress = TRACK_UNKNOWN;
		if (this.mIsTracked)
		{
			this.initADLTracking();					
			
			if (!(this.mCurTracking.mDirtyPro==true && iIsRetry==true))
			{	
				if ( !this.mCurTracking.getProgressDeterminedByMeasure())
				{
					progress = this.mCurTracking.getCompletionStatus(this.mUseCurPro);
				}
				else
				{
					if ( this.mCurTracking.hasProgressMeasure())
					{
						var measure = this.mCurTracking.getProgressMeasure();
						progress = (parseFloat(measure) >= parseFloat(this.mCurTracking.getProgressThreshold()))?TRACK_COMPLETED:TRACK_INCOMPLETE;
						this.setProgressMeasure(measure);
					}
				}
			}
		}
		return(progress == TRACK_COMPLETED);
	},
	
	getPriObjAttemptCompleted: function (iIsRetry)
	{
		var progress = TRACK_UNKNOWN;
		if ( this.mIsTracked )
		{
			this.initADLTracking();
			if (!(this.mCurTracking.mDirtyPro==true && iIsRetry==true))
			{	
				if ( !this.mCurTracking.getProgressDeterminedByMeasure())
				{
					progress = this.mCurTracking.getCompletionStatus(this.mUseCurPro);
				}
				else
				{
					if ( this.mCurTracking.hasProgressMeasure())
					{
						var measure = this.mCurTracking.getProgressMeasure();
						progress = (parseFloat(measure) >= parseFloat(this.mCurTracking.getProgressThreshold()))?TRACK_COMPLETED:TRACK_INCOMPLETE;
						
					}
				}
			}
			
		}
		return (progress == TRACK_COMPLETED);
	},
	
	setProgress: function (iProgress)
	{
		var statusChange = false;
		
		if (this.mIsTracked==true)
		{
			// Validate state data
			if (iProgress == TRACK_UNKNOWN ||
				iProgress == TRACK_COMPLETED ||
				iProgress == TRACK_INCOMPLETE)
			{
				this.initADLTracking();
				
				var prev = this.mCurTracking.getCompletionStatus(this.mUseCurPro);
				this.mCurTracking.setCompletionStatus(iProgress);
				statusChange = !(prev == iProgress);
			}
		}
		return statusChange;
	},
	
	getProgressStatus: function (iIsRetry)
	{
		var status = false;
		if (this.mIsTracked==true)
		{
			if (this.mCurTracking != null)
			{
				if (!(this.mCurTracking.mDirtyPro==true && iIsRetry==true))
				{					
					status = (this.mCurTracking.getCompletionStatus(this.mUseCurPro) != TRACK_UNKNOWN);
				}
			}
		}
		return status;
	},
	
	// call getObjMeasureStatus(retry) or
	// getObjMeasureStatus(retry, {iObjID: obj_id, iUseLocal: use_local})
	getObjMeasureStatus: function (iIsRetry, iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null,
			iUseLocal: false
			}, iOptions);
		var iObjID = iOptions.iObjID;
		var iUseLocal = iOptions.iUseLocal;
		
		var status = false;

		if (this.mIsTracked==true)
		{
			this.initADLTracking();
   
			if (this.mCurTracking != null)
			{
				
				// A null objective indicates the primary objective
				if (iObjID == null)
				{
					iObjID = this.mCurTracking.mPrimaryObj;
				}
				obj = this.mCurTracking.mObjectives[iObjID];
				
				if (obj != null)
				{
					var result = null;
					result = obj.getObjMeasure(iIsRetry, iUseLocal);
					
					if (result != TRACK_UNKNOWN)
					{
						status = true;
					}
				}
			}
		}
		return status;
	},
	
	// call clearObjMeasure() or clearObjMeasure({iObjID: obj_id})
	clearObjMeasure: function (iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null
			}, iOptions);
		var iObjID = iOptions.iObjID;

		var statusChange = false;
		if (this.mCurTracking != null)
		{
			if (iObjID == null)
			{
				iObjID = this.mCurTracking.mPrimaryObj;
			}
			obj = this.mCurTracking.mObjectives[iObjID];

			if (obj != null)
			{
				objD = obj.getObj();
				var affectSatisfaction = objD.mSatisfiedByMeasure;
				
				if (affectSatisfaction)
				{
					affectSatisfaction = !objD.mContributesToRollup ||
						(this.mActiveMeasure || !this.mIsActive);
				}
				statusChange = obj.clearObjMeasure(affectSatisfaction);
			}
		}
		return statusChange;
	},
	
	// call setObjMeasure(measure) or setObjMeasure(measure, {iObjID: obj_id})
	setObjMeasure: function (iMeasure, iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null
			}, iOptions);
		var iObjID = iOptions.iObjID;
		var statusChange = false;

		if (this.mIsTracked)
		{
			if (this.mCurTracking != null)
			{
				if (iObjID == null)
				{
					iObjID = this.mCurTracking.mPrimaryObj;
				}
				obj = this.mCurTracking.mObjectives[iObjID];

				if (obj != null)
				{
					var prev = obj.getObjStatus(false);
					var objD = obj.getObj();
					var affectSatisfaction = objD.mSatisfiedByMeasure;
   
					if (affectSatisfaction)
					{
						affectSatisfaction = !objD.mContributesToRollup ||
							(this.mActiveMeasure || !this.mIsActive);
					}
					obj.setObjMeasure(iMeasure, affectSatisfaction);
					statusChange = (prev != obj.getObjStatus(false));
				}
			}
		}
		return statusChange;
	},
	
	getObjSatisfiedByMeasure: function ()
	{
		var byMeasure = false;
		
		if (this.mCurTracking != null)
		{
			var obj = this.mCurTracking.mObjectives[this.mCurTracking.mPrimaryObj];
			
			if (obj != null)
			{
				byMeasure = obj.getByMeasure();
			}
		}
		return byMeasure;
	},
	
	// call getObjMinMeasure() or getObjMinMeasure({iObjID: obj_id})
	getObjMinMeasure: function (iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null
			}, iOptions);
		var iObjID = iOptions.iObjID;

		var minMeasure = -1.0;
		
		if (iObjID == null)
		{
			iObjID = this.mCurTracking.mPrimaryObj;
		}
		if (this.mObjectives != null)
		{
			for (var i = 0; i < this.mObjectives.length; i++)
			{
				var obj = this.mObjectives[i];
				
				if (iObjID == obj.mObjID)
				{
					minMeasure = obj.mMinMeasure;
				}
			}
		}
		return minMeasure;
	},
	
	getObjMeasure: function (iIsRetry, iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null
			}, iOptions);
		var iObjID = iOptions.iObjID;

		var measure = 0.0;
		if (this.mIsTracked)
		{
			this.initADLTracking();
   
			// A null objective indicates the primary objective
			if (iObjID == null)
			{
				iObjID = this.mCurTracking.mPrimaryObj;
			}
			
			if (this.mCurTracking != null)
			{
				var obj = this.mCurTracking.mObjectives[iObjID];
   
				if (obj != null)
				{
					var result = null;
					result = obj.getObjMeasure(iIsRetry);
					
					if (result != TRACK_UNKNOWN)
					{
						measure = parseFloat(result);
					}
				}
			}
		}
		return measure;
	},
	
	triggerObjMeasure: function ()
	{
		var measure = 0.0;

		if (this.mIsTracked)
		{
			this.initADLTracking();
			
			if (this.mCurTracking != null)
			{
				var obj = this.mCurTracking.mObjectives[this.mCurTracking.mPrimaryObj];

				if (obj != null)
				{
					if (obj.getObj().mSatisfiedByMeasure)
					{
						var result = null;
						
						result = obj.getObjMeasure(false);
						if (result != TRACK_UNKNOWN)
						{
							measure = parseFloat(result);
							obj.setObjMeasure(measure, true);
						}
						else
						{
							obj.clearObjMeasure(true);
						}
					}
				}
			}
		}
	},
	
	// use this for getObjStatus(str iObjID, bool iIsRetry)
	getObjIdStatus: function (iObjID, iIsRetry)
	{
		var status = false;
		
		if ( this.mIsTracked==true )
		{
			this.initADLTracking();
			
			if ( iObjID == null )
			{
				status = this.getObjStatus(iIsRetry);
			}
			else if ( this.mCurTracking != null )
			{
				var obj = this.mCurTracking.mObjectives[iObjID];

				if (obj != null)
				{ 
					var objData = obj.getObj();
					
					if (objData.mSatisfiedByMeasure==false 
						|| this.mActiveMeasure==true 
						|| this.mIsActive==false)
					{              
						var result = null;
						result = obj.getObjStatus(iIsRetry);
						if (result != TRACK_UNKNOWN)
						{
							status = true;
						}
					}
				}
			}
		}
		
		return status;
	},
	
	// call getObjStatus(retry) or
	// getObjStatus(retry, {iUseLocal: use_local})
	getObjStatus: function (iIsRetry, iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null,
			iUseLocal: false
			}, iOptions);
		var iObjID = iOptions.iObjID;
		var iUseLocal = iOptions.iUseLocal;

		var status = false;
		if (this.mIsTracked==true)
		{
			if ( this.mPrimaryStatusSetBySCO )
			{
				status = true;
				var obj = this.mCurTracking.mObjectives[this.mCurTracking.mPrimaryObj];
				if ( obj != null )
				{
					var res = obj.getObjStatus(iIsRetry, iUseLocal);
					status = res != TRACK_UNKNOWN;
				}
			}
			else
			{
				this.initADLTracking();
				if (this.mCurTracking != null)
				{
					
					if (iObjID == null) {
						iObjID = this.mCurTracking.mPrimaryObj;
					}
					
					var obj = this.mCurTracking.mObjectives[iObjID];

					if (obj != null)
					{
						var objData = obj.getObj();
						
						if (objData.mSatisfiedByMeasure==false || this.mActiveMeasure==true ||
							this.mIsActive==false)
						{              
							var result = null;
							result = obj.getObjStatus(iIsRetry, iUseLocal);
							if (result != TRACK_UNKNOWN)
							{
								status = true;
							}
						}
					}
				}
			}
		}
		return status;
	},
	
	getObjSatValue: function ()
	{
		var status = "";
		var obj = this.mCurTracking.mObjectives[this.mCurTracking.mPrimaryObj];
		if ( obj != null )
		{
			status = obj.getObjStatus(false, true);
		}
		return status;
	},
	
	getProgressValue: function()
	{
		return this.mCurTracking.getCompletionStatus(false);
	},
	
	// call setObjSatisfied(status) or 
	// setObjSatisfied(status, {iObjID: obj_id})
	setObjSatisfied: function (iStatus, iOptions)  
	{
		var iOptions = ilAugment({
			iObjID: null
			}, iOptions);
		var iObjID = iOptions.iObjID;

 
		var statusChange = false;

		if (this.mIsTracked)
		{         
			if (this.mCurTracking != null)
			{
				if (iObjID == null)
				{
					iObjID = this.mCurTracking.mPrimaryObj;
				}

				var obj = this.mCurTracking.mObjectives[iObjID];
				
				if (obj != null)
				{
					// Validate desired value
					if (iStatus == TRACK_UNKNOWN ||
						iStatus == TRACK_SATISFIED ||
						iStatus == TRACK_NOTSATISFIED)
					{
					
						var result = obj.getObjStatus(false);
						obj.setObjStatus(iStatus);
						statusChange = (result != iStatus);
					}
				}
			}
		}
		return statusChange;
	},
	
	// call getObjSatisfied(is_retry) or
	// getObjSatisfied(is_retry, {iObjID: obj_id})
	getObjSatisfied: function (iIsRetry, iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null
			}, iOptions);
		var iObjID = iOptions.iObjID;

		var status = false;
		
		if (this.mIsTracked)
		{
			this.initADLTracking();
			
			if (this.mCurTracking != null)
			{
				
				if (iObjID == null)
				{
					iObjID = this.mCurTracking.mPrimaryObj;
				}

				var obj = this.mCurTracking.mObjectives[iObjID];
				
				if (obj != null)
				{
					var objData = obj.getObj();
					
					if (!objData.mSatisfiedByMeasure || this.mActiveMeasure ||
						!this.mIsActive)
					{
						var result = null;
						
						result = obj.getObjStatus(iIsRetry);
						if (result == TRACK_SATISFIED)
						{
							status = true;
						}
					}
				}
			}
		}
		return status;
	},
	
	setCurAttemptExDur: function (iDur)
	{
		if (this.mCurTracking != null)
		{
			this.mCurTracking.mAttemptAbDur = iDur;
		}
	},
	
	evaluateLimitConditions: function ()
	{
		// This is an implementation of UP.1
		var disabled = false;
		
		if (this.mCurTracking != null)
		{
			// Test max attempts
			if (this.mMaxAttemptControl)
			{
				if (this.mNumAttempt >= this.mMaxAttempt)
				{
					disabled = true;
				}
			}
		
			if (this.mActivityAbDurControl && !disabled)
			{
				if (this.mActivityAbDur.compare(this.mActivityAbDur_track) 
					!= LT)
				{
					disabled = true;
				}
			}
		
			if (this.mActivityExDurControl && !disabled)
			{
				if (this.mActivityExDur.compare(this.mActivityExDur_track) != 
					LT)
				{
					disabled = true;
				}
			}
		
			if (this.mAttemptAbDurControl && !disabled)
			{
				if (this.mActivityAbDur.compare(this.mCurTracking.mAttemptAbDur)
					!= LT)
				{
					disabled = true;
				}
			}
		
			if (this.mAttemptExDurControl && !disabled)
			{
				if (this.mActivityExDur.compare(this.mCurTracking.mAttemptExDur)
					!= LT)
				{
					disabled = true;
				}
			}
		
			if (this.mBeginTimeControl && !disabled)
			{
				// -+- TODO -+-
				if (false)
				{
					disabled = true;
				}
			}
		
			if (this.mEndTimeControl && !disabled)
			{
				// -+- TODO -+-
				if (false)
				{
					disabled = true;
				}
			}
		}
		return disabled;
	},
	
	incrementSCOAttempt: function ()
	{
		this.mNumSCOAttempt++;
	},
	
	incrementAttempt: function ()
	{
		// Store existing tracking information for historical purposes
		if (this.mCurTracking != null)
		{
			if (this.mTracking == null)
			{
				this.mTracking = new Array();
			}
			this.mTracking[this.mTracking.length] = this.mCurTracking;
		}
		
		var track;
		// Create a set of tracking information for the new attempt
		if ( this.mProgressDeterminedByMeasure == true )
		{
			track = new ADLTracking(this.mObjectives, this.mLearnerID, this.mScopeID,
									this.mProgressThreshold, this.mProgressWeight);
		}
		else
		{
			track = new ADLTracking(this.mObjectives, this.mLearnerID, this.mScopeID);
		}
		
		this.mNumAttempt++;
		track.mAttempt = this.mNumAttempt;
		track.setProgressMeasureThreshold(this.mProgressThreshold);
		track.setProgressMeasureWeight(this.mProgressWeight);
		
		this.mCurTracking = track;
		
		// If this is a cluster, check useCurrent flags
		if (this.mActiveChildren != null)
		{
			for (var i = 0; i < this.mActiveChildren.length; i++)
			{
				var temp = this.mActiveChildren[i];
				
				// Flag 'dirty' data if we are supposed to only use 'current attempt
				// status -- Set existing data to 'dirty'.  When a new attempt on a
				// a child activity begins, the new tracking information will be 
				// 'clean'.
				if (this.mUseCurObj==true)
				{
					temp.setDirtyObj();
				}
				
				if (this.mUseCurPro==true)
				{
					temp.setDirtyPro();
				}
				this.mActiveChildren[i] = temp;
			}
		}	
	},
	
	setDirtyObj: function ()
	{
		if (this.mCurTracking != null)
		{
			this.mCurTracking.setDirtyObj();
		}
		
		// If this is a cluster, check useCurrent flags
		if (this.mActiveChildren != null)
		{
			for (var i = 0; i < this.mActiveChildren.length; i++)
			{
				var temp = this.mActiveChildren[i];
				
				if (this.mUseCurObj==true)
				{
					temp.setDirtyObj();
				}
				this.mActiveChildren[i] = temp;
			}
		}
	},
	
	setDirtyPro: function ()
	{
		if (this.mCurTracking != null)
		{
			this.mCurTracking.mDirtyPro = true;
		}
		
		// If this is a cluster, check useCurrent flags
		if (this.mActiveChildren != null)
		{
			for (var i = 0; i < this.mActiveChildren.length; i++)
			{
				var temp = this.mActiveChildren[i];
				if (this.mUseCurPro==true)
				{
					temp.setDirtyPro();
				}
				this.mActiveChildren[i] = temp;
			}
		}
	},
	
	resetNumAttempt: function ()
	{
		// Clear all current and historical tracking information.
		this.mNumAttempt = 0;
		this.mCurTracking = null;
		this.mTracking = null;
	},
	
	getNumAttempt: function ()
	{
		var attempt = 0;
		if (this.mIsTracked)
		{
			attempt = this.mNumAttempt;
		}
		return attempt;
	},
	
	getObjIDs: function (iObjID, iRead)
	{
		// Attempt to find the ID associated with the rolledup objective
		if (iObjID == null)
		{
			if (this.mCurTracking != null)
			{
				iObjID = this.mCurTracking.mPrimaryObj;
			}
		}
		
		var objSet = new Array();
		var mapSet = new Array();
		
		if (this.mIsTracked)
		{
			if (this.mObjMaps != null)
			{
				mapSet = this.mObjMaps[iObjID];
				if (mapSet != null)
				{
					for (var i = 0; i < mapSet.length; i++)
					{
						var map = mapSet[i];
						
						if (!iRead && map.hasWriteMaps() )
						{
							if (objSet == null)
							{
								objSet = new Array();
							}
							
							objSet[objSet.length] = map.mGlobalObjID;
						}
						else if (iRead && map.hasReadMaps() )
						{
							if (objSet == null)
							{
								objSet = new Array();
							}
							objSet[objSet.length] = map.mGlobalObjID;
						}
					}
				}
			}
		}
		return objSet;
	},
	
	addChild: function (ioChild)
	{
		if (this.mChildren == null)
		{
			this.mChildren = new Array();
		}
		
		// To maintain consistency, adding a child activity will set the active
		// children to the set of all children.
		this.mActiveChildren = this.mChildren;
		
		this.mChildren[mChildren.length] = ioChild;
		
		// Tell the child who its parent is and its order in relation to its
		// siblings.
		ioChild.setOrder(this.mChildren.length - 1);
		ioChild.setActiveOrder(this.mChildren.length - 1);
		ioChild.setParent(this);
	},
	
	setChildren: function (ioChildren, iAll)
	{
		var walk = null; 
		
		if (iAll)
		{
			this.mChildren = ioChildren;
			this.mActiveChildren = ioChildren;
			
			for (var i = 0; i < ioChildren.length; i++)
			{
				walk = ioChildren[i];
				
				walk.setOrder(i);
				walk.setActiveOrder(i);
				walk.setParent(this);
				walk.setIsSelected(true);
			}
		}
		else
		{
			for (var i = 0; i < this.mChildren.length; i++)
			{
				walk = this.mChildren[i];
				walk.setIsSelected(false);
			}
			
			this.mActiveChildren = ioChildren;
			
			for (var i = 0; i < ioChildren.length; i++)
			{
				walk = ioChildren[i];
				walk.setActiveOrder(i);
				walk.setIsSelected(true);
				walk.setParent(this);
			}
		}
	},
	
	getChildren: function (iAll)
	{
		var result = null;
		
		if (iAll)
		{
			result = this.mChildren;
		}
		else
		{
			result = this.mActiveChildren;
		}
		
		return result;
	},
	
	hasChildren: function (iAll)
	{
		var result = false;
		
		if (iAll)
		{
			result = (this.mChildren != null);
		}
		else
		{
			result = (this.mActiveChildren != null);
		}
		
		return result;
	},
	
	getNextSibling: function (iAll)
	{
		var next = null;
		var target = -1;
		
		// Make sure this activity has a parent
		if (this.mParent != null)
		{
			if (iAll)
			{
				target = this.mOrder + 1; 
			}
			else
			{
				target = this.mActiveOrder + 1;
			}
			
			// Make sure there is a 'next' sibling
			if (target < this.mParent.getChildren(iAll).length)
			{
				var all = this.mParent.getChildren(iAll);
				next = all[target];
			}
		}
		return next;
	},
	
	getPrevSibling: function (iAll)
	{
		var prev = null;
		var target = -1;
		
		// Make sure this activity has a parent
		if (this.mParent != null)
		{
			if (iAll)
			{
				target = this.mOrder - 1;
			}
			else
			{
				target = this.mActiveOrder - 1;
			}
			
			// Make sure there is a 'next' sibling
			if (target >= 0)
			{
				var all = this.mParent.getChildren(iAll);
				prev = all[target];
			}
		}
		return prev;
	},
	
	getParentID: function ()
	{
		// If the parent is not null
		if (this.mParent != null)
		{
			return this.mParent.mActivityID;
		}
		
		return null;
	},
	
	getObjStatusSet: function ()
	{
		var objSet = null;
		
		this.initADLTracking();
		
		if (this.mCurTracking.mObjectives != null)
		{
			objSet = new Array();
			
			for (var key in this.mCurTracking.mObjectives)
			{
				// Only include objectives with IDs
				if (key != "_primary_")
				{
					var obj = this.mCurTracking.mObjectives[key];
					var objStatus = new ADLObjStatus();
					
					objStatus.mObjID = obj.getObjID();
					var measure = obj.getObjMeasure(false);
					
					objStatus.mHasMeasure = (measure != TRACK_UNKNOWN);
					
					if (objStatus.mHasMeasure)
					{
						objStatus.mMeasure = parseFloat(measure);
					}
					
					objStatus.mHasRawScore = obj.getObjRawScore(false) != TRACK_UNKNOWN;
					if ( objStatus.mHasRawScore ) objStatus.mRawScore = obj.getObjRawScore(false);
					
					objStatus.mHasMinScore = obj.getObjMinScore(false) != TRACK_UNKNOWN;
					if ( objStatus.mHasMinScore ) objStatus.mMinScore = obj.getObjMinScore(false);
					
					objStatus.mHasMaxScore = obj.getObjMaxScore(false) != TRACK_UNKNOWN;
					if ( objStatus.mHasMaxScore ) objStatus.mMaxScore = obj.getObjMaxScore(false);
										
					objStatus.mHasProgressMeasure = obj.getObjProgressMeasure(false) != TRACK_UNKNOWN;
					if ( objStatus.mHasProgressMeasure ) objStatus.mProgressMeasure = obj.getObjProgressMeasure(false);
					
					objStatus.mCompletionStatus = obj.getObjCompletionStatus(false);
					
					objStatus.mStatus = obj.getObjStatus(false);
					objSet[objSet.length] = objStatus;
				}
			}
		}
		
		if (objSet != null)
		{
			if (objSet.length == 0)
			{
				objSet = null;
			}
		}
		return objSet;
	},
	
	initADLTracking: function ()
	{
		if (this.mCurTracking == null)
		{
			var track;
			
			if ( this.mProgressDeterminedByMeasure == true)
			{
				track = new ADLTracking(this.mObjectives,this.mLearnerID,
				this.mScopeID, this.mProgressThreshold, this.mProgressWeight);
			}
			else
			{
				track = new ADLTracking(this.mObjectives, this.mLearnerID, this.mScopeID);
			}
		
			track.mAttempt = this.mNumAttempt;
			
			this.mCurTracking = track;
			
			// set progress measure stuff
			track.setProgressMeasureThreshold(this.mProgressThreshold);
			track.setProgressMeasureWeight(this.mProgressWeight);
		}
	},
	
	getProMeasure: function( iIsRetry )
	{
		var measure = -1.0;
		
		if( this.mIsTracked )
		{
			this.initADLTracking();
			
			if ( this.mCurTracking != null )
			{
				//make sure the current state is valid
				if ( !(this.mCurTracking.mDirtyPro && iIsRetry))
				{
					if ( this.mCurTracking.hasProgressMeasure() )
					{
						measure = this.mCurTracking.getProgressMeasure();
					}
				}
			}
		}
		return measure;
	},
	
	setProMeasure: function( iProMeasure )
	{
		if ( this.mIsTracked )
		{
			if ( this.mCurTracking != null)
			{
				this.mCurTracking.setProgressMeasure( iProMeasure );
			}
		}
	},
	
	clearProMeasure: function()
	{
		if ( this.mCurTracking != null)
		{
			if ( this.mCurTracking != null)
			{
				this.mCurTracking.clearProMeasure();
			}
		}
	},
	
	setProgressDeterminedByMeasure: function( iDeterminedByMeasure)
	{
		this.mProgressDeterminedByMeasure = iDeterminedByMeasure;
	},
	
	setProgressThreshold: function( iThreshold )
	{
		if ( iThreshold >= 0 && iThreshold <= 1.0)
		{
			this.mProgressThreshold = iThreshold;
		}
	},
	
	setProgressWeight: function ( iWeight )
	{
		if ( iWeight >= 0 && iWeight <= 1.0)
		{
			this.mProgressWeight = iWeight;
			
			if ( this.mCurTracking != null)
			{
				this.mCurTracking.setProgressMeasureWeight( iWeight);
			}
		}
	},
	
	clearObjStatus: function()
	{
		var statusChange = false;
		
		if ( this.mCurTracking != null)
		{
			var obj = this.mCurTracking.mObjectives[this.mCurTracking.mPrimaryObj];
			
			if ( obj != null )
			{
				statusChange = obj.clearObjStatus();
			}
		}
	},
	
	getObjProgressStatus: function( iObjID, iRollup )
	{
		var status = false;
		
		if ( this.mIsTracked )
		{
			this.initADLTracking();
			var primary = false;
			
			if ( this.mCurTracking != null )
			{
				primary = iObjID == null || iObjID == (this.mCurTracking.mPrimaryObj);
			}
			
			if ( primary == true)
			{
				status = this.getProgressStatus( iRollup );
			}
			else if ( this.mCurTracking != null)
			{
				var obj = this.mCurTracking.mObjectives[iObjID];
				
				if ( obj != null)
				{
					var result = obj.getObjCompletionStatus( iRollup );
					status = !(result == TRACK_UNKNOWN);
				}
			}
		}
		return status;
	},
	
	getObjAttemptCompleted: function( iObjID, iRollup)
	{
		var status = false;
		
		if ( this.mIsTracked )
		{
			this.initADLTracking();
			var primary = false;
			
			if ( this.mCurTracking != null )
			{
				primary = iObjID == null || iObjID == this.mCurTracking.mPrimaryObj;
			}
			if ( primary == true)
			{
				status = this.getPriObjAttemptCompleted(iRollup);
			}
			else if ( this.mCurTracking != null)
			{
				var obj = this.mCurTracking.mObjectives[iObjID];
				
				if ( obj != null)
				{
					var result = obj.getObjCompletionStatus( iRollup );
					status = result == TRACK_COMPLETED;
				}
			}
		}
		return status;
	},

	setObjRawScore: function (iObjID, iRawScore)
	{
      if ( this.mIsTracked )
      {
         if ( iObjID != null )
         {
            var obj = this.getObj(iObjID);
            if ( obj != null )
            {
               obj.setObjRawScore(iRawScore);
            }
         }

      }
   },

   clearObjRawScore: function (iObjID)
   {
      var statusChange = false;
      var obj;
      if (iObjID != null)
      {
         obj = this.mCurTracking.mObjectives[iObjID];
         if ( obj != null )
         {
            statusChange = obj.clearObjRawScore();
         }
      }
      return statusChange;
   },
   
   setObjMinScore: function (iObjID, iMinScore)
   {
      if ( this.mIsTracked )
      {
         if ( iObjID != null )
         {
            var obj = this.getObj(iObjID);
            if ( obj != null )
            {
               obj.setObjMinScore(iMinScore);
            }
         }
      }
   },
   
   clearObjMinScore: function (iObjID)
   {
      var statusChange = false;
      var obj;
      if (iObjID != null)
      {
         obj = this.mCurTracking.mObjectives[iObjID];
         if ( obj != null )
         {
            statusChange = obj.clearObjMinScore();
         }
      }
      return statusChange;
   },
   
   setObjMaxScore: function (iObjID, iMaxScore)
   {
      if ( this.mIsTracked )
      {
         if ( iObjID != null )
         {
            var obj = this.getObj(iObjID);
            if ( obj != null )
            {
               obj.setObjMaxScore(iMaxScore);
            }
         }
      }
   },
   
   clearObjMaxScore: function (iObjID)
   {
      var statusChange = false;
      var obj;
      if (iObjID != null)
      {
         obj = this.mCurTracking.mObjectives[iObjID];
         if ( obj != null )
         {
            statusChange = obj.clearObjMaxScore();
         }
      }
      return statusChange;
   },
   
   setObjProgressMeasure: function (iObjID, iProgressMeasure)
   {
      
		var obj;
		if ( iObjID != null )
		{
			obj = this.getObj(iObjID);
			if ( obj != null )
			{
				obj.setObjProgressMeasure(iProgressMeasure);
			}
		}
      
   },
   
   clearObjProgressMeasure: function (iObjID)
   {
      var statusChange = false;
      var obj;
      if (iObjID != null)
      {
         obj = this.mCurTracking.mObjectives[iObjID];
         if ( obj != null )
         {
            statusChange = obj.clearObjProgressMeasure();
         }
      }
      return statusChange;
   },
   
   setObjCompletionStatus: function (iObjID, iCompletionStatus)
   {
		if ( iObjID != null )
        {
			var obj = this.getObj(iObjID);
			if ( obj != null )
			{
			   obj.setObjCompletionStatus(iCompletionStatus);
			}
        }
      
   },
   
   clearObjCompletionStatus: function (iObjID)
   {
      var statusChange = false;
      var obj;
      if (this.mCurTracking != null)
      {
         obj = this.mCurTracking.mObjectives[iObjID];
         if ( obj != null )
         {
            statusChange = obj.clearObjCompletionStatus();
         }
      }
      return statusChange;
   },
   
   getObj: function (iObjID)
   {
		if ( this.mCurTracking == null )
		{
			this.mCurTracking = new ADLTracking(this.mObjectives, this.mLearnerID, this.mScopeID);
			track.mAttempt = this.mNumAttempt;
		}
		return this.mCurTracking.mObjectives[iObjID];
   },
   
	setProgressMeasure: function (iProgressMeasure)
	{
		var statusChange = false;
		if ( this.mIsTracked )
		{
			var proMeasure = iProgressMeasure;
			this.initADLTracking();
			var previousMeasure = (this.mCurTracking.hasProgressMeasure())?this.mCurTracking.getProgressMeasure():-1;
			this.mCurTracking.setProgressMeasure(proMeasure);
			statusChange = previousMeasure != proMeasure;
		}
		return statusChange;
	},
	
	getCompletedByMeasure: function ()
	{
		var compByMeasure = false;
		if ( this.mIsTracked )
		{
			this.initADLTracking();
			compByMeasure = this.mCurTracking.getProgressDeterminedByMeasure();
		}
		return compByMeasure;
	},
	
	getProMeasureWeight: function ()
	{
		var weight = 0;
		if ( this.mIsTracked )
		{
			this.initADLTracking();
			weight = this.mCurTracking.getProgressMeasureWeight();
		}
		return weight;
	},
	
	getProMeasureStatus: function (iIsRetry)
	{
		var valid = false;
		if ( this.mIsTracked )
		{
			this.initADLTracking();
			if ( ! (this.mCurTracking.mDirtyPro && iIsRetry) )
			{
				valid = this.mCurTracking.hasProgressMeasure();
			}
		}
		return valid;
	}
};
