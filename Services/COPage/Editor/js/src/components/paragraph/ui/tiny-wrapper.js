/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

import HTMLTransform from "./html-transform.js";
import TinyDomTransform from "./tiny-dom-transform.js";
import CB from "./tiny-wrapper-cb-types.js";

/**
 * Wraps tiny
 */
export default class TinyWrapper {

  /**
   * @type {boolean}
   */
  //debug = false;

  /**
   * @type {Object}
   */
  //lib;

  /**
   * @type {string}
   */
  //id = "tinytarget";

  /**
   * @type {number}
   */
  //minwidth = 200;

  /**
   * @type {number}
   */
  //minheight = 20;

  /**
   * @type {Object}
   */
  //config = null;

  /**
   * @type {string}
   */
  //content_css;

  /**
   * @type {HTMLTransform}
   */
  //htmlTransform;

  /**
   * current range in down/right moving
   * @type {Object}
   */
  //downRightRng = null;

  /**
   * current range in up/left moving
   * @type {Object}
   */
  //upLeftRng = null;

  /**
   * New behaviour that splits paragraphs on return
   * @type {boolean}
   */
  //splitOnReturn = true;

  //splitOnReturnCallback = null;

  /**
   * @type {boolean}
   */
  //dataTableMode = false;

  /**
   * @type {Object}
   */
  /*
  text_formats = {
    Strong: { inline: 'span', classes: 'ilc_text_inline_Strong' },
    Emph: { inline: 'span', classes: 'ilc_text_inline_Emph' },
    Important: { inline: 'span', classes: 'ilc_text_inline_Important' },
    Comment: { inline: 'span', classes: 'ilc_text_inline_Comment' },
    Quotation: { inline: 'span', classes: 'ilc_text_inline_Quotation' },
    Accent: { inline: 'span', classes: 'ilc_text_inline_Accent' },
    Sup: { inline: 'sup', classes: 'ilc_sup_Sup' },
    Sub: { inline: 'sub', classes: 'ilc_sub_Sub' }
  };
  */

  /**
   */
  constructor() {
    this.debug = false;
    this.mergePrevious = false;
    this.mergeNextContent = "";
    this.gotoPrevious = false;
    this.forwardOffset = 0;

    this.id = "tinytarget";
    this.minwidth = 200;
    this.minheight = 20;
    this.config = null;
    this.content_css = "";
    this.downRightRng = null;
    this.upLeftRng = null;
    this.splitOnReturn = true;
    this.dataTableMode = false;

    this.text_formats = {
      Strong: { inline: 'span', classes: 'ilc_text_inline_Strong' },
      Emph: { inline: 'span', classes: 'ilc_text_inline_Emph' },
      Important: { inline: 'span', classes: 'ilc_text_inline_Important' },
      Comment: { inline: 'span', classes: 'ilc_text_inline_Comment' },
      Quotation: { inline: 'span', classes: 'ilc_text_inline_Quotation' },
      Accent: { inline: 'span', classes: 'ilc_text_inline_Accent' },
      Sup: { inline: 'sup', classes: 'ilc_sup_Sup' },
      Sub: { inline: 'sub', classes: 'ilc_sub_Sub' }
    };

    this.text_block_formats = {};

    this.cb = [];

    this.lib = tinyMCE;
    this.htmlTransform = new HTMLTransform();
  }

  /**
   *
   * @param {integer} type
   * @param {Function} cb
   */
  addCallback(type, cb) {
    if (typeof this.cb[type] !== 'object' || !Array.isArray(this.cb[type])) {
      this.cb[type] = [];
    }
    this.cb[type].push(cb);
  }

