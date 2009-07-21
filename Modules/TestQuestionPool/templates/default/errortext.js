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