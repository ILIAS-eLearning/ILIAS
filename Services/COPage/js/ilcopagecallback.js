
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

// Success Handler
var ilCOPageSuccessHandler = function(o)
{
	// parse headers function
	function parseHeaders()
	{
		var allHeaders = headerStr.split("\n");
		var headers;
		for(var i=0; i < headers.length; i++)
		{
			var delimitPos = header[i].indexOf(':');
			if(delimitPos != -1)
			{
				headers[i] = "<p>" +
				headers[i].substring(0,delimitPos) + ":"+
				headers[i].substring(delimitPos+1) + "</p>";
			}
		return headers;
		}
	}

	// perform page modification
	if(o.responseText !== undefined)
	{
		if (o.argument.mode == 'para')
		{
			// paragraph editing
			var ed = tinyMCE.getInstanceById('tinytarget');
			ed.setContent(o.responseText);
			ed.setProgressState(0); // Show progress

			ilCOPage.prepareTinyForEditing(false);
		}
		else
		{
			if (o.argument.mode == "saveonly")
			{
//console.log("saved");
				var el = document.getElementById('ilsaving');
				el.style.display = 'none';
			}
			else
			{
				// drag / drop
				var edit_div = document.getElementById('il_EditPage');
				var center_td = edit_div.parentNode;
				center_td.innerHTML = o.responseText;
				il.Tooltip.init();
				if (il.AdvancedSelectionList != null)
				{
					il.AdvancedSelectionList.init['style_selection']();
				}
			}
		}
	}
}

// FailureHandler
var ilCOPageFailureHandler = function(o)
{
	alert('FailureHandler');
}

function ilCOPageJSHandler(sUrl, mode)
{
	var ilCOPageCallback =
	{
		success: ilCOPageSuccessHandler,
		failure: ilCOPageFailureHandler,
		argument: { mode: mode}
	};
	var form_str = YAHOO.util.Connect.setForm("ajaxform");
	var request = YAHOO.util.Connect.asyncRequest('POST', sUrl, ilCOPageCallback);
	
	return false;
}

