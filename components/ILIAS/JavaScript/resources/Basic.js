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
 ******************************************************************** */

/*
   Please note that this file should only contain common Javascript code
   used on many ILIAS screens. Please do not add any code that is only useful
   for single components here.
   See http://www.ilias.de/docu/goto_docu_pg_38968_42.html for the JS guidelines
*/

// console dummy object
if (!window.console) {
  (function () {
    const names = ['log', 'debug', 'info', 'warn', 'error', 'assert', 'dir', 'dirxml', 'group', 'groupEnd', 'time', 'timeEnd', 'count', 'trace', 'profile', 'profileEnd'];
	    window.console = {};
	    for (let i = 0; i < names.length; ++i) window.console[names[i]] = function (data) {};
  }());
}

// global il namespace, additional objects usually should be added to this one
if (typeof il === 'undefined') {
  il = {};
}

// utility functions
il.Util = {

  addOnLoad(func) {
    $().ready(() => {
      try {
        func();
      } catch (err) {
        console.error(err);
      }
    });
    /*		if (!document.getElementById | !document.getElementsByTagName) return;

		var oldonload=window.onload;
		if (typeof window.onload != 'function')
		{
			window.onload = func;
		}
		else
		{
			window.onload = function()
			{
				oldonload();
				func();
			}
		} */
  },

  addOnUnload(func) {
    if (!document.getElementById | !document.getElementsByTagName) return;

    const oldonunload = window.onunload;
    if (typeof window.onunload !== 'function') {
      window.onunload = func;
    } else {
      window.onunload = function () {
        oldonunload();
        func();
      };
    }
  },

  /**
	 * Checks/unchecks checkboxes
	 *
	 * @param   string   parent name or id
	 * @param   string   the checkbox name (or the first characters of the name, if unique)
	 * @param   boolean  whether to check or to uncheck the element
	 * @return  boolean  always true
	 */
	 setChecked(parent_el, checkbox_name, do_check) {
	 	let name_sel = '';
	 	if (checkbox_name != '') {
	 		name_sel = `[name^="${checkbox_name}"]`;
	 	}
    if (do_check) {
      $(`#${parent_el}`).find(`input:checkbox${name_sel}`).not(':disabled').prop('checked', true);
      $(`[name="${parent_el}"]`).find(`input:checkbox${name_sel}`).not(':disabled').prop('checked', true);
    } else {
      $(`#${parent_el}`).find(`input:checkbox${name_sel}`).not(':disabled').prop('checked', false);
      $(`[name="${parent_el}"]`).find(`input:checkbox${name_sel}`).not(':disabled').prop('checked', false);
    }
	  return true;
  },

  submitOnEnter(ev, form) {
    if (typeof ev !== 'undefined' && typeof ev.keyCode !== 'undefined') {
      if (ev.keyCode == 13) {
        form.submit();
        return false;
      }
    }
    return true;
  },

  // ajax related functions

  ajaxReplace(url, el_id) {
    console.log(url);
    this.sendAjaxGetRequestToUrl(url, {}, { el_id, inner: false }, this.ajaxReplaceSuccess);
  },

  ajaxReplaceInner(url, el_id) {
    this.sendAjaxGetRequestToUrl(url, {}, { el_id, inner: true }, this.ajaxReplaceSuccess);
  },

  /**
	 * @param {string} url
	 * @param {string} data
	 * @param {string} el_id
	 */
  ajaxReplacePostRequestInner(url, data, el_id) {
    this.sendAsyncAjaxPostRequestToUrl(url, data, { el_id, inner: true }, this.ajaxReplaceSuccess);
  },

  ajaxReplaceSuccess(o) {
    // perform page modification
    if (o.responseText !== undefined) {
      if (o.argument.inner) {
        $(`#${o.argument.el_id}`).html(o.responseText);
      } else {
        $(`#${o.argument.el_id}`).replaceWith(o.responseText);
      }
      il.UICore.initDropDowns(`#${o.argument.el_id}`);
    }
  },

  sendAjaxGetRequestToUrl(url, par, args, succ_cb) {
    const cb =		{
		  success: succ_cb,
		  failure: this.handleAjaxFailure,
		  argument: args,
    };
    for (k in par) {
      url = `${url}&${k}=${par[k]}`;
    }
    const request = YAHOO.util.Connect.asyncRequest('GET', url, cb);
  },

  /**
	 *
	 * @param {string} url
	 * @param {string} data
	 * @param {array} args
	 * @param {callback} succ_cb
	 */
  sendAsyncAjaxPostRequestToUrl(url, data, args, succ_cb) {
    const cb = {
      success: succ_cb,
      failure: this.handleAjaxFailure,
      argument: args,
    };
    const request = YAHOO.util.Connect.asyncRequest('POST', url, cb, data);
  },

  sendAjaxPostRequestToUrl(url, data, succ_cb) {
    $.post(url, data, succ_cb);
  },

  // FailureHandler
  handleAjaxFailure(o) {
    console.log('il.Util.handleAjaxFailure: Ajax Error:');
    console.log(o);
  },

  /**
	 * Get region information (coordinates + size) for an element
	 */
  getRegion(el) {
    const w = $(el).outerWidth();
    const h = $(el).outerHeight();
    const o = $(el).offset();

    return {
      top: o.top, right: o.left + w, bottom: o.top + h, left: o.left, height: h, width: w, y: o.top, x: o.left,
    };
  },

  /**
	 * Get region information (coordinates + size) for viewport
	 */
  getViewportRegion() {
    const w = $(window).width();
    const h = $(window).height();
    const t = $(window).scrollTop();
    const l = $(window).scrollLeft();

    return {
      top: t, right: l + w, bottom: t + h, left: l, height: h, width: w, y: t, x: l,
    };
  },

  /**
	 * Fix position
	 */
  fixPosition(el) {
    let r = il.Util.getRegion(el);
    const vp = il.Util.getViewportRegion();
    // we only fix absolute positioned items
    if ($(el).css('position') != 'absolute') {
      return;
    }

    if (vp.right - 15 < r.right) {
      il.Util.setX(el, r.x - (r.right - vp.right + 20));
    }

    r = il.Util.getRegion(el);
    if (r.left < 0) {
      $(el).removeClass('pull-right');
      il.Util.setX(el, 0);
    }
  },

  /**
	 * Set x
	 */
  setX(el, x) {
    $(el).offset({ left: x });
  },

  setY(el, y) {
    $(el).offset({ top: y });
  },

  /**
	 * Checks whether coordinations are within an elements region
	 */
  coordsInElement(x, y, el) {
    const w = $(el).outerWidth();
    const h = $(el).outerHeight();
    const o = $(el).offset();
    if (x >= o.left && x <= o.left + w && y >= o.top && y <= o.top + h) {
      return true;
    }
    return false;
  },

  /**
	 * print current window, thanks to anoack for the mathjax fix (see bug #)
	 */
  print() {
    if (typeof (window.print) !== 'undefined') {
      if (typeof MathJax !== 'undefined' && typeof MathJax.Hub !== 'undefined') {
        MathJax.Hub.Queue(
          ['Delay', MathJax.Callback, 700],
          window.print,
        );
      } else {
        window.print();
      }
    }
  },

  // see http://stackoverflow.com/questions/1144783/replacing-all-occurrences-of-a-string-in-javascript
  escapeRegExp(string) {
    return string.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, '\\$1');
  },

  // see http://stackoverflow.com/questions/1144783/replacing-all-occurrences-of-a-string-in-javascript
  replaceAll(string, find, replace) {
    return string.replace(new RegExp(il.Util.escapeRegExp(find), 'g'), replace);
  },
};

