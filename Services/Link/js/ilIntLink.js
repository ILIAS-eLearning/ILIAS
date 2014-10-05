if (typeof il == "undefined")
{
	il = {};
}

il.IntLink =
{
	int_link_url: '',
	cfg: {},
	id: '',

	refresh: function()
	{
		this.init(this.cfg);
	},

	init: function(cfg)
	{
		// new: get link dynamically
		if(cfg.url == "")
		{
			$("a.iosEditInternalLinkTrigger").each(function(idx, el) {
				var link = $(el).attr("href");
				var id = $(el).attr("id");
				$(el).click(function() {
					il.IntLink.initPanel(link, id);
					return false;
				});
			});
		}
		// old: static id
		else
		{
			this.cfg = cfg;
			var el = document.getElementById("iosEditInternalLinkTrigger");

			if (el)
			{
				YAHOO.util.Event.addListener(el, "click", this.openIntLink);
				this.setInternalLinkUrl(cfg.url);
			}
		}
	},

	setInternalLinkUrl: function(url)
	{
		this.int_link_url = url;
	},

	getInternalLinkUrl: function()
	{
		return this.int_link_url;
	},

	// click event handler
	openIntLink: function(ev)
	{
		il.IntLink.initPanel();
		YAHOO.util.Event.preventDefault(ev);
		YAHOO.util.Event.stopPropagation(ev);
	},

	/**
	 * Init panel
	 * @param internal_link (in case of page editor undefined)
	 * @param id			(in case of page editor undefined)
	 */
	initPanel: function(internal_link, id)
	{
		// new: get link from onclick event
		if(internal_link != undefined)
		{
			this.setInternalLinkUrl(internal_link);
			this.id = id.substring(0, id.length-5);
		}

		il.IntLink.showPanel();
		var j = this.getInternalLinkUrl();
		this.initAjax({mode: 'int_link'});
	},

	/**
	 * Show panel. This function should be extracted from IntLink component, since the
	 * panel is used by other features, too (e.g. wiki link handling)
	 */
	showPanel: function() {
		$('#ilIntLinkModal').modal('show');
	},

	// cfg pars: url (if not provided and post, take form.action?), post/get, parameters (added to get/post)
	initAjax: function(cfg)
	{
		var sUrl = this.getInternalLinkUrl();
		var callback =
		{
			success: this.handleAjaxSuccess,
			upload: this.handleAjaxUpload,
			failure: this.handleAjaxFailure,
			argument: { mode: cfg.mode}
		};
		if (cfg.mode == "select_type")
		{
			var f = document.getElementById("ilIntLinkTypeForm");
			sUrl = f.action;
			YAHOO.util.Connect.setForm("ilIntLinkTypeForm");
			var request = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback);
		}
		else if (cfg.mode == "reset")
		{
			var f = document.getElementById("ilIntLinkResetForm");
			sUrl = f.action;
			YAHOO.util.Connect.setForm("ilIntLinkResetForm");
			var request = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback);
		}
		else if (cfg.mode == "save_file_link")
		{
			var f = document.getElementById("ilFileLinkUploadForm");
			sUrl = f.action + "&cmd=saveFileLink";
			YAHOO.util.Connect.setForm("ilFileLinkUploadForm", true);
			var request = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback);
		}
		else if (cfg.mode == "sel_target_obj")
		{
			sUrl = this.getInternalLinkUrl() + "&do=set&sel_id=" +
				cfg.ref_id + "&cmd=changeTargetObject&target_type=" + cfg.type;
			var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
		}
		else if (cfg.mode == "change_object")
		{
			sUrl = this.getInternalLinkUrl() + "&cmd=changeTargetObject";
			var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
		}
		else if (cfg.mode == "set_mep_fold")
		{
			sUrl = this.getInternalLinkUrl() + "&cmd=setMedPoolFolder&mep_fold=" +
				cfg.mep_fold;
			var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
		}
		else
		{
			sUrl = this.getInternalLinkUrl() + "&cmd=showLinkHelp";
			var request = YAHOO.util.Connect.asyncRequest('GET', sUrl, callback);
		}

		return false;
	},


	handleAjaxSuccess: function(o)
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
			//if (o.argument.mode == 'int_link')
			//{
			//}
			il.IntLink.insertPanelHTML(o.responseText);
		}
	},

	handleAjaxUpload: function(o)
	{
		// perform page modification
		if(o.responseText !== undefined)
		{
			//if (o.argument.mode == 'int_link')
			//{
			//}
			il.IntLink.insertPanelHTML(o.responseText);
		}
	},

	// FailureHandler
	handleAjaxFailure: function(o)
	{
		console.log("ilIntLink.js: Ajax Failure.");
	},

	insertPanelHTML: function(html)
	{
		$('#ilIntLinkModalContent').html(html);
		var el = document.getElementById("ilIntLinkTypeSelector");
		if (el)
		{
			YAHOO.util.Event.addListener(el, "change", this.selectLinkTypeEvent);
		}

		var el = document.getElementById("ilIntLinkReset");
		if (el)
		{
			YAHOO.util.Event.addListener(el, "click", this.clickResetEvent);
		}

		var el = document.getElementById("ilIntLinkReset");
		if (el)
		{
			YAHOO.util.Event.addListener(el, "click", this.clickResetEvent);
		}

		var el = document.getElementById("ilChangeTargetObject");
		if (el)
		{
			YAHOO.util.Event.addListener(el, "click", this.clickChangeTargetObjectEvent);
		}

		var el = document.getElementById("ilSaveFileLink");
		if (el)
		{
			YAHOO.util.Event.addListener(el, "click", this.clickSaveFileLinkEvent);
		}

	},

	selectLinkTypeEvent: function(ev)
	{
		il.IntLink.initAjax({mode: 'select_type'});
	},

	clickResetEvent: function(ev)
	{
		il.IntLink.initAjax({mode: 'reset'});
		YAHOO.util.Event.preventDefault(ev);
		YAHOO.util.Event.stopPropagation(ev);
	},

	clickChangeTargetObjectEvent: function(ev)
	{
		il.IntLink.initAjax({mode: 'change_object'});
		YAHOO.util.Event.preventDefault(ev);
		YAHOO.util.Event.stopPropagation(ev);
	},

	clickSaveFileLinkEvent: function(ev)
	{
		il.IntLink.initAjax({mode: 'save_file_link'});
		YAHOO.util.Event.preventDefault(ev);
		YAHOO.util.Event.stopPropagation(ev);
	},
	
	selectLinkTargetObject: function (type, ref_id)
	{
		il.IntLink.initAjax({mode: 'sel_target_obj', ref_id: ref_id, type: type});
		
		return false;
	},

	addInternalLink: function (b, e, ev)
	{
		if (typeof ilCOPage != "undefined" && ($("#ilEditTableDataCl").length == 0))
		{
			ilCOPage.cmdIntLink(b, e);
		} else if (il.Form) {
			il.Form.addInternalLink(b,e,this.id,ev);
		} else if (addInternalLink) {
			// old style, needs clean-up
			addInternalLink(b);
		}

		il.IntLink.hidePanel();
		return false;
	},

	hidePanel: function () {
		$('#ilIntLinkModal').modal('hide');
	},
	
	setMepPoolFolder: function(mep_fold_id)
	{
		il.IntLink.initAjax({mode: 'set_mep_fold', mep_fold: mep_fold_id});
		return false;
//		YAHOO.util.Event.preventDefault(ev);
//		YAHOO.util.Event.stopPropagation(ev);
	}


}
