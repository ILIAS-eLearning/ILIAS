function ilToggleNotification(ids)
{
	var indexValue;
	
	index = document.getElementById('calendar').selectedIndex;
	value = document.getElementById('calendar').options[index].value;
	
	for(var i = 0; i < ids.length; i++)
	{
		if(ids[i] == value)
		{
			document.getElementById('not').disabled = false;
			return;
		}
	}
	document.getElementById('not').disabled = true;
	document.getElementById('not').checked = false;
	return;	
	
}

