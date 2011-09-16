
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

ilNotes =
{
	ref_id: 0,
	sub_id: 0,
	panel: false,
	ajax_url: '',
	
	listNotes: function (e, ref_id, sub_id, update_code)
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
		ilOverlay.hideAllOverlays(e, true);
		
		this.ref_id = ref_id;
		this.sub_id = sub_id;
		this.update_code = update_code;
		
		// add panel
		this.initPanel(false, e);
	},
	
	listComments: function (e, ref_id, sub_id, update_code)
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
		ilOverlay.hideAllOverlays(e, true);
		
		this.ref_id = ref_id;
		this.sub_id = sub_id;
		this.update_code = update_code;
		
		// add panel
		this.initPanel(true, e);
	},
	
	// init the notes editing panel
	initPanel: function(comments, e)
	{
		if (!this.panel)
		{
			var n = document.getElementById('ilNotesPanel');
			if (!n)
			{
				var b = $("body");
				b.append("<div class='yui-skin-sam'><div id='ilNotesPanel' class='ilOverlay' style='overflow:auto;'>" +
					"&nbsp;</div>");
				var n = document.getElementById('ilNotesPanel');
			}
			
			ilOverlay.add("ilNotesPanel", {yuicfg: {}});
			ilOverlay.show(e, "ilNotesPanel");
			this.panel = true;
		}
		else
		{
			ilOverlay.show(e, "ilNotesPanel");
//			this.panel.show();
		}
		
		ilNotes.insertPanelHTML("");

		var obj = document.getElementById('ilNotesPanel');
		obj.style.position = 'fixed';
		obj.style.top = '0px';
		obj.style.bottom = '0px';
		obj.style.right = '0px';
		obj.style.left = '';
		obj.style.width = '500px';
		obj.style.height = '100%';
		
		if (comments)
		{
			this.sendAjaxGetRequest({cmd: "getOnlyCommentsHTML", notes_ref_id: this.ref_id, notes_sub_id: this.sub_id}, {mode: 'list_notes'});
		}
		else
		{
			this.sendAjaxGetRequest({cmd: "getOnlyNotesHTML", notes_ref_id: this.ref_id, notes_sub_id: this.sub_id}, {mode: 'list_notes'});
		}
	},

	cmdAjaxLink: function (e, url)
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
	},
	
	cmdAjaxForm: function (e, url)
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
	},
	
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
	sendAjaxPostRequest: function(form_id, url, args)
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
				ilNotes.insertPanelHTML(o.responseText);
				if (typeof ilNotes.update_code != "undefined" &&
					ilNotes.update_code != null && ilNotes.update_code != "")
				{
					if (o.argument.reg_type == "post")
					{
						eval(ilNotes.update_code);
					}
				}
			}
		}
	},

	// FailureHandler
	handleAjaxFailure: function(o)
	{
		console.log("ilNotes.js: Ajax Failure.");
	},

	insertPanelHTML: function(html)
	{
		$('div#ilNotesPanel').html(html);
	}
	

};
