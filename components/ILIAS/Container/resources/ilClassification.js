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

class Classification {
  initialised = false;

  ajaxBlockId = '';

  ajaxBlockUrl = '';

  ajaxContentId = '';

  ajaxContentUrl = '';

  tabsHtml = '';

  core = '';

  loader(elementId) {
    const loadergif = document.createElement('img');
    loadergif.src = './assets/images/media/loader.svg';
    loadergif.style.position = 'absolute';
    const el = document.getElementById(elementId);
    if (el) {
      this.core.setInnerHTML(el, loadergif.outerHTML);
    }
  }

  toggleReloadRender(html, args) {
    if (html !== '') {
      const st = document.getElementById('ilSubTab');
      if (st) {
        st.remove();
      }
      const t = document.getElementById('ilTab');
      if (t) {
        t.remove();
      }
      const tab = document.createElement('div');
      tab.id = 'ilTab';
      const tabsOuter = document.querySelector('#mainscrolldiv .ilTabsContentOuter');
      tabsOuter.parentNode.insertBefore(tab, tabsOuter);
      this.core.setOuterHTML(tab.id, this.tabsHtml);

      const el = document.getElementById(args.elId);
      this.core.setInnerHTML(el, html);
    } else {
      // reload parent container (object list)
      // eslint-disable-next-line no-restricted-globals,no-undef
      location.reload();
    }
  }

  toggleReload(html, args) {
    const el = document.getElementById(args.elId);
    this.core.setInnerHTML(el, html);
    this.init();
    this.core.fetchHtml(args.contentUrl, {}).then(
      (html2) => {
        this.toggleReloadRender(html2, { elId: args.contentId });
      },
    );
  }

  toggle(para) {
    this.loader(`${this.ajax_block_id}_loader`);
    this.loader(this.ajax_content_id);
    if (para.event) {
      para.event.preventDefault();
      para.event.stopPropagation();
      para.event = '';
    }
    const args = {
      elId: this.ajaxBlockId,
      contentUrl: this.ajaxContentUrl,
      contentId: this.ajaxContentId,
    };
    this.core.fetchHtml(this.ajaxBlockUrl, para).then(
      (html) => {
        this.toggleReload(html, args);
      },
    );
  }

  redraw() {
    const el = document.getElementById(this.ajaxBlockId);
    this.core.fetchReplaceInner(
      el,
      `${this.ajaxBlockUrl}&rdrw=1`,
    );
  }

  init() {
    document.querySelectorAll('.il-classification-block').forEach((bl) => {
      this.ajaxBlockId = bl.dataset.ajaxBlockId;
      this.ajaxBlockUrl = bl.dataset.ajaxBlockUrl;
      this.ajaxContentId = bl.dataset.ajaxContentId;
      this.ajaxContentUrl = bl.dataset.ajaxContentUrl;
      this.tabsHtml = JSON.parse(bl.dataset.tabsHtml);
    });
    document.querySelectorAll('.il-classification-block a').forEach((el) => {
      if (el.href) {
        const hashValue = new URL(el.href).hash.substring(1);
        if (hashValue) {
          el.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggle({ taxNode: hashValue.substring(9) });
          });
        }
      }
    });
    document.addEventListener('il_classification_redraw', () => {
      this.redraw();
    });
    this.core = il.repository.core;
    this.initialised = true;
    console.log('Classification initialised 2');
  }
}

window.addEventListener('load', () => {
  const c = new Classification();
  c.init();
}, false);
