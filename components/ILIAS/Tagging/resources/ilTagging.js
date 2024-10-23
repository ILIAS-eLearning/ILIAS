
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

ilTagging =
{
	hash: '',
	update_code: '',
	panel: false,
	ajax_url: '',
	modalTemplate: '',
	showSignal: '',
	hideSignal: '',


	listTags: function (e, hash, update_code)
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
		//il.Overlay.hideAllOverlays(e, true);
		
		this.hash = hash;
		this.update_code = update_code;
		
		// add panel
		this.initPanel(e);
	},
	
	// init the notes editing panel
	initPanel: function(e)
	{
		$('#il_tags_modal').remove();
		let modal_template = this.getModalTemplate();
		modal_template = modal_template.replace('#tag_title#', il.Language.txt('tagging_tags'));

		$('body').append(`<div id='il_tags_modal'>${modal_template}</div>`);
		document.querySelectorAll('#il_tags_modal .modal-footer').forEach((el) => {
			el.remove();
		});

		$(document).trigger(
			this.getShowSignal(),
			{
				id: this.getShowSignal(),
				triggerer: $(this),
				options: JSON.parse('[]'),
			},
		);

		this.sendAjaxGetRequest({cmd: "getHTML", cadh: this.hash}, {mode: 'list_tags'});
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
		this.sendAjaxPostRequest("ilTagFormAjax", url, {mode: 'cmd'});
	},
	
	setAjaxUrl: function(url)
	{
		this.ajax_url = url;
	},
	
	getAjaxUrl: function()
	{
		return this.ajax_url;
	},

	setModalTemplate(t) {
		this.modalTemplate = t;
	},

	getModalTemplate() {
		return JSON.parse(this.modalTemplate);
	},

	setShowSignal(t) {
		this.showSignal = t;
	},

	getShowSignal() {
		return this.showSignal;
	},

	setHideSignal(t) {
		this.hideSignal = t;
	},

	getHideSignal() {
		return this.hideSignal;
	},

	sendAjaxGetRequest: function(par, args)
	{
		var url = this.getAjaxUrl();
		this.sendAjaxGetRequestToUrl(url, par, args)
	},
	
	sendAjaxGetRequestToUrl: function(url, par, args)
	{
		for (k in par)
		{
			url = url + "&" + k + "=" + par[k];
		}
		il.repository.core.fetchHtml(url).then((html) => {
			this.handleAjaxSuccess({
				argument: args,
				responseText: html
			});
		});
	},

	// send request per ajax
	sendAjaxPostRequest: function(form_id, url, args)
	{
		args.reg_type = "post";
		const form = document.getElementById(form_id);
		const formData = new FormData(form);
		let data = {};
		formData.forEach((value, key) => (data[key] = value));
		data['cmd[saveJS]'] = "Save";
		il.repository.core.fetchHtml(url, data, true).then((html) => {
			this.handleAjaxSuccess({
				argument: args,
				responseText: html
			});
		});
		return false;
	},


	handleAjaxSuccess: function(o)
	{
		// perform page modification
		if(o.responseText !== undefined)
		{
			const body = document.querySelector("#il_tags_modal .modal-body");
			il.repository.core.setInnerHTML(body,o.responseText);
			const button = document.querySelector("#il_tags_modal .modal-header button");
			button.focus();
			if (typeof ilTagging.update_code != "undefined" &&
				ilTagging.update_code != null && ilTagging.update_code != "")
			{
				if (o.argument.reg_type == "post")
				{
					eval(ilTagging.update_code);
				}
			}

			// only on update
			if (o.argument.mode == 'cmd')
			{
				il.repository.core.trigger('il_classification_redraw');
			}
		}
	},

	insertPanelHTML: function(html)
	{
		const panel = document.getElementById("#ilTagsPanel");
		il.repository.core.setInnerHTML(panel, html);
	}
	

};
