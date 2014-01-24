function ilHideFrequencies()
{
	sections = new Array('DAILY','WEEKLY','MONTHLY','YEARLY','UNTIL');
	
	// Hide all table rows
	for(section = 0;section < 5;section++)
	{
		for(part = 1;part < 4;part++)
		{
			if(element = document.getElementById('sub_' + sections[section] + '_' + part))
			{
				element.style.display = "none";
			}
		}
	}
	
	// get selected frequence
	if(element = document.getElementById('il_recurrence_1'))
	{
		if((index = element.selectedIndex) == 0)
		{
			return true;
		}
		value = element.options[index].value;

		for(section = 0; section < 5;section++)
		{
			if(value != sections[section] && sections[section] != 'UNTIL')
				continue;
			for(part = 1;part < 4; part++)
			{
				if(element = document.getElementById('sub_' + sections[section] + '_' + part))
				{
					element.style.display = "";
				}
			}
		}
	}
}

function ilUpdateSubTypeSelection(element_id)
{
	if(element = document.getElementById(element_id))
	{
		element.checked = 'checked';
	}
}
YAHOO.util.Event.onDOMReady(ilHideFrequencies);

