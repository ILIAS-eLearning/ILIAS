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
/*
		// this a little bit complex procedure fixes innerHTML with forms in IE
		var newdiv = document.createElement("div");
		newdiv.innerHTML = o.responseText;
		var block_div = document.getElementById(o.argument.block_id);
		block_div.innerHTML = '';
		block_div.appendChild(newdiv);
		
		// for safari: eval all javascript nodes
		if ((YAHOO.env.ua.webkit != "0" && YAHOO.env.ua.webkit != "1") ||
			(YAHOO.env.ua.ie != "0" && YAHOO.env.ua.ie != "1"))
		{
			//alert("webkit or ie!");
			var els = YAHOO.util.Dom.getElementsBy(function(el){return true;}, "script", newdiv);
			for(var i= 0; i<=els.length; i++)
			{
				if (typeof els[i] != 'undefined')
				{
					eval(els[i].innerHTML);
				}
			}
		}
*/
		if (typeof il_sr_opt != "undefined")
		{
			il.Util.setScreenReaderFocus(o.argument.block_id + "_blhead");
		}
		
		
		//div.innerHTML = "Transaction id: " + o.tId;
		//div.innerHTML += "HTTP status: " + o.status;
		//div.innerHTML += "Status code message: " + o.statusText;
		//div.innerHTML += "HTTP headers: " + parseHeaders();
		//div.innerHTML += "Server response: " + o.responseText;
		//div.innerHTML += "Argument object: property foo = " + o.argument.foo +
		//				 "and property bar = " + o.argument.bar;
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
