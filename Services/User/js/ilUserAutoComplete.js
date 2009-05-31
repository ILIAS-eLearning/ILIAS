function ilUserAutoComplete(oResultData, sQuery, sResultMatch)
{ 
	return oResultData.lastname + ", " + oResultData.firstname + " [" + oResultData.login + "]";
} 