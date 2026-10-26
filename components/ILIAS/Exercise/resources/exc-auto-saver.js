(function () {
  class AutoSaver {
    container;

    form;

    url;

    textareaName;

    textareaId;

    textarea;

    intervall = 30000;

    currentlySaving = false;

    lastContent = '';

    lastSaving = 0;

    lastSaveSpan = null;

    sendForm() {
      const { form } = this;
      const { url } = this;
      return async (content) => {
        const fd = new FormData(form);

        // Force latest content under the expected field name:
        fd.set(this.textareaName, content);

        return fetch(url, {
          method: 'POST',
          body: fd,
          credentials: 'same-origin',
          headers: {
            // Hint for server; safe with FormData
            'X-Requested-With': 'XMLHttpRequest',
          },
        });
      };
    }

    getContent() {
      // TinyMCE present?
      const hasTiny = typeof window !== 'undefined'
        && window.tinymce
        && typeof window.tinymce.get === 'function';

      if (hasTiny) {
        const editor = window.tinymce.get(this.textareaId);
        if (editor) {
          const html = editor.getContent({ format: 'html' });
          // Keep underlying textarea in sync too (nice for other scripts)
          try {
            editor.save(); // copies content into the textarea
          } catch (e) {
            //
          }
          return html;
        }
      }
      // Fallback: plain textarea
      return this.textarea ? this.textarea.value : '';
    }

    async autoSave() {
      if (this.currentlySaving) return;

      const content = this.getContent();

      // unchanged since last successful save → skip
      if (content === this.lastContent) return;

      if (content === '') {
        return;
      }

      this.currentlySaving = true;
      try {
        const res = await this.sendForm()(content);

        if (!res.ok) {
          // keep lastSig unchanged so we try again later
          console.warn('Autosave failed:', res.status, res.statusText);
          return;
        }

        // success
        this.lastContent = content;
        this.lastSaving = Date.now();

        // optionally: show UI feedback
        // console.log("Autosaved at", new Date().toLocaleTimeString());
      } catch (err) {
        console.warn('Autosave error:', err);
      } finally {
        this.currentlySaving = false;
      }
    }

    updateTime() {
      if (!this.lastSaveSpan) return;
      if (!this.lastSaving) return;

      const diff = Math.max(0, Date.now() - Number(this.lastSaving));
      const mins = Math.floor(diff / 60000);
      let mess;

      if (mins !== 1) {
        mess = il.Language.txt('exc_auto_saved_minutes_p');
      } else {
        mess = il.Language.txt('exc_auto_saved_minutes_s');
      }
      mess = mess.replace('%s', mins);

      this.lastSaveSpan.textContent = ` ${mess}`;
    }

    init() {
      this.container = document.getElementById('exc_text');
      if (!this.container) return;
      this.form = this.container.querySelector('form');
      this.url = this.container.dataset.autosaveUrl;
      this.textarea = this.form.querySelector('textarea');

      if (!this.url || !this.form || !this.textarea) return;

      this.textareaName = this.textarea.name;
      this.textareaId = this.textarea.id;
      this.lastContent = this.getContent();
      this.lastSaveSpan = this.container.querySelector('.exc-last-save');

      window.setInterval(() => {
        this.autoSave();
      }, this.intervall);
      window.setInterval(() => {
        this.updateTime();
      }, 1000);
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    const autoSaver = new AutoSaver();
    autoSaver.init();
  });
}());
