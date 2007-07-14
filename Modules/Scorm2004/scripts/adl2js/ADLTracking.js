// JS port of ADL ADLTracking.java
function ADLTracking(iObjs, iLearnerID, iScopeID)  
{
	if (iObjs != null)
	{
		for (var i = 0; i < sizeof(iObjs); i++)
		{
			obj = iObjs[i];
			// Construct an objective for each local objective
			objTrack = new SeqObjectiveTracking(obj, iLearnerID, iScopeID);

			if (mObjectives == null)
			{
				mObjectives = new Object();
			}

			// todo check
			mObjectives[obj.mObjID] = objTrack;

			// Remember if this objective contributes to rollup
			if (obj.mContributesToRollup)
			{
				mPrimaryObj = obj.mObjID;
			}
		}
	}
	else
	{
		// All activities must have at least one objective and that objective
		// is the primary objective
		def = new SeqObjective();
		def.mContributesToRollup = true;
		objTrack = new SeqObjectiveTracking(def, iLearnerID, iScopeID);
		
		if (mObjectives == null)
		{
			mObjectives = new Object();
		}
		// todo check
		mObjectives[def.mObjID] = objTrack;
		mPrimaryObj = def.mObjID;
	}

}
//this.ADLTracking = ADLTracking;
ADLTracking.prototype = 
{
	TRACK_UNKNOWN: "unknown",
	TRACK_SATISFIED: "satisfied",
	TRACK_NOTSATISFIED: "notSatisfied",
	TRACK_COMPLETED: "completed",
	TRACK_INCOMPLETE: "incomplete",
	mDirtyPro: false,
	mObjectives: null,
	mPrimaryObj: "_primary_",
	mProgress: ADLTracking.TRACK_UNKNOWN,
	mAttemptAbDur: null,
	mAttemptExDur: null,
	mAttempt: 0,
	setDirtyObj: ()
	{
		if (mObjectives != null)
		{
			for (var k in mObjectives)
			{
				if (mObjectives.hasOwnProperty(k)) // hasOwnProperty requires Safari 1.2
				{
					obj = mObjectives[k];
					obj.setDirtyObj();
				}
			}
		}
	}
}
