// JS port of ADL ADLSeqUtilities.java
// FAKE: only the functions used in rollup procedure
function ADLSeqUtilities()  
{
	this.satisfied = new Object();
	this.measure = new Object();
	this.status = new Object();
	
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
	//	alert(iObjID+" ,  "+iLearnerID+", "+iScopeID+", "+iMeasure);
		
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
		return 0.0;
	},
	
	setCourseStatus: function (iCourseID, iLearnerID, iSatisfied, iMeasure, iCompleted)
	{
		if(this.status[iCourseID] == null) this.status[iCourseID] = new Object();
		this.status[iCourseID][iLearnerID] =
			{satisfied: iSatisfied, measure: iMeasure, completed: iCompleted};
	}
}
var adl_seq_utilities = new ADLSeqUtilities();
