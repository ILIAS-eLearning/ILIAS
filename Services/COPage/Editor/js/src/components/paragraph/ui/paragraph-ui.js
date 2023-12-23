import ACTIONS from '../actions/paragraph-action-types.js';
import PAGE_ACTIONS from '../../page/actions/page-action-types.js';
import TinyWrapper from './tiny-wrapper.js';
import TINY_CB from './tiny-wrapper-cb-types.js';
import AutoSave from './auto-save.js';

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

/**
 * paragraph ui
 */
export default class ParagraphUI {
  /**
   * @type {boolean}
   */
  // debug = true;

  /**
   * Model
   * @type {Model}
   */
  // page_model = {};

  /**
   * UI model
   * @type {Object}
   */
  // uiModel = {};

  /**
   * @type {Client}
   */
  // client;

  /**
   * @type {Dispatcher}
   */
  // dispatcher;

  /**
   * @type {ActionFactory}
   */
  // actionFactory;

  /**
   * @type {ToolSlate}
   */
  // toolSlate;

  /**
   * @type {TinyWrapper}
   */
  // tinyWrapper;

  /**
   * @type {pageModifier}
   */
  // pageModifier;

  /**
   *
   * @type {AutoSave}
   */
  // autoSave;

  /**
   * @type {boolean}
   */
  // dataTableMode = false;

  /**
   * @type {Object}
   */
  /*
  text_formats = {
    Strong: {inline : 'span', classes : 'ilc_text_inline_Strong'},
    Emph: {inline : 'span', classes : 'ilc_text_inline_Emph'},
    Important: {inline : 'span', classes : 'ilc_text_inline_Important'},
    Comment: {inline : 'span', classes : 'ilc_text_inline_Comment'},
    Quotation: {inline : 'span', classes : 'ilc_text_inline_Quotation'},
    Accent: {inline : 'span', classes : 'ilc_text_inline_Accent'},
    Sup: {inline : 'sup', classes : 'ilc_sup_Sup'},
    Sub: {inline : 'sub', classes : 'ilc_sub_Sub'}
  };
   */

  /**
   * @param {Client} client
   * @param {Dispatcher} dispatcher
   * @param {ActionFactory} actionFactory
   * @param {PageModel} page_model
   * @param {ToolSlate} toolSlate
   */
  constructor(client, dispatcher, actionFactory, page_model, toolSlate, pageModifier, autosave) {
    this.debug = false;

    this.text_formats = {
      Strong: { inline: 'span', classes: 'ilc_text_inline_Strong' },
      Emph: { inline: 'span', classes: 'ilc_text_inline_Emph' },
      Important: { inline: 'span', classes: 'ilc_text_inline_Important' },
      Comment: { inline: 'span', classes: 'ilc_text_inline_Comment' },
      Quotation: { inline: 'span', classes: 'ilc_text_inline_Quotation' },
      Accent: { inline: 'span', classes: 'ilc_text_inline_Accent' },
      Sup: { inline: 'sup', classes: 'ilc_sup_Sup' },
      Sub: { inline: 'sub', classes: 'ilc_sub_Sub' },
    };

    this.dataTableMode = false;
    this.page_model = {};
    this.uiModel = {};
    this.client = client;
    this.dispatcher = dispatcher;
    this.actionFactory = actionFactory;
    this.page_model = page_model;
    this.toolSlate = toolSlate;
    this.tinyWrapper = new TinyWrapper();
    this.switchToEnd = false;

    this.tinyWrapper.addCallback(TINY_CB.SPLIT_ON_RETURN, (tiny, contents) => {
      dispatcher.dispatch(actionFactory.paragraph().editor()
        .splitParagraph(this.page_model.getCurrentPCId(), tiny.getText(), tiny.getCharacteristic(), contents));
    });
    this.tinyWrapper.addCallback(TINY_CB.MERGE, (tiny, previous) => {
      this.checkMerge(tiny, previous);
    });

    this.pageModifier = pageModifier;
    this.autoSave = autosave;
    this.pc_id_str = '';
  }

  //
  // Initialisation
  //

  /**
   * @param message
   */
  log(message) {
    if (this.debug) {
      console.log(message);
    }
  }

  /**
   *
   * @param {boolean} mode
   */
  setDataTableMode(mode) {
    this.dataTableMode = mode;
    if (mode) {
      this.splitOnReturn = false;
    }
  }

  /**
   * @return {boolean}
   */
  getDataTableMode() {
    return this.dataTableMode;
  }

  /**
   */
  init(uiModel) {
    this.log('paragraph-ui.init');

    const action = this.actionFactory;
    const dispatch = this.dispatcher;
    const pageModel = this.page_model;

    this.uiModel = uiModel;
    const t = this;
    this.initTinyWrapper();
    const wrapper = this.tinyWrapper;

    this.log(`css: ${this.uiModel.config.content_css}`);

    this.initMenu();

    this.log(`set interval: ${this.uiModel.autoSaveInterval}`);
    this.autoSave.setInterval(this.uiModel.autoSaveInterval);
    this.autoSave.addOnAutoSave(() => {
      if (pageModel.getCurrentPCName() === 'Paragraph') {
        const act = action.paragraph().editor().autoSave(wrapper.getText(), wrapper.getCharacteristic());
        dispatch.dispatch(act);
      }
    });

    this.initWrapperCallbacks();
  }

