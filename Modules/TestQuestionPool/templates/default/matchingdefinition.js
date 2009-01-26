function addMatchingPair(e, obj)
{
	parent = this.parentNode;
	maincontainer = parent.parentNode;
	
	parentclone = parent.cloneNode(true);
	cleanMatchingPairElements(parentclone);
	addbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'matchingdefinition_add') ? true : false; }, 'input', parentclone);
	for (i = 0; i < addbuttons.length; i++)
	{
		YAHOO.util.Event.addListener(addbuttons[i], 'click', addMatchingPair);
	}
	removebuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'matchingdefinition_remove') ? true : false; }, 'input', parentclone);
	for (i = 0; i < removebuttons.length; i++)
	{
		YAHOO.util.Event.addListener(removebuttons[i], 'click', removeMatchingPair);
	}
	
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'numeric') ? true : false; }, 'input', maincontainer);
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

	reindexMatchingPairElements(maincontainer);
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', parentclone);
	textinputs[0].focus();
	
	return false;
}

function removeMatchingPair(e, obj)
{
	parent = this.parentNode;
	maincontainer = parent.parentNode;
	selects = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'select', maincontainer);
	if (selects.length == 1)
	{
		cleanMatchingPairElements(parent);
	}
	else
	{
		maincontainer.removeChild(parent);
		reindexMatchingPairElements(maincontainer);
	}
}

function cleanMatchingPairElements(rootel)
{
	textinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', rootel);
	for (i = 0; i < textinputs.length; i++) textinputs[i].value = '';
	selects = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'select', rootel);
	for (i = 0; i < selects.length; i++) selects[i].selectedIndex = '';
	filenames = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'hidden' && el.name.indexOf('image_filename') >= 0) ? true : false; }, 'input', rootel);
	for (i = 0; i < filenames.length; i++) rootel.removeChild(filenames[i]);
	checkboxes = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'checkbox') ? true : false; }, 'input', rootel);
	for (i = 0; i < checkboxes.length; i++)  rootel.removeChild(checkboxes[i]);
	labels = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'label', rootel);
	for (i = 0; i < labels.length; i++)  rootel.removeChild(labels[i]);
	images = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'img', rootel);
	for (i = 0; i < images.length; i++)  rootel.removeChild(images[i]);
}

function reindexMatchingPairElements(rootel)
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
	// reindex definitions
	definition = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'definition') ? true : false; }, 'input', rootel);
	for (i = 0; i < definition.length; i++)
	{
		definition[i].id = 'definition_' + i;
		definition[i].name = 'definition[' + i + ']';
	}
	// reindex pictures
	checkboxes = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'checkbox') ? true : false; }, 'input', rootel);
	for (i = 0; i < checkboxes.length; i++)
	{
		checkboxes[i].id = 'picture_delete_' + i;
		checkboxes[i].name = 'picture_delete[' + i + ']';
	}
	labels = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'label', rootel);
	for (i = 0; i < labels.length; i++)
	{
		labels[i].htmlFor = 'picture_delete_' + i;
	}
	filenames = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'hidden' && el.name.indexOf('image_filename') >= 0) ? true : false; }, 'input', rootel);
	for (i = 0; i < filenames.length; i++)
	{
		filenames[i].name = 'image_filename[' + i + ']';
	}
	fileinputs = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'file') ? true : false; }, 'input', rootel);
	for (i = 0; i < fileinputs.length; i++)
	{
		fileinputs[i].id = 'picture_' + i;
		fileinputs[i].name = 'picture[' + i + ']';
	}
	// reindex terms, points and buttons
	points = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'numeric') ? true : false; }, 'input', rootel);
	for (i = 0; i < points.length; i++)
	{
		points[i].id = 'points_' + i;
		points[i].name = 'points[' + i + ']';
	}
	select = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'select', rootel);
	for (i = 0; i < select.length; i++)
	{
		select[i].id = 'matchingterms_' + i;
		select[i].name = 'matchingterms[' + i + ']';
	}
	addbuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'matchingdefinition_add') ? true : false; }, 'input', rootel);
	for (i = 0; i < addbuttons.length; i++)
	{
		addbuttons[i].id = 'add_matchingdefinition_' + i;
		addbuttons[i].name = 'cmd[addMatchingDefinition][' + i + ']';
	}
	removebuttons = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'matchingdefinition_remove') ? true : false; }, 'input', rootel);
	for (i = 0; i < removebuttons.length; i++)
	{
		removebuttons[i].id = 'remove_matchingdefinition_' + i;
		removebuttons[i].name = 'cmd[removeMatchingDefinition][' + i + ']';
	}
}

