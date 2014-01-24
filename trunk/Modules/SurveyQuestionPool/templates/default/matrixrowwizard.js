function rowWizardCleanElements(rootel)
{
	var textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', rootel);
	for (i = 0; i < textinputs.length; i++) textinputs[i].value = '';
}

function rowWizardReindexRows(rootel, postvar)
{
	var rows = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'tr', rootel);
	for (i = 0; i < rows.length; i++)
	{
		// set row class
		YAHOO.util.Dom.removeClass(rows[i], "odd");
		YAHOO.util.Dom.removeClass(rows[i], "even");
		YAHOO.util.Dom.removeClass(rows[i], "first");
		YAHOO.util.Dom.removeClass(rows[i], "last");
		var alter = (i % 2 == 0) ? "even" : "odd";
		YAHOO.util.Dom.addClass(rows[i], alter);
		var add = (i == 0) ? "first" : ((i == rows.length-1) ? "last" : "");
		if (add.length > 0) YAHOO.util.Dom.addClass(rows[i], add);

		// change id and name of text fields
		var textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', rows[i]);
		for (j = 0; j < textinputs.length; j++)
		{
			if (textinputs[j].id.indexOf('[answer]') >= 0)
			{
				textinputs[j].id = postvar + '[answer][' + i + ']';
				textinputs[j].name = postvar + '[answer][' + i + ']';
			} 
			else if (textinputs[j].id.indexOf('[label]') >= 0)
			{
				textinputs[j].id = postvar + '[label][' + i + ']';
				textinputs[j].name = postvar + '[label][' + i + ']';
			} 
		}

		// change id and name of checkboxes
		var checkboxes = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'checkbox') ? true : false; }, 'input', rows[i]);
		for (j = 0; j < checkboxes.length; j++)
		{
			if (checkboxes[j].id.indexOf('[other]') >= 0)
			{
				checkboxes[j].id = postvar + '[other][' + i + ']';
				checkboxes[j].name = postvar + '[other][' + i + ']';
			} 
		}

		var addbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'matrixrowwizard_add') ? true : false; }, 'input', rows[i]);
		for (j = 0; j < addbuttons.length; j++)
		{
			addbuttons[j].id = 'add_' + postvar + '[' + i + ']';
			addbuttons[j].name = 'cmd[add' + postvar + '][' + i + ']';
			YAHOO.util.Event.addListener(addbuttons[j], 'click', rowWizardAddRow);
		}
		var removebuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'matrixrowwizard_remove') ? true : false; }, 'input', rows[i]);
		for (j = 0; j < removebuttons.length; j++)
		{
			removebuttons[j].id = 'remove_' + postvar + '[' + i + ']';
			removebuttons[j].name = 'cmd[remove' + postvar + '][' + i + ']';
			YAHOO.util.Event.addListener(removebuttons[j], 'click', rowWizardRemoveRow);
		}
		var upbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'matrixrowwizard_up') ? true : false; }, 'input', rows[i]);
		if (upbuttons.length > 0)
		{
			for (j = 0; j < upbuttons.length; j++)
			{
				upbuttons[j].id = 'up_' + postvar + '[' + i + ']';
				upbuttons[j].name = 'cmd[up' + postvar + '][' + i + ']';
				YAHOO.util.Event.addListener(upbuttons[j], 'click', rowWizardMoveRowUp);
			}
		}
		var downbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'matrixrowwizard_down') ? true : false; }, 'input', rows[i]);
		if (downbuttons.length > 0)
		{
			for (j = 0; j < downbuttons.length; j++)
			{
				downbuttons[j].id = 'down_' + postvar + '[' + i + ']';
				downbuttons[j].name = 'cmd[down' + postvar + '][' + i + ']';
				YAHOO.util.Event.addListener(downbuttons[j], 'click', rowWizardMoveRowDown);
			}
		}
	}
}

function rowWizardRemoveListeners(rootel)
{
	var addbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'matrixrowwizard_add') ? true : false; }, 'input', rootel);
	for (i = 0; i < addbuttons.length; i++)
	{
		YAHOO.util.Event.purgeElement(addbuttons[i]);
	}
	var removebuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'matrixrowwizard_remove') ? true : false; }, 'input', rootel);
	for (i = 0; i < removebuttons.length; i++)
	{
		YAHOO.util.Event.purgeElement(removebuttons[i]);
	}
	var upbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'matrixrowwizard_up') ? true : false; }, 'input', rootel);
	for (i = 0; i < upbuttons.length; i++)
	{
		YAHOO.util.Event.purgeElement(upbuttons[i]);
	}
	var downbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'matrixrowwizard_down') ? true : false; }, 'input', rootel);
	for (i = 0; i < downbuttons.length; i++)
	{
		YAHOO.util.Event.purgeElement(downbuttons[i]);
	}
}