// ILIAS Object related functions
il.Object = {
  url_redraw_ah: '',
  url_redraw_li: '',
  url_rating: '',

  setRedrawAHUrl(url) {
    this.url_redraw_ah = url;
  },

  getRedrawAHUrl() {
    return this.url_redraw_ah;
  },

  redrawActionHeader() {
    const ah = document.getElementById('il_head_action');
    if (this.url_redraw_ah && ah !== null) {
      il.Util.ajaxReplaceInner(this.url_redraw_ah, 'il_head_action');
    }
  },

  setRedrawListItemUrl(url) {
    this.url_redraw_li = url;
  },

  getRedrawListItemUrl() {
    return this.url_redraw_li;
  },

  redrawListItem(ref_id) {
    if (this.url_redraw_li) {
      const url = this.url_redraw_li;
      $(`div[id^=lg_div_${ref_id}_pref_]`).each(function () {
        const id = $(this).attr('id');
        const parent = id.split('_').pop();
        il.Util.ajaxReplace(`${url}&child_ref_id=${ref_id}&parent_ref_id=${parent}`, id);
      });
    }
  },

  togglePreconditions(link, id, txt_show, txt_hide) {
    let li = document.getElementById(`il_list_item_precondition_obl_${id}`);
    if (li !== null) {
      if (li.style.display == 'none') {
        li.style.display = '';
        $(link).html(`&raquo; ${txt_hide}`);
      } else {
        li.style.display = 'none';
        $(link).html(`&raquo; ${txt_show}`);
      }
    }
    li = document.getElementById(`il_list_item_precondition_opt_${id}`);
    if (li !== null) {
      if (li.style.display == 'none') {
        li.style.display = '';
        $(link).html(`&raquo; ${txt_hide}`);
      } else {
        li.style.display = 'none';
        $(link).html(`&raquo; ${txt_show}`);
      }
    }
  },

  setRatingUrl(url) {
    this.url_rating = url;
  },

  saveRating(mark) {
    il.Util.sendAjaxGetRequestToUrl(`${this.url_rating}&rating=${mark}`, {}, { url_redraw: this.url_redraw_ah }, this.redrawAfterRating);
  },

  redrawAfterRating(o) {
    const ah = document.getElementById('il_head_action');
    if (ah !== null) {
      il.Util.ajaxReplaceInner(o.argument.url_redraw, 'il_head_action');
      if (typeof WebuiPopovers !== 'undefined') {
        WebuiPopovers.hideAll();
      }
    }
  },

  saveRatingFromListGUI(ref_id, hash, mark) {
    il.Util.sendAjaxGetRequestToUrl(`${this.url_rating}&rating=${mark}&child_ref_id=${ref_id}&cadh= ${hash}`, {}, { url_redraw: this.url_redraw_li, ref_id }, this.redrawAfterRatingFromListGUI);
  },

  redrawAfterRatingFromListGUI(o) {
    $(`div[id^=lg_div_${o.argument.ref_id}_pref_]`).each(function () {
      const id = $(this).attr('id');
      const parent = id.split('_').pop();
      il.Util.ajaxReplace(`${o.argument.url_redraw}&child_ref_id=${o.argument.ref_id}&parent_ref_id=${parent}`, id);
    });
    if (typeof WebuiPopovers !== 'undefined') {
      WebuiPopovers.hideAll();
    }
  },
};

