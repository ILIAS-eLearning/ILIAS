function cleanTextWizardElements(rootel)
{
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', rootel);
	for (i = 0; i < textinputs.length; i++) textinputs[i].value = '';
}

function reindexTextWizardElements(rootel)
{
	// reindex rows
	var rows = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className.indexOf('odd') >= 0 || el.className.indexOf('even') >= 0) ? true : false; }, 'div', rootel);
	for (i = 0; i < rows.length; i++)
	{
		YAHOO.util.Dom.removeClass(rows[i], "odd");
		YAHOO.util.Dom.removeClass(rows[i], "even");
		YAHOO.util.Dom.removeClass(rows[i], "first");
		YAHOO.util.Dom.removeClass(rows[i], "last");
		alter = (i % 2 == 0) ? "even" : "odd";
		YAHOO.util.Dom.addClass(rows[i], alter);
		add = (i == 0) ? "first" : ((i == rows.length-1) ? "last" : "");
		if (add.length > 0) YAHOO.util.Dom.addClass(rows[i], add);
	}
	var textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', rootel);
	for (i = 0; i < textinputs.length; i++)
	{
		textinputs[i].id = rootel.id + '[' + i + ']';
		textinputs[i].name = rootel.id + '[' + i + ']';
	}
	var addbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'textwizard_add') ? true : false; }, 'input', rootel);
	for (i = 0; i < addbuttons.length; i++)
	{
		addbuttons[i].id = 'add_' + rootel.id + '[' + i + ']';
		addbuttons[i].name = 'cmd[add' + rootel.id + '][' + i + ']';
		YAHOO.util.Event.addListener(addbuttons[i], 'click', addTextField);
	}
	var removebuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'textwizard_remove') ? true : false; }, 'input', rootel);
	for (i = 0; i < removebuttons.length; i++)
	{
		removebuttons[i].id = 'remove_' + rootel.id + '[' + i + ']';
		removebuttons[i].name = 'cmd[remove' + rootel.id + '][' + i + ']';
		YAHOO.util.Event.addListener(removebuttons[i], 'click', removeTextField);
	}
	var upbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'textwizard_up') ? true : false; }, 'input', rootel);
	for (i = 0; i < upbuttons.length; i++)
	{
		upbuttons[i].id = 'up_' + rootel.id + '[' + i + ']';
		upbuttons[i].name = 'cmd[up' + rootel.id + '][' + i + ']';
		YAHOO.util.Event.addListener(upbuttons[i], 'click', moveTextFieldUp);
	}
	var downbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'textwizard_down') ? true : false; }, 'input', rootel);
	for (i = 0; i < downbuttons.length; i++)
	{
		downbuttons[i].id = 'down_' + rootel.id + '[' + i + ']';
		downbuttons[i].name = 'cmd[down' + rootel.id + '][' + i + ']';
		YAHOO.util.Event.addListener(downbuttons[i], 'click', moveTextFieldDown);
	}
}

function removeListeners(rootel)
{
	var addbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'textwizard_add') ? true : false; }, 'input', rootel);
	for (i = 0; i < addbuttons.length; i++)
	{
		YAHOO.util.Event.purgeElement(addbuttons[i]);
	}
	var removebuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'textwizard_remove') ? true : false; }, 'input', rootel);
	for (i = 0; i < removebuttons.length; i++)
	{
		YAHOO.util.Event.purgeElement(removebuttons[i]);
	}
	var upbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'textwizard_up') ? true : false; }, 'input', rootel);
	for (i = 0; i < upbuttons.length; i++)
	{
		YAHOO.util.Event.purgeElement(upbuttons[i]);
	}
	var downbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'textwizard_down') ? true : false; }, 'input', rootel);
	for (i = 0; i < downbuttons.length; i++)
	{
		YAHOO.util.Event.purgeElement(downbuttons[i]);
	}
}

