"use strict";
/* global $, il, YAHOO */

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

const ilNotes = {
  hash: "",
  update_code: "",
  panel: false,
  ajax_url: "",
  old: false,

  listNotes: function (e, hash, update_code) {
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

  listComments: function (e, hash, update_code) {
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
  initPanel: function (comments, e) {
    let head_str;
    const t = ilNotes;

    if (comments) {
      head_str = il.Language.txt("notes_public_comments");
    } else {
      head_str = il.Language.txt("private_notes");
    }

    il.Modal.dialogue({
      id: "il_notes_modal",
      show: true,
      header: head_str,
      buttons: {}
    });
    $("#il_notes_modal .modal-body").html("");
    $("#il_notes_modal").data("status", "loading");

    if (comments) {
      this.sendAjaxGetRequest({ cmd: "getOnlyCommentsHTML", cadh: this.hash },
        { mode: 'list_notes' });
    } else {
      this.sendAjaxGetRequest({ cmd: "getOnlyNotesHTML", cadh: this.hash },
        { mode: 'list_notes' });
    }
  },

  cmdAjaxLink: function (e, url) {
    e.preventDefault();
    this.sendAjaxGetRequestToUrl(url, {}, { mode: 'cmd' });
  },

  cmdAjaxForm: function (e, url) {
    e.preventDefault();

    this.sendAjaxPostRequest(e.target, url, { mode: 'cmd' });
  },

  setAjaxUrl: function (url) {
    this.ajax_url = url;
  },

  getAjaxUrl: function () {
    return this.ajax_url;
  },

  sendAjaxGetRequest: function (par, args) {
    var url = this.getAjaxUrl();
    this.sendAjaxGetRequestToUrl(url, par, args)
  },

  sendAjaxGetRequestToUrl: function (url, par, args) {
    var k;
    args.reg_type = "get";
    args.url = url;
    var cb =
      {
        success: this.handleAjaxSuccess,
        failure: this.handleAjaxFailure,
        argument: args
      };
    for (k in par) {
      url = url + "&" + k + "=" + par[k];
    }
    var request = YAHOO.util.Connect.asyncRequest('GET', url, cb);
  },

  // send request per ajax
  sendAjaxPostRequest: function (form, url, args) {
    args.reg_type = "post";
    var cb =
      {
        success: this.handleAjaxSuccess,
        failure: this.handleAjaxFailure,
        argument: args
      };
    var form_str = YAHOO.util.Connect.setForm(form);
    var request = YAHOO.util.Connect.asyncRequest('POST', url, cb);

    return false;
  },

  inModal: function () {
    const status = $("#il_notes_modal").data("status");
    const cs = $("#il_notes_modal").css("display");
    return ($("#il_notes_modal").length &&  (status === "loading" || cs !== "none"));
  },

  handleAjaxSuccess: function (o) {
    var t;
    // perform page modification
    if (o.responseText !== undefined) {
      if (o.argument.mode == 'xxx') {
      } else {
        t = ilNotes;

        // default action: replace html
        if (t.old) {
          il.UICore.setRightPanelContent(o.responseText);
        } else {
          if (t.inModal()) {
            $("#il_notes_modal .modal-body").html(o.responseText);
          } else {
            $("#notes_embedded_outer").html(o.responseText);
          }
          $("#il_notes_modal .modal-header button").focus();
        }

        //				ilNotes.insertPanelHTML(o.responseText);
        if (typeof ilNotes.update_code != "undefined" &&
          ilNotes.update_code != null && ilNotes.update_code !== "") {
          if (o.argument.reg_type === "post" ||
            (typeof o.argument.url == "string" &&
              (o.argument.url.indexOf("cmd=activateComments") !== -1 ||
                o.argument.url.indexOf("cmd=deactivateComments") !== -1
              ))) {
            eval(ilNotes.update_code);
          }
        }
      }
    }
  },

  // FailureHandler
  handleAjaxFailure: function (o) {
    console.log("ilNotes.js: Ajax Failure.");
  },

  // FailureHandler
  updateWidget: function (id, url) {
    il.Util.ajaxReplace(url, id);
  }
};