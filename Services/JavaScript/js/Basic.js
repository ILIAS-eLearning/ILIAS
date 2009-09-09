/**
* Adds a function to the window onload event
*/
function ilAddOnLoad(func)
{
	if (!document.getElementById | !document.getElementsByTagName) return
	
	var oldonload=window.onload
	if (typeof window.onload != 'function')
	{
		window.onload = func
	}
	else
	{
		window.onload = function()
		{
			oldonload();
			func()
		}
	}
}

/**
* Adds a function to the window unonload event
*/
function ilAddOnUnload(func)
{
	if (!document.getElementById | !document.getElementsByTagName) return
	
	var oldonunload = window.onunload
	if (typeof window.onunload != 'function')
	{
		window.onunload = func
	}
	else
	{
		window.onunload = function()
		{
			oldonunload();
			func()
		}
	}
}

// The following functions have been in <skin>/functions.js before.
// @todo Revision of javascript function names and usage

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

function setCheckedTest(e)
{
	return true;
}

/**
 * Checks/unchecks checkboxes
 *
 * @param   string   the form name
 * @param   string   the checkbox name (or the first characters of the name, if unique)
 * @param   boolean  whether to check or to uncheck the element
 * @return  boolean  always true
 */
function setChecked(parent_el, checkbox_name, do_check){
	var e = document.forms[parent_el];
	if (!e)
	{
		e = document.getElementById(parent_el);
	}
	ilCheckBoxName = checkbox_name;
	els = YAHOO.util.Dom.getElementsBy(setCheckedTest, "input", e, null, null, null); 
	for (var i=0;i<els.length;i++)
	{
		if ((typeof els[i].name != 'undefined') && els[i].name.indexOf(checkbox_name) == 0
			&& els[i].disabled != true)
		{
			els[i].checked = do_check;
		}
	}
  return true;
} // end of the 'setCheckboxes()' function

/**
 * Checks/unchecks checkboxes
 *
 * @param   string   the form name
 * @param   string   the checkbox name (or the first characters of the name, if unique)
 * @param   boolean  whether to check or to uncheck the element
 * @return  boolean  always true
 */
function setCheckedById(the_form, id_name, do_check)
{
	for (var i=0;i<document.forms[the_form].elements.length;i++)
	{
		var e = document.forms[the_form].elements[i];
		if(e.id == id_name)
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

/**
 * Opens a chat window
 *
 * @param   object	the link which was clicked
 * @param   int		desired width of the new window
 * @param   int		desired height of the new window
 */
function openChatWindow(oLink, width, height)
{
	if(width == null)
	{
		width = screen.availWidth;
	}
	leftPos = (screen.availWidth / 2)- (width / 2);	
	
	if(height == null)
	{
		height = screen.availHeight;
	}
	topPos = (screen.availHeight / 2)- (height / 2);				

	oChatWindow = window.open(
		oLink.href, 
		oLink.target, 
		'width=' + width + ',height=' + height + ',left=' + leftPos + ',top=' + topPos +
		',resizable=yes,scrollbars=yes,status=yes,toolbar=yes,menubar=yes,location=yes'
	);

	oChatWindow.focus();
}

// Set focus for screen reader
function ilGoSRFocus(id)
{
	obj = document.getElementById(id);
	if (obj)
	{
		obj.focus();
		self.location.hash = id;
	}
}

// Set focus for screen reader
function ilScreenReaderFocus()
{
	obj = document.getElementById("il_message_focus");
	if (obj)
	{
		obj.focus();
		self.location.hash = 'il_message_focus';
	}
	else
	{
		obj = document.getElementById("il_lm_head");
		if (obj && self.location.hash == '')
		{
			obj.focus();
			self.location.hash = 'il_lm_head';
		}
		else
		{
			obj = document.getElementById("il_mhead_t_focus");
			if (obj && self.location.hash == '')
			{
				obj.focus();
				self.location.hash = 'il_mhead_t_focus';
			}
		}
	}
}

function ilSubmitOnEnter(ev, form)
{
	if (typeof ev != 'undefined' && typeof ev.keyCode != 'undefined')
	{
		if (ev.keyCode == 13)
		{
			form.submit();
			return false;
		}
	}
	return true;
}