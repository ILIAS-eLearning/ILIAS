
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

ilNotes =
{
	ref_id: 0,
	sub_id: 0,
	panel: false,
	ajax_url: '',
	
	listNotes: function (e, ref_id, sub_id)
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
		
		// add panel
		this.initPanel(false);
	},
	
	listComments: function (e, ref_id, sub_id)
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
		
		// add panel
		this.initPanel(true);
	},
	
	// init the notes editing panel
	initPanel: function(comments)
	{
		if (!this.panel)
		{
			var n = document.getElementById('ilNotesPanel');
			if (!n)
			{
				var b = $("body");
				b.append("<div class='yui-skin-sam'><div id='ilNotesPanel' style='overflow:auto; background-color:white;'>" +
					"<div id='ilNotesPanelBody' style='background-color:white; padding:20px;'>&nbsp;</div></div>");
				var n = document.getElementById('ilNotesPanel');
			}
			
			// Create a panel Instance, from the 'resizablepanel' DIV standard module markup
			var panel = new YAHOO.widget.Panel("ilNotesPanel", {
				draggable: false,
				width: "500px",
				autofillheight: "body", // default value, specified here to highlight its use in the example
				constraintoviewport:true
			});
			panel.render();
			this.panel = panel;
		}
		else
		{
			this.panel.show();
		}
		
		ilNotes.insertPanelHTML("");

		var obj = document.getElementById('ilNotesPanel_c');
		obj.style.position = 'fixed';
		obj.style.top = '0px';
		obj.style.bottom = '0px';
		obj.style.right = '2px';
		obj.style.left = '';
		obj = document.getElementById('ilNotesPanel');
		obj.style.position = 'relative';
		obj.style.top = '0px';
		obj.style.right = '2px';
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
		this.sendAjaxPostRequest("ilNoteForm", url, {mode: 'cmd'});
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
		$('div#ilNotesPanelBody').html(html);
	}
	

};
