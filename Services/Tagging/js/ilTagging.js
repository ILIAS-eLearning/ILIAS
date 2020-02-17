
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

ilTagging = {
  hash: '',
  update_code: '',
  panel: false,
  ajax_url: '',

  listTags(e, hash, update_code) {
    // prevent the default action
    if (e && e.preventDefault) {
      e.preventDefault();
    } else if (window.event && window.event.returnValue) {
      window.eventReturnValue = false;
    }

    // hide overlays
    il.Overlay.hideAllOverlays(e, true);

    this.hash = hash;
    this.update_code = update_code;

    // add panel
    this.initPanel(e);
  },

  // init the notes editing panel
  initPanel(e) {
    il.Modal.dialogue({
      id: 'il_tags_modal',
      show: true,
      header: il.Language.txt('tagging_tags'),
      buttons: {
      },
    });
    this.sendAjaxGetRequest({ cmd: 'getHTML', cadh: this.hash }, { mode: 'list_tags' });
    return;


    if (!this.panel) {
      var n = document.getElementById('ilTagsPanel');
      if (!n) {
        const b = $('body');
        b.append("<div class='yui-skin-sam'><div id='ilTagsPanel' class='ilOverlay' style='overflow:auto;'>"
					+ '&nbsp;</div>');
        var n = document.getElementById('ilTagsPanel');
      }

      il.Overlay.add('ilTagsPanel', { yuicfg: {} });
      il.Overlay.show(e, 'ilTagsPanel');
      this.panel = true;
    } else {
      il.Overlay.show(e, 'ilTagsPanel');
    }

    ilTagging.insertPanelHTML('');

    const obj = document.getElementById('ilTagsPanel');
    obj.style.position = 'fixed';
    obj.style.top = '0px';
    obj.style.bottom = '0px';
    obj.style.right = '0px';
    obj.style.left = '';
    obj.style.width = '500px';
    obj.style.height = '100%';

    this.sendAjaxGetRequest({ cmd: 'getHTML', cadh: this.hash }, { mode: 'list_tags' });
  },

  cmdAjaxLink(e, url) {
    // prevent the default action
    if (e && e.preventDefault) {
      e.preventDefault();
    } else if (window.event && window.event.returnValue) {
      window.eventReturnValue = false;
    }

    this.sendAjaxGetRequestToUrl(url, {}, { mode: 'cmd' });
  },

  cmdAjaxForm(e, url) {
    // prevent the default action
    if (e && e.preventDefault) {
      e.preventDefault();
    } else if (window.event && window.event.returnValue) {
      window.eventReturnValue = false;
    }
    this.sendAjaxPostRequest('ilTagFormAjax', url, { mode: 'cmd' });
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
    const cb =		{
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
  sendAjaxPostRequest(form_id, url, args) {
    args.reg_type = 'post';
    const cb =		{
		  success: this.handleAjaxSuccess,
		  failure: this.handleAjaxFailure,
		  argument: args,
    };
    const form_str = YAHOO.util.Connect.setForm(form_id);
    const request = YAHOO.util.Connect.asyncRequest('POST', url, cb);

    return false;
  },


  handleAjaxSuccess(o) {
    // perform page modification
    if (o.responseText !== undefined) {
      if (o.argument.mode == 'xxx') {
      } else {
        // default action: replace html
        $('#il_tags_modal .modal-body').html(o.responseText);
        // ilTagging.insertPanelHTML(o.responseText);
        if (typeof ilTagging.update_code !== 'undefined'
					&& ilTagging.update_code != null && ilTagging.update_code != '') {
          if (o.argument.reg_type == 'post') {
            eval(ilTagging.update_code);
          }
        }

        // only on update
        if (o.argument.mode == 'cmd') {
          $(document).trigger('il_classification_redraw');
        }
      }
    }
  },

  // FailureHandler
  handleAjaxFailure(o) {
    console.log('ilTagging.js: Ajax Failure.');
  },

  insertPanelHTML(html) {
    $('div#ilTagsPanel').html(html);
  },


};
