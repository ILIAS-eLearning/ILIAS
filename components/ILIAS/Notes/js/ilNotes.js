/* global il */

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
 ******************************************************************** */

const ilNotes = {
  hash: '',
  update_code: '',
  panel: false,
  ajax_url: '',
  old: false,

  listNotes(e, hash, update_code) {
    // prevent the default action
    e.preventDefault();
    e.stopPropagation(); // #11546 - list properties not working

    // hide overlays
    // il.Overlay.hideAllOverlays(e, true);

    this.hash = hash;
    this.update_code = update_code;

    // add panel
    this.initPanel(false, e);
  },

  clickTrigger(e) {
    let tr = e.target;

    // unfortunately glyphs currently set the ID on the surrounding <a> tag
    // but the events are coming from the inner span
    if (tr.dataset.noteType !== 'trigger') {
      tr = tr.closest("[data-note-ui-type='trigger']");
    }

    const queryUrl = tr.dataset.noteQueryUrl;
    const { noteKey } = tr.dataset;
    console.log('_--');
    console.log(e.target);
    console.log(queryUrl);
    console.log(noteKey);
    e.preventDefault();
    e.stopPropagation(); // #11546 - list properties not working
    this.update_code = '';
    if (noteKey) {
      this.hash = noteKey;
    }
    this.initPanel(null, e, queryUrl);
  },

  listComments(e, hash, update_code) {
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
  initPanel(comments, e, queryUrl) {
    let head_str;
    const t = ilNotes;

    head_str = il.Language.txt('private_notes');
    if (queryUrl) {
      if (queryUrl.includes('ilCommentGUI')) {
        head_str = il.Language.txt('notes_public_comments');
      }
      if (queryUrl.includes('ilMessageGUI')) {
        head_str = il.Language.txt('notes_messages');
      }
    } else if (comments) {
      head_str = il.Language.txt('notes_public_comments');
    }

    il.Modal.dialogue({
      id: 'il_notes_modal',
      show: true,
      header: head_str,
      buttons: {},
    });
    const modalBody = document.querySelector('#il_notes_modal .modal-body');
    const modal = document.getElementById('il_notes_modal');
    modalBody.innerHTML = '';
    modal.dataset.status = 'loading';

    if (queryUrl) {
      this.sendAjaxGetRequestToUrl(queryUrl, {}, {});
    } else if (comments) {
      this.sendAjaxGetRequest(
        { cmd: 'getCommentsHTML', cadh: this.hash },
        { mode: 'list_notes' },
      );
    } else {
      this.sendAjaxGetRequest(
        { cmd: 'getNotesHTML', cadh: this.hash },
        { mode: 'list_notes' },
      );
    }
  },

  cmdAjaxLink(e, url) {
    e.preventDefault();
    this.sendAjaxGetRequestToUrl(url, {}, { mode: 'cmd' });
  },

  cmdAjaxForm(e, url) {
    e.preventDefault();

    this.sendAjaxPostRequest(e.target, url, { mode: 'cmd' });
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

  // send request per ajax
  sendAjaxPostRequest(form, url, args) {
    args.reg_type = 'post';
    const formData = new FormData(form);
    const data = {};
    formData.forEach((value, key) => (data[key] = value));
    il.repository.core.fetchHtml(url, data, true).then((html) => {
      this.handleAjaxSuccess({
        argument: args,
        responseText: html,
      });
    });

    return false;
  },

  inModal() {
    const modal = document.getElementById('il_notes_modal');
    if (!modal) {
      return false;
    }
    const { status } = modal.dataset;
    const cs = modal.style.display;
    return (status === 'loading' || cs !== 'none');
  },

  handleAjaxSuccess(o) {
    let t;
    const modal = document.getElementById('il_notes_modal');
    // perform page modification
    if (o.responseText !== undefined) {
      t = ilNotes;

      // default action: replace html
      if (t.inModal()) {
        const modalBody = document.querySelector('#il_notes_modal .modal-body');
        modal.dataset.status = '';
        il.repository.core.setInnerHTML(modalBody, o.responseText);
        ilNotes.init(document.getElementById('il_notes_modal'));
      } else {
        const embedOuter = document.getElementById('notes_embedded_outer');
        il.repository.core.setInnerHTML(embedOuter, o.responseText);
        ilNotes.init(document.getElementById('notes_embedded_outer'));
      }
      const headButton = document.querySelector('#il_notes_modal .modal-header button');
      if (headButton) {
        headButton.focus();
      }
    }

    //				ilNotes.insertPanelHTML(o.responseText);
    if (typeof ilNotes.update_code !== 'undefined'
      && ilNotes.update_code != null && ilNotes.update_code !== '') {
      if (o.argument.reg_type === 'post'
        || (typeof o.argument.url === 'string'
          && (o.argument.url.indexOf('cmd=activateComments') !== -1
            || o.argument.url.indexOf('cmd=deactivateComments') !== -1
            || o.argument.url.indexOf('cmd=confirmDelete') !== -1
          ))) {
        eval(ilNotes.update_code);
      }
    } else if (ilNotes.hash) {
      document.querySelectorAll(`[data-note-ui-type='widget'][data-note-key='${ilNotes.hash}']`).forEach((el) => {
        const url = el.dataset.noteUpdateUrl;
        if (url) {
          ilNotes.updateWidget(el.id, url);
        }
      });
    }
  },

  // FailureHandler
  handleAjaxFailure(o) {
    console.log('ilNotes.js: Ajax Failure.');
  },

  updateWidget(id, url) {
    il.repository.core.fetchReplace(id, url);
  },

  init(node) {
    if (node == null) {
      node = document;
    }
    // focus textarea if requested
    const focus_element = node.querySelector("[data-note-focus='1'] form textarea");
    if (focus_element) {
      focus_element.focus();
    }

    // edit form button
    node.querySelectorAll("[data-note-el='edit-form-area']").forEach((area) => {
      const b = area.querySelector('button');
      const f = area.querySelector("[data-note-el='edit-form'] form");
      const submitButton = area.querySelector("[data-note-el='edit-form'] form .il-standard-form-footer button");
      const fArea = area.querySelector("[data-note-el='edit-form']");

      // clone cancel from submit button
      let cancelButton = submitButton.cloneNode(true);
      cancelButton = submitButton.parentNode.appendChild(cancelButton);
      cancelButton.innerHTML = fArea.dataset.noteFormCancelText;
      cancelButton.addEventListener('click', (event) => {
        event.preventDefault();
        if (fArea.dataset.noteFormCancelAction != '') {
          ilNotes.cmdAjaxLink(event, fArea.dataset.noteFormCancelAction);
        } else {
          fArea.style.display = 'none';
          b.style.display = '';
        }
      });
      // add listener to "add" comment/note button -> show form
      b.addEventListener('click', (event) => {
        const mess = document.querySelector('.il-notes-section .alert-success');
        if (mess) {
          mess.style.display = 'none';
        }

        fArea.style.display = '';
        fArea.querySelector('form textarea').focus();
        event.target.style.display = 'none';
      });
      f.addEventListener('submit', (event) => {
        f.querySelectorAll('button').forEach((b) => {
          b.disabled = true;
        });
        event.preventDefault();
        ilNotes.cmdAjaxForm(event, fArea.dataset.noteFormAction);
      });
    });

    // edit form
  },
};

il.Util.addOnLoad(() => {
  ilNotes.init(null);
});
