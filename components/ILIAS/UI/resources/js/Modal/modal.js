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
        const $container = $(`#${id}`);
        $container.load(options.ajaxRenderUrl, () => {
          document.querySelector(`#${id} > dialog`).showModal();
          triggeredSignalsStorage[signalData.id] = false;
        });
      } else {
        // $modal.modal(options);
        document.getElementById(id).showModal();
        triggeredSignalsStorage[signalData.id] = false;
      }
      initializedModalboxes[signalData.id] = id;
    };

    const closeModal = function (id) {
      document.getElementById(id).close();
    };

    /**
         * Replace the content of the modalbox showed by the given showSignal with the data returned by the URL
         * set in the signal options.
         *
         * @param id component ID
         * @param signalData Object containing all data from the replace signal
         */
    const replaceFromSignal = function (id, signalData) {
      const { url } = signalData.options;

      il.UI.core.replaceContent(id, url, 'component');
    };

    return {
      showModal,
      closeModal,
      replaceFromSignal,
    };
  }($));
}($, il.UI));
