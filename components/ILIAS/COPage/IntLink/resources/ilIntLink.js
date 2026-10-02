if (typeof il === 'undefined') {
  il = {};
}

il.IntLink = {
  int_link_url: '',
  cfg: {},
  id: '',
  modalTemplate: '',
  modalShowSignal: '',
  modalCloseSignal: '',

  save_pars: {
    // "target_type": "",
    link_par_ref_id: 0,
    link_par_obj_id: 0,
    link_par_fold_id: 0,
    link_type: '',
  },

  getURLParameter(url, name) {
    return decodeURIComponent((new RegExp(`[?|&]${name}=` + '([^&;]+?)(&|#|;|$)').exec(window.location.search) || [null, ''])[1].replace(/\+/g, '%20')) || null;
  },

  getUrlParameters(url) {
    let match;
    const pl = /\+/g; // Regex for replacing addition symbol with a space
    const search = /([^&=]+)=?([^&]*)/g;
    const decode = function (s) { return decodeURIComponent(s.replace(pl, ' ')); };
    let query;

    query = url.substring(url.indexOf('?') + 1);

    const urlParams = {};
    while (match = search.exec(query)) {
      urlParams[decode(match[1])] = decode(match[2]);
    }
    return urlParams;
  },

  replaceUrlParam(url, paramName, paramValue) {
    const pattern = new RegExp(`\\b(${paramName}=).*?(&|$)`);

    if (paramValue == null) {
      paramValue = '';
    }
    if (url.search(pattern) >= 0) {
      return url.replace(pattern, `$1${paramValue}$2`);
    }
    return `${url + (url.indexOf('?') > 0 ? '&' : '?') + paramName}=${paramValue}`;
  },

  replaceSavePars(url) {
    t = il.IntLink;
    for (p in t.save_pars) {
      url = t.replaceUrlParam(url, p, t.save_pars[p]);
    }
    return url;
  },

  refresh() {
    this.init(this.cfg);
  },

  init(cfg) {
    // console.trace();
    // new: get link dynamically
    if (cfg.url == '') {
      $('a.iosEditInternalLinkTrigger').each((idx, el) => {
        const link = $(el).attr('href');
        const id = $(el).attr('id');
        $(el).click(() => {
          il.IntLink.initPanel(link, id);
          return false;
        });
      });
    }
    // old: static id
    else {
      this.cfg = cfg;
      $('#iosEditInternalLinkTrigger').on('click', this.openIntLink);
      this.setInternalLinkUrl(cfg.url);
    }
  },

  setInternalLinkUrl(url) {
    let p;
    const t = il.IntLink;
    const pars = t.getUrlParameters(url);

    // console.log("setInternalLinkUrl: " + url);
    for (p in t.save_pars) {
      t.save_pars[p] = '';
      if (pars[p]) {
        t.save_pars[p] = pars[p];
      }
    }
    t.int_link_url = url;
  },

  getInternalLinkUrl() {
    return this.int_link_url;
  },

  // click event handler
  openIntLink(ev, addCallback) {
    this.addCallback = addCallback;
    il.IntLink.initPanel();
    if (ev) {
      ev.preventDefault();
      ev.stopPropagation();
    }
  },

  /**
	 * Init panel
	 * @param internal_link (in case of page editor undefined)
	 * @param id			(in case of page editor undefined)
	 */
  initPanel(internal_link, id) {
    // move node to body to prevent form in form, see e.g. #16369
    $('#ilIntLinkModal').appendTo('body');
    if (internal_link != undefined) {
      this.setInternalLinkUrl(internal_link);
      this.id = id.substring(0, id.length - 5);
    }

    const parent = document.getElementById('ilIntLinkModal');
    parent.innerHTML = JSON.parse(il.IntLink.modalTemplate);

    il.IntLink.showPanel();
    const j = this.getInternalLinkUrl();
    this.initAjax({ mode: 'int_link' });
  },

  /**
	 * Show panel. This function should be extracted from IntLink component, since the
	 * panel is used by other features, too (e.g. wiki link handling)
	 */
  showPanel() {
    const showSignal = il.IntLink.modalShowSignal;
    $(document).trigger(
      showSignal,
      {
        id: showSignal,
        triggerer: $(this),
        options: JSON.parse('[]'),
      },
    );
  },

  sendAjaxGetRequestToUrl(url, par = {}, args = {}) {
    let k;
    args.reg_type = 'get';
    args.url = url;
    for (k in par) {
      url = `${url}&${k}=${par[k]}`;
    }
    il.repository.core.fetchHtml(url).then((html) => {
      this.handleAjaxSuccess({
        argument: args,
        responseText: html,
      });
    });
  },

  sendAjaxPostRequest(form, url, args, cmd, cb) {
    args.reg_type = 'post';
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => (data[key] = value));
    if (cmd !== '') {
      data[cmd] = 'x';
    }
    il.repository.core.fetchHtml(url, data, true).then((html) => {
      cb({
        argument: args,
        responseText: html,
      });
    });

    return false;
  },

  // cfg pars: url (if not provided and post, take form.action?), post/get, parameters (added to get/post)
  initAjax(cfg) {
    let sUrl = this.getInternalLinkUrl();
    let f;

    const callback =		{
		  success: this.handleAjaxSuccess,
		  upload: this.handleAjaxUpload,
		  failure: this.handleAjaxFailure,
		  argument: { mode: cfg.mode },
    };
    // console.log(cfg.mode);
    if (cfg.mode == 'select_type') {
      f = document.getElementById('ilIntLinkTypeForm');
      sUrl = f.action;

      // sUrl = this.getInternalLinkUrl() + "&cmd=changeLinkType";

      this.save_pars.link_type = $('#ilIntLinkTypeSelector').val();
      sUrl = this.replaceSavePars(sUrl);
      this.sendAjaxGetRequestToUrl(sUrl);
    } else if (cfg.mode == 'reset') {
      f = document.getElementById('ilIntLinkResetForm');
      sUrl = f.action;
      const form = document.getElementById('ilIntLinkResetForm');
      this.sendAjaxPostRequest(form, sUrl, {}, 'cmd[resetLinkList]', this.handleAjaxSuccess);
    } else if (cfg.mode == 'save_file_link') {
      f = document.getElementById('ilFileLinkUploadForm');
      sUrl = `${f.action}&cmd=saveFileLink`;
      const form = document.getElementById('ilFileLinkUploadForm');
      this.sendAjaxPostRequest(form, sUrl, {}, 'cmd[saveFileLink]', this.handleAjaxSuccess);
    } else if (cfg.mode == 'sel_target_obj') {
      sUrl = `${this.getInternalLinkUrl()}&do=set&sel_id=${
        cfg.ref_id}&cmd=changeTargetObject`;
      // this.save_pars.target_type = cfg.type;
      this.save_pars.link_type = cfg.link_type;
      this.save_pars.link_par_ref_id = cfg.ref_id;
      this.save_pars.link_par_obj_id = '';

      sUrl = this.replaceSavePars(sUrl);
      this.sendAjaxGetRequestToUrl(sUrl);
    } else if (cfg.mode == 'change_object') {
      sUrl = `${this.getInternalLinkUrl()}&cmd=changeTargetObject`;
      sUrl = this.replaceSavePars(sUrl);
      this.sendAjaxGetRequestToUrl(sUrl);
    } else if (cfg.mode == 'set_mep_fold') {
      sUrl = `${this.getInternalLinkUrl()}&cmd=setMedPoolFolder&mep_fold=${
        cfg.mep_fold}`;
      sUrl = this.replaceSavePars(sUrl);
      this.sendAjaxGetRequestToUrl(sUrl);
    } else {
      sUrl = `${this.getInternalLinkUrl()}&cmd=showLinkHelp`;
      sUrl = this.replaceSavePars(sUrl);
      this.sendAjaxGetRequestToUrl(sUrl);
    }

    return false;
  },

  handleAjaxSuccess(o) {
    // perform page modification
    if (o.responseText !== undefined) {
      il.IntLink.insertPanelHTML(o.responseText);
      il.IntLink.initEvents();
    }
  },

  initEvents() {
    $('#form_link_user_search_form').on('submit', function (e) {
      e.preventDefault();
      let sUrl = `${il.IntLink.getInternalLinkUrl()}&cmd=showLinkHelp`;
      sUrl = il.IntLink.replaceSavePars(sUrl);
      $.ajax({
        type: 'POST',
        url: sUrl,
        data: $(this).serializeArray(),
        success(o) {
          il.IntLink.insertPanelHTML(o);
          il.IntLink.initEvents();
        },
      });
      // console.log("search user");
    });
  },

  handleAjaxUpload(o) {
    // perform page modification
    if (o.responseText !== undefined) {
      il.IntLink.insertPanelHTML(o.responseText);
    }
  },

  // FailureHandler
  handleAjaxFailure(o) {
    console.log('ilIntLink.js: Ajax Failure.');
  },

  insertPanelHTML(html) {
    $('#ilIntLinkModalContent').html(html);
    $('#ilIntLinkTypeSelector').on('change', this.selectLinkTypeEvent);
    $('#ilIntLinkReset').on('click', this.clickResetEvent);
    $('#ilChangeTargetObject').on('click', this.clickChangeTargetObjectEvent);
    $('#ilSaveFileLink').on('click', this.clickSaveFileLinkEvent);
  },

  selectLinkTypeEvent(ev) {
    il.IntLink.initAjax({ mode: 'select_type' });
  },

  clickResetEvent(ev) {
    il.IntLink.initAjax({ mode: 'reset' });
    ev.preventDefault();
    ev.stopPropagation();
  },

  clickChangeTargetObjectEvent(ev) {
    il.IntLink.initAjax({ mode: 'change_object' });
    ev.preventDefault();
    ev.stopPropagation();
  },

  clickSaveFileLinkEvent(ev) {
    il.IntLink.initAjax({ mode: 'save_file_link' });
    ev.preventDefault();
    ev.stopPropagation();
  },

  selectLinkTargetObject(type, ref_id, link_type) {
    il.IntLink.initAjax({
      mode: 'sel_target_obj', ref_id, type, link_type,
    });
    return false;
  },

  addInternalLink(b, e, ev, c) {
    if (this.addCallback) {
      this.addCallback(b, e, c);
    } else if (il.Form && $('#par_content').length == 0 && $('#cell_0_0').length == 0) {
      il.Form.addInternalLink(b, e, this.id, ev);
    } else if (addInternalLink) {
      // old style, needs clean-up
      addInternalLink(b);
    }

    il.IntLink.hidePanel();
    return false;
  },

  hidePanel() {
    const closeSignal = il.IntLink.modalCloseSignal;
    $(document).trigger(
      closeSignal,
      {
        id: closeSignal,
        triggerer: $(this),
        options: JSON.parse('[]'),
      },
    );
  },

  setMepPoolFolder(mep_fold_id) {
    il.IntLink.initAjax({ mode: 'set_mep_fold', mep_fold: mep_fold_id });
    return false;
  },

  setModalTemplate(modalTemplate, showSignal, closeSignal) {
    il.IntLink.modalTemplate = modalTemplate;
    // AJAX forms can replace the container with signals for a different modal.
    // Keep the signals paired with the template registered on page load.
    il.IntLink.modalShowSignal = showSignal;
    il.IntLink.modalCloseSignal = closeSignal;
  },

};
