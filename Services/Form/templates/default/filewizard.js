function cleanImageWizardElements(rootel)
{
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'file') ? true : false; }, 'input', rootel);
	for (i = 0; i < textinputs.length; i++) textinputs[i].value = '';
	hidden = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'hidden') ? true : false; }, 'input', rootel);
	for (i = 0; i < hidden.length; i++) rootel.removeChild(hidden[i]);
	images = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'img', rootel);
	for (i = 0; i < images.length; i++) rootel.removeChild(images[i]);
}

function reindexImageWizardElements(rootel, postvar)
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

		var addbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'imagewizard_add') ? true : false; }, 'input', rows[i]);
		for (j = 0; j < addbuttons.length; j++)
		{
			addbuttons[j].id = 'add_' + postvar + '[' + i + ']';
			addbuttons[j].name = 'cmd[add' + postvar + '][' + i + ']';
			YAHOO.util.Event.addListener(addbuttons[j], 'click', addTextField);
		}
		var removebuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'imagewizard_remove') ? true : false; }, 'input', rows[i]);
		for (j = 0; j < removebuttons.length; j++)
		{
			removebuttons[j].id = 'remove_' + postvar + '[' + i + ']';
			removebuttons[j].name = 'cmd[remove' + postvar + '][' + i + ']';
			YAHOO.util.Event.addListener(removebuttons[j], 'click', removeTextField);
		}
		var upbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'imagewizard_up') ? true : false; }, 'input', rows[i]);
		if (upbuttons.length > 0)
		{
			for (j = 0; j < upbuttons.length; j++)
			{
				upbuttons[j].id = 'up_' + postvar + '[' + i + ']';
				upbuttons[j].name = 'cmd[up' + postvar + '][' + i + ']';
				YAHOO.util.Event.addListener(upbuttons[j], 'click', moveTextFieldUp);
			}
		}
		var downbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'imagewizard_down') ? true : false; }, 'input', rows[i]);
		if (downbuttons.length > 0)
		{
			for (j = 0; j < downbuttons.length; j++)
			{
				downbuttons[j].id = 'down_' + postvar + '[' + i + ']';
				downbuttons[j].name = 'cmd[down' + postvar + '][' + i + ']';
				YAHOO.util.Event.addListener(downbuttons[j], 'click', moveTextFieldDown);
			}
		}
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
}

function addTextField(e, obj)
{
	var parent = this.parentNode;
	var maincontainer = parent.parentNode;
	removeListeners(maincontainer);
	
	parentclone = parent.cloneNode(true);
	cleanImageWizardElements(parentclone);
	
	var textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'file') ? true : false; }, 'input', maincontainer);
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
	reindexImageWizardElements(maincontainer, maincontainer.id);
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'file') ? true : false; }, 'input', parentclone);
	textinputs[0].focus();
	return false;
}

function removeTextField(e, obj)
{
	var parent = this.parentNode;
	var maincontainer = parent.parentNode;
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
		removeListeners(maincontainer);
		reindexImageWizardElements(maincontainer, maincontainer.id);
	}
}

function moveTextFieldUp(e, obj)
{
	var parent = this.parentNode;
	var maincontainer = parent.parentNode;
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
		reindexImageWizardElements(maincontainer, maincontainer.id);
	}
}

function moveTextFieldDown(e, obj)
{
	var parent = this.parentNode;
	var maincontainer = parent.parentNode;
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
		reindexImageWizardElements(maincontainer, maincontainer.id);
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

function removeListeners(rootel)
{
	var addbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'imagewizard_add') ? true : false; }, 'input', rootel);
	for (i = 0; i < addbuttons.length; i++)
	{
		YAHOO.util.Event.purgeElement(addbuttons[i]);
	}
	var removebuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'imagewizard_remove') ? true : false; }, 'input', rootel);
	for (i = 0; i < removebuttons.length; i++)
	{
		YAHOO.util.Event.purgeElement(removebuttons[i]);
	}
	var upbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'imagewizard_up') ? true : false; }, 'input', rootel);
	for (i = 0; i < upbuttons.length; i++)
	{
		YAHOO.util.Event.purgeElement(upbuttons[i]);
	}
	var downbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'imagewizard_down') ? true : false; }, 'input', rootel);
	for (i = 0; i < downbuttons.length; i++)
	{
		YAHOO.util.Event.purgeElement(downbuttons[i]);
	}
}

YAHOO.util.Event.onDOMReady(imagewizardEvents);