  initTinyWrapper() {
    const wrapper = this.tinyWrapper;

    this.uiModel.config.text_formats.forEach((f) => wrapper.addTextFormat(f));
    wrapper.setTextBlockFormats(this.uiModel.config.text_block_formats);

    il.Util.addOnLoad(() => {
      $(window).resize(() => {
        wrapper.autoResize();
      });
    });

    wrapper.setContentCss(this.uiModel.config.content_css);
  }

  /**
   */
  reInit() {
  }

  initMenu() {
    const action = this.actionFactory;
    const dispatch = this.dispatcher;

    document.querySelectorAll("[data-copg-ed-type='par-button']").forEach((parButton) => {
      switch (parButton.dataset.paction) {
        case 'cancel':
          parButton.addEventListener('click', (event) => {
            dispatch.dispatch(action.page().editor().componentCancel());
          });
          break;
      }
    });

    // characteristic selection
    document.querySelectorAll('#ilAdvSelListTable_style_selection li').forEach((li) => {
      let cl;
      li.removeAttribute('onclick');
      li.addEventListener('click', (event) => {
        cl = li.querySelector('.ilCOPgEditStyleSelectionItem').querySelector('h1,h2,h3,div').classList[0];
        this.log(cl);
        cl = cl.split('_');
        cl = cl[cl.length - 1];
        this.setParagraphClass(cl);
      });
    });
  }

  //
  // PORTED STUFF
  //

  /// /
  /// / Text editor commands
  /// /

  cmdCancel() {
    const pcId = this.page_model.getCurrentPCId();
    const undo_pc_model = this.page_model.getUndoPCModel(pcId);

    if (undo_pc_model && this.page_model.getComponentState() === this.page_model.STATE_COMPONENT_EDIT) {
      this.setParagraphClass(undo_pc_model.characteristic);
      this.tinyWrapper.setContent(
        undo_pc_model.text,
        undo_pc_model.characteristic,
      );
    }

    this.autoSave.resetAutoSave();
    this.tinyWrapper.stopEditing();
  }

  cmdSpan(t) {
    this.log(`paragraph-ui.cmdSpan ${t}`);
    this.tinyWrapper.toggleFormat(t);
  }

  cmdSup() {
    this.tinyWrapper.toggleFormat('Sup');
  }

  cmdSub() {
    this.tinyWrapper.toggleFormat('Sub');
  }

  cmdRemoveFormat() {
    this.tinyWrapper.removeFormat();
  }

  addIntLink(b, e, content) {
    this.addBBCode(b, e, false, content);
  }

  cmdIntLink() {
    const t = this;
    il.IntLink.openIntLink(null, (b, e, content) => {
      t.addIntLink(b, e, content);
    });
  }

  getSelection() {
    const ed = tinyMCE.get('tinytarget');
    ed.focus();
    return ed.selection.getContent();
  }

  getListStyleDropdown(action) {
    let dd = document.querySelector(`[data-copg-ed-action='${action}']`);
    if (dd) {
      dd = dd.closest('div.dropdown');
    }
    return dd;
  }

  enableListStyleDropdown(dd, enable) {
    if (dd) {
      dd.querySelector('button').disabled = !enable;
      dd.style.display = '';
    }
  }

  hideListStyleDropdown(dd) {
    if (dd) {
      dd.style.display = 'none';
    }
  }

  updateListStyleDropdowns() {
    const ed = tinyMCE.get('tinytarget');
    const currentRng = ed.selection.getRng();
    const bulletDD = this.getListStyleDropdown('list.bulletStyle');
    const numberDD = this.getListStyleDropdown('list.numberStyle');
    const itemDD = this.getListStyleDropdown('list.itemStyle');

    let currentParent = '';
    if (currentRng.startContainer === currentRng.endContainer) {
      const cont = currentRng.startContainer.parentNode;
      const list = cont.closest('ol,ul');
      if (list) {
        currentParent = list.nodeName.toLowerCase();
      }
    }

    if (currentParent === 'ul') {
      this.enableListStyleDropdown(bulletDD, true);
      this.enableListStyleDropdown(itemDD, true);
      this.enableListStyleDropdown(numberDD, false);
      if (bulletDD && numberDD) {
        this.hideListStyleDropdown(numberDD);
      }
    } else if (currentParent === 'ol') {
      this.enableListStyleDropdown(numberDD, true);
      this.enableListStyleDropdown(itemDD, true);
      this.enableListStyleDropdown(bulletDD, false);
      if (bulletDD && numberDD) {
        this.hideListStyleDropdown(bulletDD);
      }
    } else {
      this.enableListStyleDropdown(bulletDD, false);
      this.enableListStyleDropdown(numberDD, false);
      this.enableListStyleDropdown(itemDD, false);
      if (bulletDD && numberDD) {
        this.hideListStyleDropdown(numberDD);
      }
    }
  }

