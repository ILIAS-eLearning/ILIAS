function cleanElements(rootel)
{
	var textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', rootel);
	for (i = 0; i < textinputs.length; i++) textinputs[i].value = '';
}

function removeAllListeners(rootel)
{
	var addbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'categorywizard_add') ? true : false; }, 'input', rootel);
	for (i = 0; i < addbuttons.length; i++)
	{
		YAHOO.util.Event.purgeElement(addbuttons[i]);
	}
	var removebuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'categorywizard_remove') ? true : false; }, 'input', rootel);
	for (i = 0; i < removebuttons.length; i++)
	{
		YAHOO.util.Event.purgeElement(removebuttons[i]);
	}
	var upbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'categorywizard_up') ? true : false; }, 'input', rootel);
	for (i = 0; i < upbuttons.length; i++)
	{
		YAHOO.util.Event.purgeElement(upbuttons[i]);
	}
	var downbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'categorywizard_down') ? true : false; }, 'input', rootel);
	for (i = 0; i < downbuttons.length; i++)
	{
		YAHOO.util.Event.purgeElement(downbuttons[i]);
	}
}

function reindexRows(rootel, postvar)
{
	var rows = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'tr', rootel);
	for (i = 0; i < rows.length; i++)
	{
		// set row class
		YAHOO.util.Dom.removeClass(rows[i], "odd");
		YAHOO.util.Dom.removeClass(rows[i], "even");
		YAHOO.util.Dom.removeClass(rows[i], "first");
		YAHOO.util.Dom.removeClass(rows[i], "last");
		alter = (i % 2 == 0) ? "even" : "odd";
		YAHOO.util.Dom.addClass(rows[i], alter);
		add = (i == 0) ? "first" : ((i == rows.length-1) ? "last" : "");
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
			else if (textinputs[j].id.indexOf('[scale]') >= 0)
			{
				textinputs[j].id = postvar + '[scale][' + i + ']';
				textinputs[j].name = postvar + '[scale][' + i + ']';
				textinputs[j].value = i + 1;
			}
		}

		var addbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'categorywizard_add') ? true : false; }, 'input', rows[i]);
		for (j = 0; j < addbuttons.length; j++)
		{
			addbuttons[j].id = 'add_' + postvar + '[' + i + ']';
			addbuttons[j].name = 'cmd[add' + postvar + '][' + i + ']';
			YAHOO.util.Event.addListener(addbuttons[j], 'click', addRow);
		}
		var removebuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'categorywizard_remove') ? true : false; }, 'input', rows[i]);
		for (j = 0; j < removebuttons.length; j++)
		{
			removebuttons[j].id = 'remove_' + postvar + '[' + i + ']';
			removebuttons[j].name = 'cmd[remove' + postvar + '][' + i + ']';
			YAHOO.util.Event.addListener(removebuttons[j], 'click', removeRow);
		}
		var upbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'categorywizard_up') ? true : false; }, 'input', rows[i]);
		if (upbuttons.length > 0)
		{
			for (j = 0; j < upbuttons.length; j++)
			{
				upbuttons[j].id = 'up_' + postvar + '[' + i + ']';
				upbuttons[j].name = 'cmd[up' + postvar + '][' + i + ']';
				YAHOO.util.Event.addListener(upbuttons[j], 'click', moveRowUp);
			}
		}
		var downbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'categorywizard_down') ? true : false; }, 'input', rows[i]);
		if (downbuttons.length > 0)
		{
			for (j = 0; j < downbuttons.length; j++)
			{
				downbuttons[j].id = 'down_' + postvar + '[' + i + ']';
				downbuttons[j].name = 'cmd[down' + postvar + '][' + i + ']';
				YAHOO.util.Event.addListener(downbuttons[j], 'click', moveRowDown);
			}
		}
	}
}

function addRow(e, obj)
{
	var row = this.parentNode.parentNode;
	var tbody = row.parentNode;
	
	removeAllListeners(tbody);
	var rowclone = row.cloneNode(true);
	cleanElements(rowclone);
	
	var trs = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'tr', tbody);
	parentindex = 0;
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
	reindexRows(tbody, tbody.parentNode.parentNode.id);
	var textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', rowclone);
	textinputs[0].focus();
	return false;
}

function removeRow(e, obj)
{
	var row = this.parentNode.parentNode;
	var tbody = row.parentNode;
	var trs = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'tr', tbody);
	if (trs.length == 1)
	{
		cleanElements(trs[0]);
	}
	else
	{
		tbody.removeChild(row);
		removeAllListeners(tbody);
		reindexRows(tbody, tbody.parentNode.parentNode.id);
	}
}

function moveRowUp(e, obj)
{
	var row = this.parentNode.parentNode;
	var tbody = row.parentNode;
	var rows = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'tr', tbody);
	foundindex = 0;
	for (i = 0; i < rows.length; i++)
	{
		if (rows[i] == row) foundindex = i;
	}

	if (foundindex > 0)
	{
		removeAllListeners(tbody);
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
		reindexRows(tbody, tbody.parentNode.parentNode.id);
	}
}

function moveRowDown(e, obj)
{
	var row = this.parentNode.parentNode;
	var tbody = row.parentNode;
	var rows = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'tr', tbody);
	foundindex = 0;
	for (i = 0; i < rows.length; i++)
	{
		if (rows[i] == row) foundindex = i;
	}

	if (foundindex < rows.length-1)
	{
		removeAllListeners(tbody);
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
		reindexRows(tbody, tbody.parentNode.parentNode.id);
	}
}

function categorywizardEvents(e)
{
	var addbuttons = YAHOO.util.Dom.getElementsByClassName('categorywizard_add');
	for (i = 0; i < addbuttons.length; i++)
	{
		button = addbuttons[i];
		YAHOO.util.Event.addListener(button, 'click', addRow);
	}
	var removebuttons = YAHOO.util.Dom.getElementsByClassName('categorywizard_remove');
	for (i = 0; i < removebuttons.length; i++)
	{
		button = removebuttons[i];
		YAHOO.util.Event.addListener(button, 'click', removeRow);
	}
	var upbuttons = YAHOO.util.Dom.getElementsByClassName('categorywizard_up');
	for (i = 0; i < upbuttons.length; i++)
	{
		button = upbuttons[i];
		YAHOO.util.Event.addListener(button, 'click', moveRowUp);
	}
	var downbuttons = YAHOO.util.Dom.getElementsByClassName('categorywizard_down');
	for (i = 0; i < downbuttons.length; i++)
	{
		button = downbuttons[i];
		YAHOO.util.Event.addListener(button, 'click', moveRowDown);
	}
}

YAHOO.util.Event.onDOMReady(categorywizardEvents);