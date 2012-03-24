
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

ilHelp =
{
	panel: false,
	ajax_url: '',
	padding_old: '-',
	
	listHelp: function (e)
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
	},
	
	// init the help
	initPanel: function(e)
	{
		if (!this.panel)
		{
			var n = document.getElementById('ilHelpPanel');
			if (!n)
			{
				var b = $("body");
				b.append("<div class='yui-skin-sam'><div id='ilHelpPanel' class='ilOverlay' style='overflow:auto;'>" +
					"&nbsp;</div>");
				var n = document.getElementById('ilHelpPanel');
			}
			
			il.Overlay.add("ilHelpPanel", {yuicfg: {}});
			il.Overlay.show(e, "ilHelpPanel");
			this.panel = true;
		}
		else
		{
			il.Overlay.show(e, "ilHelpPanel");
//			this.panel.show();
		}
		ilHelp.insertPanelHTML("");
		ilHelp.reduceMainContentArea();

		var obj = document.getElementById('ilHelpPanel');
		obj.style.position = 'fixed';
		obj.style.top = '0px';
		obj.style.bottom = '0px';
		obj.style.right = '0px';
		obj.style.left = '';
		obj.style.width = '300px';
		obj.style.height = '100%';
		
		this.sendAjaxGetRequest({cmd: "showHelp"}, {});
	},

	showPage: function (id)
	{
		this.sendAjaxGetRequest({cmd: "showPage", help_page: id}, {});
		return false;
	},
	
	/*cmdAjaxLink: function (e, url)
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

		this.sendAjaxGetRequestToUrl(url, {}, {mode: 'cmd'});
	},*/
	
	/*cmdAjaxForm: function (e, url)
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
		this.sendAjaxPostRequest("ilNoteFormAjax", url, {mode: 'cmd'});
	},*/
	
	setAjaxUrl: function(url)
	{
		this.ajax_url = url;
	},
	
	getAjaxUrl: function()
	{
		return this.ajax_url;
	},
	
	sendAjaxGetRequest: function(par, args)
	{
		var url = this.getAjaxUrl();
		this.sendAjaxGetRequestToUrl(url, par, args)
	},
	
	sendAjaxGetRequestToUrl: function(url, par, args)
	{
		args.reg_type = "get";
		args.url = url;
		var cb =
		{
			success: this.handleAjaxSuccess,
			failure: this.handleAjaxFailure,
			argument: args
		};
		for (k in par)
		{
			url = url + "&" + k + "=" + par[k];
		}
		var request = YAHOO.util.Connect.asyncRequest('GET', url, cb);
	},

	// send request per ajax
	/*sendAjaxPostRequest: function(form_id, url, args)
	{
		args.reg_type = "post";
		var cb =
		{
			success: this.handleAjaxSuccess,
			failure: this.handleAjaxFailure,
			argument: args
		};
		var form_str = YAHOO.util.Connect.setForm(form_id);
		var request = YAHOO.util.Connect.asyncRequest('POST', url, cb);
		
		return false;
	},*/


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
				ilHelp.insertPanelHTML(o.responseText);
				
				// todo: only when called initially
				ilInitAccordionById('oh_acc');
			}
		}
	},

	// FailureHandler
	handleAjaxFailure: function(o)
	{
		console.log("ilHelp.js: Ajax Failure.");
	},

	insertPanelHTML: function(html)
	{
		$('div#ilHelpPanel').html(html);
	},
	
	reduceMainContentArea: function()
	{
		var obj = document.getElementById('mainspacekeeper');
		if (ilHelp.padding_old == "-")
		{
			ilHelp.padding_old = obj.style.paddingRight;
		}
		obj.style.paddingRight = '300px';
	},
	
	resetMainContentArea: function()
	{
		var obj = document.getElementById('mainspacekeeper');
		obj.style.paddingRight = this.padding_old;
	},
	
	closePanel: function(e)
	{
		if (this.panel)
		{
			il.Overlay.hide(e, "ilHelpPanel");
			ilHelp.panel = false;
			ilHelp.resetMainContentArea();
		}
	}

};