  addBBCode(stag, etag, clearselection, content) {
    const ed = tinyMCE.get('tinytarget'); let r; let
      rcopy;
    ed.focus();
    if (!content) {
      content = '';
    }
    const rng = ed.selection.getRng();
    const { startContainer } = rng;
    const { endContainer } = rng;
    const { startOffset } = rng;
    const { endOffset } = rng;

    let mode = '';
    if (startContainer.nodeName === '#text'
      && startContainer.parentNode.nodeName === 'P') {
      mode = 'text';
    }

    if (ed.selection.getContent() === '') {
      stag += content;
      const nc = stag + ed.selection.getContent() + etag;

      ed.selection.setContent(nc); // note: this changes the start/end container

      const rcopy = ed.selection.getRng().cloneRange();
      const r = ed.dom.createRng();
      ed.focus();
      if (mode === 'text') { // usual text node
        let targetElement = rcopy.startContainer;
        if (targetElement.nodeName === 'P') {
          targetElement = targetElement.firstChild;
        }
        r.setEnd(targetElement, stag.length + startOffset);
        r.setStart(targetElement, stag.length + startOffset);
        ed.selection.setRng(r);
      }
    } else if (clearselection) {
      ed.selection.setContent(stag + etag);
    } else {
      ed.selection.setContent(stag + ed.selection.getContent() + etag);
    }
    this.tinyWrapper.autoResize(ed);
  }

  cmdWikiLink() {
    this.addBBCode('[[', ']]');
  }

  cmdWikiLinkSelection(url) {
    const t = this;
    il.Wiki.Edit.openLinkDialog(url, this.getSelection(), (stag) => {
      t.addBBCode(stag, '', true);
    });
  }

  cmdTex() {
    this.addBBCode('[tex]', '[/tex]');
  }

  cmdFn() {
    this.addBBCode('[fn]', '[/fn]');
  }

  cmdKeyword() {
    this.addBBCode('[kw]', '[/kw]');
  }

  cmdExtLink() {
    this.addBBCode('[xln url="https://"]', '[/xln]');
  }

  cmdUserLink() {
    this.addBBCode(`[iln user="${this.uiModel.config.user}"/]`, '');
  }

  cmdAnc() {
    this.addBBCode('[anc name=""]', '[/anc]');
  }

  cmdBList() {
    this.tinyWrapper.bulletList();
  }

  listBulletStyle(format) {
    this.tinyWrapper.bulletListStyle(format);
  }

  listNumberStyle(format) {
    this.tinyWrapper.numberListStyle(format);
  }

  listItemStyle(format) {
    this.tinyWrapper.itemListStyle(format);
  }

  cmdNList() {
    this.tinyWrapper.numberedList();
  }

  cmdListIndent() {
    this.tinyWrapper.listIndent();
  }

  cmdListOutdent() {
    this.tinyWrapper.listOutdent();
  }

  setParagraphClass(i) {
    this.log('setParagraphClass');
    this.log(i);
    const fc = document.querySelector('.ilTinyParagraphClassSelector .dropdown button');
    const ddbtn = document.querySelector(
      `.ilTinyParagraphClassSelector button[data-copg-ed-par-class='${i}']`,
    );
    this.log(fc);
    if (fc) {
      this.log(`SETTin DROP DOWN BUTTON: ${i}`);
      fc.firstChild.textContent = `${ddbtn.textContent} `;
    }
    this.tinyWrapper.setParagraphClass(i);
  }

  /// /
  /// / Content modifier
  /// /

  /**
   * Get content to be sent per ajax to server.
   */
  getContentForSaving() {
    const ed = tinyMCE.get('tinytarget');
    const cl = ed.dom.getRoot().className;
    let c = ed.getContent();

    c = this.p2br(c);

    // add wrapping div with style class
    c = `<div id='${this.pc_id_str}' class='${cl}'>${c}</div>`;

    return c;
  }

  /// /
  /// / Table editing
  /// /

  editTD(id) {
    this.editParagraph(id, 'td', false);
  }

  editNextCell() {
    // check whether next cell exists
    const cdiv = this.current_td.split('_');
    let next = `cell_${cdiv[1]}_${parseInt(cdiv[2]) + 1}`;
    let nobj = document.getElementById(`div_${next}`);
    if (nobj == null) {
      next = `cell_${parseInt(cdiv[1]) + 1}_0`;
      nobj = document.getElementById(`div_${next}`);
    }
    if (nobj != null) {
      this.editParagraph(next, 'td', false);
    }
  }

