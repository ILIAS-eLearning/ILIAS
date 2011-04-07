/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

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
				ilCOPage.initDragElements();
				ilTooltip.init();
				if (ilAdvancedSelectionList != null)
				{
					ilAdvancedSelectionList.init['style_selection']();
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

