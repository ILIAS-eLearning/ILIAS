
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

ilNotes =
{
	hash: '',
	update_code: '',
	panel: false,
	ajax_url: '',
	
	listNotes: function (e, hash, update_code)
	{				
		// prevent the default action
		e.preventDefault();
		e.stopPropagation(); // #11546 - list properties not working		

		// hide overlays
		il.Overlay.hideAllOverlays(e, true);
		
		this.hash = hash;
		this.update_code = update_code;
		
		// add panel
		this.initPanel(false, e);
	},
	
	listComments: function (e, hash, update_code)
	{		
		// prevent the default action
		e.preventDefault();
		e.stopPropagation(); // #11546 - list properties not working	

		// hide overlays
		il.Overlay.hideAllOverlays(e, true);
		
		this.hash = hash;
		this.update_code = update_code;
		
		// add panel
		this.initPanel(true, e);
	},
	
	// init the notes editing panel
	initPanel: function(comments, e)
	{
		il.UICore.showRightPanel(e);
		
		if (comments)
		{
			this.sendAjaxGetRequest({cmd: "getOnlyCommentsHTML", cadh: this.hash}, {mode: 'list_notes'});
		}
		else
		{
			this.sendAjaxGetRequest({cmd: "getOnlyNotesHTML", cadh: this.hash}, {mode: 'list_notes'});
		}
	},

	cmdAjaxLink: function (e, url)
	{				
		e.preventDefault();
		
		this.sendAjaxGetRequestToUrl(url, {}, {mode: 'cmd'});
	},
	
	cmdAjaxForm: function (e, url)
	{			
		e.preventDefault();
		
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
				il.UICore.setRightPanelContent(o.responseText);
//				ilNotes.insertPanelHTML(o.responseText);
				if (typeof ilNotes.update_code != "undefined" &&
					ilNotes.update_code != null && ilNotes.update_code != "")
				{
					if (o.argument.reg_type == "post" || 
						(typeof o.argument.url == "string" &&
							(o.argument.url.indexOf("cmd=activateComments") != -1 ||
							o.argument.url.indexOf("cmd=deactivateComments") != -1
							)))
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
	}
};
