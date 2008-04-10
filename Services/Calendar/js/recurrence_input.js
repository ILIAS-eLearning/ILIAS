
function ilHideFrequencies()
{
	//form = document.getElementById("form_");
	//form.method = "GET";
	element = document.getElementById("sub_prop_interval_DAILY");
	element.style.display = "none";
	element = document.getElementById("sub_prop_interval_WEEKLY");
	element.style.display = "none";
	element = document.getElementById("sub_prop_WEEKLY_byday");
	element.style.display = "none";
	element = document.getElementById("sub_prop_interval_MONTHLY");
	element.style.display = "none";
	element = document.getElementById("sub_prop_interval_YEARLY");
	element.style.display = "none";
	ilShowSelected();
}
	
function ilShowSelected()
{
	select = document.getElementById('frequence');

	for(var i = 0; i < select.options.length; i++)
	{
		var value = select.options[i].value;
		var selected = select.options[i].selected;

		if(value == "DAILY" || value == "WEEKLY" || value == "MONTHLY" ||  value == "YEARLY")
		{
			var element = document.getElementById("sub_prop_interval_" + value);
			if(selected != true)
			{
				element.style.display = "none";
			}
			else
			{
				element.style.display = "";
			}
		}
	}
}