/* UICore */
il.UICore = {

  //
  // Layout related
  //

  right_panel_wrapper: '',

  is_page_visible: true,

  /**
	 *
	 * @param {boolean} status
	 */
  setPageVisibilityStatus(status) {
    il.UICore.is_page_visible = status;
  },

  /**
	 *
	 * @returns {boolean}
	 */
  isPageVisible() {
    return il.UICore.is_page_visible;
  },

  scrollToHash() {
    let h = self.location.hash;
    if (h != '') {
      h = h.substr(1);
      if ($(`a[name='${h}']`).length !== 0) {
        il.UICore.scrollToElement(`a[name='${h}']`);
      } else if ($(`#${h}`).length !== 0) {
        il.UICore.scrollToElement(`#${h}`);
      }
    }
  },

  // take care of initial layout
  scrollToElement(el) {
    // if we have an anchor, fix scrolling "behind" fixed top header
    const fixed_top_height = parseInt($('#mainspacekeeper').css('margin-top'))
				+ parseInt($('#mainspacekeeper').css('padding-top'));
    const vp_reg = il.Util.getViewportRegion();
    const el_reg = il.Util.getRegion(el);
    if (fixed_top_height > 0) {
      $('html, body').scrollTop(el_reg.top - fixed_top_height);
    }
  },

  handleScrolling() {
    il.UICore.refreshLayout();
  },

  refreshLayout() {
    const el = document.getElementById('left_nav');
    const sm = document.getElementById('mainspacekeeper');
    const bc = document.getElementById('bot_center_area');
    const fc = document.getElementById('fixed_content');
    const ft = document.getElementById('ilFooter');
    const rtop = document.getElementById('right_top_area');
    const rbot = document.getElementById('right_bottom_area');
    let nb_reg; let vp_reg; let ft_reg; let rtop_reg; let rbot_reg; let el_reg; let
      bc_reg;

    vp_reg = il.Util.getViewportRegion();
    $('.ilFrame').each(function () {
      const t = $(this);
      //			console.log(t);
      const freg = il.Util.getRegion(this);
      if (freg.bottom < vp_reg.bottom) {
        t.height(t.height() + vp_reg.bottom - freg.bottom - 1);
      }
      // console.log(freg);
      // console.log(vp_reg);
    });

    // fix fixed content
    if ($(fc).css('position') != 'static') {
      if (fc && sm) {
        sm_reg = il.Util.getRegion(sm);
        fc_reg = il.Util.getRegion(fc);
        if (sm_reg.top < vp_reg.top) {
          $(fc).offset({ top: vp_reg.top });
        } else {
          $(fc).offset({ top: sm_reg.top });
        }
      }

      // fix left navigation area
      if (el && sm) {
        sm_reg = il.Util.getRegion(sm);
        nb_reg = il.Util.getRegion(el);
        vp_reg = il.Util.getViewportRegion();
        if (sm_reg.top < vp_reg.top) {
          $(el).css('top', '0px');
          $(fc).css('top', '0px');
        } else {
          $(el).css('top', `${sm_reg.top - vp_reg.top}px`);
          $(fc).css('top', `${sm_reg.top - vp_reg.top}px`);
        }

        // bottom center area?
        if (bc) {
          bc_reg = il.Util.getRegion(bc);
          $(fc).css('bottom', `${bc_reg.height}px`);
        } else {
          $(fc).css('bottom', '0px');
        }
      }
    }

    if (el && bc) {
      el_reg = il.Util.getRegion(el);
      bc_reg = il.Util.getRegion(bc);
      if ($(el).is(':visible')) {
        il.Util.setX(bc, el_reg.right);
      } else if (sm) {
        sm_reg = il.Util.getRegion(sm);
        il.Util.setX(bc, sm_reg.left);		// #0019851
      }
    }

    if (bc && sm) {
      sm_reg = il.Util.getRegion(sm);
      bc_reg = il.Util.getRegion(bc);
      $(bc).css('width', `${parseInt(sm_reg.right - bc_reg.left)}px`);
    }

    // footer vs. left nav
    if (ft && el) {
      ft_reg = il.Util.getRegion(ft);
      if (ft_reg.top < vp_reg.bottom) {
        $(el).css('bottom', `${vp_reg.bottom - ft_reg.top}px`);
      } else {
        $(el).css('bottom', '0px');
      }
    }

    // fit width of right top/bottom regions into mainspacekeeper area
    if (rtop && sm) {
      sm_reg = il.Util.getRegion(sm);
      rtop_reg = il.Util.getRegion(rtop);
      $(rtop).css('width', `${parseInt(sm_reg.right - rtop_reg.left)}px`);
    }
    if (rbot && sm) {
      sm_reg = il.Util.getRegion(sm);
      rbot_reg = il.Util.getRegion(rbot);
      $(rbot).css('width', `${parseInt(sm_reg.right - rbot_reg.left)}px`);
    }

    il.UICore.collapseTabs(false);
  },

  collapseTabs(recheck) {
    const tabs = $('#ilTab.ilCollapsable'); let tabsHeight; let count; let children; let
      collapsed;
    if (tabs) {
      tabsHeight = tabs.innerHeight();

      let more_than_two_lines;
      more_than_two_lines = tabsHeight >= 50;
      if (more_than_two_lines) {
        $('#ilLastTab a').removeClass('ilNoDisplay');

        // as long as we have two lines...
        while (more_than_two_lines) {
          children = tabs.children('li:not(:last-child)');
          count = children.length;

          // ...put last child into collapsed drop down
          $(children[count - 1]).prependTo('#ilTabDropDown');
          if(count == 0) {
            more_than_two_lines = false;
          } else {
            more_than_two_lines = tabs.innerHeight() >= 50;
          }
        }
      } else {
        // as long as we have one line...
        while (tabsHeight < 50 && ($('#ilTabDropDown').children('li').length > 0)) {
          collapsed = $('#ilTabDropDown').children('li');
          count = collapsed.length;
          $(collapsed[0]).insertBefore(tabs.children('li:last-child'));
          tabsHeight = tabs.innerHeight();
        }
        if ($('#ilTabDropDown').children('li').length == 0) {
          $('#ilLastTab a').addClass('ilNoDisplay');
        }
        if (tabsHeight > 50 && !recheck) { // double chk height again
          il.UICore.collapseTabs(true);
        }
      }
    }
  },

  initFixedDropDowns() {
    $('.ilMainMenu.ilTopFixed .dropdown').on('shown.bs.dropdown', function () {
      const el = $(this).children('.dropdown-menu')[0];
      if (!el) {
        return;
      }
      const r = il.Util.getRegion(el);
      const vp = il.Util.getViewportRegion();
      let newHeight;

      // make it smaller, if window height is not sufficient
      if (vp.bottom < r.bottom) {
        newHeight = r.height - r.bottom + vp.bottom;
        el.style.height = `${newHeight}px`;
        $(el).css('overflow', 'auto');
      }
    }).on('hidden.bs.dropdown', () => {
    });
  },

  initLayoutDrag() {
    $('#bot_center_area_drag').mousedown((e) => {
      e.preventDefault();
      $('#drag_zmove').css('display', 'block');
      $('#drag_zmove').mousemove((e) => {
        const vp_reg = il.Util.getViewportRegion();
        const drag_y = e.pageY;
        $('#bot_center_area').css('height', vp_reg.height - drag_y);
        il.UICore.refreshLayout();
      });
    });
    $(document).mouseup((e) => {
      $('#bot_center_area_drag').off('mousemove');
      $('#drag_zmove').css('display', 'none');
      $(document).off('mousemove');
    });
  },

  initDropDowns(context) {
    // fix positions of drop-downs to viewport
    $(`${context} .dropdown-menu`).parent().on('shown.bs.dropdown', function (e) {
      $(this).children('.dropdown-menu').each(function () {
        il.Util.fixPosition(this);
      });
    });
  },

  showRightPanel() {
    this.right_panel = il.Modal.dialogue({
      id: 'il_right_panel',
      show: true,
      body: "<div id='ilRightPanel'></div>",
      buttons: {
      },
    });
  },

  setRightPanelContent(c) {
    $('div#ilRightPanel').html(c);
  },

  // load content from wrapper element into right panel
  loadWrapperToRightPanel(wrapper_id) {
    this.right_panel_wrapper = wrapper_id;
    $(`#${wrapper_id}`).children().appendTo('#ilRightPanel');
  },

  // move the right panel content back to wrapper
  unloadWrapperFromRightPanel() {
    if (this.right_panel_wrapper != '') {
      $('#ilRightPanel').children().appendTo(`#${this.right_panel_wrapper}`);
    }
    this.right_panel_wrapper = '';
  },

  hideRightPanel() {
    il.UICore.unloadWrapperFromRightPanel();

    if (this.right_panel) {
      this.right_panel.hide();
    }
    return;

    il.Overlay.hide(null, 'ilRightPanel');
  },

};