  editPreviousCell() {
    // check whether next cell exists
    let prev = '';
    const cdiv = this.current_td.split('_');
    if (parseInt(cdiv[2]) > 0) {
      prev = `cell_${cdiv[1]}_${parseInt(cdiv[2]) - 1}`;
      const pobj = document.getElementById(`div_${prev}`);
    } else if (parseInt(cdiv[1]) > 0) {
      let p = `cell_${parseInt(cdiv[1]) - 1}_0`;
      let o = document.getElementById(`div_${p}`);
      let i = 0;
      while (o != null) {
        pobj = o;
        prev = p;
        p = `cell_${parseInt(cdiv[1]) - 1}_${i}`;
        o = document.getElementById(`div_${p}`);
        i++;
      }
    }
    if (prev !== '') {
      var pobj = document.getElementById(`div_${prev}`);
      if (pobj != null) {
        this.editParagraph(prev, 'td', false);
      }
    }
  }

  handleDataTableCommand(type, command) {
    // let pars = this.tds;
    pars.tab_cmd_type = type;
    pars.tab_cmd = command;
    pars.tab_cmd_id = current_row_col;
    this.sendCmdRequest(
      'saveDataTable',
      this.ed_para,
      null,
      pars,
      false,
      null,
      null,
    );
  }

  // we got the content for editing per ajax
  loadCurrentParagraphIntoTiny(switched) {
    const pcId = this.page_model.getCurrentPCId();
    const pc_model = this.page_model.getPCModel(pcId);
    this.pc_id_str = pcId;
    this.tinyWrapper.setContent(pc_model.text);
    this.setParagraphClass(pc_model.characteristic);
  }

  /// /
  /// / Various stuff, needs to be reorganised
  /// /

  renderQuestions() {
    // get all spans
    obj = document.getElementsByTagName('div');

    // run through them
    for (let i = 0; i < obj.length; i++) {
      // find all questions
      if (/ilc_question_/.test(obj[i].className)) {
        let { id } = obj[i];
        if (id.substr(0, 9) == 'container') {
          // re-draw
          id = id.substr(9);
          eval(`renderILQuestion${id}()`);
        }
      }
    }
  }

  extractHierId(id) {
    const i = id.indexOf(':');
    if (i > 0) {
      id = id.substr(0, i);
    }

    return id;
  }

  insertParagraph(pcid, after_pcid, content = '', characteristic = 'Standard') {
    this.log('paragraph-ui.insertParagraph');
    this.pageModifier.insertComponentAfter(after_pcid, pcid, 'Paragraph', content, 'Paragraph');
    const content_el = document.querySelector(`[data-copg-ed-type='pc-area'][data-pcid='${pcid}']`);
    this.showToolbar(true, true);
    this.tinyWrapper.initInsert(content_el, () => {
      this.tinyWrapper.setContent(content, characteristic);
      this.setParagraphClass(characteristic);
      this.setSectionClassSelector(this.getSectionClass(pcid));
    }, () => {
      this.autoSave.handleAutoSaveKeyPressed();
    }, () => {
      this.switchToPrevious();
    }, () => {
      this.switchToNext();
    });
  }

  performAutoSplit(pcid, text, characteristic, newParagraphs) {
    let afterPcid = pcid;
    this.tinyWrapper.setContent(text, characteristic);
    for (let k = 0; k < newParagraphs.length; k++) {
      this.tinyWrapper.stopEditing();
      this.insertParagraph(newParagraphs[k].pcid, afterPcid, newParagraphs[k].model.text, 'Standard');
      afterPcid = newParagraphs[k].pcid;
    }
  }

  handleSaveOnInsert() {
    this.tinyWrapper.stopEditing();
  }

  handleSaveOnEdit() {
    this.handleSaveOnInsert();
  }

  getSwitchParameters() {
    return {
      text: this.tinyWrapper.getText(),
      characteristic: this.tinyWrapper.getCharacteristic(),
    };
  }

  switchToPrevious() {
    console.log('paragraph-ui switchToPrevious');
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    const { page_model } = this;
    const cpcid = page_model.getCurrentPCId();
    let found = false;
    let previousPcid = null;
    let previousHierId = null;
    document.querySelectorAll("[data-copg-ed-type='pc-area']").forEach((el) => {
      const { pcid } = el.dataset;
      const { hierid } = el.dataset;
      const { cname } = el.dataset;
      if (cname === 'Paragraph') {
        const pcModel = page_model.getPCModel(pcid);
        if (pcModel.characteristic !== 'Code') {
          if (!found && cpcid === pcid) {
            found = true;
          }
          if (!found) {
            previousPcid = pcid;
            previousHierId = hierid;
          }
        }
      }
    });
    if (previousPcid) {
      dispatch.dispatch(action.page().editor().componentSwitch(
        'Paragraph',
        this.page_model.getComponentState(),
        this.page_model.getCurrentPCId(),
        this.getSwitchParameters(),
        previousPcid,
        previousHierId,
        true,
      ));
    }
  }

