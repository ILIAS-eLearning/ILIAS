function cleanTextWizardElements(rootel)
{
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', rootel);
	for (i = 0; i < textinputs.length; i++) textinputs[i].value = '';
}

function reindexTextWizardElements(rootel)
{
	// reindex rows
	rows = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className.indexOf('odd') >= 0 || el.className.indexOf('even') >= 0) ? true : false; }, 'div', rootel);
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
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', rootel);
	for (i = 0; i < textinputs.length; i++)
	{
		textinputs[i].id = rootel.id + '[' + i + ']';
		textinputs[i].name = rootel.id + '[' + i + ']';
	}
	addbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'textwizard_add') ? true : false; }, 'input', rootel);
	for (i = 0; i < addbuttons.length; i++)
	{
		addbuttons[i].id = 'add_' + rootel.id + '[' + i + ']';
		addbuttons[i].name = 'cmd[add' + rootel.id + '][' + i + ']';
	}
	removebuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'textwizard_remove') ? true : false; }, 'input', rootel);
	for (i = 0; i < removebuttons.length; i++)
	{
		removebuttons[i].id = 'remove_' + rootel.id + '[' + i + ']';
		removebuttons[i].name = 'cmd[remove' + rootel.id + '][' + i + ']';
	}
}

function addTextField(e, obj)
{
	parent = this.parentNode;
	maincontainer = parent.parentNode;
	
	parentclone = parent.cloneNode(true);
	cleanTextWizardElements(parentclone);
	addbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'textwizard_add') ? true : false; }, 'input', parentclone);
	for (i = 0; i < addbuttons.length; i++)
	{
		YAHOO.util.Event.addListener(addbuttons[i], 'click', addTextField);
	}
	removebuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'textwizard_remove') ? true : false; }, 'input', parentclone);
	for (i = 0; i < removebuttons.length; i++)
	{
		YAHOO.util.Event.addListener(removebuttons[i], 'click', removeTextField);
	}
	
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', maincontainer);
	parentindex = 0;
	for (i = 0; i < textinputs.length; i++)
	{
		if (textinputs[i].parentNode == parent) parentindex = i+1;
	}
	if (parentindex == textinputs.length)
	{
		maincontainer.appendChild(parentclone);
	}
	else
	{
		maincontainer.insertBefore(parentclone, textinputs[parentindex].parentNode);
	}
	reindexTextWizardElements(maincontainer);
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', parentclone);
	textinputs[0].focus();
	return false;
}

function removeTextField(e, obj)
{
	parent = this.parentNode;
	maincontainer = parent.parentNode;
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', maincontainer);
	if (textinputs.length == 1)
	{
		id = this.id.substr(7, this.id.length);
		input = YAHOO.util.Dom.get(id);
		input.value = '';
	}
	else
	{
		maincontainer.removeChild(parent);
		reindexTextWizardElements(maincontainer);
	}
}

function textwizardEvents(e)
{
	addbuttons = YAHOO.util.Dom.getElementsByClassName('textwizard_add');
	for (i = 0; i < addbuttons.length; i++)
	{
		button = addbuttons[i];
		YAHOO.util.Event.addListener(button, 'click', addTextField);
	}
	removebuttons = YAHOO.util.Dom.getElementsByClassName('textwizard_remove');
	for (i = 0; i < removebuttons.length; i++)
	{
		button = removebuttons[i];
		YAHOO.util.Event.addListener(button, 'click', removeTextField);
	}
}

YAHOO.util.Event.onDOMReady(textwizardEvents);