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
var ilBlockSuccessHandler = function(o)
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

	// perform block modification
	if(typeof o.responseText != "undefined")
	{
		$('#' + o.argument.block_id).html(o.responseText);
		il.UICore.initDropDowns('#' + o.argument.block_id);
		if (typeof il_sr_opt != "undefined")
		{
			il.Util.setScreenReaderFocus(o.argument.block_id + "_blhead");
		}
	}
}

// Success Handler
var ilBlockFailureHandler = function(o)
{
	//alert('FailureHandler');
}

function ilBlockJSHandler(block_id, sUrl)
{
	var ilBlockCallback =
	{
		success: ilBlockSuccessHandler,
		failure: ilBlockFailureHandler,
		argument: { block_id: block_id}
	};

	var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, ilBlockCallback);
	
	obj = document.getElementById(block_id + "_loader");
	if (obj)
	{
		var loadergif = document.createElement('img');
		loadergif.src = "./templates/default/images/loader.svg";
		loadergif.border = 0;
		$(loadergif).css("position", "absolute");
		obj.appendChild(loadergif);
	}
	return false;
}

function ilBlockToggleInfo(block_id)
{
	var block_span = document.getElementById('det_info_' + block_id);

	if (block_span)
	{
		if (block_span.style.display == 'none')
		{
			block_span.style.display = "";
		}
		else
		{
			block_span.style.display = "none";
		}
	}
}