  switchToNext() {
    this.log('paragraph-ui switchToNext');
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    const { page_model } = this;
    const cpcid = page_model.getCurrentPCId();
    let found = false;
    let nextPcid = null;
    let nextHierId = null;
    document.querySelectorAll("[data-copg-ed-type='pc-area']").forEach((el) => {
      const { pcid } = el.dataset;
      const { hierid } = el.dataset;
      const { cname } = el.dataset;
      if (cname === 'Paragraph') {
        const pcModel = page_model.getPCModel(pcid);
        if (pcModel.characteristic !== 'Code') {
          if (found && !nextPcid) {
            nextPcid = pcid;
            nextHierId = hierid;
          }
          if (!found && cpcid === pcid) {
            found = true;
          }
        }
      }
    });
    if (nextPcid) {
      dispatch.dispatch(action.page().editor().componentSwitch(
        'Paragraph',
        this.page_model.getComponentState(),
        this.page_model.getCurrentPCId(),
        this.getSwitchParameters(),
        nextPcid,
        nextHierId,
      ));
    }
  }

  escape() {
    const b = document.querySelector("[data-copg-ed-action='save.return']");
    b.focus();
  }

  initWrapperCallbacks() {
    const wrapper = this.tinyWrapper;
    const parUI = this;
    const pageModel = parUI.page_model;

    wrapper.addCallback(TINY_CB.SWITCH_LEFT, () => {
      if (pageModel.getCurrentPCName() === 'Paragraph') {
        parUI.switchToPrevious();
      }
    });
    wrapper.addCallback(TINY_CB.SWITCH_UP, () => {
      if (pageModel.getCurrentPCName() === 'Paragraph') {
        parUI.switchToPrevious();
      }
    });
    wrapper.addCallback(TINY_CB.SWITCH_RIGHT, () => {
      if (pageModel.getCurrentPCName() === 'Paragraph') {
        parUI.switchToNext();
      }
    });
    wrapper.addCallback(TINY_CB.ESCAPE, () => {
      if (pageModel.getCurrentPCName() === "Paragraph") {
        parUI.escape();
      }
    });
    wrapper.addCallback(TINY_CB.SWITCH_DOWN, () => {
      if (pageModel.getCurrentPCName() === 'Paragraph') {
        parUI.switchToNext();
      }
    });
    wrapper.addCallback(TINY_CB.KEY_UP, () => {
      if (pageModel.getCurrentPCName() === 'Paragraph') {
        parUI.autoSave.handleAutoSaveKeyPressed();
      }
    });
    wrapper.addCallback(TINY_CB.NODE_CHANGE, () => {
      if (pageModel.getCurrentPCName() === 'Paragraph') {
        parUI.updateListStyleDropdowns();
      }
    });
    wrapper.addCallback(TINY_CB.AFTER_INIT, () => {
      if (pageModel.getCurrentPCName() === 'Paragraph') {
        let pcId;
        pcId = pageModel.getCurrentPCId();
        const pcModel = pageModel.getPCModel(pcId);
        if (pcModel) {
          wrapper.initContent(pcModel.text, pcModel.characteristic);
        }
        parUI.showToolbar(true, true);
        if (pcModel) {
          parUI.setParagraphClass(pcModel.characteristic);
          parUI.setSectionClassSelector(parUI.getSectionClass(pcId));
        }
        if (parUI.switchToEnd) {
          wrapper.switchToEnd();
        }
      }

      const ed = wrapper.tiny;
      ed.shortcuts.add('meta+b', '', () => { parUI.cmdSpan('Strong'); });
      ed.shortcuts.add('meta+u', '', () => { parUI.cmdSpan('Important'); });
      ed.shortcuts.add('meta+i', '', () => { parUI.cmdSpan('Emph'); });
      wrapper.checkSplitOnReturn();
    });
  }

  editParagraph(pcId, switchToEnd) {
    this.autoSave.resetAutoSave();
    this.log('paragraph-ui.editParagraph');
    const content_el = document.querySelector(`[data-copg-ed-type='pc-area'][data-pcid='${pcId}']`);
    const pc_model = this.page_model.getPCModel(pcId);
    const wrapper = this.tinyWrapper;
    this.switchToEnd = switchToEnd;
    wrapper.initEdit(content_el, pc_model.text, pc_model.characteristic);
  }

  eventT(ed) {
    // window vs document
    //	console.log(window);
    //	console.log(tinymce.dom.Event);
    tinymce.dom.Event.add(
      tinymce.dom.doc,
      'mousedown',
      () => { console.log('mouse down'); },
      false,
    );
  }

  ilEditMultiAction(cmd) {
    if (cmd === 'selectAll') {
      let divs = $('div.il_editarea');
      if (divs.length > 0) {
        for (var i = 0; i < divs.length; i++) {
          sel_edit_areas[divs[i].id] = true;
          divs[i].className = 'il_editarea_selected';
        }
      } else {
        divs = $('div.il_editarea_selected');
        for (var i = 0; i < divs.length; i++) {
          sel_edit_areas[divs[i].id] = false;
          divs[i].className = 'il_editarea';
        }
      }

      return false;
    }

    const hid_exec = document.getElementById('cmform_exec');
    hid_exec.name = `cmd[${cmd}]`;
    const hid_cmd = document.getElementById('cmform_cmd');
    hid_cmd.name = cmd;
    form = document.getElementById('cmform');

    let sel_ids = '';
    let delim = '';
    for (const key in sel_edit_areas) {
      if (sel_edit_areas[key]) {
        sel_ids = sel_ids + delim + key.substr(7);
        delim = ';';
      }
    }

    const hid_target = document.getElementById('cmform_target');
    hid_target.value = sel_ids;

    form.submit();

    return false;
  }

