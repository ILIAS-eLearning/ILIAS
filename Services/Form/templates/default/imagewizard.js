function cleanImageWizardElements(rootel)
{
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'file') ? true : false; }, 'input', rootel);
	for (i = 0; i < textinputs.length; i++) textinputs[i].value = '';
	hidden = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'hidden') ? true : false; }, 'input', rootel);
	for (i = 0; i < hidden.length; i++) rootel.removeChild(hidden[i]);
	images = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'img', rootel);
	for (i = 0; i < images.length; i++) rootel.removeChild(images[i]);
}

function reindexImageWizardElements(rootel)
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
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'file') ? true : false; }, 'input', rootel);
	for (i = 0; i < textinputs.length; i++)
	{
		textinputs[i].id = rootel.id + '[' + i + ']';
		textinputs[i].name = rootel.id + '[' + i + ']';
		hiddenfields = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'hidden') ? true : false; }, 'input', textinputs[i].parentNode);
		for (j = 0; j < hiddenfields.length; j++)
		{
			hiddenfields[j].name = 'picture_' + rootel.id + '[' + i + ']';
		}
	}
	addbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'imagewizard_add') ? true : false; }, 'input', rootel);
	for (i = 0; i < addbuttons.length; i++)
	{
		addbuttons[i].id = 'add_' + rootel.id + '[' + i + ']';
		addbuttons[i].name = 'cmd[add' + rootel.id + '][' + i + ']';
	}
	removebuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'imagewizard_remove') ? true : false; }, 'input', rootel);
	for (i = 0; i < removebuttons.length; i++)
	{
		removebuttons[i].id = 'remove_' + rootel.id + '[' + i + ']';
		removebuttons[i].name = 'cmd[remove' + rootel.id + '][' + i + ']';
	}
	upbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'imagewizard_up') ? true : false; }, 'input', rootel);
	for (i = 0; i < upbuttons.length; i++)
	{
		upbuttons[i].id = 'up_' + rootel.id + '[' + i + ']';
		upbuttons[i].name = 'cmd[up' + rootel.id + '][' + i + ']';
	}
	downbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'imagewizard_down') ? true : false; }, 'input', rootel);
	for (i = 0; i < downbuttons.length; i++)
	{
		downbuttons[i].id = 'down_' + rootel.id + '[' + i + ']';
		downbuttons[i].name = 'cmd[down' + rootel.id + '][' + i + ']';
	}
}

function addTextField(e, obj)
{
	parent = this.parentNode;
	maincontainer = parent.parentNode;
	
	parentclone = parent.cloneNode(true);
	cleanImageWizardElements(parentclone);
	addbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'imagewizard_add') ? true : false; }, 'input', parentclone);
	for (i = 0; i < addbuttons.length; i++)
	{
		YAHOO.util.Event.addListener(addbuttons[i], 'click', addTextField);
	}
	removebuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'imagewizard_remove') ? true : false; }, 'input', parentclone);
	for (i = 0; i < removebuttons.length; i++)
	{
		YAHOO.util.Event.addListener(removebuttons[i], 'click', removeTextField);
	}
	upbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'imagewizard_up') ? true : false; }, 'input', parentclone);
	for (i = 0; i < upbuttons.length; i++)
	{
		YAHOO.util.Event.addListener(upbuttons[i], 'click', moveTextFieldUp);
	}
	downbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'imagewizard_down') ? true : false; }, 'input', parentclone);
	for (i = 0; i < downbuttons.length; i++)
	{
		YAHOO.util.Event.addListener(downbuttons[i], 'click', moveTextFieldDown);
	}
	
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'file') ? true : false; }, 'input', maincontainer);
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
	reindexImageWizardElements(maincontainer);
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'file') ? true : false; }, 'input', parentclone);
	textinputs[0].focus();
	return false;
}

function removeTextField(e, obj)
{
	parent = this.parentNode;
	maincontainer = parent.parentNode;
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'file') ? true : false; }, 'input', maincontainer);
	if (textinputs.length == 1)
	{
		id = this.id.substr(7, this.id.length);
		input = YAHOO.util.Dom.get(id);
		input.value = '';
	}
	else
	{
		maincontainer.removeChild(parent);
		reindexImageWizardElements(maincontainer);
	}
}

function moveTextFieldUp(e, obj)
{
	parent = this.parentNode;
	maincontainer = parent.parentNode;
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'file') ? true : false; }, 'input', maincontainer);
	rows = new Array();
	foundindex = 0;
	for (i = 0; i < textinputs.length; i++)
	{
		row = textinputs[i].parentNode;
		if (row == parent) foundindex = i;
		rows.push(row);
	}
	if (foundindex > 0)
	{
		temp = rows[foundindex-1];
		rows[foundindex-1] = parent;
		rows[foundindex] = temp;
		children = maincontainer.childNodes;
		for (j = 0; j < children.length; j++)
		{
			maincontainer.removeChild(children[j]);
		}
		for (j = 0; j < rows.length; j++)
		{
			maincontainer.appendChild(rows[j]);
		}
		reindexImageWizardElements(maincontainer);
	}
}

function moveTextFieldDown(e, obj)
{
	parent = this.parentNode;
	maincontainer = parent.parentNode;
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'file') ? true : false; }, 'input', maincontainer);
	rows = new Array();
	foundindex = 0;
	for (i = 0; i < textinputs.length; i++)
	{
		row = textinputs[i].parentNode;
		if (row == parent) foundindex = i;
		rows.push(row);
	}
	if (foundindex < rows.length-1)
	{
		temp = rows[foundindex+1];
		rows[foundindex+1] = parent;
		rows[foundindex] = temp;
		children = maincontainer.childNodes;
		for (j = 0; j < children.length; j++)
		{
			maincontainer.removeChild(children[j]);
		}
		for (j = 0; j < rows.length; j++)
		{
			maincontainer.appendChild(rows[j]);
		}
		reindexImageWizardElements(maincontainer);
	}
}

function imagewizardEvents(e)
{
	addbuttons = YAHOO.util.Dom.getElementsByClassName('imagewizard_add');
	for (i = 0; i < addbuttons.length; i++)
	{
		button = addbuttons[i];
		YAHOO.util.Event.addListener(button, 'click', addTextField);
	}
	removebuttons = YAHOO.util.Dom.getElementsByClassName('imagewizard_remove');
	for (i = 0; i < removebuttons.length; i++)
	{
		button = removebuttons[i];
		YAHOO.util.Event.addListener(button, 'click', removeTextField);
	}
	upbuttons = YAHOO.util.Dom.getElementsByClassName('imagewizard_up');
	for (i = 0; i < upbuttons.length; i++)
	{
		button = upbuttons[i];
		YAHOO.util.Event.addListener(button, 'click', moveTextFieldUp);
	}
	downbuttons = YAHOO.util.Dom.getElementsByClassName('imagewizard_down');
	for (i = 0; i < downbuttons.length; i++)
	{
		button = downbuttons[i];
		YAHOO.util.Event.addListener(button, 'click', moveTextFieldDown);
	}
}

YAHOO.util.Event.onDOMReady(imagewizardEvents);