function ilToggleAppointmentTime(check)
{
	var status;

	
	if(check.checked == true)
	{
		status = true;
	}
	else
	{
		status = false;
	}
	
	document.getElementById('start[time]_h').disabled = status;
	document.getElementById('start[time]_m').disabled = status;
	document.getElementById('end[time]_h').disabled = status;
	document.getElementById('end[time]_m').disabled = status;
	
	return;
}

function ilDisableAppointmentTime()
{
	return ilToggleAppointmentTime(document.getElementById('fullday'));
}
YAHOO.util.Event.onDOMReady(ilDisableAppointmentTime);
