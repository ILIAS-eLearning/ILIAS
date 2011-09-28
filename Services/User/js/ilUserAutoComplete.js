function ilUserAutoComplete(oResultData, sQuery, sResultMatch)
{
	var email = "";
	if((oResultData.email !== undefined) && oResultData.email.toUpperCase().indexOf(sQuery.toUpperCase()) > -1)
	{
		email = ", " + oResultData.email;
	}
	return oResultData.lastname + ", " + oResultData.firstname + " [" + oResultData.login + "]" + email;
} 