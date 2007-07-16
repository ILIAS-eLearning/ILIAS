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
	@author Alex Killing <alex.killing@gmx.de>
	
	This .js file is GPL licensed (see above) but based on
	SeqActivity.java by ADL Co-Lab, which is licensed as:
	
	Advanced Distributed Learning Co-Laboratory (ADL Co-Lab) Hub grants you 
	("Licensee") a non-exclusive, royalty free, license to use, modify and 
	redistribute this software in source and binary code form, provided that 
	i) this copyright notice and license appear on all copies of the software; 
	and ii) Licensee does not utilize the software in a manner which is 
	disparaging to ADL Co-Lab Hub.

	This software is provided "AS IS," without a warranty of any kind.  ALL 
	EXPRESS OR IMPLIED CONDITIONS, REPRESENTATIONS AND WARRANTIES, INCLUDING 
	ANY IMPLIED WARRANTY OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE 
	OR NON-INFRINGEMENT, ARE HEREBY EXCLUDED.  ADL Co-Lab Hub AND ITS LICENSORS 
	SHALL NOT BE LIABLE FOR ANY DAMAGES SUFFERED BY LICENSEE AS A RESULT OF 
	USING, MODIFYING OR DISTRIBUTING THE SOFTWARE OR ITS DERIVATIVES.  IN NO 
	EVENT WILL ADL Co-Lab Hub OR ITS LICENSORS BE LIABLE FOR ANY LOST REVENUE, 
	PROFIT OR DATA, OR FOR DIRECT, INDIRECT, SPECIAL, CONSEQUENTIAL, 
	INCIDENTAL OR PUNITIVE DAMAGES, HOWEVER CAUSED AND REGARDLESS OF THE 
	THEORY OF LIABILITY, ARISING OUT OF THE USE OF OR INABILITY TO USE 
	SOFTWARE, EVEN IF ADL Co-Lab Hub HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH 
	DAMAGES.
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
	
	// not implemented trivial stuff (access member vars directly!):
	
	// set/getControlModeChoice(): mControl_choice
	// set/getControlModeChoiceExit(): mControl_choiceExit
	// set/getControlModeFlow(): mControl_flow
	// set/getControlForwardOnly: mControl_forwardOnly
	// set/getConstrainChoice: mConstrainChoice
	// set/getPreventActivation: mPreventActivation
	// set/getUseCurObjective: mUseCurObj
	// set/getUseCurProgress: mUseCurPro
	// set/getPreSeqRules: mPreConditionRules
	// set/getExitSeqRules: mExitActionRules
	// set/getPostSeqRules: mPostConditionRules
	// getAttemptLimitControl: mMaxAttemptControl
	// getAttemptLimit: mMaxAttempt
	// getAttemptAbDurControl: mAttemptAbDurControl
	// getAttemptExDurControl: mAttemptExDurControl
	// getActivityAbDurControl: mActivityAbDurControl
	// getActivityExDurControl: mActivityExDurControl
	// getBeginTimeLimitControl: mBeginTimeControl
	// getBeginTimeLimit: mBeginTime
	// getEndTimeLimitControl: mEndTimeControl
	// getEndTimeLimit: mEndTime
	// set/getAuxResources: mAuxResources (Vector)
	// set/getRollupRules: mRollupRules (SeqRollupRuleset)
	// set/getSatisfactionIfActive: mActiveMeasure
	// set/getRequiredForSatisfied: mRequiredForSatisfied
	// set/getRequiredForNotSatisfied: mRequiredForNotSatisfied
	// set/getRequiredForCompleted: mRequiredForCompleted
	// set/getRequiredForIncomplete: mRequiredForIncomplete
	// getObjectives: mObjectives (Vector)
	// set/getIsObjRolledUp: mIsObjectiveRolledUp
	// set/getObjMeasureWeight: mObjMeasureWeight
	// set/getIsProgressRolledUp: mIsProgressRolledUp
	// getSelectionTiming: mSelectTiming
	// getSelectStatus: mSelectStatus
	// getRandomTiming: mRandomTiming
	// set/getReorderChildren: mReorder
	// set/getIsTracked: mIsTracked
	// set/getSetCompletion: mContentSetsCompletion
	// set/getSetObjective: mContentSetsObj
	// getDeliveryMode: mDeliveryMode
	// set/getResourceID: mResourceID
	// set/getStateID: mStateID
	// set/getID: mActivityID
	// set/getTitle: mTitle
	// set/getXMLFragment: mXML
	// set/getLearnerID: mLearnerID
	// set/getIsSelected: mSelected
	// set/getScopeID: mScopeID
	// set/getIsVisible: mIsVisible
	// set/getIsActive: mIsActive
	// set/getIsSuspended: mIsSuspended
	// getNumSCOAttempt: mNumSCOAttempt
	// set/getParent: mParent
	// set/getActiveOrder: mActiveOrder
	// set/getDepth: mDepth
	// set/getCount: mCount
	// set/getSelection: mSelection
	// set/getRandomized: mRandomized
	// setOrder: mOrder
	
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
		this.mObjectives = iObjs;

		if (iObjs != null)
		{
			for (var i = 0; i < iObjs.length; i++)
			{
				obj = iObjs[i];
				
				if (obj.mMaps != null)
				{
					if (this.mObjMaps == null)
					{
						this.mObjMaps = new Object();	// was Hashtable
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
			if (this.mCurTracking == null)
			{
				track = new ADLTracking(this.mObjectives, 
					this.mLearnerID, this.mScopeID);
				track.mAttempt = this.mNumAttempt;
				this.mCurTracking = track;
			}
			
			// make sure the current state is valid
			if (!(this.mCurTracking.mDirtyPro && iIsRetry))
			{
				progress = this.mCurTracking.mProgress;
			}
		}
		return(progress == TRACK_COMPLETED);
	},
	
	setProgress: function (iProgress)
	{
		var statusChange = false;
		
		if (this.mIsTracked)
		{
			// Validate state data
			if (iProgress == TRACK_UNKNOWN ||
				iProgress == TRACK_COMPLETED ||
				iProgress == TRACK_INCOMPLETE)
			{
				if (this.mCurTracking == null)
				{
					this.mCurTracking = new ADLTracking(this.mObjectives,
						this.mLearnerID, this.mScopeID);
				}
				
				var prev = this.mCurTracking.mProgress;
				
				this.mCurTracking.mProgress = iProgress;
				statusChange = !(prev == iProgress);
			}
		}
		return statusChange;
	},
	
	getProgressStatus: function (iIsRetry)
	{
		var status = false;
		if (this.mIsTracked)
		{
			if (this.mCurTracking != null)
			{
				if (!(this.mCurTracking.mDirtyPro && iIsRetry))
				{
					status = (this.mCurTracking.mProgress != TRACK_UNKNOWN);
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

		if (this.mIsTracked)
		{
			if (this.mCurTracking == null)
			{
				var track = new ADLTracking(this.mObjectives,
					this.mLearnerID, this.mScopeID);
				track.mAttempt = this.mNumAttempt;
				this.mCurTracking = track;
			}
   
			if (this.mCurTracking != null)
			{
				// A null objective indicates the primary objective
				if (iObjID == null)
				{
					iObjID = this.mCurTracking.mPrimaryObj;
				}
				obj = this.mCurTracking.mObjectives.get(iObjID);
				
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
			obj = this.mCurTracking.mObjectives.get(iObjID);

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
				obj = this.mCurTracking.mObjectives.get(iObjID);

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
			var obj = this.mCurTracking.mObjectives.get(this.mCurTracking.mPrimaryObj);
			
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
			if (this.mCurTracking == null)
			{
				var track = new ADLTracking(this.mObjectives,this.mLearnerID,
					this.mScopeID);
				track.mAttempt = this.mNumAttempt;
				this.mCurTracking = track;
			}
   
			// A null objective indicates the primary objective
			if (iObjID == null)
			{
				iObjID = this.mCurTracking.mPrimaryObj;
			}
			
			if (this.mCurTracking != null)
			{
				var obj = this.mCurTracking.mObjectives.get(iObjID);
   
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
			if (this.mCurTracking == null)
			{
				var track = new ADLTracking(this.mObjectives,this.mLearnerID,
					this.mScopeID);
				track.mAttempt = mNumAttempt;
				this.mCurTracking = track;
			}
			if (this.mCurTracking != null)
			{
				var obj = this.mCurTracking.mObjectives.get(
					this.mCurTracking.mPrimaryObj);

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
	
	// call getObjStatus(retry) or 
	// getObjStatus(retry, {iObjID: obj_id, iUseLocal: use_local})
	getObjStatus: function (iIsRetry, iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null,
			iUseLocal: false
			}, iOptions);
		var iObjID = iOptions.iObjID;
		var iUseLocal = iOptions.iUseLocal;

		var status = false;

		if (this.mIsTracked)
		{
			if (this.mCurTracking == null)
			{
				var track = new ADLTracking(this.mObjectives,
					this.mLearnerID, this.mScopeID);
   
				track.mAttempt = this.mNumAttempt;
				this.mCurTracking = track;
			}
   			
			if (this.mCurTracking != null)
			{
				// A null objective indicates the primary objective
				if (iObjID == null)
				{
					iObjID = this.mCurTracking.mPrimaryObj;
				}

				var obj = this.mCurTracking.mObjectives.get(iObjID);

				if (obj != null)
				{
					var objData = obj.getObj();
					
					if (!objData.mSatisfiedByMeasure || this.mActiveMeasure ||
						!this.mIsActive)
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

				var obj = this.mCurTracking.mObjectives.get(iObjID);
				
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
			if (this.mCurTracking == null)
			{
				var track = new ADLTracking(this.mObjectives,this.mLearnerID,
					this.mScopeID);
				track.mAttempt = this.mNumAttempt;
				this.mCurTracking = track;
			}
			
			if (this.mCurTracking != null)
			{
				if (iObjID == null)
				{
					iObjID = this.mCurTracking.mPrimaryObj;
				}

				var obj = this.mCurTracking.mObjectives.get(iObjID);
				
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
			// todo: check
			if (this.mTracking == null)
			{
				this.mTracking = new Array();
			}
			this.mTracking[this.mTracking.length] = this.mCurTracking;
		}
		
		// Create a set of tracking information for the new attempt
		var track = new ADLTracking(this.mObjectives, this.mLearnerID,
			this.mScopeID);
		
		this.mNumAttempt++;
		track.mAttempt = this.mNumAttempt;
		
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
				if (this.mUseCurObj)
				{
					temp.setDirtyObj();
				}
				
				if (this.mUseCurPro)
				{
					temp.setDirtyPro();
				}
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
				
				if (this.mUseCurObj)
				{
					temp.setDirtyObj();
				}
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
				if (this.mUseCurPro)
				{
					temp.setDirtyPro();
				}
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
		
		// todo check
		var objSet = new Array();
		var mapSet = new Array();
		
		if (this.mIsTracked)
		{
			if (this.mObjMaps != null)
			{
				mapSet = this.mObjMaps.get(iObjID);
				if (mapSet != null)
				{
					for (var i = 0; i < mapSet.length; i++)
					{
						var map = mapSet[i];
						
						if (!iRead && (map.mWriteStatus || map.mWriteMeasure))
						{
							if (objSet == null)
							{
								objSet = new Array();
							}
							
							objSet[objSet.length] = map.mGlobalObjID;
						}
						else if (iRead && (map.mReadStatus || map.mReadMeasure))
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
		
		if (this.mCurTracking == null)
		{
			var track = new ADLTracking(this.mObjectives,this.mLearnerID,
				this.mScopeID);
			track.mAttempt = this.mNumAttempt;
			this.mCurTracking = track;
		}
		
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
					
					objStatus.mHasMeasure =
						(measure != TRACK_UNKNOWN);
					
					if (objStatus.mHasMeasure)
					{
						objStatus.mMeasure = parseFloat(measure);
					}
					
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
	}
}
