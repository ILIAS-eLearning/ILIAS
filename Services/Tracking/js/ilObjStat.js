
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

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

		// hide overlays
		il.Overlay.hideAllOverlays(e, true);
		
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
				b.append("<div class='yui-skin-sam'><div id='ilobjstatlpdt' class='ilOverlay' style='overflow:auto;'>" +
					"&nbsp;</div>");
				var n = document.getElementById('ilobjstatlpdt');
			}
			
			il.Overlay.add("ilobjstatlpdt", {yuicfg: {}});
			il.Overlay.show(e, "ilobjstatlpdt");
			this.panel = true;
		}
		else
		{
			il.Overlay.show(e, "ilobjstatlpdt");
//			this.panel.show();
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
														
				// add close event			
				$('#ilobjstatlpdtclosebtn').click(function(e) {							
						il.Overlay.hideAllOverlays(e.originalEvent, true);							
						return false;
					});
			}
		}
	},
	
	insertPanelHTML: function(html)
	{		
		$('div#ilobjstatlpdt').html(html);
	}
};