$(document).on('visibilitychange', () => {
  il.UICore.setPageVisibilityStatus(!document.hidden);
});

// fixing anchor links presentation, unfortunately there
// is no event after browsers have scrolled to an anchor hash
// and at least firefox seems to do this multiple times when rendering a page
$(window).on('load', () => {
  window.setTimeout(() => {
    il.UICore.scrollToHash();
  }, 500);
});

$(window).on('hashchange', () => {
  il.UICore.scrollToHash();
});

il.Util.addOnLoad(() => {
  $(window).resize(il.UICore.refreshLayout);
  $(window).scroll(il.UICore.handleScrolling);

  il.UICore.refreshLayout();
  il.Util.omitPreventDoubleSubmission = false;

  // jQuery plugin to prevent double submission of forms
  // see http://stackoverflow.com/questions/2830542/prevent-double-submission-of-forms-in-jquery
  jQuery.fn.preventDoubleSubmission = function () {
    let t; let
      ev;

    if ($(this).get(0)) {
      t = $(this).get(0).tagName;
      ev = (t == 'FORM') ? 'submit' : 'click';
      if (t == 'FORM') {
        $(this).find(':input[type=submit]').on('click', function (e) {
          il.Util.omitPreventDoubleSubmission = false;
          if ($(this).hasClass('omitPreventDoubleSubmission')) {
            il.Util.omitPreventDoubleSubmission = true;
          }
        });
      }
      $(this).on(ev, function (e) {
        const $el = $(this);

        // If form/submit button has been tagged do not prevent anything
        if ($el.hasClass('omitPreventDoubleSubmission')) {
          return this;
        }

        if (ev == 'submit') {
          // if the submit button has been tagged separately
          if ($(':input[type=submit]:focus').hasClass('omitPreventDoubleSubmission') || il.Util.omitPreventDoubleSubmission) {
            return this;
          }
        }

        if ($el.data('submitted') === true) {
          // Previously submitted - don't submit again
          e.preventDefault();
        } else {
          // Mark it so that the next submit can be ignored
          $('form.preventDoubleSubmission, .preventDoubleSubmission a.submit, a.preventDoubleSubmission').data('submitted', true);
          $('form.preventDoubleSubmission input:submit, .preventDoubleSubmission a.submit, a.preventDoubleSubmission').addClass('ilSubmitInactive');
          $('area.preventDoubleSubmission').data('submitted', true);
        }
      });
    }

    // Keep chainability
    return this;
  };
  // note: we need to call this two times, since the first time all forms will be handled,
  // the second time all links, and the get(0) line above handles only sets of elements
  // of the same type correctly
  $('form.preventDoubleSubmission').preventDoubleSubmission();
  $('.preventDoubleSubmission a.submit, a.preventDoubleSubmission').preventDoubleSubmission();
  // Used for image maps in "hot spot" questions:Modules/TestQuestionPool/templates/default/tpl.il_as_qpl_imagemap_question_output.html
  $('area.preventDoubleSubmission').preventDoubleSubmission();

  il.UICore.initDropDowns('');

  // fix mouse-relative positions of context menus (based on drop-downs) to viewport
  $('.contextmenu').click(function (e) {
    // fixPosition (see above) will fix the x-dimension, we are doing y ourselves
    const offset = $(this).offset();
    const menu = $(this).next();
    const menu_height = menu.outerHeight();
    menu.css({
      position: 'absolute',
      left: e.pageX - offset.left,
      top: (($(window).scrollTop() + $(window).height() - e.pageY) < menu_height)
        ? e.pageY - offset.top - menu_height
        : e.pageY - offset.top,
    });
  });

  // Handled IE/Edge issues with HTML5 buttons and form attribute, see: http://caniuse.com/#search=form
  $('button[form][type="submit"]').filter(() => (function () {
    return (
      typeof navigator !== 'undefined'
				&& typeof navigator.appName !== 'undefined'
				&& typeof navigator.appVersion !== 'undefined'
				&& (
				  navigator.appName == 'Microsoft Internet Explorer'
					|| (navigator.appName == 'Netscape' && (navigator.appVersion.indexOf('Trident') > -1 || navigator.appVersion.indexOf('Edge') > -1))
				)
    );
  }())).on('click', function (e) {
    const $elm = $(this); const
      $form = $(`#${$elm.attr('form')}`);

    e.preventDefault();
    e.stopPropagation();

    $('<input/>')
      .attr('type', 'hidden')
      .attr('name', $elm.attr('name'))
      .val(1)
      .appendTo($form);

    $form.find('input[type="submit"]').prop('disabled', true);
    $form.submit();
  });

  il.UICore.initFixedDropDowns();
});

