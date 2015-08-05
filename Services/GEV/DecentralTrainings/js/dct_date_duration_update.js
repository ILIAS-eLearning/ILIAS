function ilInitDuration(event,args,prefix)
{
	var prefix = "time";
	old = new Date(
			document.getElementById("date[date]_y").options[document.getElementById("date[date]_y").selectedIndex].value, 
			document.getElementById("date[date]_m").selectedIndex, 
			document.getElementById("date[date]_d").selectedIndex + 1,
			document.getElementById(prefix + "[start][time]_h").selectedIndex,
			document.getElementById(prefix + "[start][time]_m").selectedIndex,
			0
		);
	
	document.getElementById(prefix + "[start][time]_m").setAttribute("onChange", "ilUpdateEndDate()");

	document.getElementById(prefix + "[end][time]_h").disabled = true;
	document.getElementById(prefix + "[end][time]_m").disabled = true;
	
	$("#form_").submit(function() {
		document.getElementById(prefix + "[end][time]_h").disabled = false;
		document.getElementById(prefix + "[end][time]_m").disabled = false;
	});
}

function ilUpdateEndDate(cal)
{
	var start;	
	var end;
	var diff;
	
		start = new Date(
			document.getElementById("date[date]_y").options[document.getElementById("date[date]_y").selectedIndex].value, 
			document.getElementById("date[date]_m").selectedIndex, 
			document.getElementById("date[date]_d").selectedIndex + 1,
			document.getElementById(prefix + "[start][time]_h").selectedIndex,
			document.getElementById(prefix + "[start][time]_m").selectedIndex,
			0
		);
			

		end = new Date(
			document.getElementById("date[date]_y").options[document.getElementById("date[date]_y").selectedIndex].value, 
			document.getElementById("date[date]_m").selectedIndex, 
			document.getElementById("date[date]_d").selectedIndex + 1,
			document.getElementById(prefix + "[end][time]_h").selectedIndex,
			document.getElementById(prefix + "[end][time]_m").selectedIndex,
			0
		);
		
	diff = end.getTime() - old.getTime();
	end.setTime(start.getTime() + diff);

	//alert(end.toDateString());
	var end_hours_index = end.getHours();
	var end_minute_index = end.getMinutes();
	var start_minute_index = start.getMinutes();
	var old_minute_index = old.getMinutes();

	if((end_minute_index >= 12) && (start_minute_index > old_minute_index)){
		end_hours_index = end_hours_index + 1;
		end_minute_index = end_minute_index - 12;
	}

	if((end_minute_index >= 12) && (start_minute_index < old_minute_index)){
		end_minute_index = 12 - (60 - end_minute_index);
	}

	var hour = document.getElementById(prefix + "[end][time]_h");
	for(i = 0; i < hour.options.length;i++)
	{
		if(i == end_hours_index)
		{
			hour.selectedIndex = i;
			break;
		}
	}
	
	var minute = document.getElementById(prefix + "[end][time]_m");
	for(i = 0; i < minute.options.length;i++)
	{
		if(i == end_minute_index)
		{
			minute.selectedIndex = i;
			break;
		}
	}

	// Save current date
	old = start;
}

function ilToggleFullTime(check,prefix)
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

	if(status)
	{
		old.setHours(0);
		old.setMinutes(0);
	}
	else
	{
		old.setHours(document.getElementById(prefix + "[start][time]_h").selectedIndex);
		old.setMinutes(document.getElementById(prefix + "[start][time]_m").selectedIndex);
	}

	document.getElementById(prefix + '[start][time]_h').disabled = status;
	document.getElementById(prefix + '[start][time]_m').disabled = status;
	document.getElementById(prefix + '[end][time]_h').disabled = status;
	document.getElementById(prefix + '[end][time]_m').disabled = status;
	
	return;
}

function ilDisableSessionTime(event,args,prefix)
{
	return ilToggleFullTime(document.getElementById(prefix + "_fulltime"),prefix);
}