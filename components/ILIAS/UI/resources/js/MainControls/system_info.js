il = il || {};
il.UI = il.UI || {};
il.UI.maincontrols = il.UI.maincontrols || {};

(function ($, maincontrols) {
  maincontrols.system_info = (function ($) {
    var calculating = false;
    /**
     * decide and init condensed/wide version
     */
    var init = function (id) {
      const item = $(`#${id}`);
      const more_button = item.find('.il-system-info-more');
      more_button.click(() => {
        item.toggleClass('full');
        more_button.hide();
      });

      maybeShowMoreButton(item, more_button);
      $(window).resize(() => {
        if (!calculating) {
          maybeShowMoreButton(item);
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