  //
  // js paragraph editing
  //

  // copied from TinyMCE editor_template_src.js
  showToolbar(showParagraphClass, showSectionFormat) {
    let obj;
    const tiny = this.tinyWrapper;
    const dispatch = this.dispatcher;
    const action = this.actionFactory;
    const ef = action.paragraph().editor();
    const tblact = action.table().editor();
    const paragraphUI = this;
    const ifrm = document.getElementById('tinytarget_ifr');
    ifrm.title = il.Language.txt("copg_edit_iframe_title");

    // #0017152
    $('#tinytarget_ifr').contents().find('html').attr('lang', $('html').attr('lang'));
    $('#tinytarget_ifr').contents().find('html').attr('dir', $('html').attr('dir'));

    //    $("#tinytarget_ifr").parent().css("border-width", "0px");
    //    $("#tinytarget_ifr").parent().parent().parent().css("border-width", "0px");

    this.toolSlate.setContentFromComponent('Paragraph', 'menu');
    console.log('*** SHOW TOOLBAR');

    const map = {};
    map[ACTIONS.SELECTION_REMOVE_FORMAT] = () => ef.selectionRemoveFormat();
    map[ACTIONS.SELECTION_KEYWORD] = () => ef.selectionKeyword();
    map[ACTIONS.SELECTION_TEX] = () => ef.selectionTex();
    map[ACTIONS.SELECTION_FN] = () => ef.selectionFn();
    map[ACTIONS.SELECTION_ANCHOR] = () => ef.selectionAnchor();
    map[ACTIONS.LIST_NUMBER] = () => ef.listNumber();
    map[ACTIONS.LIST_BULLET] = () => ef.listBullet();
    map[ACTIONS.LIST_OUTDENT] = () => ef.listOutdent();
    map[ACTIONS.LIST_INDENT] = () => ef.listIndent();
    map[ACTIONS.LINK_WIKI] = () => ef.linkWiki();
    map[ACTIONS.LINK_INTERNAL] = () => ef.linkInternal();
    map[ACTIONS.LINK_EXTERNAL] = () => ef.linkExternal();
    map[ACTIONS.LINK_USER] = () => ef.linkUser();

    document.querySelectorAll("[data-copg-ed-type='par-action']").forEach((char_button) => {
      const actionType = char_button.dataset.copgEdAction;
      let format;
      switch (actionType) {
        case ACTIONS.SELECTION_FORMAT:
          format = char_button.dataset.copgEdParFormat;
          char_button.addEventListener('click', (event) => {
            dispatch.dispatch(action.paragraph().editor().selectionFormat(format));
          });
          break;

        case ACTIONS.LIST_BULLET_STYLE:
          format = char_button.dataset.copgEdParFormat;
          char_button.addEventListener('click', (event) => {
            dispatch.dispatch(action.paragraph().editor().listBulletStyle(format));
          });
          break;

        case ACTIONS.LIST_NUMBER_STYLE:
          format = char_button.dataset.copgEdParFormat;
          char_button.addEventListener('click', (event) => {
            dispatch.dispatch(action.paragraph().editor().listNumberStyle(format));
          });
          break;

        case ACTIONS.LIST_ITEM_STYLE:
          format = char_button.dataset.copgEdParFormat;
          char_button.addEventListener('click', (event) => {
            dispatch.dispatch(action.paragraph().editor().listItemStyle(format));
          });
          break;

        case ACTIONS.PARAGRAPH_CLASS:
          const par_class = char_button.dataset.copgEdParClass;
          char_button.addEventListener('click', (event) => {
            dispatch.dispatch(action.paragraph().editor().paragraphClass(par_class));
          });
          break;

        case ACTIONS.SECTION_CLASS:
          const sec_class = char_button.dataset.copgEdParClass;
          char_button.addEventListener('click', (event) => {
            dispatch.dispatch(action.paragraph().editor().sectionClass(
              this.tinyWrapper.getText(),
              this.tinyWrapper.getCharacteristic(),
              this.getSectionClass(this.page_model.getCurrentPCId()),
              sec_class,
            ));
          });
          break;

        case ACTIONS.SAVE_RETURN:
          char_button.addEventListener('click', (event) => {
            if (!paragraphUI.getDataTableMode()) {
              dispatch.dispatch(ef.saveReturn(tiny.getText(), tiny.getCharacteristic()));
            } else {
              dispatch.dispatch(tblact.saveReturn(tiny.getText()));
            }
          });
          break;

        case PAGE_ACTIONS.COMPONENT_CANCEL:
          char_button.addEventListener('click', (event) => {
            if (!paragraphUI.getDataTableMode()) {
              dispatch.dispatch(action.page().editor().componentCancel());
            } else {
              dispatch.dispatch(tblact.cancelCellEdit());
            }
          });
          break;

        case ACTIONS.LINK_WIKI_SELECTION:
          const url = char_button.dataset.copgEdParUrl;
          char_button.addEventListener('click', (event) => {
            dispatch.dispatch(ef.linkWikiSelection(url));
          });
          break;

        default:
          char_button.addEventListener('click', (event) => {
            dispatch.dispatch(map[actionType]());
          });
          break;
      }
    });

    if (!showParagraphClass) {
      document.querySelector('.ilTinyParagraphClassSelector').remove();
    }
    if (!showSectionFormat) {
      document.querySelector('.ilSectionClassSelector').remove();
    }
  }

