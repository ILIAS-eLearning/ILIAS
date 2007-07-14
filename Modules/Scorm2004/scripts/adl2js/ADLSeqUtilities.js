// JS port of ADL ADLSeqUtilities.java
// FAKE: only the functions used in rollup procedure
function ADLSeqUtilities()  
{
}
//this.ADLSeqUtilities = ADLSeqUtilities;
ADLSeqUtilities.prototype = 
{
	setGlobalObjSatisfied: (iObjID, iLearnerID, iScopeID, iSatisfied)
	{
		ADLSeqUtilities[iObjID][iLearnerID][iScopeID]["Satisfied"] = iSatisfied;
	},
	
	getGlobalObjSatisfied: (iObjID, iLearnerID, iScopeID)
	{
		return ADLSeqUtilities[iObjID][iLearnerID][iScopeID]["Satisfied"];
	},
	
	setGlobalObjMeasure: (iObjID, iLearnerID,iScopeID, iMeasure)
	{
		ADLSeqUtilities[iObjID][iLearnerID][iScopeID]["Measure"] = iMeasure;
	},
	
	getGlobalObjSatisfied(iObjID, iLearnerID, iScopeID)
	{
		return ADLSeqUtilities[iObjID][iLearnerID][iScopeID]["Measure"];
	}
}