/* Rating */
il.Rating = {

  cache: [],

  setValue(category_id, value, prefix) {
    // set hidden field
    $(`#${prefix}rating_value_${category_id}`).val(value);

    // handle icons
    for (i = 1; i <= 5; i++) {
      const icon_id = `${prefix}rating_icon_${category_id}_${i}`;
      let src = $(`#${icon_id}`).attr('src');

      // active
      if (i <= value) {
        if (src.substring(src.length - 6) == 'on.svg') {
          src = `${src.substring(0, src.length - 6)}on_user.svg`;
        } else if (src.substring(src.length - 7) == 'off.svg') {
          src = `${src.substring(0, src.length - 7)}on_user.svg`;
        }
      }
      // inactive
      else if (src.substring(src.length - 6) == 'on.svg') {
        src = `${src.substring(0, src.length - 6)}off.svg`;
      } else if (src.substring(src.length - 11) == 'on_user.svg') {
        src = `${src.substring(0, src.length - 11)}off.svg`;
      }

      // resetting img cache so onmouseout will not change icons again
      il.Rating.cache[icon_id] = '';

      $(`#${icon_id}`).attr('src', src);
    }

    return false;
  },

  toggleIcon(el, value, is_out) {
    $(el).children().each(function () {
      if ($(this).attr('id')) {
        const org = $(this).attr('id');
        const grp = org.substring(0, org.length - 1);
        for (i = 1; i <= 5; i++) {
          const id = grp + i;

          if (is_out == undefined) {
            // determine type of current icon
            const src_parts = $(`#${id}`).attr('src').split('_');
            let icon_type = src_parts.pop();
            icon_type = icon_type.substring(0, icon_type.length - 4);
            if (icon_type == 'user') {
              icon_type = src_parts.pop();
            }
            if ($.isNumeric(icon_type)) {
              icon_type = 'on';
            }
            const icon_base = src_parts.join('_');

            // onmouseout should revert to original img
            var src = $(`#${id}`).attr('src');
            il.Rating.cache[id] = src;

            // active
            if (i <= value) {
              src = `${icon_base}_on_user.svg`;
            }
            // inactive
            else {
              src = `${icon_base}_off.svg`;
            }
          } else {
            var src = il.Rating.cache[id];
          }

          if (src) {
            $(`#${id}`).attr('src', src);
          }
        }
      }
    });
  },
};