  removeToolbar() {
    // console.log("removing toolbar");
    if (this.menu_panel) {
      let obj = document.getElementById('iltinymenu');
      $(obj).remove();
      $('#copg-editor-help').css('display', '');
      $('.il_droparea').css('visibility', '');

      this.menu_panel = null;

      // this element exists, if internal link panel has been clicked
      obj = document.getElementById('ilEditorPanel_c');
      if (obj && obj.parentNode) {
        $(obj.parentNode).remove();
      }

      // this element still exists, if interna link panel has not been clicked
      obj = document.getElementById('ilEditorPanel');
      if (obj && obj.parentNode) {
        $(obj.parentNode).remove();
      }
    }
  }

  autoSaveStarted() {
    document.querySelector("[data-copg-ed-action='save.return']").disabled = true;
    // document.querySelector("[data-copg-ed-action='component.cancel']").disabled = true;
    this.autoSave.displayAutoSave(il.Language.txt('cont_saving'));
  }

  autoSaveEnded() {
    document.querySelector("[data-copg-ed-action='save.return']").disabled = false;
    // document.querySelector("[data-copg-ed-action='component.cancel']").disabled = false;
    this.autoSave.displayAutoSave('&nbsp;');
  }

  replaceRenderedParagraph(pcid, content) {
    const pcarea = document.querySelector(`[data-copg-ed-type='pc-area'][data-pcid='${pcid}']`);
    const children = Array.from(pcarea.childNodes);
    let cnt = 0;

    // we remove all children except the first one which is the EditLabel div
    children.forEach((item) => {
      if (cnt > 0) {
        item.remove();
      }
      cnt++;
    });

    pcarea.innerHTML += content;

    // replacing the content may move the editing area, so
    // we need to synch the tiny position
    this.syncTiny();
  }

  // ugly but it does not work right away for whatever reason,
  // e.g. in replaceRenderedParagraph, direct after inserting the new content
  syncTiny() {
    const w = this.tinyWrapper;
    window.setTimeout(() => {
      w.synchInputRegion();
    }, 100);
    window.setTimeout(() => {
      w.synchInputRegion();
    }, 300);
  }

  showLastUpdate(last_update) {
    this.autoSave.displayAutoSave(`${il.Language.txt('cont_last_update')}: ${last_update}`);
  }

  setSectionClass(pcid, characteristic) {
    const currentPar = document.querySelector(`[data-copg-ed-type='pc-area'][data-pcid='${pcid}']`);
    const parentComp = currentPar.parentNode.closest("[data-copg-ed-type='pc-area']");
    if (parentComp && parentComp.dataset.cname === 'Section' && characteristic !== '') {
      const contentDiv = parentComp.querySelector('div.ilCOPageSection,a.ilCOPageSection');
      contentDiv.className = `ilc_section_${characteristic} ilCOPageSection`;
    }
    this.setSectionClassSelector(characteristic);
    this.tinyWrapper.synchInputRegion();
  }

  /**
   * Get outer section class for paragraph
   * @param {string} pcid paragraph pcid
   */
  getSectionClass(pcid) {
    let secClass = '';
    const currentPar = document.querySelector(`[data-copg-ed-type='pc-area'][data-pcid='${pcid}']`);
    const parentComp = currentPar.parentNode.closest("[data-copg-ed-type='pc-area']");
    if (parentComp && parentComp.dataset.cname === 'Section') {
      const contentDiv = parentComp.querySelector('div.ilCOPageSection,a.ilCOPageSection');
      contentDiv.classList.forEach((c) => {
        if (c.substr(0, 12) === 'ilc_section_') {
          secClass = c.substr(12);
        }
      });
    }
    return secClass;
  }

  setSectionClassSelector(i) {
    if (i === '') {
      i = il.Language.txt('cont_no_block');
    } else {
      const dropdownEl = document.querySelector(`[data-copg-ed-par-class='${i}'] div.ilc_section_${i}`);
      if (dropdownEl) {
        i = document.querySelector(`[data-copg-ed-par-class='${i}'] div.ilc_section_${i}`).innerHTML;
      } else {
        i = '';
      }
    }
    const fc = document.querySelector('.ilSectionClassSelector .dropdown button');
    if (fc) {
      fc.firstChild.textContent = `${i} `;
    }
  }