  /**
   *
   * @param {integer} type
   * @return {*[]|*}
   */
  getCallbacks(type) {
    if (typeof this.cb[type] === 'object' && Array.isArray(this.cb[type])) {
      return this.cb[type];
    }
    return [];
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

  setContentCss(content_css) {
    this.log("### tiny set content css:" + content_css);
    this.content_css = content_css;
  }

  /**
   * @param message
   */
  log(message) {
    if (this.debug) {
      console.log(message);
    }
  }


  getConfig() {
    this.log("*** getting config " + this.content_css);
    if (!this.config) {
      this.config = {
        /* part of 4 */
        toolbar: false,
        menubar: false,
        statusbar: false,
        language: "en",
        height: "100%",
        plugins: "save,paste,lists",
        smart_paste: false,
        save_onsavecallback: "saveParagraph",
        mode: "exact",
        elements: this.id,
        content_css: this.content_css,
        fix_list_elements: true,
        valid_elements: "p,br[_moz_dirty],span[class],code,sub[class],sup[class],ul[class],ol[class],li[class]",
        forced_root_block: 'p',
        entity_encoding: "raw",
        paste_remove_styles: true,
        formats: this.text_formats,
        /* not found in 4 code or docu (the configs for p/br are defaults for 3, so this should be ok) */
        removeformat_selector: 'span,code',
        remove_linebreaks: true,
        convert_newlines_to_brs: false,
        force_p_newlines: true,
        force_br_newlines: false,
        /* not found in 3 docu (anymore?) */
        cleanup_on_startup: true,
        cleanup: true,
        paste_auto_cleanup_on_paste: true,
        branding: false,
        paste_preprocess: (pl, o) => {
          this.pastePreProcess(pl, o);
        },
        paste_postprocess: (pl, o) => {
          this.pastePostProcess(pl, o);
        },
        setup: (tiny) => {
          this.setup(tiny);
        },
      };
    }
    return this.config;
  }

  addTextFormat(f) {
    this.text_formats[f] = { inline: 'span', classes: 'ilc_text_inline_' + f };
  }

  setTextBlockFormats(formats) {
    this.text_block_formats = formats;
  }

  pastePreProcess(pl, o) {
    const html = this.htmlTransform;

    // see #23696, since tinymce4 it seems not possible to disable link conversion (even if <a> tags are not valid elements)
    // so we paste http string "on our own" and reset the paste content
    /* ILIAS7: this does not seem to be necessary anymore with current tiny
    if (o.content.substring(0, 4) === "http") {
      par_ui.addBBCode(o.content, '', true);
      o.content = '';
    }*/

    if (o.wordContent) {
      o.content = html.removeLineFeeds(o.content);
    }
    o.content = html.removeAttributesFromTag("p", o.content);
    o.content = html.removeTag("div", o.content);
  }

  getTinyDomTransform() {
    return new TinyDomTransform(this.tiny);
  }

  pastePostProcess(pl, o) {
    const tiny = this.tiny;
    const tinyDom = this.getTinyDomTransform();

    // we must handle all valid elements here
    // p (handled in paste_preprocess)
    // br[_moz_dirty] (investigate)
    // span[class] (todo)
    // code (should be ok, since no attributes allowed)
    // ul[class],ol[class],li[class] handled here

    // add standard ilias list classes
    tinyDom.addListClasses(o.node);

    // replace all b, u, i tags by ilias spans
    tinyDom.replaceBoldUnderlineItalic(o.node);

    // remove all id attributes from the content
    tinyDom.removeIds(o.node);

    this.pasting = true;
  }

  // check if there is no following node we could move to
  // with down/right
  isLastNode(node) {
    while(node.parentNode) {
      if (node.nextSibling) {
        return false;
      }
      node = node.parentNode;
    }
    return true;
  }

  // check if there is no previous node we could move to
  // with left/up
  isFirstNode(node) {
    while(node.parentNode) {
      if (node.previousSibling) {
        return (node.previousSibling.nodeName === "HEAD");
      }
      node = node.parentNode;
    }
    return true;
  }

  setup(tiny) {
    this.log("tiny-wrapper.init.setup");
    this.tiny = tiny;
    const wrapper = this;

    // if this does not work this.tiny = this.lib.get(this.id); ??

    tiny.on('KeyUp', function (ev) {
      wrapper.autoResize();

      wrapper.getCallbacks(CB.KEY_UP).forEach((cb) => {
        cb();
      });

      const currentRng = tiny.selection.getRng();

      // down, right
      if ([39,40].includes(ev.keyCode)) {
        if (
          currentRng.collapsed &&
          currentRng.commonAncestorContainer.nodeName === "#text" &&
          wrapper.isLastNode(currentRng.commonAncestorContainer) &&
          currentRng.startOffset === currentRng.endOffset &&
          currentRng.startOffset === wrapper.forwardOffset    // means offset did not change = end
        ) {
          if (ev.keyCode === 39) {
            wrapper.getCallbacks(CB.SWITCH_RIGHT).forEach((cb) => {
              cb();
            });
          }
          if (ev.keyCode === 40) {
            wrapper.getCallbacks(CB.SWITCH_DOWN).forEach((cb) => {
              cb();
            });
          }
        }
      }

      // up, left
      if ([37,38].includes(ev.keyCode)) {
        if (wrapper.gotoPrevious) {
          if (ev.keyCode === 37) {
            wrapper.getCallbacks(CB.SWITCH_LEFT).forEach((cb) => {
              cb();
            });
          }
          if (ev.keyCode === 38) {
            wrapper.getCallbacks(CB.SWITCH_UP).forEach((cb) => {
              cb();
            });
          }
        }
      }

      // backspace (8) -> merge with previous
      if ([8].includes(ev.keyCode)) {
        if (wrapper.mergePrevious) {
          let dom = tiny.dom;
          // add split point
          let sp = dom.create("span", {class: 'split-point'}, " ");
          tiny.selection.setNode(sp);

          wrapper.getCallbacks(CB.MERGE).forEach((cb) => {
            cb(wrapper, true);
          });

          // select and remove splitpoint
          dom = tiny.dom;
          sp = tiny.dom.select('span.split-point');
          tiny.selection.select(sp[0]);
          dom.remove(sp[0]);

        }
      }

      // delete (46) -> merge with next
      if ([46].includes(ev.keyCode)) {
        if (currentRng.collapsed &&
          currentRng.startOffset === currentRng.endOffset &&
          wrapper.mergeNextContent === wrapper.getText()) {

          // add split point
          let dom = tiny.dom;
          let sp = dom.create("span", {class: 'split-point'}, " ");
          tiny.selection.setNode(sp);

          wrapper.getCallbacks(CB.MERGE).forEach((cb) => {
            cb(wrapper, false);
          });

          // select and remove splitpoint
          dom = tiny.dom;
          sp = tiny.dom.select('span.split-point');
          tiny.selection.select(sp[0]);
          dom.remove(sp[0]);
        }
      }

      wrapper.checkSplitOnReturn();
    });

    tiny.on('KeyDown', function (ev) {
      const currentRng = tiny.selection.getRng();

      if (ev.keyCode === 35 || ev.keyCode === 36) {
        const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
        if (!ev.shiftKey && isMac) {
          ev.preventDefault();
          ev.stopPropagation();
        }
      }

      // down, right
      if ([39,40].includes(ev.keyCode) && !ev.shiftKey) {
        if (
          currentRng.collapsed &&
          currentRng.startOffset === currentRng.endOffset &&
          currentRng.commonAncestorContainer.nodeName === "#text" &&
          wrapper.isLastNode(currentRng.commonAncestorContainer)
        ) {
          wrapper.forwardOffset = currentRng.startOffset;
        }
      }

      // up, left
      if ([37,38].includes(ev.keyCode) && !ev.shiftKey) {
        wrapper.gotoPrevious = (
          wrapper.isFirstNode(currentRng.commonAncestorContainer) &&
          currentRng.collapsed &&
          currentRng.startOffset === 0 &&
          currentRng.endOffset === 0
        );
      }

      // backspace (8)
      if ([8].includes(ev.keyCode)) {
        wrapper.mergePrevious = (
          wrapper.isFirstNode(currentRng.commonAncestorContainer) &&
          currentRng.collapsed &&
          currentRng.startOffset === 0 &&
          currentRng.endOffset === 0
        );

        if (wrapper.mergePrevious) {
          const dom = tiny.dom;
          if (dom.select('ol,ul')) {    // do not allow to outdent first list element
            ev.preventDefault();
            ev.stopPropagation();
            return false;
          }
        }
      }

      // delete (46)
      if ([46].includes(ev.keyCode)) {
        if (currentRng.collapsed &&
          currentRng.startOffset === currentRng.endOffset
        ) {
          wrapper.mergeNextContent = wrapper.getText();
        }
      }

      if (ev.keyCode === 9 && !ev.shiftKey) {
        ev.preventDefault();
        ev.stopPropagation();
        wrapper.getCallbacks(CB.TAB).forEach((cb) => {
          cb(wrapper, false);
        });
      }

      if (ev.keyCode === 9 && ev.shiftKey) {
        ev.preventDefault();
        ev.stopPropagation();
        wrapper.getCallbacks(CB.SHIFT_TAB).forEach((cb) => {
          cb(wrapper, false);
        });
      }
    });


    tiny.on('NodeChange', function (cm, n) {

      // clean content after paste (has this really an effect?)
      // (yes, it does, at least splitSpans is important here #13019)
      if (wrapper.pasting) {
        wrapper.pasting = false;
        wrapper.getTinyDomTransform().splitDivs();
        wrapper.getTinyDomTransform().fixListClasses(false);
        wrapper.getTinyDomTransform().splitSpans();
      }

      // update state of indent/outdent buttons
      const ibut = document.querySelector("[data-copg-ed-action='list.indent']");
      const obut = document.querySelector("[data-copg-ed-action='list.outdent']");
      if (ibut != null && obut != null) {
        if (tiny.queryCommandState('InsertUnorderedList') ||
          tiny.queryCommandState('InsertOrderedList')) {
          ibut.disabled = false;
          obut.disabled = false;
        } else {
          ibut.disabled = true;
          obut.disabled = true;
        }
      }
    });

    let width = wrapper.ghost_reg.width;
    let height = wrapper.ghost_reg.height;
    if (width < wrapper.minwidth) {
      width = wrapper.minwidth;
    }
    if (height < wrapper.minheight) {
      height = wrapper.minheight;
    }

    //ed.onInit.add(function(ed, evt)
    tiny.on('init', function (evt) {

      let ed = tiny;
      let mode = "insert";                                      // MISSING

      ed.formatter.register('mycode', {
        inline: 'code'
      });

      wrapper.log("tiny-wrapper.init.tiny-init");

      wrapper.setEditFrameSize(width, height);           // MISSING
      if (mode === 'edit') {
        pdiv.style.display = "none";

        var tinytarget = document.getElementById("tinytarget_div");
        ta_div.style.position = '';
        ta_div.style.left = '';

        ed.setProgressState(1); // Show progress
        //          par_ui.loadCurrentParagraphIntoTiny(switched);                        // MISSING
      }


      if (mode === 'insert') {
        wrapper.initContent("<p></p>", 'ilc_text_block_Standard');
      }
      /*
      if (mode == 'td')
      {
        //console.log("Setting content to: " + pdiv.innerHTML);           // MISSING
        ed.setContent(pdiv.innerHTML);
        this.splitBR();
        this.prepareTinyForEditing(false, false);
        this.synchInputRegion();
        this.focusTiny(true);
        //              cmd_called = false;
      }*/

      $('#tinytarget_ifr').contents().find("html").attr('lang', $('html').attr('lang'));
      $('#tinytarget_ifr').contents().find("html").attr('dir', $('html').attr('dir'));
      $('#tinytarget_ifr').contents().find("html").css("overflow", "auto");


      wrapper.getCallbacks(CB.AFTER_INIT).forEach((cb) => {
        cb();
      });
    });
  }

  initContent(content, characteristic) {
    this.log("tiny-wrapper.initContent");
    this.setContent(content);
    let ed = this.tiny;
    this.setParagraphClass(characteristic);
    this.synchInputRegion();
    this.focusTiny(true);
  }

  initEdit(content_element, text, characteristic) {
    const wrapper = this;
    this.log('tiny-wrapper.initEdit');

    this.setGhostAt(content_element);

    this.addCallback(CB.AFTER_INIT, () => {
      wrapper.autoScroll();
    });

    if (!this.tiny) {
      this.createTextAreaForTiny();
      this.lib.init(this.getConfig());
    } else {
      this.showAfter(content_element);
      wrapper.getCallbacks(CB.AFTER_INIT).forEach((cb) => {
        cb();
      });
      wrapper.autoScroll();
      this.clearUndo();
    }
  }

  // see e.g. #32336
  clearUndo() {
    if (this.tiny) {
      this.tiny.undoManager.clear();
    }
  }

  initInsert(content_element, after_init, after_keyup, previous, next) {
    const wrapper = this;
    this.log('tiny-wrapper.initInsert');

    this.setGhostAt(content_element);
    if (!this.tiny) {
      this.createTextAreaForTiny();
      this.lib.init(this.getConfig(() => {
        after_init();
        wrapper.autoScroll();
      }, after_keyup, previous, next));
    } else {
      this.showAfter(content_element);
      this.initContent("<p></p>", 'ilc_text_block_Standard');
      after_init();
      wrapper.autoScroll();
    }
  }

  hide() {
    const tdiv = document.getElementById("tinytarget_div");
    if (tdiv) {
      tdiv.style.display = "none";
    }
  }

  showAfter(content_element) {
    const tdiv = document.getElementById("tinytarget_div");
    tdiv.style.display = "";
  }

  /**
   * Note: we always add tiny at the end of the document, since it does not
   * like to be moved around, see
   * https://stackoverflow.com/questions/2535569/tinymce-editor-dislikes-being-moved-around
   */
  createTextAreaForTiny() {
    this.log("tiny-wrapper.createTextAraForTiny");

    let ta = document.createElement("textarea");
    let ta_div = document.createElement("div");

    const parent = document.getElementById("ilContentContainer");

    parent.appendChild(ta_div);
    ta_div.appendChild(ta);

    ta_div.id = 'tinytarget_div';
    ta.id = "tinytarget";
    ta.className = 'par_textarea';
  }


  setGhostAt(content_element) {
    this.log("tiny-wrapper.setGhostAt " + content_element);
    // get paragraph edit div
    this.ghost = content_element;
    this.ghost.classList.add("copg-ghost-wrapper");

    this.ghost_reg = YAHOO.util.Region.getRegion(this.ghost);
  }

  // copy input of tiny to ghost div in background
  copyInputToGhost(add_final_spacer) {
    this.log('tiny-wrapper.copyInputToGhost');
    let tag;
    let ed = this.tiny;
    let html = this.htmlTransform;

    if (this.ghost) {
      let cl = ed.dom.getRoot().className;
      let c = html.p2br(ed.getContent());

      cl = "copg-input-ghost " + cl;
      this.log(cl);
      const cl_arr = cl.split("_");
      const characteristic = cl_arr[cl_arr.length - 1];
      switch (characteristic) {
        case "Headline1":
          tag = "h1";
          break;
        case "Headline2":
          tag = "h2";
          break;
        case "Headline3":
          tag = "h3";
          break;
        default:
          tag = "div";
          break;
      }

      if (add_final_spacer) {
        c = c + "<br />.";
      }

      let label = "";
      let char_text = characteristic;
      if (!this.getDataTableMode()) {
        if (this.text_block_formats[characteristic]) {
          char_text = this.text_block_formats[characteristic];
        }
        label = "<div class='ilEditLabel'>" + il.Language.txt("cont_ed_par") +
          " (" + char_text + ")</div>";
      }

      c = label + "<" + tag + " style='position:static;' class='" + cl + "'>" + c + "</" + tag + ">";

      // we remove the first child div content div (edit label)
      this.ghost.querySelector("div").remove();             // edit label in case of paragraph, content div in case of td
      const div2 = this.ghost.querySelector("div, h1, h2, h3");     // content element in case of paragraph
      if (div2) {
        div2.remove();
      }

      this.log("replacing with: " + c);

      // we replace the second div (content) with c
      this.ghost.innerHTML = c;

    }
  }

  stopEditing() {
    this.copyInputToGhost(false);
    this.clearGhost();
    this.hide();
  }

  clearGhost() {
    this.log('tiny-wrapper.clearGhost');

    if (this.ghost) {
      this.ghost.classList.remove("copg-ghost-wrapper");
      this.ghost.style.overflow = "";
      this.ghost.style.height = "";
      const content = this.ghost.querySelector(".copg-input-ghost");
      content.classList.remove("copg-input-ghost");
      this.ghost = null;
    }
  }

  // synchs the size/position of the tiny to the space the ghost
  // object uses in the background
  synchInputRegion() {
    this.log('tiny-wrapper.synchInputRegion');

    let back_el, dummy;

    back_el = this.ghost;

    if (this.current_td) {              // MISSING
      back_el = back_el.parentNode;
    }

    this.log(back_el);

    if (!back_el) {
      return;
    }

    back_el.style.paddingLeft = "";
    back_el.style.paddingRight = "";

    let tdiv = document.getElementById("tinytarget_div");

    // make sure, background element does not go beyond page bottom
    back_el.style.display = '';
    back_el.style.overflow = 'auto';
    back_el.style.height = '';
    var back_reg = YAHOO.util.Region.getRegion(back_el);

    this.log("Ghost region: ");
    this.log(back_reg);

    var cl_reg = YAHOO.util.Dom.getClientRegion();
    if (back_reg.y + back_reg.height + 20 > cl_reg.top + cl_reg.height) {
      back_el.style.overflow = 'hidden';
      back_el.style.height = (cl_reg.top + cl_reg.height - back_reg.y - 20) + "px";
      back_reg = YAHOO.util.Region.getRegion(back_el);
    }

    YAHOO.util.Dom.setX(tdiv, back_reg.x);
    YAHOO.util.Dom.setY(tdiv, back_reg.y);
    this.setEditFrameSize(back_reg.width,
      back_reg.height);

    if (!this.current_td) {
//      this.autoScroll();
    }

    // force redraw for webkit based browsers (ILIAS chrome bug #0010871)
    // http://stackoverflow.com/questions/3485365/how-can-i-force-webkit-to-redraw-repaint-to-propagate-style-changes
    // no feature detection here since we are fixing a webkit bug and IE does not like this patch (starts flickering
    // on "short" pages)
    /*
    let isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
    let isSafari = /Safari/.test(navigator.userAgent) && /Apple Computer/.test(navigator.vendor);
    if (isChrome || isSafari) {
      back_el.style.display='none';
      dummy = back_el.offsetHeight;
      back_el.style.display='';
    }*/
  }

  autoResize() {
    this.log('tiny-wrapper.autoResize');
    this.copyInputToGhost(true);
    this.synchInputRegion();
  }

  // scrolls position of editor under editor menu
  autoScroll() {
    const tiny_el = document.getElementById("tinytarget_div");
    const content_el = document.querySelector("main.il-layout-page-content");
    const tiny_rect = tiny_el.getBoundingClientRect();

    let scroll = false;

    // if end of tiny is not visible
    if (tiny_rect.bottom > (window.innerHeight || document.documentElement.clientHeight)) {
      scroll = true;
    }

    // if top is not at least 30px over bottom
    if (tiny_rect.top + 50 > (window.innerHeight || document.documentElement.clientHeight)) {
      scroll = true;
    }

    // if top of tiny is not visible
    if (content_el.scrollTop > tiny_el.offsetTop - 20) {
      scroll = true;
    }

    if (scroll) {
      content_el.scrollTop = tiny_el.offsetTop - 20;
      this.autoResize();
    }
  }

  removeTiny() {
    this.log('tiny-wrapper.removeTiny');
    tinyMCE.execCommand('mceRemoveEditor', false, 'tinytarget');
    let tt = document.getElementById("tinytarget");
    tt.style.display = 'none';
  }

  // set frame size of editor
  setEditFrameSize(width, height) {
    this.log('tiny-wrapper.setEditFrameSize');
    let tinyifr = document.getElementById("tinytarget_ifr");
    let tinytd = document.getElementById("tinytarget_tbl");
    tinyifr.style.width = width + "px";
    tinyifr.style.height = height + "px";

    $("#tinytarget_ifr").css("width", width + "px");
    $("#tinytarget_ifr").css("height", height + "px");
    $("#tinytarget_div").css("width", width + "px");
    $("#tinytarget_div").css("height", height + "px");

    this.ed_width = width;
    this.ed_height = height;
  }

  focusTiny(delayed) {
    this.log('tiny-wrapper.focusTiny');
    let timeout = 1;
    if (delayed) {
      timeout = 500;
    }

    setTimeout(function () {
      let ed = tinyMCE.get('tinytarget');
      if (ed) {
        let e = tinyMCE.DOM.get(ed.id + '_external');
        let r = ed.dom.getRoot();
        let fc = r.childNodes[0];
        if (r.className != null) {
          var st = r.className.substring(15);
        }

        ed.getWin().focus();
      }
    }, timeout);
  }


  setContent (text, characteristic) {
    const ed = this.tiny;
    ed.setContent(text);
    if (!this.splitOnReturn) {
      this.getTinyDomTransform().splitBR();
    }
    this.autoResize();
    this.setParagraphClass(characteristic);
    this.clearUndo();
  }

  getText() {
    let ed = this.tiny;
    let html = this.htmlTransform;
    let c = ed.getContent();
    c = html.p2br(c);         // this is kept event if we "split on return" to remove the outer <p> tag
    return c;
  }

  getCharacteristic() {
    let ed = this.tiny;
    let parts = ed.dom.getRoot().className.split("_");
    //console.log("---");
    return parts[parts.length - 1];
  }


  setParagraphClass(i) {
    let ed = tinyMCE.activeEditor;
    ed.focus();
    let snode = ed.dom.getRoot();

    //snode = snode.querySelector("p");

    if (snode) {
      //snode.className = "ilc_text_block_" + i['hid_val'];
      snode.className = "ilc_text_block_" + i;
      snode.style.position = 'static';
    }
    snode.parentNode.className = "il-no-tiny-bg";

    this.autoResize();
  }

  toggleFormat(t) {
    let ed = this.tiny;
    if (t === "Code") {
      t = "mycode";
    }
    ed.execCommand('mceToggleFormat', false, t);
    ed.focus();
    //ed.selection.collapse(false); // see #33963
    this.autoResize();
  }

  removeFormat() {
    let ed = this.tiny;
    ed.focus();
    ed.execCommand('RemoveFormat', false);
    this.autoResize(ed);
  }

  bulletList() {
    let ed = this.tiny;
    ed.focus();
    ed.execCommand('InsertUnorderedList', false);
    this.getTinyDomTransform().fixListClasses(true);
    this.autoResize(ed);
  }

  numberedList() {
    let ed = this.tiny;
    ed.focus();
    ed.execCommand('InsertOrderedList', false);
    this.getTinyDomTransform().fixListClasses(true);
    this.autoResize(ed);
  }

  listIndent() {
    let blockq = false, range, ed = this.tiny;

    this.log("listIndent");

    ed.focus();
    ed.execCommand('Indent');
    range = ed.selection.getRng(true);

    // if path contains blockquote, top level list has been indented -> undo, see bug #0016243
    let cnode = range.startContainer;
    while (cnode = cnode.parentNode) {
      if (cnode.nodeName === "BLOCKQUOTE") {
        blockq = true;
      }
    }
    if (blockq) {
      ed.execCommand('Undo', false);
    }

    //tinyMCE.execCommand('mceCleanup', false, 'tinytarget');
    this.getTinyDomTransform().fixListClasses(false);
    this.autoResize(ed);
  }

  listOutdent() {
    this.log("listOutdent");
    let ed = this.tiny;
    ed.focus();
    ed.execCommand('Outdent', false);
    this.getTinyDomTransform().fixListClasses(true);
    this.autoResize(ed);
  }

  checkSplitOnReturn() {
    const tiny = this.tiny;
    let contents = [];
    let html = this.htmlTransform;
    let children = tiny.dom.getRoot().childNodes;
    if (children.length > 1) {
      if (this.splitOnReturn) {
        let dummy = document.createElement("div");
        dummy.innerHTML = tiny.getContent().replace("\n", "");        // we are not using the dom directly, since getContent() removes tiny internal stuff
        children = dummy.childNodes;
        for (var k = 0; k < children.length; k++) {
          if (children[k].nodeName === "P") {   // paragraphs
            contents.push(html.p2br(children[k].innerHTML));
          } else if (children[k].nodeType === 3) {                 // text nodes (seems to be only \n)
//            contents.push(html.p2br(children[k].textContent));
          } else {
            contents.push(html.p2br(children[k].outerHTML));   // should be only lists
          }
        }
        this.getCallbacks(CB.SPLIT_ON_RETURN).forEach((cb) => {
          cb(this, contents);
        });
      }
    }
  }

  switchToEnd() {
    let ed = this.tiny;
    ed.selection.select(ed.getBody(), true);
    ed.selection.collapse(false);
  }

  disable() {
    const ed = this.tiny;
    ed.getBody().setAttribute('contenteditable', false);
  }

  enable() {
    const ed = this.tiny;
    ed.getBody().setAttribute('contenteditable', true);
  }
}