function rowWizardAddRow(e, obj)
{
	var row = this.parentNode.parentNode;
	var tbody = row.parentNode;
	rowWizardRemoveListeners(tbody);
	var rowclone = row.cloneNode(true);
	rowWizardCleanElements(rowclone);
	
	var trs = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'tr', tbody);
	var parentindex = 0;
	for (i = 0; i < trs.length; i++)
	{
		if (trs[i] == row) parentindex = i+1;
	}
	if (parentindex == trs.length)
	{
		tbody.appendChild(rowclone);
	}
	else
	{
		tbody.insertBefore(rowclone, trs[parentindex]);
	}
	rowWizardReindexRows(tbody, tbody.parentNode.parentNode.id);
	var textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', rowclone);
	textinputs[0].focus();
	return false;
}

function rowWizardRemoveRow(e, obj)
{
	var row = this.parentNode.parentNode;
	var tbody = row.parentNode;
	var trs = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'tr', tbody);
	if (trs.length == 1)
	{
		rowWizardCleanElements(trs[0]);
	}
	else
	{
		tbody.removeChild(row);
		rowWizardRemoveListeners(tbody);
		rowWizardReindexRows(tbody, tbody.parentNode.parentNode.id);
	}
}

function rowWizardMoveRowUp(e, obj)
{
	var row = this.parentNode.parentNode;
	var tbody = row.parentNode;
	var rows = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'tr', tbody);
	var foundindex = 0;
	for (i = 0; i < rows.length; i++)
	{
		if (rows[i] == row) foundindex = i;
	}

	if (foundindex > 0)
	{
		rowWizardRemoveListeners(tbody);
		temp = rows[foundindex-1];
		rows[foundindex-1] = row;
		rows[foundindex] = temp;
		children = tbody.childNodes;
		for (j = 0; j < children.length; j++)
		{
			tbody.removeChild(children[j]);
		}
		for (j = 0; j < rows.length; j++)
		{
			tbody.appendChild(rows[j]);
		}
		rowWizardReindexRows(tbody, tbody.parentNode.parentNode.id);
	}
}

function rowWizardMoveRowDown(e, obj)
{
	var row = this.parentNode.parentNode;
	var tbody = row.parentNode;
	var rows = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'tr', tbody);
	var foundindex = 0;
	for (i = 0; i < rows.length; i++)
	{
		if (rows[i] == row) foundindex = i;
	}

	if (foundindex < rows.length-1)
	{
		rowWizardRemoveListeners(tbody);
		temp = rows[foundindex+1];
		rows[foundindex+1] = row;
		rows[foundindex] = temp;
		children = tbody.childNodes;
		for (j = 0; j < children.length; j++)
		{
			tbody.removeChild(children[j]);
		}
		for (j = 0; j < rows.length; j++)
		{
			tbody.appendChild(rows[j]);
		}
		rowWizardReindexRows(tbody, tbody.parentNode.parentNode.id);
	}
}

function matrixrowwizardEvents(e)
{
	var addbuttons = YAHOO.util.Dom.getElementsByClassName('matrixrowwizard_add');
	for (i = 0; i < addbuttons.length; i++)
	{
		var button = addbuttons[i];
		YAHOO.util.Event.addListener(button, 'click', rowWizardAddRow);
	}
	var removebuttons = YAHOO.util.Dom.getElementsByClassName('matrixrowwizard_remove');
	for (i = 0; i < removebuttons.length; i++)
	{
		var button = removebuttons[i];
		YAHOO.util.Event.addListener(button, 'click', rowWizardRemoveRow);
	}
	var upbuttons = YAHOO.util.Dom.getElementsByClassName('matrixrowwizard_up');
	for (i = 0; i < upbuttons.length; i++)
	{
		var button = upbuttons[i];
		YAHOO.util.Event.addListener(button, 'click', rowWizardMoveRowUp);
	}
	var downbuttons = YAHOO.util.Dom.getElementsByClassName('matrixrowwizard_down');
	for (i = 0; i < downbuttons.length; i++)
	{
		var button = downbuttons[i];
		YAHOO.util.Event.addListener(button, 'click', rowWizardMoveRowDown);
	}
}

YAHOO.util.Event.onDOMReady(matrixrowwizardEvents);