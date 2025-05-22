il = il || {};
il.Like = il.Like || {};
(function ($, il) {
  il.Like = (function ($) {
    const toggle = function (url, glyph_id, widget_id, exp_id) {
      let val;
      if ($(`#${glyph_id}`).hasClass('highlighted')) {
        $(`#${glyph_id}`).removeClass('highlighted');
        val = 0;
      } else {
        $(`#${glyph_id}`).addClass('highlighted');
        val = 1;
      }
      // il.Util.ajaxReplace(url + "&cmd=saveExpression&exp=" + exp_id + "&val=" + val + "&dom_id=" + widget_id, widget_id + "_ec");
      il.repository.core.fetchReplace(
        `${widget_id}_ec`,
        `${url}&cmd=saveExpression&exp=${exp_id}&val=${val}&dom_id=${widget_id}`,
      );
    };

    return {
      toggle,
    };
  }($));
}($, il));
