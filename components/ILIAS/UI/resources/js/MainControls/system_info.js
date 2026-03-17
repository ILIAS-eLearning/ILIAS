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
 */

il = il || {};
il.UI = il.UI || {};
il.UI.maincontrols = il.UI.maincontrols || {};

(function ($, maincontrols) {
  maincontrols.system_info = (function ($) {
    var calculating = false;
    var documentHandlersBound = false;

    var bindDocumentHandlers = function () {
      if (documentHandlersBound) return;
      documentHandlersBound = true;
      $(document).on('click', '.il-system-info-more', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const item = $(this).closest('.il-system-info');
        item.toggleClass('full');
        $(this).hide();
      });
      $(document).on('click', '.il-system-info-close', function (e) {
        e.preventDefault();
      });
    };

    /**
     * decide and init condensed/wide version
     */
    var init = function (id) {
      bindDocumentHandlers();
      const item = document.getElementById(id);
      if (!item) return;
      const $item = $(item);
      const more_button = $item.find('.il-system-info-more');
      more_button.find('button').attr('type', 'button');
      $item.find('.il-system-info-close button').attr('type', 'button');
      maybeShowMoreButton($item, more_button);
      $(window).resize(() => {
        if (!calculating) {
          maybeShowMoreButton($item, more_button);
        }
      });
    };

    const maybeShowMoreButton = function (item, more_button) {
      calculating = true;
      let content = item.find('.il-system-info-content');
      let item_height = item.prop('offsetHeight');
      let content_height = content.prop('offsetHeight');

      if (content_height > item_height) {
        more_button.show();
      } else {
        more_button.hide();
      }
      calculating = false;
    };

    var close = function (id) {
      let element = $('#' + id);
      let close_uri = decodeURI(element.data('closeUri'));
      $.ajax({
        async: false,
        type: 'GET',
        url: close_uri,
        success: function (data) {
          element.slideUp(500, function () {
            $(this).remove();
          });
        }
      });
    };

    return {
      init: init,
      close: close,
    }

  })($);
})($, il.UI.maincontrols);

