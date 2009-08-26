
// Hide all on load
ilAddOnLoad(ilInitTableFilters)
var ilTableHideFilter = new Object();

/** 
* Hide all ilFormHelpLink elements
*/
function ilInitTableFilters()
{
	// hide filters
	filtrs = YAHOO.util.Dom.getElementsByClassName('ilTableFilterSec');
	for (var i = 0; i < filtrs.length; i++)
	{
		if (ilTableHideFilter[filtrs[i].id] == 1)
		{
			filtrs[i].style.display = 'none';
		}
	}

	// show filter activators
	filactvtrs = YAHOO.util.Dom.getElementsByClassName('ilTableFilterActivator');
	for (var i = 0; i < filactvtrs.length; i++)
	{
		if (ilTableHideFilter[filactvtrs[i].id] == 1)
		{
			filactvtrs[i].style.display = '';
		}
	}
}

function ilShowTableFilter(id, sUrl)
{
	var obj = document.getElementById(id);
	obj.style.display = '';
	var obj2 = document.getElementById("a" + id);
	obj2.style.display = 'none';
	if (sUrl != "")
	{
		ilTableJSHandler(sUrl);
	}
	return false;
}

function ilHideTableFilter(id, sUrl)
{
	var obj = document.getElementById(id);
	obj.style.display = 'none';
	var obj2 = document.getElementById("a" + id);
	obj2.style.display = '';
	if (sUrl != "")
	{
		ilTableJSHandler(sUrl);
	}
	return false;
}

// Success Handler
var ilTableSuccessHandler = function(o)
{
	// parse headers function
	function parseHeaders()
	{
	}
}

// Success Handler
var ilTableFailureHandler = function(o)
{
	//alert('FailureHandler');
}

function ilTableJSHandler(sUrl)
{
	var ilTableCallback =
	{
		success: ilTableSuccessHandler,
		failure: ilTableFailureHandler,
	};

	var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, ilTableCallback);
	
	return false;
}

