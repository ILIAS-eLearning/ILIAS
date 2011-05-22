// JS port of ADL ADLSeqUtilities.java
// FAKE: only the functions used in rollup procedure
function ADLSeqUtilities()  
{
	this.satisfied = new Object();
	this.measure = new Object();
	this.status = new Object();
	this.score_raw = new Object();
	this.score_min = new Object();
	this.score_max = new Object();
	this.completion_status = new Object();
	this.progress_measure = new Object();
	
}
ADLSeqUtilities.prototype = 
{
	// usage: adl_seq_utilities.setGlobalObjSatisfied(2, 10, "scope", true);
	setGlobalObjSatisfied: function (iObjID, iLearnerID, iScopeID, iSatisfied)
	{
	//	alert(iObjID+" ,  "+iLearnerID+ ", "+iScopeID+", "+iSatisfied);
		if(this.satisfied[iObjID] == null) this.satisfied[iObjID] = new Object();
		if(this.satisfied[iObjID][iLearnerID] == null) this.satisfied[iObjID][iLearnerID] = new Object();
		this.satisfied[iObjID][iLearnerID][iScopeID] = iSatisfied;
	},
	
	getGlobalObjSatisfied: function (iObjID, iLearnerID, iScopeID)
	{
		if (this.satisfied[iObjID] != null
			&& this.satisfied[iObjID][iLearnerID] != null
			&& this.satisfied[iObjID][iLearnerID][iScopeID] != null)
		{
			return this.satisfied[iObjID][iLearnerID][iScopeID];
		}
		return null;
	},
	
	setGlobalObjMeasure: function (iObjID, iLearnerID,iScopeID, iMeasure)
	{
	   // alert(iObjID+" ,  "+iLearnerID+", "+iScopeID+", "+iMeasure);
		
		if(this.measure[iObjID] == null) this.measure[iObjID] = new Object();
		if(this.measure[iObjID][iLearnerID] == null) this.measure[iObjID][iLearnerID] = new Object();
		this.measure[iObjID][iLearnerID][iScopeID] = iMeasure;
	},
	
	getGlobalObjMeasure: function (iObjID, iLearnerID, iScopeID)
	{
	
		//alert("GET GLOBAL"+iObjID+""+iLearnerID+""+iScopeID);
		if (this.measure[iObjID] != null
			&& this.measure[iObjID][iLearnerID]
			&& this.measure[iObjID][iLearnerID][iScopeID])
		{
			return this.measure[iObjID][iLearnerID][iScopeID];
		}
		return null;
	},
	
	setCourseStatus: function (iCourseID, iLearnerID, iSatisfied, iMeasure, iCompleted)
	{
		//fix for IE
		if (this.status==null) {
			this.status = new Object();
		}
		
		if(this.status[iCourseID] == null) this.status[iCourseID] = new Object();
			this.status[iCourseID][iLearnerID] =
			{satisfied: iSatisfied, measure: iMeasure, completed: iCompleted};
	},
	
	getGlobalObjRawScore: function (iObjID, iLearnerID, iScopeID)
	{
		if (this.score_raw[iObjID] != null
			&& this.score_raw[iObjID][iLearnerID]
			&& this.score_raw[iObjID][iLearnerID][iScopeID])
		{
			return this.score_raw[iObjID][iLearnerID][iScopeID];
		}
		return null;
	},
	
	setGlobalObjRawScore: function (iObjID, iLearnerID,iScopeID, iScore_Raw)
	{
	   // alert(iObjID+" ,  "+iLearnerID+", "+iScopeID+", "+iScore_Raw);
		
		if(this.score_raw[iObjID] == null) this.score_raw[iObjID] = new Object();
		if(this.score_raw[iObjID][iLearnerID] == null) this.score_raw[iObjID][iLearnerID] = new Object();
		this.score_raw[iObjID][iLearnerID][iScopeID] = iScore_Raw;
	},
	
	getGlobalObjMinScore: function (iObjID, iLearnerID, iScopeID)
	{
		if (this.score_min[iObjID] != null
			&& this.score_min[iObjID][iLearnerID]
			&& this.score_min[iObjID][iLearnerID][iScopeID])
		{
			return this.score_min[iObjID][iLearnerID][iScopeID];
		}
		return null;
	},
	
	setGlobalObjMinScore: function (iObjID, iLearnerID,iScopeID, iScore_Min)
	{
	   // alert(iObjID+" ,  "+iLearnerID+", "+iScopeID+", "+iScore_Min);
		
		if(this.score_min[iObjID] == null) this.score_min[iObjID] = new Object();
		if(this.score_min[iObjID][iLearnerID] == null) this.score_min[iObjID][iLearnerID] = new Object();
		this.score_min[iObjID][iLearnerID][iScopeID] = iScore_Min;
	},
	
	getGlobalObjMaxScore: function (iObjID, iLearnerID, iScopeID)
	{
		if (this.score_max[iObjID] != null
			&& this.score_max[iObjID][iLearnerID]
			&& this.score_max[iObjID][iLearnerID][iScopeID])
		{
			return this.score_max[iObjID][iLearnerID][iScopeID];
		}
		return null;
	},
	
	setGlobalObjMaxScore: function (iObjID, iLearnerID,iScopeID, iScore_Max)
	{
	   // alert(iObjID+" ,  "+iLearnerID+", "+iScopeID+", "+iScore_Max);
		
		if(this.score_max[iObjID] == null) this.score_max[iObjID] = new Object();
		if(this.score_max[iObjID][iLearnerID] == null) this.score_max[iObjID][iLearnerID] = new Object();
		this.score_max[iObjID][iLearnerID][iScopeID] = iScore_Max;
	},
	
	getGlobalObjProgressMeasure: function (iObjID, iLearnerID, iScopeID)
	{
		if (this.progress_measure[iObjID] != null
			&& this.progress_measure[iObjID][iLearnerID]
			&& this.progress_measure[iObjID][iLearnerID][iScopeID])
		{
			return this.progress_measure[iObjID][iLearnerID][iScopeID];
		}
		return null;
	},
	
	setGlobalObjProgressMeasure: function (iObjID, iLearnerID,iScopeID, iProgressMeasure)
	{
	   // alert(iObjID+" ,  "+iLearnerID+", "+iScopeID+", "+iScore_Raw);
		
		if(this.progress_measure[iObjID] == null) this.progress_measure[iObjID] = new Object();
		if(this.progress_measure[iObjID][iLearnerID] == null) this.progress_measure[iObjID][iLearnerID] = new Object();
		this.progress_measure[iObjID][iLearnerID][iScopeID] = iProgressMeasure;
	},
	
	getGlobalObjCompletion: function (iObjID, iLearnerID, iScopeID)
	{
		if(this.completion_status[iObjID] != null
			&& this.completion_status[iObjID][iLearnerID]
			&& this.completion_status[iObjID][iLearnerID][iScopeID])
		{
			return this.completion_status[iObjID][iLearnerID][iScopeID];
		}
		return null;
	},

	setGlobalObjCompletion: function(iObjID, iLearnerID,iScopeID, iCompletionStatus)
	{
		if(this.completion_status[iObjID] == null) this.completion_status[iObjID] = new Object();
		if(this.completion_status[iObjID][iLearnerID] == null) this.completion_status[iObjID][iLearnerID] = new Object();
		this.completion_status[iObjID][iLearnerID][iScopeID] = iCompletionStatus;
	}
	
};
var adl_seq_utilities = new ADLSeqUtilities();