function addTextField(e, obj)
{
	var addparent = this.parentNode;
	var addmaincontainer = addparent.parentNode;
	
	removeListeners(addmaincontainer);
	var parentclone = addparent.cloneNode(true);
	cleanTextWizardElements(parentclone);
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', addmaincontainer);
	parentindex = 0;
	for (i = 0; i < textinputs.length; i++)
	{
		if (textinputs[i].parentNode == addparent) parentindex = i+1;
	}
	if (parentindex == textinputs.length)
	{
		addmaincontainer.appendChild(parentclone);
	}
	else
	{
		addmaincontainer.insertBefore(parentclone, textinputs[parentindex].parentNode);
	}
	reindexTextWizardElements(addmaincontainer);
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', parentclone);
	textinputs[0].focus();
	return false;
}

function removeTextField(e, obj)
{
	var removeparent = this.parentNode;
	var removemaincontainer = removeparent.parentNode;
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', removemaincontainer);
	if (textinputs.length == 1)
	{
		id = this.id.substr(7, this.id.length);
		input = YAHOO.util.Dom.get(id);
		input.value = '';
	}
	else
	{
		removeListeners(removemaincontainer);
		removemaincontainer.removeChild(removeparent);
		reindexTextWizardElements(removemaincontainer);
	}
}

function moveTextFieldUp(e, obj)
{
	var upparent = this.parentNode;
	var upmaincontainer = upparent.parentNode;
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', upmaincontainer);
	rows = new Array();
	foundindex = 0;
	for (i = 0; i < textinputs.length; i++)
	{
		row = textinputs[i].parentNode;
		if (row == upparent) foundindex = i;
		rows.push(row);
	}
	if (foundindex > 0)
	{
		temp = rows[foundindex-1];
		rows[foundindex-1] = upparent;
		rows[foundindex] = temp;
		children = upmaincontainer.childNodes;
		for (j = 0; j < children.length; j++)
		{
			upmaincontainer.removeChild(children[j]);
		}
		for (j = 0; j < rows.length; j++)
		{
			upmaincontainer.appendChild(rows[j]);
		}
		removeListeners(upmaincontainer);
		reindexTextWizardElements(upmaincontainer);
	}
}

function moveTextFieldDown(e, obj)
{
	var downparent = this.parentNode;
	var downmaincontainer = downparent.parentNode;
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', downmaincontainer);
	rows = new Array();
	foundindex = 0;
	for (i = 0; i < textinputs.length; i++)
	{
		row = textinputs[i].parentNode;
		if (row == downparent) foundindex = i;
		rows.push(row);
	}
	if (foundindex < rows.length-1)
	{
		temp = rows[foundindex+1];
		rows[foundindex+1] = downparent;
		rows[foundindex] = temp;
		children = downmaincontainer.childNodes;
		for (j = 0; j < children.length; j++)
		{
			downmaincontainer.removeChild(children[j]);
		}
		for (j = 0; j < rows.length; j++)
		{
			downmaincontainer.appendChild(rows[j]);
		}
		removeListeners(downmaincontainer);
		reindexTextWizardElements(downmaincontainer);
	}
}

function textwizardEvents(e)
{
	addbuttons = YAHOO.util.Dom.getElementsByClassName('textwizard_add');
	for (i = 0; i < addbuttons.length; i++)
	{
		YAHOO.util.Event.addListener(addbuttons[i], 'click', addTextField);
	}
	removebuttons = YAHOO.util.Dom.getElementsByClassName('textwizard_remove');
	for (i = 0; i < removebuttons.length; i++)
	{
		YAHOO.util.Event.addListener(removebuttons[i], 'click', removeTextField);
	}
	upbuttons = YAHOO.util.Dom.getElementsByClassName('textwizard_up');
	for (i = 0; i < upbuttons.length; i++)
	{
		YAHOO.util.Event.addListener(upbuttons[i], 'click', moveTextFieldUp);
	}
	downbuttons = YAHOO.util.Dom.getElementsByClassName('textwizard_down');
	for (i = 0; i < downbuttons.length; i++)
	{
		YAHOO.util.Event.addListener(downbuttons[i], 'click', moveTextFieldDown);
	}
}

YAHOO.util.Event.onDOMReady(textwizardEvents);