il.Language = {
  lng: {},

  setLangVar(key, value) {
    il.Language.lng[key] = value;
  },

  txt(key) {
    if (il.Language.lng[key]) {
      let translation = il.Language.lng[key];
      if (typeof arguments[1] !== 'undefined') {
        for (let i = 1; i < arguments.length; i++) {
          translation = translation.replace(new RegExp('%s'), arguments[i]);
        }
      }
      return translation;
    }
    return `-${key}-`;
  },
};

/* keep ios wepapp mode (do not open safari mobile if links are clicked) */
/* if (("standalone" in window.navigator) && !window.navigator.standalone ){
	il.Util.addOnLoad(function () {
		$(document).on(
			"click",
			'a[href!="#"][href!=""]',
			function(event){
				if (event.target.nodeName == "A") {
					event.preventDefault();
					location.href = $(event.target).attr("href");
				}
			}
		);
	});
} */

/// /
/// / The following methods should be moved to the corresponding components
/// /

function startSAHS(SAHSurl, SAHStarget, SAHSopenMode, SAHSwidth, SAHSheight) {
  if (SAHSopenMode == 1) {
    SAHSwidth = '100%';
    SAHSheight = '650';
    if (document.getElementById('mainspacekeeper').offsetHeight) {
      SAHSheight = document.getElementById('mainspacekeeper').offsetHeight;
    }
  }
  if (SAHSopenMode == 1 || SAHSopenMode == 2) {
    document.getElementById('mainspacekeeper').innerHTML = `<iframe src="${SAHSurl}" width="${SAHSwidth}" height=${SAHSheight} frameborder="0"></iframe>`;
  } else if (SAHSopenMode == 5) {
    window.open(SAHSurl, SAHStarget, 'top=0,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no');
  } else {
    window.open(SAHSurl, SAHStarget, `top=0,width=${SAHSwidth},height=${SAHSheight},location=no,menubar=no,resizable=yes,scrollbars=yes,status=no`);
  }
}

