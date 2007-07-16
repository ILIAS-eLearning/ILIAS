// JS port of ADL SeqActivity.java
function SeqActivity()  
{
}
//this.SeqActivity = SeqActivity;
SeqActivity.prototype = 
{
	TIMING_NEVER: "never",
	TIMING_ONCE: "once",
	TIMING_EACHNEW: "onEachNewAttempt",
	TER_EXITALL: "_EXITALL_",
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
	mRequiredForSatisfied: SeqRollupRule.ROLLUP_CONSIDER_ALWAYS,
	mRequiredForNotSatisfied: SeqRollupRule.ROLLUP_CONSIDER_ALWAYS,
	mRequiredForCompleted: SeqRollupRule.ROLLUP_CONSIDER_ALWAYS,
	mRequiredForIncomplete: SeqRollupRule.ROLLUP_CONSIDER_ALWAYS,
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
	
	setAttemptLimit: (iMaxAttempt)
	{
		if (iMaxAttempt != null)
		{
			var value = iMaxAttempt;
			if (value >= 0)
			{
				mMaxAttemptControl = true;
				mMaxAttempt = value;
			}
			else
			{
				mMaxAttemptControl = false;
				mMaxAttempt = -1;
			}
		}
	},
	
	getAttemptAbDur: ()
	{
		var dur = null;
	
		if (mAttemptAbDur != null)
		{
			dur = mAttemptAbDur.format(ADLDuration.FORMAT_SCHEMA);
		}
		return dur;
	},
	
	setAttemptAbDur: (iDur)
	{
		if (iDur != null)
		{
			mAttemptAbDurControl = true;
			mAttemptAbDur = new ADLDuration(
				{iFormat: ADLDuration.FORMAT_SCHEMA, iValue: iDur});
		}
		else
		{
			mAttemptAbDurControl = false;
			mAttemptAbDur = null;
		}
	},
	
	getAttemptExDur: ()
	{
		var dur = null;
		if (mAttemptExDur != null)
		{
			dur = mAttemptExDur.format(ADLDuration.FORMAT_SCHEMA);
		}
		return dur;
	},
	
	setAttemptExDur(String iDur)
	{
		if ( iDur != null )
		{
			mAttemptExDurControl = true;
			mAttemptExDur = new ADLDuration(
				{iFormat: ADLDuration.FORMAT_SCHEMA, iValue: iDur});
		}
		else
		{
			mAttemptExDurControl = false;
		}
	},
	
	getActivityAbDur: ()
	{
		var dur = null;
		if ( mActivityAbDur != null )
		{
			dur = mActivityAbDur.format(ADLDuration.FORMAT_SCHEMA);
		}
		return dur;
   }
   
	setActivityAbDur: (iDur)
	{
		if ( iDur != null )
		{
			mActivityAbDurControl = true;
			mActivityAbDur = new ADLDuration(
				{iFormat: ADLDuration.FORMAT_SCHEMA, iValue: iDur});
		}
		else
		{
			mActivityAbDurControl = false;
		}
	},
	
	getActivityExDur: ()
	{
		var dur = null;
		if ( mActivityExDur != null )
		{
			dur = mActivityExDur.format(ADLDuration.FORMAT_SCHEMA);
		}
		return dur;
	},
	
	setActivityExDur: (iDur)
	{
		if ( iDur != null )
		{
			mActivityExDurControl = true;
			mActivityExDur = new ADLDuration(
				{iFormat: ADLDuration.FORMAT_SCHEMA, iValue: iDur});
		}
		else
		{
			mActivityExDurControl = false;
		}
	},
	
	setBeginTimeLimit: (iTime)
	{
		if (iTime != null)
		{
			mBeginTimeControl = true;
			mBeginTime = iTime;
		}
		else
		{
			mBeginTimeControl = false;
		}
	},
	
	setEndTimeLimit: (iTime)
	{
		if (iTime != null)
		{
			mEndTimeControl = true;
			mEndTime = iTime;
		}
		else
		{
			mEndTimeControl = false;
		}
	},
	
	setObjectives: (iObjs)
	{
		mObjectives = iObjs;

		if (iObjs != null)
		{
			for (var i = 0; i < sizeof(iObjs); i++)
			{
				obj = iObjs[i];
				
				if (obj.mMaps != null)
				{
					if (mObjMaps == null)
					{
						mObjMaps = new Object();	// was Hashtable
					}
					mObjMaps[obj.mObjID] = obj.mMaps;
				}
			}
		}
	},
	
	setSelectionTiming: (iTiming)
	{
		// Validate vocabulary
		if (!(iTiming == SeqActivity.TIMING_NEVER || 
			iTiming == SeqActivity.TIMING_ONCE ||
			iTiming == SeqActivity.TIMING_EACHNEW))
		{
			mSelectTiming = SeqActivity.TIMING_NEVER;
		}
		else
		{
			mSelectTiming = iTiming;
		}
   },
   
	getSelectCount: ()
	{
		// If the number to be randomized is greater than the number of children
		// available, no  selection is required
		if (mChildren != null)
		{
			if (mSelectCount >= sizeof(mChildren))
			{
				mSelectTiming = "never";
				mSelectCount = sizeof(mChildren);
			}
		}
		else
		{
			// No children to select from; can't select
			mSelectStatus = false;
			mSelectCount = 0;
		}
		return mSelectCount;
	},
	
	setSelectCount: (iCount)
	{
		if (iCount >= 0)
		{
			mSelectStatus = true;
			mSelectCount = iCount;
		}
		else
		{
			mSelectStatus = false;
		}
	},
	
	setRandomTiming: (iTiming)
	{
		// Validate vocabulary
		if (!(iTiming == SeqActivity.TIMING_NEVER || 
			iTiming == SeqActivity.TIMING_ONCE ||
			iTiming == SeqActivity.TIMING_EACHNEW ))
		{
			mSelectTiming = SeqActivity.TIMING_NEVER;
		}
		else
		{
			mRandomTiming = iTiming;
		}
	},
	
	setDeliveryMode: (iDeliveryMode)
	{
		// Test vocabulary
		if (iDeliveryMode == "browse" || iDeliveryMode == "review" ||
			iDeliveryMode == "normal")
		{
			mDeliveryMode = iDeliveryMode;
		}
		else
		{
			mDeliveryMode = "normal";
		}
	},
	
	getActivityAttempted: ()
	{
		return(mNumAttempt != 0);
	},
	
	getAttemptCompleted: (iIsRetry)
	{
		var progress = ADLTracking.TRACK_UNKNOWN;
		
		if (mIsTracked)
		{
			if (mCurTracking == null)
			{
				track = new ADLTracking(mObjectives, 
					mLearnerID, mScopeID);
				track.mAttempt = mNumAttempt;
				mCurTracking = track;
			}
			
			// make sure the current state is valid
			if (!(mCurTracking.mDirtyPro && iIsRetry))
			{
				progress = mCurTracking.mProgress;
			}
		}
		return(progress == ADLTracking.TRACK_COMPLETED);
	},
	
	setProgress: (iProgress)
	{
		var statusChange = false;
		
		if (mIsTracked)
		{
			// Validate state data
			if (iProgress == ADLTracking.TRACK_UNKNOWN ||
				iProgress == ADLTracking.TRACK_COMPLETED ||
				iProgress == ADLTracking.TRACK_INCOMPLETE)
			{
				if (mCurTracking == null)
				{
					mCurTracking = new ADLTracking(mObjectives, mLearnerID, mScopeID);
				}
				
				var prev = mCurTracking.mProgress;
				
				mCurTracking.mProgress = iProgress;
				statusChange = !(prev == iProgress);
			}
		}
		return statusChange;
	},
	
	getProgressStatus: (iIsRetry)
	{
		var status = false;
		if (mIsTracked)
		{
			if (mCurTracking != null)
			{
				if (!(mCurTracking.mDirtyPro && iIsRetry))
				{
					status = !(mCurTracking.mProgress == 
						ADLTracking.TRACK_UNKNOWN);
				}
			}
		}
		return status;
	},
	
	// call getObjMeasureStatus(retry) or 
	// getObjMeasureStatus(retry, {iObjID: obj_id, iUseLocal: use_local})
	getObjMeasureStatus: (iIsRetry, iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null,
			iUseLocal: false
			}, iOptions);
		var iObjID = iOptions.iObjID;
		var iUseLocal = iOptions.iUseLocal;
		
		var status = false;

		if (mIsTracked)
		{
			if (mCurTracking == null)
			{
				ADLTracking track = new ADLTracking(mObjectives,
					mLearnerID, mScopeID);
				track.mAttempt = mNumAttempt;
				mCurTracking = track;
			}
   
			if (mCurTracking != null)
			{
				// A null objective indicates the primary objective
				if (iObjID == null)
				{
					iObjID = mCurTracking.mPrimaryObj;
				}
				obj = mCurTracking.mObjectives.get(iObjID);
				
				if (obj != null)
				{
					var result = null;
					result = obj.getObjMeasure(iIsRetry, iUseLocal);
					if (result != ADLTracking.TRACK_UNKNOWN)
					{
						status = true;
					}
				}
			}
		}
		return status;
	},
	
	// call clearObjMeasure() or clearObjMeasure({iObjID: obj_id})
	clearObjMeasure: (iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null
			}, iOptions);
		var iObjID = iOptions.iObjID;

		var statusChange = false;
		if (mCurTracking != null)
		{
			if (iObjID == null)
			{
				iObjID = mCurTracking.mPrimaryObj;
			}
			obj = mCurTracking.mObjectives.get(iObjID);

			if (obj != null)
			{
				objD = obj.getObj();
				var affectSatisfaction = objD.mSatisfiedByMeasure;
				
				if (affectSatisfaction)
				{
					affectSatisfaction = !objD.mContributesToRollup ||
						(mActiveMeasure || !mIsActive);
				}
				statusChange = obj.clearObjMeasure(affectSatisfaction);
			}
		}
		return statusChange;
	},
	
	// call setObjMeasure(measure) or setObjMeasure(measure, {iObjID: obj_id})
	setObjMeasure: (iMeasure, iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null
			}, iOptions);
		var iObjID = iOptions.iObjID;
		
		var statusChange = false;

		if (mIsTracked)
		{
			if (mCurTracking != null)
			{
				if (iObjID == null)
				{
					iObjID = mCurTracking.mPrimaryObj;
				}
				obj = mCurTracking.mObjectives.get(iObjID);

				if (obj != null)
				{
					var prev = obj.getObjStatus(false);
					var objD = obj.getObj();
					var affectSatisfaction = objD.mSatisfiedByMeasure;
   
					if (affectSatisfaction)
					{
						affectSatisfaction = !objD.mContributesToRollup ||
							( mActiveMeasure || !mIsActive );
					}

					obj.setObjMeasure(iMeasure, affectSatisfaction);
					statusChange = (prev != obj.getObjStatus(false));
				}
			}
		}
		return statusChange;
	},
	
	getObjSatisfiedByMeasure: ()
	{
		var byMeasure = false;
		
		if (mCurTracking != null)
		{
			var obj = mCurTracking.mObjectives.get(mCurTracking.mPrimaryObj);
			
			if (obj != null)
			{
				byMeasure = obj.getByMeasure();
			}
		}
		return byMeasure;
	},
	
	// call getObjMinMeasure() or getObjMinMeasure({iObjID: obj_id})
	getObjMinMeasure: (iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null
			}, iOptions);
		var iObjID = iOptions.iObjID;

		var minMeasure = -1.0;
		
		if (iObjID == null)
		{
			iObjID = mCurTracking.mPrimaryObj
		}
		if (mObjectives != null)
		{
			for (var i = 0; i < sizeof(mObjectives); i++)
			{
				var obj = mObjectives[i];
				
				if (iObjID == obj.mObjID)
				{
					minMeasure = obj.mMinMeasure;
				}
			}
		}
		return minMeasure;
	},
	
	getObjMeasure: (iIsRetry, iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null
			}, iOptions);
		var iObjID = iOptions.iObjID;

		var measure = 0.0;
		if (mIsTracked)
		{
			if (mCurTracking == null)
			{
				var track = new ADLTracking(mObjectives,mLearnerID,mScopeID);
				track.mAttempt = mNumAttempt;
				mCurTracking = track;
			}
   
			// A null objective indicates the primary objective
			if (iObjID == null)
			{
				iObjID = mCurTracking.mPrimaryObj;
			}
			
			if (mCurTracking != null)
			{
				var obj = mCurTracking.mObjectives.get(iObjID)));
   
				if (obj != null)
				{
					var result = null;
					result = obj.getObjMeasure(iIsRetry);
					
					if (result != ADLTracking.TRACK_UNKNOWN)
					{
						measure = result;
					}
				}
			}
		}
		return measure;
	},
	
	triggerObjMeasure: ()
	{
		var measure = 0.0;

		if (mIsTracked)
		{
			if (mCurTracking == null)
			{
				var track = new ADLTracking(mObjectives,mLearnerID,mScopeID);
				track.mAttempt = mNumAttempt;
				mCurTracking = track;
			}
			if (mCurTracking != null)
			{
				var obj = mCurTracking.mObjectives.get(mCurTracking.mPrimaryObj);

				if (obj != null)
				{
					if (obj.getObj().mSatisfiedByMeasure)
					{
						var result = null;
						
						result = obj.getObjMeasure(false);
						if (result != ADLTracking.TRACK_UNKNOWN)
						{
							measure = result;
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
	getObjStatus: (iIsRetry, iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null,
			iUseLocal: false
			}, iOptions);
		var iObjID = iOptions.iObjID;
		var iUseLocal = iOptions.iUseLocal;

		var status = false;

		if (mIsTracked)
		{
			if (mCurTracking == null)
			{
				var track = new ADLTracking(mObjectives,mLearnerID,mScopeID);
   
				track.mAttempt = mNumAttempt;
				mCurTracking = track;
			}
   			
			if (mCurTracking != null)
			{
				// A null objective indicates the primary objective
				if (iObjID == null)
				{
					iObjID = mCurTracking.mPrimaryObj;
				}

				var obj = mCurTracking.mObjectives.get(iObjID);

				if (obj != null)
				{
					var objData = obj.getObj();
					
					if (!objData.mSatisfiedByMeasure || mActiveMeasure || !mIsActive)
					{              
						var result = null;
						result = obj.getObjStatus(iIsRetry);
						if (result != ADLTracking.TRACK_UNKNOWN)
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
	setObjSatisfied: (iStatus, iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null
			}, iOptions);
		var iObjID = iOptions.iObjID;

		var statusChange = false;

		if (mIsTracked)
		{         
			if (mCurTracking != null)
			{
				if (iObjID == null)
				{
					iObjID = mCurTracking.mPrimaryObj;
				}

				var obj = mCurTracking.mObjectives.get(iObjID);
				
				if (obj != null)
				{
					// Validate desired value
					if (iStatus == ADLTracking.TRACK_UNKNOWN ||
						iStatus == ADLTracking.TRACK_SATISFIED ||
						iStatus == ADLTracking.TRACK_NOTSATISFIED)
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
	getObjSatisfied: (iIsRetry, iOptions)
	{
		var iOptions = ilAugment({
			iObjID: null
			}, iOptions);
		var iObjID = iOptions.iObjID;

		var status = false;
		if (mIsTracked)
		{
			if (mCurTracking == null)
			{
				var track = new ADLTracking(mObjectives,mLearnerID,mScopeID);
				track.mAttempt = mNumAttempt;
				mCurTracking = track;
			}
			
			if (mCurTracking != null)
			{
				if (iObjID == null)
				{
					iObjID = mCurTracking.mPrimaryObj;
				}

				var obj = mCurTracking.mObjectives.get(iObjID);
				
				if (obj != null)
				{
					var objData = obj.getObj();
					
					if (!objData.mSatisfiedByMeasure || mActiveMeasure || !mIsActive)
					{
						var result = null;
						
						result = obj.getObjStatus(iIsRetry);
						if (result == ADLTracking.TRACK_SATISFIED)
						{
							status = true;
						}
					}
				}
			}
		}
		return status;
	},
	
	setCurAttemptExDur: (iDur)
	{
		if (mCurTracking != null)
		{
			mCurTracking.mAttemptAbDur = iDur;
		}
	},
	
	evaluateLimitConditions: ()
	{
		// This is an implementation of UP.1
		var disabled = false;
		
		if (mCurTracking != null)
		{
			// Test max attempts
			if (mMaxAttemptControl)
			{
				if (mNumAttempt >= mMaxAttempt)
				{
					disabled = true;
				}
			}
		
			if (mActivityAbDurControl && !disabled)
			{
				if (mActivityAbDur.compare(mActivityAbDur_track) 
					!= ADLDuration.LT)
				{
					disabled = true;
				}
			}
		
			if (mActivityExDurControl && !disabled)
			{
				if (mActivityExDur.compare(mActivityExDur_track) != 
					ADLDuration.LT)
				{
					disabled = true;
				}
			}
		
			if (mAttemptAbDurControl && !disabled)
			{
				if (mActivityAbDur.compare(mCurTracking.mAttemptAbDur)
					!= ADLDuration.LT)
				{
					disabled = true;
				}
			}
		
			if (mAttemptExDurControl && !disabled)
			{
				if (mActivityExDur.compare(mCurTracking.mAttemptExDur)
					!= ADLDuration.LT)
				{
					disabled = true;
				}
			}
		
			if (mBeginTimeControl && !disabled)
			{
				// -+- TODO -+-
				if (false)
				{
					disabled = true;
				}
			}
		
			if (mEndTimeControl && !disabled)
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
	
	incrementSCOAttempt: ()
	{
		mNumSCOAttempt++;
	},
	
	incrementAttempt: ()
	{
		// Store existing tracking information for historical purposes
		if (mCurTracking != null)
		{
			// todo: check
			if (mTracking == null)
			{
				mTracking = new Array();
			}
			mTracking[sizeof(mTracking)] = mCurTracking;
		}
		
		// Create a set of tracking information for the new attempt
		var track = new ADLTracking(mObjectives, mLearnerID, mScopeID);
		
		mNumAttempt++;
		track.mAttempt = mNumAttempt;
		
		mCurTracking = track;
		
		// If this is a cluster, check useCurrent flags
		if (mActiveChildren != null)
		{
			for (var i = 0; i < sizeof(mActiveChildren); i++)
			{
				var temp = mActiveChildren[i];
				
				// Flag 'dirty' data if we are supposed to only use 'current attempt
				// status -- Set existing data to 'dirty'.  When a new attempt on a
				// a child activity begins, the new tracking information will be 
				// 'clean'.
				if (mUseCurObj)
				{
					temp.setDirtyObj();
				}
				
				if (mUseCurPro)
				{
					temp.setDirtyPro();
				}
			}
		}	
	},
	
	setDirtyObj: ()
	{
		if (mCurTracking != null)
		{
			mCurTracking.setDirtyObj();
		}
		
		// If this is a cluster, check useCurrent flags
		if (mActiveChildren != null)
		{
			for (var i = 0; i < sizeof(mActiveChildren); i++)
			{
				var temp = mActiveChildren[i];
				
				if (mUseCurObj)
				{
					temp.setDirtyObj();
				}
			}
		}
	},
	
	setDirtyPro: ()
	{
		if (mCurTracking != null)
		{
			mCurTracking.mDirtyPro = true;
		}
		
		// If this is a cluster, check useCurrent flags
		if (mActiveChildren != null)
		{
			for (var i = 0; i < sizeof(mActiveChildren); i++)
			{
				var temp = mActiveChildren[i];
				if (mUseCurPro)
				{
					temp.setDirtyPro();
				}
			}
		}
	},
	
	resetNumAttempt: ()
	{
		// Clear all current and historical tracking information.
		mNumAttempt = 0;
		mCurTracking = null;
		mTracking = null;
	},
	
	getNumAttempt: ()
	{
		var attempt = 0;
		if (mIsTracked)
		{
			attempt = mNumAttempt;
		}
		return attempt;
	},
	
	getObjIDs: (iObjID, iRead)
	{
		// Attempt to find the ID associated with the rolledup objective
		if (iObjID == null)
		{
			if (mCurTracking != null)
			{
				iObjID = mCurTracking.mPrimaryObj;
			}
		}
		
		// todo check
		var objSet = new Array();
		var mapSet = new Array();
		
		if (mIsTracked)
		{
			if (mObjMaps != null)
			{
				mapSet = mObjMaps.get(iObjID);
				if (mapSet != null)
				{
					for (var i = 0; i < sizeof(mapSet); i++)
					{
						var map = mapSet[i];
						
						if (!iRead && (map.mWriteStatus || map.mWriteMeasure))
						{
							if (objSet == null)
							{
								objSet = new Array();
							}
							
							objSet[sizeof(objSet)] = map.mGlobalObjID;
						}
						else if (iRead && (map.mReadStatus || map.mReadMeasure))
						{
							if (objSet == null)
							{
								objSet = new Array();
							}
							objSet[sizeof(objSet)] = map.mGlobalObjID;
						}
					}
				}
			}
		}
		return objSet;
	},
	
	addChild: (ioChild)
	{
		if (mChildren == null)
		{
			mChildren = new Array();
		}
		
		// To maintain consistency, adding a child activity will set the active
		// children to the set of all children.
		mActiveChildren = mChildren;
		
		mChildren[sizeof(mChildren)] = ioChild;
		
		// Tell the child who its parent is and its order in relation to its
		// siblings.
		ioChild.setOrder(sizeof(mChildren) - 1);
		ioChild.setActiveOrder(sizeof(mChildren) - 1);
		ioChild.setParent(this);
	},
	
	setChildren: (ioChildren, iAll)
	{
		var walk = null; 
		
		if (iAll)
		{
			mChildren = ioChildren;
			mActiveChildren = ioChildren;
			
			for (var i = 0; i < sizeof(ioChildren); i++)
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
			for (var i = 0; i < sizeof(mChildren); i++)
			{
				walk = mChildren[i];
				walk.setIsSelected(false);
			}
			
			mActiveChildren = ioChildren;
			
			for (var i = 0; i < sizeof(ioChildren); i++)
			{
				walk = ioChildren[i];
				walk.setActiveOrder(i);
				walk.setIsSelected(true);
				walk.setParent(this);
			}
		}
	},
	
	getChildren: (iAll)
	{
		var result = null;
		
		if (iAll)
		{
			result = mChildren;
		}
		else
		{
			result = mActiveChildren;
		}
		
		return result;
	},
	
	hasChildren: (iAll)
	{
		var result = false;
		
		if (iAll)
		{
			result = mChildren != null;
		}
		else
		{
			result = mActiveChildren != null;
		}
		
		return result;
	},
	
	getNextSibling: (iAll)
	{
		var next = null;
		var target = -1;
		
		// Make sure this activity has a parent
		if (mParent != null)
		{
			if (iAll)
			{
				target = mOrder + 1; 
			}
			else
			{
				target = mActiveOrder + 1;
			}
			
			// Make sure there is a 'next' sibling
			if (target < sizeof(mParent.getChildren(iAll)))
			{
				var all = mParent.getChildren(iAll);
				next = all[target];
			}
		}
		return next;
	},
	
	getPrevSibling: (iAll)
	{
		var prev = null;
		var target = -1;
		
		// Make sure this activity has a parent
		if (mParent != null)
		{
			if (iAll)
			{
				target = mOrder - 1;
			}
			else
			{
				target = mActiveOrder - 1;
			}
			
			// Make sure there is a 'next' sibling
			if (target >= 0)
			{
				var all = mParent.getChildren(iAll);
				prev = all[target];
			}
		}
		return prev;
	},
	
	getParentID: ()
	{
		// If the parent is not null
		if (mParent != null)
		{
			return mParent.mActivityID;
		}
		
		return null;
	},
	
	getObjStatusSet: ()
	{
		var objSet = null;
		
		if (mCurTracking == null)
		{
			var track = new ADLTracking(mObjectives,mLearnerID,mScopeID);
			track.mAttempt = mNumAttempt;
			mCurTracking = track;
		}
		
		if (mCurTracking.mObjectives != null)
		{
			objSet = new Array();
			
			for (var k in mCurTracking.mObjectives)
			{
				if (mCurTracking.mObjectives.hasOwnProperty(k)) // hasOwnProperty requires Safari 1.2
				{
					var key = k;
					
					// Only include objectives with IDs
					if ( !key.equals("_primary_") )
					{
						var obj = mCurTracking.mObjectives[key];
						var objStatus = new ADLObjStatus();
						
						objStatus.mObjID = obj.getObjID();
						var measure = obj.getObjMeasure(false);
						
						objStatus.mHasMeasure =
							(measure != ADLTracking.TRACK_UNKNOWN);
						
						if (objStatus.mHasMeasure)
						{
							objStatus.mMeasure = measure;
						}
						
						objStatus.mStatus = obj.getObjStatus(false);
						objSet[siezof(objSet)] = objStatus;
					}
				}
			}
		}
		
		if (objSet != null)
		{
			if (sizeof(objSet) == 0)
			{
				objSet = null;
			}
		}
		return objSet;
	},
	
	
}
	
	
