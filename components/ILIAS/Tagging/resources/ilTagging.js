
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
		il.Modal.dialogue({
			id:       "il_tags_modal",
			show: true,
			header: il.Language.txt('tagging_tags'),
			buttons:  {
			}
		});
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
