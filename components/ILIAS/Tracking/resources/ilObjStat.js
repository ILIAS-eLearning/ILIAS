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

ilObjStat =
{
	panel: false,
	ajax_url: '',
	
	showLPDetails: function (e, ajax_url)
	{
		// prevent the default action
		if (e && e.preventDefault)
		{
			e.preventDefault();
		}
		else if (window.event && window.event.returnValue)
		{
			window.eventReturnValue = false;
		}
		
		// add panel
		this.initPanel(e);
				
		il.Util.sendAjaxGetRequestToUrl(ajax_url, {}, {}, this.handleAjaxSuccess);
	},
	
	initPanel: function(e)
	{
		if (!this.panel)
		{
			var n = document.getElementById('ilobjstatlpdt');
			if (!n)
			{
				var b = $("body");
				b.append("<div class='yui-skin-sam'><div id='ilobjstatlpdt' style='overflow:auto;'>" +
					"&nbsp;</div>");
				var n = document.getElementById('ilobjstatlpdt');
			}

			this.panel = true;
		}
		
		ilObjStat.insertPanelHTML("");

		var obj = document.getElementById('ilobjstatlpdt');
		obj.style.position = 'fixed';
		obj.style.top = '0px';
		obj.style.bottom = '0px';
		obj.style.right = '0px';
		obj.style.left = '';
		obj.style.width = '500px';
		obj.style.height = '100%';		
	},

	handleAjaxSuccess: function(o)
	{
		// perform page modification
		if(o.responseText !== undefined)
		{
			if (o.argument.mode == 'xxx')
			{
			}
			else
			{
				// default action: replace html
				ilObjStat.insertPanelHTML(o.responseText);
			}
		}
	},
	
	insertPanelHTML: function(html)
	{		
		$('div#ilobjstatlpdt').html(html);
	}
};
