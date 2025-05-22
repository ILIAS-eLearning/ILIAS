
// Hide all on load
var ilTableHideFilter = new Object();

/**
* Hide all ilFormHelpLink elements
*/
function ilInitTableFilters()
{
	// hide filters
	filtrs = document.getElementsByClassName('ilTableFilterSec');
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
	filactvtrs = document.getElementsByClassName('ilTableFilterActivator');
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
	fildctvtrs = document.getElementsByClassName('ilTableFilterDeactivator');
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
	document.getElementById(id).style.display = '';
	document.getElementById('a' + id).style.display = 'none';
	const elem = document.getElementById('d' + id);
  if (elem !== null) {
    elem.style.display = '';
  }
	if (sUrl !== '') {
		ilTableJSHandler(sUrl);
	}
	return false;
}

function ilHideTableFilter(id, sUrl)
{
	document.getElementById(id).style.display = 'none';
  document.getElementById('a' + id).style.display = '';
	const elem = document.getElementById('d' + id);
  if (elem !== null) {
    elem.style.display = 'none';
  }
	if (sUrl !== '') {
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
  const xhr = new XMLHttpRequest();
  xhr.open('GET', sUrl);
  xhr.onload = (e) => {
    if (xhr.readyState === 4 && xhr.status === 200) {
      ilTableSuccessHandler(e);
    } else {
      ilTableFailureHandler(e);
    }
  };
  xhr.onerror = (e) => {
    ilTableFailureHandler(e);
  };
  xhr.send();
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
