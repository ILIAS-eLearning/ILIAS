<script type="text/javascript">
<!--
var maxinput = -1;

function createTextInput(name, size)
{
	input = document.createElement('input');
	input.setAttribute("type","text");
	input.setAttribute("size", size);
	input.setAttribute("name", name);
	input.setAttribute("id", name);
	return input;
}

function isAppletParam(e)
{
	if (e.id == 'singleFlashAppletParam')
	{
		return true;
	}
	else
	{
		return false;
	}
}

function deleteParameter(e)
{
	if (confirm("{TEXT_CONFIRM_DELETE_PARAMETER}"))
	{
		this.checked = false;
		this.parentNode.parentNode.removeChild(this.parentNode);
	}
	this.checked = false;
}

function newParamClicked()
{
	strName = '{TEXT_NAME}';
	strValue = '{TEXT_VALUE}';
	
	if (maxinput == -1)
	{
		allinputs = YAHOO.util.Dom.getElementsBy(isAppletParam, 'div');
		maxinput = allinputs.length;
	}
	else
	{
		maxinput++;
	}
	nameinput = createTextInput('{POST_VAR}[flash_param_name][' + maxinput + ']', 25);
	valueinput = createTextInput('{POST_VAR}[flash_param_value][' + maxinput + ']', 25);
	div = document.createElement('div');
	div.setAttribute("id", "singleFlashAppletParam");
	div.setAttribute("style", "padding: 0.5em 0;");
	label = document.createElement('label');
	label.setAttribute("for", '{POST_VAR}_flash_param_name[' + maxinput + ']');
	labeltext = document.createTextNode(strName + " ");
	label.appendChild(labeltext);
	div.appendChild(label);
	div.appendChild(nameinput);
	label = document.createElement('label');
	label.setAttribute("for", '{POST_VAR}_flash_param_value[' + maxinput + ']');
	labeltext = document.createTextNode(" " + strValue + " ");
	label.appendChild(labeltext);
	div.appendChild(label);
	div.appendChild(valueinput);
	div.appendChild(document.createTextNode(" "));
	checkbox = document.createElement('input');
	checkbox.setAttribute("type", "checkbox");
	checkbox.setAttribute("name", '{POST_VAR}[flash_param_delete][' + maxinput + ']');
	checkbox.className = '{POST_VAR}_deleteFlashParam';
	checkbox.setAttribute("id", '{POST_VAR}_flash_param_delete[' + maxinput + ']');
	checkbox.setAttribute("value", "1");
	div.appendChild(checkbox);
	label = document.createElement('label');
	label.setAttribute("for", '{POST_VAR}_flash_param_delete[' + maxinput + ']');
	labeltext = document.createTextNode(" " + '{TEXT_DELETE_PARAM}' + " ");
	label.appendChild(labeltext);
	div.appendChild(label);
	paramContainer = YAHOO.util.Dom.get("flashAppletParams");
	paramContainer.appendChild(div);
	YAHOO.util.Event.addListener('{POST_VAR}_flash_param_delete[' + maxinput + ']', "click", deleteParameter);
	return false;
}

function flashWizardEvents_{POST_VAR}(e)
{
	deletebuttons = YAHOO.util.Dom.getElementsByClassName('{POST_VAR}_deleteFlashParam');
	for (i = 0; i < deletebuttons.length; i++)
	{
		button = deletebuttons[i];
		YAHOO.util.Event.addListener(button, 'click', deleteParameter);
	}
}

YAHOO.util.Event.onDOMReady(flashWizardEvents_{POST_VAR});
//-->
</script>