function updateMatchingTerms(e, obj)
{
	terms = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', YAHOO.util.Dom.get('terms'));
	addbuttons = YAHOO.util.Dom.getElementsByClassName('matchingdefinition_add');
	rootel = addbuttons[0].parentNode.parentNode;
	matchingtermselects = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'select', rootel);
	for (i = 0; i < matchingtermselects.length; i++)
	{
		selectedIndex = matchingtermselects[i].selectedIndex;
		options = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'option', matchingtermselects[i]);
		for (j = 0; j < options.length; j++) matchingtermselects[i].removeChild(options[j]);
		for (j = 0; j < terms.length; j++)
		{
			option = document.createElement('option');
			option.value = j;
			optiontext = document.createTextNode(terms[j].value);
			option.appendChild(optiontext);
			matchingtermselects[i].appendChild(option);
		}
		if (selectedIndex < 0) selectedIndex = 0;
		matchingtermselects[i].selectedIndex = selectedIndex;
	}
	addbuttons = YAHOO.util.Dom.getElementsByClassName('textwizard_add');
	for (i = 0; i < addbuttons.length; i++)
	{
		button = addbuttons[i];
		YAHOO.util.Event.removeListener(button, 'click', updateMatchingTerms);
		YAHOO.util.Event.addListener(button, 'click', updateMatchingTerms);
	}
	removebuttons = YAHOO.util.Dom.getElementsByClassName('textwizard_remove');
	for (i = 0; i < removebuttons.length; i++)
	{
		button = removebuttons[i];
		YAHOO.util.Event.removeListener(button, 'click', updateMatchingTerms);
		YAHOO.util.Event.addListener(button, 'click', updateMatchingTerms);
	}
	for (i = 0; i < terms.length; i++)
	{
		term = terms[i];
		YAHOO.util.Event.removeListener(term, 'change', updateMatchingTerms);
		YAHOO.util.Event.addListener(term, 'change', updateMatchingTerms);
	}
}

function matchingdefinitionEvents(e)
{
	addbuttons = YAHOO.util.Dom.getElementsByClassName('matchingdefinition_add');
	for (i = 0; i < addbuttons.length; i++)
	{
		button = addbuttons[i];
		YAHOO.util.Event.addListener(button, 'click', addMatchingPair);
	}
	removebuttons = YAHOO.util.Dom.getElementsByClassName('matchingdefinition_remove');
	for (i = 0; i < removebuttons.length; i++)
	{
		button = removebuttons[i];
		YAHOO.util.Event.addListener(button, 'click', removeMatchingPair);
	}
	addbuttons = YAHOO.util.Dom.getElementsByClassName('textwizard_add');
	for (i = 0; i < addbuttons.length; i++)
	{
		button = addbuttons[i];
		YAHOO.util.Event.addListener(button, 'click', updateMatchingTerms);
	}
	removebuttons = YAHOO.util.Dom.getElementsByClassName('textwizard_remove');
	for (i = 0; i < removebuttons.length; i++)
	{
		button = removebuttons[i];
		YAHOO.util.Event.addListener(button, 'click', updateMatchingTerms);
	}
	terms = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'text') ? true : false; }, 'input', YAHOO.util.Dom.get('terms'));
	for (i = 0; i < terms.length; i++)
	{
		term = terms[i];
		YAHOO.util.Event.addListener(term, 'change', updateMatchingTerms);
	}
}

YAHOO.util.Event.onDOMReady(matchingdefinitionEvents);