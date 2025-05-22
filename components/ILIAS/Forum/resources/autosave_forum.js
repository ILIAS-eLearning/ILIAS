((root, scope, factory) => {
  scope.ForumDraftsAutosave = factory(root);
})(window, il, (root) => {
  const ForumDraftsAutosave = (() => {
    let autosaveIntervalHandle = null;
    let autosaveActive = true;

    const disableAutosave = () => {
      console.log('Pause autosave...');
      autosaveActive = false;
    };

    const enableAutosave = () => {
      console.log('Enabling autosave...');
      autosaveActive = true;
    };

    const createElement = (tag, attributes = {}, text = '') => {
      const element = document.createElement(tag);
      Object.entries(attributes).forEach(([key, value]) => {
        element.setAttribute(key, value);
      });
      if (text) element.textContent = text;
      return element;
    };

    const toggleSavingIndicator = (show) => {
      const savingIndicator = document.getElementById('ilsaving');
      if (savingIndicator) {
        savingIndicator.classList.toggle('ilNoDisplay', !show);
      }
    };

    const updateLoadingUI = (form, loadingImgSrc, isSaving) => new Promise((resolve) => {
      const submitButtons = form.querySelectorAll('input[type="submit"]');
      submitButtons.forEach((submit) => {
        submit.disabled = isSaving;
      });

      if (isSaving) {
        console.log('Locking UI...');
        form.querySelectorAll('.ilFormCmds').forEach((cmd) => {
          const img = createElement('img', {
            src: loadingImgSrc,
            class: 'ilFrmLoadingImg',
            style: 'padding-right: 10px;',
          });
          const submitBtn = cmd.querySelector('input[type="submit"]');
          cmd.insertBefore(img, submitBtn);
        });
      } else {
        console.log('Unlocking UI...');
        document.querySelectorAll('.ilFrmLoadingImg').forEach((img) => img.remove());
      }

      resolve();
    });

    const ensureElements = (form, settings) => new Promise((resolve) => {
      let savingIndicator = document.getElementById('ilsaving');
      if (!savingIndicator) {
        savingIndicator = createElement('div', {
          id: 'ilsaving',
          class: 'ilHighlighted ilNoDisplay',
          style: 'z-index: 10000;',
        }, il.Language.txt('saving'));
        document.body.appendChild(savingIndicator);
      }

      let draftIdField = form.querySelector('#draft_id');
      if (!draftIdField) {
        draftIdField = createElement('input', {
          type: 'hidden',
          id: 'draft_id',
          name: 'draft_id',
          value: settings.draftId,
        });
        form.appendChild(draftIdField);
      }

      resolve();
    });

    const fetchSaveDraft = (url, formData) => fetch(url, {
      method: 'POST',
      body: formData,
    }).then((response) => response.json());

    const saveDraft = (form, settings) => {
      const { url, loadingImgSrc } = settings;

      if (typeof tinyMCE !== 'undefined') {
        tinyMCE.triggerSave();
      }

      const subject = document.getElementById('subject').value;
      const message = document.getElementById('message').value;

      if (!autosaveActive || !subject || !message) {
        console.log('Skipping autosave...');
        return Promise.resolve(); // No save needed
      }

      const formData = new FormData(form);

      return updateLoadingUI(form, loadingImgSrc, true)
        .then(() => {
          toggleSavingIndicator(true);
          disableAutosave();
          return fetchSaveDraft(url, formData);
        })
        .then((result) => {
          console.log('Draft saved:', result);
          if (result.draft_id) {
            const draftIdField = form.querySelector('#draft_id');
            if (draftIdField) {
              draftIdField.value = result.draft_id;
            }
          }
        })
        .catch((error) => {
          console.error('Error saving draft:', error);
        })
        .finally(() => {
          toggleSavingIndicator(false);
          updateLoadingUI(form, loadingImgSrc, false);
          enableAutosave();
        });
    };

    const init = (options) => {
      const settings = {
        interval: 10000,
        url: '',
        loadingImgSrc: '',
        draftId: 0,
        selectors: { form: '' },
        ...options,
      };

      const form = document.querySelector(settings.selectors.form);
      if (!form) return;

      ensureElements(form, settings)
        .then(() => {
          console.log('Starting autosave interval...');
          autosaveIntervalHandle = setInterval(() => saveDraft(form, settings), settings.interval);

          form.addEventListener('submit', () => {
            console.log('Clearing autosave interval...');
            clearInterval(autosaveIntervalHandle);
          });
        });
    };

    return { init, disableAutosave, enableAutosave };
  })();

  return ForumDraftsAutosave;
});

il.Util.addOnLoad(() => {
  il.Util.addOnLoad(() => {
    const threadHistory = document.querySelectorAll('.found_threat_history_to_restore');
    if (threadHistory.length > 0) {
      const dialog = document.querySelector('[data-modal-id="frm_autosave_restore"]');
      if (dialog) {
        il.ForumDraftsAutosave.disableAutosave();

        const primaryButtons = dialog.querySelectorAll('.modal-footer .btn-primary');
        primaryButtons.forEach((button) => button.remove());

        if (dialog.showModal) {
          console.log('Show dialog...');
          dialog.showModal();
          dialog.addEventListener('close', () => {
            console.log('Closing dialog...');
            il.ForumDraftsAutosave.enableAutosave();
          });
        } else {
          console.error('Dialog API not supported.');
        }
      }
    }
  });
});
