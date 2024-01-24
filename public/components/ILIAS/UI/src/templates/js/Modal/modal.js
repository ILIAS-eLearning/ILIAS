var il = il || {};
il.UI = il.UI || {};

(function ($, UI) {
  UI.modal = (function ($) {
    const triggeredSignalsStorage = [];
    const defaultShowOptions = {
      backdrop: true,
      keyboard: true,
      ajaxRenderUrl: '',
      trigger: 'click',
    };

    const initializedModalboxes = {};

    const showModal = function (id, options, signalData) {
      if (triggeredSignalsStorage[signalData.id] === true) {
        return;
      }
      triggeredSignalsStorage[signalData.id] = true;
      options = $.extend(defaultShowOptions, options);
      if (options.ajaxRenderUrl) {
        const container = document.getElementById(id);
        if (container === null) {
          throw new Error(`Could not find modal placeholder '${id}'.`);
        }

        il.UI.core.AsyncRenderer.attachElements(options.ajaxRenderUrl, (elements) => {
          container.append(...elements);
          const $modal = $(container).find('.modal');
          if ($modal.length) {
            $modal.modal(options);
          }
          triggeredSignalsStorage[signalData.id] = false;
          return container;
        });
      } else {
        const $modal = $(`#${id}`);
        $modal.modal(options);
        triggeredSignalsStorage[signalData.id] = false;
      }
      initializedModalboxes[signalData.id] = id;
    };

    const closeModal = function (id) {
      $(`#${id}`).modal('close');
    };

    /**
     * Replace the content of the modalbox showed by the given showSignal with the data returned
     * by the URL set in the signal options.
     *
     * @param id component ID
     * @param signalData Object containing all data from the replace signal
     */
    const replaceFromSignal = function (id, { options }) {
      const modalWrapper = document.getElementById(id);
      const modalDialog = modalWrapper?.querySelector('.modal-dialog');

      if (modalDialog === null) {
        throw new Error(`Could not find modal dialog for roundtrip '${id}'.`);
      }

      // content is either a modal which replaces the existing modal, or
      // different content, which only replaces the current dialog.
      il.UI.core.AsyncRenderer.attachElements(options.url, (elements) => {
        const [newElement, newScript] = elements;

        const newModalDialog = newElement.querySelector('.modal-dialog');
        if (newModalDialog !== null) {
          modalDialog.replaceWith(newModalDialog, newScript ?? '');
        } else {
          modalDialog
            .querySelector('.modal-body')
            ?.replaceChildren(...elements);
        }

        return modalWrapper;
      });
    };

    return {
      showModal,
      closeModal,
      replaceFromSignal,
    };
  }($));
}($, il.UI));
