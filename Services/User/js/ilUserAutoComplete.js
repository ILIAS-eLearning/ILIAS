function ilUserAutoComplete(oResultData, sQuery, sResultMatch)
{
	var email = "";
	var needle = sQuery.replace("%", "");
	if((oResultData.email !== undefined) && oResultData.email.toUpperCase().indexOf(needle.toUpperCase()) > -1)
	{
		email = ", " + oResultData.email;
	}
	return oResultData.lastname + ", " + oResultData.firstname + " [" + oResultData.login + "]" + email;
} 