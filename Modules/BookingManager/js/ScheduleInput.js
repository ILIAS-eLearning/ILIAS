ilAddOnLoad(ilFormMultiInit)

function ilFormMultiInit()
{
	var obj = document.getElementsByTagName('input');
	for(var i=0;i<obj.length;i++)
	{
		if(/ilMultiAdd~/.test(obj[i].id))
		{
			function fnCallback(e) { ilFormMultiAddEvent(e) }
			YAHOO.util.Event.addListener(obj[i], "click", fnCallback);
		}
		if(/ilMultiRmv~/.test(obj[i].id))
		{
			function fnCallback(e) { ilFormMultiRemoveEvent(e) }
			YAHOO.util.Event.addListener(obj[i], "click", fnCallback);
		}
	}
}

function ilFormMultiAdd(id)
{
	// find original field
	var row = document.getElementById('ilFormField~'+id);
	if(!row)
	{
		return;
	}
	
	// count original & copies
    var max = 0;
	for(var i=0;i<row.parentNode.childNodes.length;i++)
	{		
		var id = row.parentNode.childNodes[i].id;
		if(id)
		{
			var parts = row.parentNode.childNodes[i].id.split("~");
			if(parts[0] == "ilFormField" && parts[2] > max)
			{
				max = parseInt(parts[2]);
			}
		}
	}	
	max = max+1;
	
	// create clone and fix ids
	var clone = row.cloneNode(true);
	for(var i=0;i<clone.childNodes.length;i++)
	{
		if(/ilMultiAdd~/.test(clone.childNodes[i].id))
		{
			YAHOO.util.Event.removeListener(clone.childNodes[i], "click");
			
			clone.childNodes[i].id = fixId(clone.childNodes[i].id, max);
			clone.childNodes[i].style.display = "none";
		}
		if(/ilMultiRmv~/.test(clone.childNodes[i].id))
		{
			YAHOO.util.Event.removeListener(clone.childNodes[i], "click");
			
			clone.childNodes[i].id = fixId(clone.childNodes[i].id, max);
			clone.childNodes[i].style.display = "";

			function fnCallback(e) { ilFormMultiRemoveEvent(e) }
			YAHOO.util.Event.addListener(clone.childNodes[i], "click", fnCallback);
		}
	}

	// insert clone into html
	clone.id = fixId(clone.id, max);
	row.parentNode.appendChild(clone);
}

function ilFormMultiAddEvent(e)
{
	var target = (e.currentTarget) ? e.currentTarget : e.srcElement;
	var id = target.id.substr(11);
	ilFormMultiAdd(id);
}

function fixId(old_id, new_count)
{
	var parts = old_id.split("~");
	return parts[0]+"~"+parts[1]+"~"+new_count;
}

function ilFormMultiRemoveEvent(e)
{
	var target = (e.currentTarget) ? e.currentTarget : e.srcElement;
	var id = target.id.substr(11);
	if(id.substr(-2) != "~0")
	{
		var row = document.getElementById('ilFormField~'+id);
		row.parentNode.removeChild(row);
	}
}