if (typeof il == "undefined")
{
	il = {};
}

il.IntLink =
{
	int_link_url: "",
	cfg: {},
	id: "",

	save_pars: {
		//"target_type": "",
		"link_par_ref_id": "",
		"link_par_obj_id": "",
		"link_par_fold_id": "",
		"link_type": ""
	},

	getURLParameter: function(url, name) {
		return decodeURIComponent((new RegExp("[?|&]" + name + "=" + "([^&;]+?)(&|#|;|$)").exec(window.location.search) || [null, ""])[1].replace(/\+/g, "%20")) || null;
	},

	getUrlParameters: function (url) {
		var match,
			pl     = /\+/g,  // Regex for replacing addition symbol with a space
			search = /([^&=]+)=?([^&]*)/g,
			decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
			query;

		query = url.substring(url.indexOf("?") + 1);

		var urlParams = {};
		while (match = search.exec(query)) {
			urlParams[decode(match[1])] = decode(match[2]);
		}
		return urlParams;
	},

	replaceUrlParam: function (url, paramName, paramValue) {
		var pattern = new RegExp('\\b('+paramName+'=).*?(&|$)');

		if(paramValue == null) {
			paramValue = "";
		}
		if(url.search(pattern)>=0) {
			return url.replace(pattern,'$1' + paramValue + '$2');
		}
		return url + (url.indexOf("?")>0 ? "&" : "?") + paramName + "=" + paramValue;
	},

	replaceSavePars: function (url) {
		t = il.IntLink;
		for (p in t.save_pars) {
			url = t.replaceUrlParam(url, p, t.save_pars[p]);
		}
		return url;
	},

	refresh: function()
	{
		this.init(this.cfg);
	},

	init: function(cfg)
	{
		//console.log("init with cfgurl:" + cfg.url);
		//console.trace();
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
		else {
			this.cfg = cfg;
			$("#iosEditInternalLinkTrigger").on("click", this.openIntLink);
			this.setInternalLinkUrl(cfg.url);
		}
	},

	setInternalLinkUrl: function(url) {
		var p;
		var t = il.IntLink;
		var pars = t.getUrlParameters(url);

		//console.log("setInternalLinkUrl: " + url);
		for (p in t.save_pars) {
			t.save_pars[p] = "";
			if (pars[p]) {
				t.save_pars[p] = pars[p];
			}
		}
		t.int_link_url = url;
	},

	getInternalLinkUrl: function()
	{
		return this.int_link_url;
	},

	// click event handler
	openIntLink: function(ev) {
		il.IntLink.initPanel();
		ev.preventDefault();
		ev.stopPropagation();
	},

	/**
	 * Init panel
	 * @param internal_link (in case of page editor undefined)
	 * @param id			(in case of page editor undefined)
	 */
	initPanel: function(internal_link, id)
	{
		// move node to body to prevent form in form, see e.g. #16369
		$("#ilIntLinkModal").appendTo("body");
		//console.log("ilIntLinkModal: appendTo body");
		//console.trace();
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
		var f;

		var callback =
		{
			success: this.handleAjaxSuccess,
			upload: this.handleAjaxUpload,
			failure: this.handleAjaxFailure,
			argument: { mode: cfg.mode}
		};
		//console.log(cfg.mode);
		if (cfg.mode == "select_type")
		{
			f = document.getElementById("ilIntLinkTypeForm");
			sUrl = f.action;

			//sUrl = this.getInternalLinkUrl() + "&cmd=changeLinkType";

			this.save_pars.link_type = $("#ilIntLinkTypeSelector").val();
			//this.save_pars.link_par_ref_id = "";
			//this.save_pars.link_par_obj_id = "";
			sUrl = this.replaceSavePars(sUrl);
			//console.log(this.save_pars);
			//console.log("Select Type: " + sUrl);
			il.Util.sendAjaxGetRequestToUrl(sUrl, {}, {}, this.handleAjaxSuccess);
		}
		else if (cfg.mode == "reset")
		{
			f = document.getElementById("ilIntLinkResetForm");
			sUrl = f.action;
			YAHOO.util.Connect.setForm("ilIntLinkResetForm");
			var request = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback);
		}
		else if (cfg.mode == "save_file_link")
		{
			f = document.getElementById("ilFileLinkUploadForm");
			sUrl = f.action + "&cmd=saveFileLink";
			YAHOO.util.Connect.setForm("ilFileLinkUploadForm", true);
			var request = YAHOO.util.Connect.asyncRequest('POST', sUrl, callback);
		}
		else if (cfg.mode == "sel_target_obj")
		{
			sUrl = this.getInternalLinkUrl() + "&do=set&sel_id=" +
				cfg.ref_id + "&cmd=changeTargetObject";
			//this.save_pars.target_type = cfg.type;
			this.save_pars.link_type = cfg.link_type;
			this.save_pars.link_par_ref_id = cfg.ref_id;
			this.save_pars.link_par_obj_id = "";

			sUrl = this.replaceSavePars(sUrl);
			il.Util.sendAjaxGetRequestToUrl(sUrl, {}, {}, this.handleAjaxSuccess);
		}
		else if (cfg.mode == "change_object")
		{
			sUrl = this.getInternalLinkUrl() + "&cmd=changeTargetObject";
			sUrl = this.replaceSavePars(sUrl);
			il.Util.sendAjaxGetRequestToUrl(sUrl, {}, {}, this.handleAjaxSuccess);
		}
		else if (cfg.mode == "set_mep_fold")
		{
			sUrl = this.getInternalLinkUrl() + "&cmd=setMedPoolFolder&mep_fold=" +
				cfg.mep_fold;
			sUrl = this.replaceSavePars(sUrl);
			//console.log("Set mep folder: " + cfg.mep_fold);
			il.Util.sendAjaxGetRequestToUrl(sUrl, {}, {}, this.handleAjaxSuccess);
		}
		else
		{
			sUrl = this.getInternalLinkUrl() + "&cmd=showLinkHelp";
			sUrl = this.replaceSavePars(sUrl);
			il.Util.sendAjaxGetRequestToUrl(sUrl, {}, {}, this.handleAjaxSuccess);
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
			il.IntLink.insertPanelHTML(o.responseText);
			il.IntLink.initEvents();
		}
	},

	initEvents: function () {
		$("#form_link_user_search_form").on("submit", function(e) {
			e.preventDefault();
			var sUrl = il.IntLink.getInternalLinkUrl() + "&cmd=showLinkHelp";
			sUrl = il.IntLink.replaceSavePars(sUrl);
			$.ajax({type: "POST",
				url: sUrl,
				data: $(this).serializeArray(),
				success: function(o) {
					il.IntLink.insertPanelHTML(o);
					il.IntLink.initEvents();
				}
			});
			//console.log("search user");
		});
	},

	handleAjaxUpload: function(o)
	{
		// perform page modification
		if(o.responseText !== undefined)
		{
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
		$("#ilIntLinkModalContent").html(html);
		$("#ilIntLinkTypeSelector").on("change", this.selectLinkTypeEvent);
		$("#ilIntLinkReset").on("click", this.clickResetEvent);
		$("#ilChangeTargetObject").on("click", this.clickChangeTargetObjectEvent);
		$("#ilSaveFileLink").on("click", this.clickSaveFileLinkEvent);
	},

	selectLinkTypeEvent: function(ev)
	{
		il.IntLink.initAjax({mode: 'select_type'});
	},

	clickResetEvent: function(ev) {
		il.IntLink.initAjax({mode: 'reset'});
		ev.preventDefault();
		ev.stopPropagation();
	},

	clickChangeTargetObjectEvent: function(ev) {
		il.IntLink.initAjax({mode: 'change_object'});
		ev.preventDefault();
		ev.stopPropagation();
	},

	clickSaveFileLinkEvent: function(ev) {
		il.IntLink.initAjax({mode: 'save_file_link'});
		ev.preventDefault();
		ev.stopPropagation();
	},
	
	selectLinkTargetObject: function (type, ref_id, link_type)
	{
		il.IntLink.initAjax({mode: 'sel_target_obj', ref_id: ref_id, type: type, link_type: link_type});
		return false;
	},

	addInternalLink: function (b, e, ev, c)
	{
		if (typeof ilCOPage != "undefined" && ($("#ilEditTableDataCl").length == 0)) {
			ilCOPage.cmdIntLink(b, e, c);
		} else if (il.Form && $("#par_content").length == 0 && $("#cell_0_0").length == 0) {
			il.Form.addInternalLink(b,e,this.id,ev);
		}
		else if (addInternalLink) {
			// old style, needs clean-up
			addInternalLink(b);
		}

		il.IntLink.hidePanel();
		return false;
	},

	hidePanel: function () {
		$('#ilIntLinkModal').modal('hide');
	},
	
	setMepPoolFolder: function(mep_fold_id) {
		il.IntLink.initAjax({mode: 'set_mep_fold', mep_fold: mep_fold_id});
		return false;
	}


}
