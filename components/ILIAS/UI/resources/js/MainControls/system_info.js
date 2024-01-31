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
      listener(id);
      $(window).resize(function () {
        if (!calculating) {
          listener(id);
        }
      });
    };

    var listener = function (id) {
      calculating = true;
      let item = $('#' + id);
      let content = item.find('.il-system-info-content');
      let item_height = item.prop('offsetHeight');
      let content_height = content.prop('offsetHeight');
      let more_button = item.find('.il-system-info-more');

      if (content_height > item_height) {
        more_button.show();
        more_button.click(function () {
          item.toggleClass('full');
          more_button.hide();
        });
      } else {
        more_button.hide();
        more_button.unbind();
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

