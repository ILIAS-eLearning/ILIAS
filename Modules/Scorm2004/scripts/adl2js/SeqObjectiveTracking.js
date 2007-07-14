// JS port of ADL SeqObjectiveTracking.java
function SeqObjectiveTracking(iObj,iLearnerID,iScopeID)  
{
	if ( iObj != null )
	{
		mObj = iObj;
		mLearnerID = iLearnerID;
		mScopeID = iScopeID;

		if ( iObj.mMaps != null )
		{
			for ( int i = 0; i < mObj.mMaps.size(); i++ )
			{
				var map = mObj.mMaps[i];
				
				if (map.mReadStatus)
				{
					mReadStatus = map.mGlobalObjID;
				}
			
				if (map.mReadMeasure)
				{
					mReadMeasure = map.mGlobalObjID;
				}
			
				if (map.mWriteStatus)
				{
					if (mWriteStatus == null)
					{
						mWriteStatus = new Array();
					}
				
					// todo: check
					mWriteStatus[sizeof(mWriteStatus)] = map.mGlobalObjID;
				}
			
				if (map.mWriteMeasure)
				{
					if ( mWriteMeasure == null )
					{
						mWriteMeasure = new Array();
					}
					
					// todo: check
					mWriteMeasure[sizeof(mWriteMeasure)] = map.mGlobalObjID;
				}
			}
		}
	}
}
//this.SeqObjectiveTracking = SeqObjectiveTracking;
SeqObjectiveTracking.prototype = 
{
	mLearnerID: null,
	mScopeID: null,
	mObj: null,
	mDirtyObj: false,
	mSetOK: false,
	mHasSatisfied: false,
	mSatisfied: false,
	mHasMeasure: false,
	mMeasure: 0.0,
	mReadStatus: null,
	mReadMeasure: null,
	mWriteStatus: null,
	mWriteMeasure: null,
	
	// trivial, not implemented:
	// getObjID: return mObj.mObjID
	// getObj: return mObj
	// setDirtyObj: mDirtyObj = true;
	
	forceObjStatus: (iSatisfied)
	{
		if (iSatisfied == ADLTracking.TRACK_UNKNOWN)
		{
			this.clearObjStatus();
		}
		else
		{
			// Set any global objectives
			if (mWriteStatus != null)
			{
				for (var i = 0; i < sizeof(mWriteStatus); i++)
				{
					ADLSeqUtilities.setGlobalObjSatisfied(mWriteStatus[i], 
						mLearnerID,mScopeID,iSatisfied);
				}
			}
			
			mHasSatisfied = true;
			if (iSatisfied == ADLTracking.TRACK_SATISFIED)
			{
				mSatisfied = true;
			}
			else
			{
				mSatisfied = false;
			}
		}
	},
	
	// todo: optimization: can be merged with previous function
	setObjStatus: (iSatisfied)
	{
		// If the objective is only satified my measure, don't set its status
		if ( mObj.mSatisfiedByMeasure && !mSetOK )
		{
			// obj satisfied by measure
		}
		else
		{
			if (iSatisfied == ADLTracking.TRACK_UNKNOWN)
			{
				clearObjStatus();
			}
			else
			{
				// Set any global objectives
				if (mWriteStatus != null)
				{
					for (var i = 0; i < sizeof(mWriteStatus); i++)
					{
						ADLSeqUtilities.setGlobalObjSatisfied(mWriteStatus[i], 
							mLearnerID,mScopeID,iSatisfied);
					}
				}
				mHasSatisfied = true;

				if ( iSatisfied.equals(ADLTracking.TRACK_SATISFIED) )
				{
					mSatisfied = true;
				}
				else
				{
					mSatisfied = false;
				}
			}
		}
	},
	
	// todo: optimization: can be merged with previous function
	clearObjStatus: ()
	{
		var statusChange = false;

		if (mHasSatisfied)
		{
			if (mObj.mSatisfiedByMeasure)
			{
				// only satisfied by measure
			}
			else
			{
				if (mWriteStatus != null)
				{
					for (var i = 0; i < sizeof(mWriteStatus); i++)
					{
						ADLSeqUtilities.setGlobalObjSatisfied(mWriteStatus[i], 
							mLearnerID,mScopeID,ADLTracking.TRACK_UNKNOWN);
					}
				}
				// Clear the satisfaction status
				mHasSatisfied = false;
				statusChange = true;
			}
		}
		return statusChange;
	},
	
	clearObjMeasure: (iAffectSatisfaction)
	{
		var statusChange = false;

		if (mHasMeasure)
		{
			if (mWriteMeasure != null)
			{
				for (var i = 0; i < sizeof(mWriteMeasure); i++)
				{
					ADLSeqUtilities.setGlobalObjMeasure(mWriteMeasure[i], 
						mLearnerID,mScopeID,ADLTracking.TRACK_UNKNOWN);
				}
			}
			
			// Clear the measure
			mHasMeasure = false;
			
			// If measure is used to determine status, status is also cleared
			if (iAffectSatisfaction)
			{
				this.forceObjStatus(ADLTracking.TRACK_UNKNOWN);
			}
		}
		return statusChange;	// ???
	}
	
	setObjMeasure: (iMeasure, iAffectSatisfaction)             
	{
		// Validate the range of the measure
		if (iMeasure < -1.0 || iMeasure > 1.0)
		{
			// assume unknown
			this.clearObjMeasure(iAffectSatisfaction);
		}
		else
		{
			mHasMeasure = true;
			mMeasure = iMeasure;

			// Set any global objectives
			if ( mWriteMeasure != null )
			{
				for (var i = 0; i < sizeof(mWriteMeasure); i++)
				{
					ADLSeqUtilities.setGlobalObjMeasure(mWriteMeasure[i], 
						mLearnerID,mScopeID,iMeasure);
				}
			}

			// If objective status is determined by measure, set it
			if (iAffectSatisfaction)
			{
				if (mMeasure >= mObj.mMinMeasure)
				{
					this.forceObjStatus(ADLTracking.TRACK_SATISFIED);
				}
				else
				{
					this.forceObjStatus(ADLTracking.TRACK_NOTSATISFIED);
				}
			}
		}
	},
	
	// call getObjStatis(retry) or getObjStatis(retry, {iUseLocal: use_local})
	getObjStatus: (iIsRetry, iOptions)
	{
		var iOptions = ilAugment({
			iUseLocal: false
			}, iOptions );
		var iUseLocal = iOptions.iUseLocal;
		
		var ret = ADLTracking.TRACK_UNKNOWN;
		var done = false;

		// if satisfied by measure, ensure that it has been set if a measure is
		// avaliable.
		if (mObj.mSatisfiedByMeasure)
		{
			done = true;
			var measure = null;
			
			// Is there a 'read' objective map?
			if (mReadMeasure != null)
			{
				measure = ADLSeqUtilities.getGlobalObjMeasure(mReadMeasure, 
					mLearnerID, mScopeID);
			}
			
			if (mHasMeasure && measure == null)
			{
				if (mHasMeasure && !(iIsRetry && mDirtyObj))
				{
					measure = (new Double(mMeasure)).toString();
				}
			}

			var val = -999.0;
			val = (new Double(measure)).doubleValue();

			// Validate the range of the measure
			if ( val < -1.0 || val > 1.0 )
			{
				// invalid measure
			}
			else
			{
				if (val >= mObj.mMinMeasure)
				{
					ret = ADLTracking.TRACK_SATISFIED;
				}
				else
				{
					ret = ADLTracking.TRACK_NOTSATISFIED;
				}
			}
		}

		if (!done)
		{        
			// Is there a 'read' objective map?
			if ( mReadStatus != null )
			{
				// Retrieve shared competency mastery status
				var status = ADLSeqUtilities.getGlobalObjSatisfied(mReadStatus, 
					mLearnerID,mScopeID);
				if (status != null)
				{
					ret = status;
					done = true;
				}
			}

			if (mHasSatisfied && (!done || iUseLocal))
			{
				if (mHasSatisfied && !(iIsRetry && mDirtyObj))
				{
					if ( mSatisfied )
					{
						ret = ADLTracking.TRACK_SATISFIED;
					}
					else
					{
						ret = ADLTracking.TRACK_NOTSATISFIED;
					}
				}
			}
		}
		return ret;
	},
	
	// call getObjMeasure(retry) or getObjMeasure(retry, {iUseLocal: use_local})
	getObjMeasure: (iIsRetry, iOptions)
	{
		var iOptions = ilAugment({
			iUseLocal: false
			}, iOptions );
		var iUseLocal = iOptions.iUseLocal;
		
		// Do not assume there is a valid measure
		var ret = ADLTracking.TRACK_UNKNOWN;
		var done = false;

		// Is there a 'read' objective map?
		if ( mReadMeasure != null )
		{
			var measure = ADLSeqUtilities.getGlobalObjMeasure(mReadMeasure, 
				mLearnerID, mScopeID);

			// Always use shared measure if available
			if (measure != null)
			{
				ret = measure;
				done = true;
			}
		}

		if (mHasMeasure && (!done || iUseLocal ))
		{
			if (mHasMeasure && !(iIsRetry && mDirtyObj))
			{
				ret = mMeasure;
			}
		}

		if (ret != ADLTracking.TRACK_UNKNOWN &&
			mObj.mSatisfiedByMeasure && !(iIsRetry && mDirtyObj))
		{
			double val = -999.0;
			val = ret;

			// Validate the range of the measure
			if ( val < -1.0 || val > 1.0 )
			{
				// invalid measure
			}
			else
			{
				mSetOK = true;
				if (val >= mObj.mMinMeasure)
				{
					this.setObjStatus(ADLTracking.TRACK_SATISFIED);
				}
				else
				{
					this.setObjStatus(ADLTracking.TRACK_NOTSATISFIED);
				}
				mSetOK = false;
			}
		}
		return ret;
	},
	
	getByMeasure: ()
	{
		var byMeasure = false;
		
		if (mObj != null)
		{
			byMeasure = mObj.mSatisfiedByMeasure;
		}
		
		return byMeasure;
	}
}
