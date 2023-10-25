il.Classification = {
  ajax_block_id: '',
  ajax_block_url: '',
  ajax_content_id: '',
  ajax_content_url: '',
  core: il.repository.core,

  setAjax(block_id, block_url, content_id, content_url, tabs_html) {
    this.ajax_block_id = block_id;
    this.ajax_block_url = block_url;
    this.ajax_content_id = content_id;
    this.ajax_content_url = content_url;
    this.tabs_html = tabs_html;

    document.addEventListener('il_classification_redraw', () => {
      this.redraw();
    });
  },
  toggle(para) {
    this.loader(`${this.ajax_block_id}_loader`);
    this.loader(this.ajax_content_id);
    if (para.event) {
      para.event.preventDefault();
      para.event.stopPropagation();
      para.event = '';
    }
    const args = { el_id: this.ajax_block_id, content_url: this.ajax_content_url, content_id: this.ajax_content_id };
    this.core.fetchHtml(this.ajax_block_url, para).then(
      (html) => {
        this.toggleReload(html, args);
      },
    );
  },

  toggleReload(html, args) {
    const el = document.getElementById(args.el_id);
    this.core.setInnerHTML(el, html);
    this.core.fetchHtml(args.content_url, {}).then(
      (html) => {
        il.Classification.toggleReloadRender(html, { el_id: args.content_id });
      },
    );
  },

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
      const tabs_outer = document.querySelector('#mainscrolldiv .ilTabsContentOuter');
      tabs_outer.parentNode.insertBefore(tab, tabs_outer);
      this.core.setOuterHTML(tab.id, il.Classification.tabs_html);

      const el = document.getElementById(args.el_id);
      this.core.setInnerHTML(el, html);
    } else {
      // reload parent container (object list)
      location.reload();
    }
  },
  redraw() {
    const el = document.getElementById(il.Classification.ajax_block_id);
    this.core.fetchReplaceInner(
      el,
      `${il.Classification.ajax_block_url}&rdrw=1`,
    );
  },

  loader(element_id) {
    const loadergif = document.createElement('img');
    loadergif.src = './templates/default/images/media/loader.svg';
    loadergif.style.position = 'absolute';
    const el = document.getElementById(element_id);
    if (el) {
      this.core.setInnerHTML(el, loadergif.outerHTML);
    }
  },

  returnToParent() {
    this.loader(`${this.ajax_block_id}_loader`);
    document.location.reload();
  },

};
