function popup_window(url, x1, x2, y1, y2)
{
	var xpos;

	xpos = screen.availWidth / 100 * x1;
	ypos = screen.availHeight / 100 * y1;
	xwidth = (screen.availWidth / 100 * (x2 - x1)) - 5;
	yheight = (screen.availHeight / 100 * (y2 - y1)) - 30;

	window.open(url,"list","height=" + yheight + ",width=" + xwidth + ",left=" +xpos + ",ScreenX=" + xpos + ",ScreenY=" + ypos + ",top=" + ypos + ",resizable=yes,menubar=no,status=no,directories=no,toolbar=no,scrollbars=yes");

	return false;
}

function CheckAll()
{
	if(document.cmd.all)
	{
		var c = document.cmd.all.checked;
	}
	for (var i=0;i<document.cmd.elements.length;i++)
	{
		var e = document.cmd.elements[i];
 	  	if(e.name != 'all') e.checked = c;
   	}
}

function isEmpty(form, a_values, a_checks) 
{	
	feed_back = "";
	
	if (a_values != "")
	{
		if (a_values == "all")
		{
			for(var i=0;i<form.length;i++)
			{				
				if (form.elements[i].type == "text" || form.elements[i].type == "textarea")
				{
					if (form.elements[i].value == "")
						feed_back += "-> " + form.elements[i].id + "\n";
				}
			}
		}
	}
	
	if (feed_back != "") {
		alert("Please insert these data:\n\n" + feed_back);
		return false;
	}
	
	return true;
}

function printPage()
{
	window.print();
	return true;
}

function CheckAllBoxes(form){
	if(form.all)
	{
		var c = form.all.checked;
	}
	for (var i=0;i<form.elements.length;i++)
	{
		var e = form.elements[i];
 	  	if(e.name != 'all') e.checked = c;
   	}
}

/**
 * Checks/unchecks checkboxes
 *
 * @param   string   the form name
 * @param   string   the checkbox name (or the first characters of the name, if unique)
 * @param   boolean  whether to check or to uncheck the element
 * @return  boolean  always true
 */
function setChecked(the_form, checkbox_name, do_check)
{
	for (var i=0;i<document.forms[the_form].elements.length;i++)
	{
		var e = document.forms[the_form].elements[i];
		if (e.name.indexOf(checkbox_name) == 0)
		{
			e.checked = do_check;
		}
	}
  return true;
} // end of the 'setCheckboxes()' function

/**
 * Disables a submit button and adds a hidden input with the name and the value
 * of the button. This helps to prevent multiple clicking of submit buttons to which
 * could lead to duplicated database values.
 * This function also disables all other buttons in the given form.
 * Tested in IE 6, Firefox 1.5, Safari, Opera 8.5
 *
 * @param   string   the form name
 * @param   object   the submit button object
 * @param   string   a new text which replaces the text of the disabled button
 *                   or an empty string for no changes
 */
function disableButton(formname, button, new_text)
{
	var name = button.name;
	var value = button.value;
	var hidden = document.createElement("input");
	button.name = name + "_1";
	if (new_text.length > 0)
	{
		button.value = new_text;
	}
	button.className = 'submit_disabled';
	hidden.name = name;
	hidden.type = "hidden";
	hidden.value = value;
	document.forms[formname].appendChild(hidden);
	button.disabled = true;
	for (var i = 0; i < document.forms[formname].elements.length; i++)
	{
		if (document.forms[formname].elements[i].type == 'submit')
		{
			document.forms[formname].elements[i].disabled = true;
		}
	}
	document.forms[formname].submit();
}
