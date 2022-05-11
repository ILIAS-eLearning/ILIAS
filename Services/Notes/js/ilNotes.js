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
    console.log("listNotes");
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
    console.log("listComments");
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
    console.log("initPanel");
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
      buttons: {},
    });
    $("#il_notes_modal .modal-body").html("");
    $("#il_notes_modal").data("status", "loading");
    console.log($("#il_notes_modal").data("status"));

    if (comments) {
      this.sendAjaxGetRequest({ cmd: "getCommentsHTML", cadh: this.hash },
        { mode: 'list_notes' });
    } else {
      this.sendAjaxGetRequest({ cmd: "getNotesHTML", cadh: this.hash },
        { mode: 'list_notes' });
    }
  },

  cmdAjaxLink: function (e, url) {
    console.log("cmdAjaxLink");
    console.log(url);
    e.preventDefault();
    this.sendAjaxGetRequestToUrl(url, {}, { mode: 'cmd' });
  },

  cmdAjaxForm: function (e, url) {
    console.log("cmdAjaxForm");
    e.preventDefault();

    this.sendAjaxPostRequest(e.target, url, { mode: 'cmd' });
  },

  setAjaxUrl: function (url) {
    console.log("setAjaxUrl");
    this.ajax_url = url;
  },

  getAjaxUrl: function () {
    console.log("getAjaxUrl");
    return this.ajax_url;
  },

  sendAjaxGetRequest: function (par, args) {
    console.log("sendAjaxGetRequest");
    var url = this.getAjaxUrl();
    this.sendAjaxGetRequestToUrl(url, par, args)
  },

  sendAjaxGetRequestToUrl: function (url, par, args) {
    console.log("sendAjaxGetRequestToUrl");
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
    console.log("sendAjaxPostRequest");
    console.log(form);
    console.log(url);
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
    console.log("inModal");
    const status = $("#il_notes_modal").data("status");
    const cs = $("#il_notes_modal").css("display");
    console.log($("#il_notes_modal").length);
    console.log($("#il_notes_modal").data("status"));
    console.log(status);
    return ($("#il_notes_modal").length && (status === "loading" || cs !== "none"));
  },

  handleAjaxSuccess: function (o) {
    console.log("handleAjaxSuccess");
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
            console.log("setting " + "#il_notes_modal .modal-body");
            $("#il_notes_modal").data("status", "");
            $("#il_notes_modal .modal-body").html(o.responseText);
            ilNotes.init(document.getElementById("il_notes_modal"));
          } else {
            console.log("setting " + "#notes_embedded_outer");
            $("#notes_embedded_outer").html(o.responseText);
            ilNotes.init(document.getElementById("notes_embedded_outer"));
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
    console.log("updateWidget");
    console.log(id);
    console.log(url);
    il.Util.ajaxReplace(url, id);
  },

  init: function (node) {
    if (node == null) {
      node = document;
    }

    // edit form button
    node.querySelectorAll("[data-note-el='edit-form-area']").forEach(area => {
      const b = area.querySelector("button");
      const f = area.querySelector("[data-note-el='edit-form'] form");
      const submitButton = area.querySelector("[data-note-el='edit-form'] form .il-standard-form-footer button");
      const fArea = area.querySelector("[data-note-el='edit-form']");

      // clone cancel from submit button
      let cancelButton = submitButton.cloneNode(true);
      cancelButton = submitButton.parentNode.appendChild(cancelButton);
      cancelButton.innerHTML = fArea.dataset.noteFormCancelText;
      cancelButton.addEventListener("click", (event) => {
        event.preventDefault();
        if (fArea.dataset.noteFormCancelAction != "") {
          ilNotes.cmdAjaxLink(event, fArea.dataset.noteFormCancelAction);
        } else {
          fArea.style.display = 'none';
          b.style.display = '';
        }
      });

      // add listener to "add" comment/note button
      b.addEventListener("click", (event) => {
        fArea.style.display = "";
        event.target.style.display = 'none';
      });
      f.addEventListener("submit", (event) => {
        event.preventDefault();
        ilNotes.cmdAjaxForm(event, fArea.dataset.noteFormAction);
      });
    });

    // edit form
  }
}

$(() => {
  ilNotes.init(null);
});