function ilAugment (oSelf, oOther)
{
	if (oSelf == null)
	{
		oSelf = {};
	}
	for (var i = 1; i < arguments.length; i++)
	{
		var o = arguments[i];
		if (typeof(o) != 'undefined' && o != null)
		{
			for (var j in o)
			{
				oSelf[j] = o[j];
			}
		}
	}
	return oSelf;
}