  checkMerge(tiny, previous) {
    // check if previous page content element is a paragraph
    const { dispatcher } = this;
    const { actionFactory } = this;
    const pcid = this.page_model.getCurrentPCId();
    let firstParagraph; let firstParagraphPcid; let firstParent; let
      firstAddArea;
    let secondParagraph; let secondParagraphPcid; let
      secondParent;

    let el1 = document.createElement('div');
    let el2 = document.createElement('div');

    if (previous) { // merge current tiny with previous paragraph (backspace)
      secondParagraph = document.querySelector(`[data-copg-ed-type='pc-area'][data-pcid='${pcid}']`);
      secondParagraphPcid = pcid;
      firstAddArea = secondParagraph.parentNode.previousSibling;

      // we have an add-area in between...
      if (firstAddArea && firstAddArea.dataset.copgEdType === 'add-area') {
        firstParent = firstAddArea.previousSibling;
        if (firstParent && firstParent.firstChild && firstParent.firstChild.dataset.copgEdType === 'pc-area'
          && firstParent.firstChild.dataset.cname === 'Paragraph') {
          firstParagraph = firstParent.firstChild;
          firstParagraphPcid = firstParagraph.dataset.pcid;
          const firstModel = this.page_model.getPCModel(firstParagraphPcid);
          el1.innerHTML = firstModel.text;
          el2.innerHTML = tiny.getText();
        }
      }
    } else { // merge current tiny with next paragraph (delete)
      firstParagraph = document.querySelector(`[data-copg-ed-type='pc-area'][data-pcid='${pcid}']`);
      firstParagraphPcid = pcid;
      firstAddArea = firstParagraph.parentNode.nextSibling;
      if (firstAddArea && firstAddArea.dataset.copgEdType === 'add-area') {
        secondParent = firstAddArea.nextSibling;
        if (secondParent && secondParent.firstChild && secondParent.firstChild.dataset.copgEdType === 'pc-area'
          && secondParent.firstChild.dataset.cname === 'Paragraph') {
          secondParagraph = secondParent.firstChild;
          secondParagraphPcid = secondParagraph.dataset.pcid;
          const secondModel = this.page_model.getPCModel(secondParagraphPcid);
          el1.innerHTML = tiny.getText();
          el2.innerHTML = secondModel.text;
        }
      }
    }

    let newContent;
    if (firstParagraph && secondParagraph) {
      // first element is a list
      while (el1.querySelector('ul') && el1.querySelector('ul').lastChild.nodeName === 'UL') {
        el1 = el1.querySelector('ul').lastChild;
      }
      if (el1.querySelector('ul')) {
        // both are lists
        if (el2.querySelector('ul')) {
          const childs = Array.from(el2.querySelector('ul').children);
          const el1Ul = el1.querySelector('ul');
          childs.forEach((c) => {
            el1Ul.append(c);
          });
          newContent = el1.innerHTML;
        } else { // first is list, second is no list
          const { lastChild } = el1.querySelector('ul');
          if (lastChild.nodeName === 'LI') {
            lastChild.innerHTML += el2.innerHTML;
          }
          newContent = el1.innerHTML;
        }
      } else { // first element is not a list
        // second element is a list
        while (el2.querySelector('ul') && el2.querySelector('ul').firstChild.nodeName === 'UL') {
          el2 = el2.querySelector('ul').firstChild;
        }
        if (el2.querySelector('ul')) {
          const { firstChild } = el2.querySelector('ul');
          if (firstChild.nodeName === 'LI') {
            firstChild.innerHTML = el1.innerHTML + firstChild.innerHTML;
          }
          newContent = el2.innerHTML;
        } else { // first and second element are no lists
          newContent = el1.innerHTML + el2.innerHTML;
        }
      }

      this.pageModifier.removeInsertedComponent(secondParagraphPcid);
      dispatcher.dispatch(actionFactory.paragraph().editor().mergePrevious(
        secondParagraphPcid,
        newContent,
        firstParagraphPcid,
      ));
    }
  }

  performMergePrevious(pcid, previousPcid, newContent) {
    this.tinyWrapper.stopEditing();
    this.editParagraph(previousPcid, true);
  }

  showError(error) {
    this.pageModifier.displayError(error);
  }

  clearError() {
    this.pageModifier.clearError();
  }

  disableButtons() {
    document.querySelectorAll('#iltinymenu button').forEach((el) => {
      el.disabled = true;
    });
  }

  enableButtons() {
    document.querySelectorAll('#iltinymenu button').forEach((el) => {
      el.disabled = false;
    });
  }

  showLoader() {
    const tl = document.querySelector("[data-copg-ed-type='top-loader']");
    if (tl) {
      tl.style.display = '';
    }
  }

  hideLoader() {
    const tl = document.querySelector("[data-copg-ed-type='top-loader']");
    if (tl) {
      tl.style.display = 'none';
    }
  }

  disableEditing() {
    this.disableButtons();
    this.tinyWrapper.disable();
    this.showLoader();
  }

  enableEditing() {
    this.enableButtons();
    this.tinyWrapper.enable();
    this.hideLoader();
  }
}
