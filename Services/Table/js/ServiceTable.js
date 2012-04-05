
// Hide all on load
il.Util.addOnLoad(ilInitTableFilters)
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
		else
		{
			filtrs[i].style.display = '';
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
		else
		{
			filactvtrs[i].style.display = 'none';
		}
	}

	// hide filter deactivators
	fildctvtrs = YAHOO.util.Dom.getElementsByClassName('ilTableFilterDeactivator');
	for (var i = 0; i < fildctvtrs.length; i++)
	{
		if (ilTableHideFilter[fildctvtrs[i].id] == 1)
		{
			fildctvtrs[i].style.display = 'none';
		}
		else
		{
			fildctvtrs[i].style.display = '';
		}
	}

}

function ilShowTableFilter(id, sUrl)
{
	var obj = document.getElementById(id);
	obj.style.display = '';
	var obj2 = document.getElementById("a" + id);
	obj2.style.display = 'none';
	var obj3 = document.getElementById("d" + id);
	obj3.style.display = '';
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
	var obj3 = document.getElementById("d" + id);
	obj3.style.display = 'none';
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
		failure: ilTableFailureHandler
	};

	var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, ilTableCallback);
	
	return false;
}

function ilTablePageSelection(el, cmd)
{
	var input = document.createElement("input");
	input.setAttribute("type", "hidden");
	input.setAttribute("name", cmd);
	input.setAttribute("value", "1");
	el.parentNode.appendChild(input);
	el.form.submit();
	return false;
}
