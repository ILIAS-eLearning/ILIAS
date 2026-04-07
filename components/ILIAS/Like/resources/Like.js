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
 *********************************************************************/

il = il || {};
il.Like = il.Like || {};
(function ($, il) {
  il.Like = (function ($) {
    const toggle = function (url, glyph_id, widget_id, exp_id) {
      const glyphEl = $(`#${glyph_id} .glyph`).first();
      const highlightTarget = glyphEl.length ? glyphEl : $(`#${glyph_id}`);
      let val;
      if (highlightTarget.hasClass('highlighted')) {
        highlightTarget.removeClass('highlighted');
        val = 0;
      } else {
        highlightTarget.addClass('highlighted');
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
