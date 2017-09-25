"use strict";

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

var ilNotes =
{
	hash: "",
	update_code: "",
	panel: false,
	ajax_url: "",
	old: false,
	
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
		var head_str;
		var t = ilNotes;

		if (comments) {
			head_str = il.Language.txt("notes_public_comments");
		} else {
			head_str = il.Language.txt("private_notes");
		}

		if (t.old) {
			il.UICore.showRightPanel(e);
		}
		else {
			il.Modal.dialogue({
				id:       "il_notes_modal",
				show: true,
				header: head_str,
				buttons:  {
					/*confirm: {
						type:      "button",
						label:     il.Language.txt("confirm"),
						className: "btn btn-primary",
						callback:  function (e, $modal) {
							e.stopPropagation();
							$modal.find('.btn').addClass('.ilSubmitInactive').attr('disabled', true);

							$.ajax({
								url:      t.ajax_url + "&cmd=deleteNewsComment",
								type:     "POST",
								dataType: "json",
								data:     {
									id: id
								}
							}).done(function (response) {
								if (response.data != undefined) {
									nr_of_comments.html(response.txt_nr_of_comments);

									if (response.data.id) {
										container
											.find(".ilNewsStreamCommentEditedDatetime")
											.text(response.data.editedDatetime);

										container
											.find(".ilNewsStreamCommentEditedDatetime")
											.parent()
											.removeClass("ilNoDisplay");

										comment_container.html(response.data.text);

										comment_container.removeClass("ilNoDisplay");
										editing_container.addClass("ilNoDisplay");
										container.find(".ilNewsCommentEditButton").addClass("ilNoDisplay");
										container.find(".ilNewsCommentDeleteButton").addClass("ilNoDisplay");
									} else {
										$('#' + target_id).parent().prev().find('.ilNewsCommentDeleteButton').removeClass('ilNoDisplay');
										$('#' + target_id).parent().remove();
									}

									$modal.modal("hide");
									updateMasonry();
								} else {
									window.location.href = t.ajax_url;
								}
							}).fail(function (e) {
								// Left the happy path ...
								window.location.href = t.ajax_url;
							});
						}
					},
					cancel:  {
						label:     il.Language.txt("cancel"),
						type:      "button",
						className: "btn btn-default",
						callback:  function (e, $modal) {
							e.stopPropagation();
							$modal.find(".btn").addClass(".ilSubmitInactive").attr("disabled", true);
							$modal.modal("hide");
						}
					}*/
				}
			});
			$("#il_notes_modal .modal-body").html("");
		}

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
		var k;
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
		var t;
		// perform page modification
		if(o.responseText !== undefined)
		{
			if (o.argument.mode == 'xxx')
			{
			}
			else
			{
				t = ilNotes;

				// default action: replace html
				if (t.old) {
					il.UICore.setRightPanelContent(o.responseText);
				}
				else {
					$("#il_notes_modal .modal-body").html(o.responseText);
				}

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