/**
 * Related to https://mantis.ilias.de/view.php?id=26494
 * jQuery "inputFilter" Extension.
 */
(function ($) {
  /**
	 * @param {mixed} inputFilter
	 * @returns {jQuery}
	 */
  $.fn.inputFilter = function (inputFilter) {
    return this.on('input keydown keyup mousedown mouseup select contextmenu drop', function (e) {
      if ($.trim(this.value) === '-') {
        // https://mantis.ilias.de/view.php?id=29417
      } else if (inputFilter(this.value)) {
        this.oldValue = this.value;
        this.oldSelectionStart = this.selectionStart;
        this.oldSelectionEnd = this.selectionEnd;
      } else if (this.hasOwnProperty('oldValue')) {
        this.value = this.oldValue;
        this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
      } else {
        this.value = '';
      }
    });
  };
}(jQuery));

/**
 * Related to https://mantis.ilias.de/view.php?id=26494
 * UI-Feedback : check if a numeric field isset but value is not numeric.
 */
function numericInputCheck() {
  const numericInput = $('.ilcqinput_NumericInput');

  // Only if present.
  if (numericInput.length) {
    // Append ilcqinput_NumericInputInvalid class for visually distinguishable numeric input fields.
    // -> Onload.
    const value = $(numericInput).val().toString().replace(',', '.');
    if (value && !$.isNumeric(value)) {
      $(numericInput).addClass('ilcqinput_NumericInputInvalid');
    } else {
      $(numericInput).removeClass('ilcqinput_NumericInputInvalid');
    }
    // -> OnChange.
    $(numericInput).on('change', function () {
      const value = $(this).val().toString().replace(',', '.');
      if (value && !$.isNumeric(value)) {
        $(this).addClass('ilcqinput_NumericInputInvalid');
      } else {
        $(this).removeClass('ilcqinput_NumericInputInvalid');
      }
    });

    // Only allow numeric values foreach ".ilcqinput_NumericInput" classified input field.
    $(numericInput).inputFilter((value) => {
      value = value.toString().replace(',', '.');
      return !$.trim(value) || $.isNumeric(value);
    });
  }
}

$(document).ready(() => {
  numericInputCheck();
});
