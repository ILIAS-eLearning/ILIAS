/* global $, il, YAHOO */

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

const ilNotes = {
  hash: '',
  updateCode: '',
  panel: false,
  ajax_url: '',
  old: false,

  listNotes(e, hash, updateCode) {
    // prevent the default action
    e.preventDefault();
    e.stopPropagation(); // #11546 - list properties not working

    // hide overlays
    il.Overlay.hideAllOverlays(e, true);

    this.hash = hash;
    this.updateCode = updateCode;

    // add panel
    this.initPanel(false, e);
  },

  listComments(e, hash, updateCode) {
    // prevent the default action
    e.preventDefault();
    e.stopPropagation(); // #11546 - list properties not working

    // hide overlays
    il.Overlay.hideAllOverlays(e, true);

    this.hash = hash;
    this.updateCode = updateCode;

    // add panel
    this.initPanel(true, e);
  },

  // init the notes editing panel
  initPanel(comments) {
    let headStr;

    if (comments) {
      headStr = il.Language.txt('notes_public_comments');
    } else {
      headStr = il.Language.txt('private_notes');
    }

    il.Modal.dialogue({
      id: 'il_notes_modal',
      show: true,
      header: headStr,
      buttons: {},
    });
    $('#il_notes_modal .modal-body').html('');

    if (comments) {
      this.sendAjaxGetRequest({ cmd: 'getOnlyCommentsHTML', cadh: this.hash },
        { mode: 'list_notes' });
    } else {
      this.sendAjaxGetRequest({ cmd: 'getOnlyNotesHTML', cadh: this.hash },
        { mode: 'list_notes' });
    }
  },

  cmdAjaxLink(e, url) {
    e.preventDefault();

    this.sendAjaxGetRequestToUrl(url, {}, { mode: 'cmd' });
  },

  cmdAjaxForm(e, url) {
    e.preventDefault();

    this.sendAjaxPostRequest('ilNoteFormAjax', url, { mode: 'cmd' });
  },

  setAjaxUrl(url) {
    this.ajax_url = url;
  },

  getAjaxUrl() {
    return this.ajax_url;
  },

  sendAjaxGetRequest(par, args) {
    const url = this.getAjaxUrl();
    this.sendAjaxGetRequestToUrl(url, par, args);
  },

  sendAjaxGetRequestToUrl(url, par, args) {
    let k;
    args.reg_type = 'get';
    args.url = url;
    const cb = {
      success: this.handleAjaxSuccess,
      failure: this.handleAjaxFailure,
      argument: args,
    };
    for (k in par) {
      url = `${url}&${k}=${par[k]}`;
    }
    const request = YAHOO.util.Connect.asyncRequest('GET', url, cb);
  },

  // send request per ajax
  sendAjaxPostRequest(formId, url, args) {
    console.log(`form_id${formId}`);
    args.reg_type = 'post';
    const cb = {
      success: this.handleAjaxSuccess,
      failure: this.handleAjaxFailure,
      argument: args,
    };
    YAHOO.util.Connect.setForm(formId);
    YAHOO.util.Connect.asyncRequest('POST', url, cb);

    return false;
  },


  handleAjaxSuccess(o) {
    let t;
    // perform page modification
    if (o.responseText !== undefined) {
      if (o.argument.mode == 'xxx') {
      } else {
        t = ilNotes;

        // default action: replace html
        if (t.old) {
          il.UICore.setRightPanelContent(o.responseText);
        } else {
          $('#il_notes_modal .modal-body').html(o.responseText);
        }

        // ilNotes.insertPanelHTML(o.responseText);
        if (typeof ilNotes.updateCode !== 'undefined'
          && ilNotes.updateCode != null && ilNotes.updateCode != '') {
          if (o.argument.reg_type == 'post'
            || (typeof o.argument.url === 'string'
              && (o.argument.url.indexOf('cmd=activateComments') != -1
                || o.argument.url.indexOf('cmd=deactivateComments') != -1
              ))) {
            eval(ilNotes.updateCode);
          }
        }
      }
    }
  },

  // FailureHandler
  handleAjaxFailure(o) {
    console.log('ilNotes.js: Ajax Failure.');
  },

  // FailureHandler
  updateWidget(id, url) {
    il.Util.ajaxReplace(url, id);
  },
};
