function initDateDurationPicker(picker_id, picker2_id, toggle_id) {		
	var el = $("#"+picker_id);
	var dp = $(el).data("DateTimePicker"); 
	var el2 = $("#"+picker2_id);
	var dp2 = $(el2).data("DateTimePicker"); 	
	
	
	// init		
	
	// set limit by current date of other picker
	if(dp2.date())
	{		
		dp.maxDate(dp2.date());
	}
	if(dp.date())
	{		
		dp2.minDate(dp.date()); 
		
		// store current value for diff magic 
		$(el).data("DateTimePickerOld", dp.date());
	}	


	// onchange
	
	$(el).on("dp.change", function(e) { 
		
		// limit to value of end picker
		dp2.minDate(e.date); 

		// keep diff the same
		var old = $(this).data("DateTimePickerOld"); 
		
		if(old && dp2.date()) { 
			var diff = dp2.date().diff(old); 
			dp2.date(e.date.clone().add(diff)); 
		}

		// keep current date for diff parsing (see above);
		$(this).data("DateTimePickerOld", e.date);		
		
	});
		
	$(el2).on("dp.change", function(e) { 	
		
		// limit to value of start picker
		dp.maxDate(e.date);		
	});

		
	// toggle

	if(toggle_id)
	{
		var toggle = $("#"+toggle_id);
		var full_format = dp.format();
		
		
		// init
		
		if($(toggle).prop("checked")) {  
			var format = dp.format();
			dp.format(format.substr(0, 10));
			format = dp2.format();
			dp2.format(format.substr(0, 10));
		}


		// onchange
		
		$(toggle).change(function(e) { 
			
			if(!$(this).prop("checked")) {  
				dp.format(full_format); 
				dp2.format(full_format); 				
			} 
			else { 
				var short_format = full_format.substr(0, 10);
				dp.format(short_format); 
				dp2.format(short_format); 				
			}
			
		});	
	}	
}








function ilInitDuration(event,args,prefix)
{
	if (ilIsFulltime(prefix)) 
	{
		old = new Date(
			document.getElementById(prefix + "[start][date]_y").options[document.getElementById(prefix + "[start][date]_y").selectedIndex].value,
			document.getElementById(prefix + "[start][date]_m").selectedIndex,
			document.getElementById(prefix + "[start][date]_d").selectedIndex + 1
		);
	}
	else
	{
		old = new Date(
			document.getElementById(prefix + "[start][date]_y").options[document.getElementById(prefix + "[start][date]_y").selectedIndex].value, 
			document.getElementById(prefix + "[start][date]_m").selectedIndex, 
			document.getElementById(prefix + "[start][date]_d").selectedIndex + 1,
			document.getElementById(prefix + "[start][time]_h").selectedIndex,
			document.getElementById(prefix + "[start][time]_m").selectedIndex,
			0
		);
	}
}

function ilIsFulltime(prefix)
{
	var checkFull = document.getElementById(prefix + "_fulltime");
	
	if(checkFull != null)
	{
		return checkFull.checked;
	}
	// No fulltime toggle
	return document.getElementById(prefix + "[start][time]_h") == null ? true : false;
}

function ilUpdateEndDate(cal)
{
	var start;
	var end;
	var diff;

	if (ilIsFulltime(prefix)) 
	{
		start = new Date(
			document.getElementById(prefix + "[start][date]_y").options[document.getElementById(prefix + "[start][date]_y").selectedIndex].value, 
			document.getElementById(prefix + "[start][date]_m").selectedIndex, 
			document.getElementById(prefix + "[start][date]_d").selectedIndex + 1);
		end = new Date(
			document.getElementById(prefix + "[end][date]_y").options[document.getElementById(prefix + "[end][date]_y").selectedIndex].value, 
			document.getElementById(prefix + "[end][date]_m").selectedIndex, 
			document.getElementById(prefix + "[end][date]_d").selectedIndex + 1);
	}
	else
	{
		start = new Date(
			document.getElementById(prefix + "[start][date]_y").options[document.getElementById(prefix + "[start][date]_y").selectedIndex].value, 
			document.getElementById(prefix + "[start][date]_m").selectedIndex, 
			document.getElementById(prefix + "[start][date]_d").selectedIndex + 1,
			document.getElementById(prefix + "[start][time]_h").selectedIndex,
			document.getElementById(prefix + "[start][time]_m").selectedIndex,
			0
		);
			

		end = new Date(
			document.getElementById(prefix + "[end][date]_y").options[document.getElementById(prefix + "[end][date]_y").selectedIndex].value, 
			document.getElementById(prefix + "[end][date]_m").selectedIndex, 
			document.getElementById(prefix + "[end][date]_d").selectedIndex + 1,
			document.getElementById(prefix + "[end][time]_h").selectedIndex,
			document.getElementById(prefix + "[end][time]_m").selectedIndex,
			0
		);
			
		
	}
	diff = end.getTime() - old.getTime();
	end.setTime(start.getTime() + diff);

	// Save current date
	old = start;
	
	//alert(end.toDateString());
	
	var year = document.getElementById(prefix + "[end][date]_y");
	for(i = 0; i < year.options.length;i++)
	{
		//alert(year.options[i].value + " " + end.getFullYear());
		if(year.options[i].value == end.getFullYear())
		{
			year.selectedIndex = i;
			break;
		}
	}
	
	var month = document.getElementById(prefix + "[end][date]_m");
	//alert("Month: " + end.getMonth() + " length: " + month.options.length + " " + end.toDateString());
	for(i = 0; i < month.options.length;i++)
	{
		//alert(month.options[i].value + " " + end.getMonth());
		if((month.options[i].value - 1) == end.getMonth())
		{
			//alert("Hit " + i);
			month.selectedIndex = i;
			break;
		}
	}

	var day = document.getElementById(prefix + "[end][date]_d");
	for(i = 0; i < day.options.length;i++)
	{
		if(day.options[i].value == end.getDate())
		{
			day.selectedIndex = i;
			break;
		}
	}
	
	if(ilIsFulltime(prefix))
	{
		return;
	}
	
	var hour = document.getElementById(prefix + "[end][time]_h");
	for(i = 0; i < hour.options.length;i++)
	{
		if(hour.options[i].value == end.getHours())
		{
			hour.selectedIndex = i;
			break;
		}
	}
	
	var minute = document.getElementById(prefix + "[end][time]_m");
	for(i = 0; i < minute.options.length;i++)
	{
		if(minute.options[i].value == end.getMinutes())
		{
			minute.selectedIndex = i;
			break;
		}
	}
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
