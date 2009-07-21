function setSelectionsForElement(elem)
{
	anchors = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'a', elem);
	positions = new Array();
	for (j = 0; j < anchors.length; j++)
	{
		ye = new YAHOO.util.Element(anchors[j]);
		if (ye.hasClass('sel'))
		{
			positions.push(j);
		}
	}
	hidden = YAHOO.util.Dom.getElementsBy(function (el) { return (el.type == 'hidden') ? true : false; }, 'input', elem);
	for (j = 0; j < hidden.length; j++)
	{
		console.log(positions.join(','));
		hidden[j].value = positions.join(',');
	}
}

function toggleSelection(e, obj)
{
	elem = new YAHOO.util.Element(this);
	if (elem.hasClass('sel'))
	{
		elem.removeClass('sel');
	}
	else
	{
		elem.addClass('sel');
	}
	setSelectionsForElement(this.parentNode.parentNode);
}

function errortextEvents(e)
{
	errortexts = YAHOO.util.Dom.getElementsBy(function (el) { return (el.className == 'errortext') ? true : false; }, 'div', document);
	for (i = 0; i < errortexts.length; i++)
	{
		anchors = YAHOO.util.Dom.getElementsBy(function (el) { return true; }, 'a', errortexts[i]);
		for (j = 0; j < anchors.length; j++)
		{
			YAHOO.util.Event.addListener(anchors[j], 'click', toggleSelection);
		}
	}
}

YAHOO.util.Event.onDOMReady(errortextEvents);