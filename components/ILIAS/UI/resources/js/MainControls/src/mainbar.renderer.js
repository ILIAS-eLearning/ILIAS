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

const renderer = function ($) {
  const css = {
    engaged: 'engaged',
    disengaged: 'disengaged',
    hidden: 'hidden',
    page_div: 'il-layout-page',
    page_has_engaged_slated: 'with-mainbar-slates-engaged',
    tools_btn: 'il-mainbar-tools-button',
    toolentries_wrapper: 'il-mainbar-tools-entries',
    remover_class: 'il-mainbar-remove-tool',
    mainbar: 'il-mainbar',
    mainbar_buttons: '.il-mainbar .il-mainbar-entries .btn-bulky, .il-mainbar .il-mainbar-entries .link-bulky',
    mainbar_entries: 'il-mainbar-entries',
  };

  const dom_references = {};
  const dom_ref_to_element = {};
  const thrown_for = {};
  const dom_element = {
    withHtmlId(html_id) {
      return { ...this, html_id };
    },
    getElement() {
      // return document.getElementById(this.html_id);
      return $(`#${this.html_id}`);
    },
    engage() {
      const element = this.getElement();

      element.addClass(css.engaged);
      element.removeClass(css.disengaged);

      if (il.UI.page.isSmallScreen() && il.UI.maincontrols.metabar) {
        il.UI.maincontrols.metabar.disengageAll();
      }
      this.additional_engage();
    },
    disengage() {
      this.getElement().addClass(css.disengaged);
      this.getElement().removeClass(css.engaged);
      this.additional_disengage();
    },
    mb_hide(on_parent) {
      let element = this.getElement();
      if (on_parent) {
        element = element.parent();
      }
      element.addClass(css.hidden);
    },
    mb_show(on_parent) {
      let element = this.getElement();
      if (on_parent) {
        element = element.parent();
      }
      element.removeClass(css.hidden);
    },
    additional_engage() {},
    additional_disengage() {},
  };
  const parts = {
    triggerer: {
      ...dom_element,
      remove() {},
      additional_engage() {
        this.getElement().attr('aria-expanded', true);
      },
      additional_disengage() {
        this.getElement().attr('aria-expanded', false);
      },
    },
    slate: {
      ...dom_element,
      remove: null,
      mb_hide: null,
      mb_show: null,
      additional_engage() {
        const element = this.getElement();
        const entry_id = dom_ref_to_element[this.html_id];
        const isInView = il.UI.maincontrols.mainbar.model.isInView(entry_id);
        const thrown = thrown_for[entry_id];

        element.attr('aria-hidden', false);
        // https://www.w3.org/TR/wai-aria-practices-1.1/examples/accordion/accordion.html
        const currentRole = element.attr('role');
        if (!currentRole || currentRole === 'region') {
          element.attr('role', 'region');
        }
        if (isInView && !thrown) {
          element.trigger('in_view'); // this is most important for async loading of slates,
          // it triggers the GlobalScreen-Service.
          thrown_for[entry_id] = true;
        }
        if (!isInView) {
          thrown_for[entry_id] = false;
        }
      },
      additional_disengage() {
        const entry_id = dom_ref_to_element[this.html_id];
        thrown_for[entry_id] = false;
        const element = this.getElement();
        element.attr('aria-hidden', true);
        if (element.attr('role') === 'region') {
          element.removeAttr('role');
        }
      },
    },
    remover: {
      ...dom_element,
      engage: null,
      disengage: null,
      mb_show() { this.getElement().parent().show(); },
    },
    page: {
      getElement() {
        return $(`.${css.page_div}`);
      },
      slatesEngaged(engaged) {
        if (engaged) {
          this.getElement().addClass(css.page_has_engaged_slated);
        } else {
          this.getElement().removeClass(css.page_has_engaged_slated);
        }
      },
    },
    removers: {
      getElement() {
        return $(`.${css.remover_class}`);
      },
      mb_hide() {
        this.getElement().hide();
      },

    },
    tools_area: {
      ...dom_element,
      getElement() {
        return $(` .${css.toolentries_wrapper}`);
      },
    },
    tools_button: {
      ...dom_element,
      getElement() {
        return $(`.${css.tools_btn} .btn`);
      },
      remove: null,
      additional_engage() {
        this.getElement().attr('aria-expanded', true);
      },
      additional_disengage() {
        this.getElement().attr('aria-expanded', false);
      },
    },
    mainbar: {
      getElement() {
        return $(`.${css.mainbar}`);
      },
      getOffsetTop() {
        return this.getElement().offset().top;
      },
    },
  };

  // more-slate
  const more = {
    calcAmountOfButtons() {
      const window_height = $(window).height();
      const window_width = $(window).width();
      const horizontal = il.UI.page.isSmallScreen();
      const btn = $(css.mainbar_buttons).first();
      btn_height = btn.height(),
      btn_width = btn.width(),
      amount_buttons = Math.floor(
        (window_height - parts.mainbar.getOffsetTop()) / btn_height,
      );

      if (horizontal) {
        amount_buttons = Math.floor(window_width / btn_width);
      }
      return amount_buttons;
    },
  };

  var actions = {
    addEntry(entry_id, part, html_id) {
      dom_references[entry_id] = dom_references[entry_id] || {};
      dom_references[entry_id][part] = html_id;
      dom_ref_to_element[html_id] = entry_id;
      thrown_for[entry_id] = false;
    },
    renderEntry(entry, is_tool) {
      if (!dom_references[entry.id]) {
        return;
      }

      const triggerer = parts.triggerer.withHtmlId(dom_references[entry.id].triggerer);
      const slate = parts.slate.withHtmlId(dom_references[entry.id].slate);

      // a11y
      triggerer.getElement().attr('aria-controls', slate.html_id);
      triggerer.getElement().attr('aria-labelledby', triggerer.html_id);
      // a11y

      // The slate is outside the trigger’s natural Tab order.
      if (slate.html_id) {
        triggerer.getElement().off('keydown.mainbar-slate').on('keydown.mainbar-slate', (event) => {
          if (event.key === 'Tab' && !event.shiftKey && entry.engaged && actions.focusSubentry(entry.id)) {
            event.preventDefault();
          }
        });
      }

      if (entry.hidden) {
        triggerer.mb_hide(is_tool);
      } else {
        triggerer.mb_show(is_tool);
      }

      if (entry.engaged) {
        triggerer.engage();
        slate.engage();
        if (entry.removeable) {
          remover = parts.remover.withHtmlId(dom_references[entry.id].remover);
          remover.mb_show(true);
        }
      } else {
        triggerer.disengage();
        slate.disengage();
      }
    },

    moveToplevelTriggerersToMore(model_state) {
      const entry_ids = Object.keys(model_state.entries);
      const last_entry_id = entry_ids[entry_ids.length - 1];
      const more_entry = model_state.entries[last_entry_id];
      const more_slate = parts.slate.withHtmlId(dom_references[more_entry.id].slate);
      const root_entries = il.UI.maincontrols.mainbar.model.getTopLevelEntries();
      const root_entries_length = root_entries.length - 1;
      let max_buttons = more.calcAmountOfButtons() - 1; // room for the more-button

      if (model_state.any_tools_visible()) { max_buttons--; }

      // Pathological case: there even is no space for one button.
      // We pretend there still is room...
      if (max_buttons < 0) { max_buttons = 0; }

      for (i = max_buttons; i < root_entries_length; i++) {
        btn = parts.triggerer.withHtmlId(dom_references[root_entries[i].id].triggerer);
        list = btn.getElement().parent();
        btn.getElement().appendTo(more_slate.getElement().children('.il-maincontrols-slate-content'));
        list.remove();
      }
    },
    render(model_state) {
      const entry_ids = Object.keys(model_state.entries);

      if (entry_ids.length == 0) {
        return;
      }

      const last_entry_id = entry_ids[entry_ids.length - 1];
      const more_entry = model_state.entries[last_entry_id];
      const more_button = parts.triggerer.withHtmlId(dom_references[more_entry.id].triggerer);
      const more_slate = parts.slate.withHtmlId(dom_references[more_entry.id].slate);
      // reset
      btns = more_slate.getElement().find('.btn-bulky, .link-bulky');

      for (let i = 0; i < btns.length; i += 1) {
        li = document.createElement('li');
        li.appendChild(btns[i]);
        li.setAttribute('role', 'none');
        $(li).insertBefore(more_button.getElement().parent());
      }

      if (model_state.more_available) {
        more_button.getElement().parent().show();
        actions.moveToplevelTriggerersToMore(model_state);
      } else {
        more_button.getElement().parent().hide();
      }

      parts.page.slatesEngaged(model_state.any_entry_engaged || model_state.tools_engaged);

      if (model_state.any_tools_visible()) {
        parts.tools_button.mb_show();
      } else {
        parts.tools_button.mb_hide();
      }

      if (model_state.tools_engaged) {
        parts.tools_button.engage();
        parts.tools_area.engage();
      } else {
        parts.tools_button.disengage();
        parts.tools_area.disengage();
      }

      for (idx in model_state.entries) {
        actions.renderEntry(model_state.entries[idx], false);
      }
      for (idx in model_state.tools) {
        actions.renderEntry(model_state.tools[idx], true);
      }

      if (model_state.last_active_top && dom_references[model_state.last_active_top]) {
        const activeTriggerer = parts.triggerer.withHtmlId(dom_references[model_state.last_active_top].triggerer);
        const slateName = activeTriggerer.getElement().find('.bulky-label').text().trim();
        if (slateName) {
          const closeButton = $('.il-mainbar-close-slates .btn-bulky');
          const closeLabel = `${il.Language.txt('close')} ${slateName}`;
          closeButton.attr('aria-label', closeLabel);
          closeButton.find('.bulky-label').text(closeLabel);
        }
      }

      // unfortunately, this does not work properly via a class
      $(`.${css.mainbar_entries}`).css('visibility', 'visible');
    },
    focusSubentry(triggered_entry_id) {
      const ids = dom_references[triggered_entry_id];
      const controls = $(`#${ids.slate}`).find('a[href], button, input, select, textarea, [tabindex]');
      for (const control of controls) {
        if (control.tabIndex < 0 || $(control).is(':disabled')) {
          continue;
        }
        control.focus();
        if (document.activeElement === control) {
          $(control).off('keydown.mainbar-slate-first').on('keydown.mainbar-slate-first', (event) => {
            if (event.key === 'Tab' && event.shiftKey) {
              actions.focusTopentry(triggered_entry_id);
              if (document.activeElement.id === ids.triggerer) {
                event.preventDefault();
              }
            }
          });
          return true;
        }
      }
      return false;
    },
    focusTopentry(top_entry_id) {
      const triggerer = dom_references[top_entry_id];
      if (triggerer) {
        document.getElementById(triggerer.triggerer).focus();
      }
    },

    dispatchResizeNotification() {
      const event = new CustomEvent(
        'resize',
        { detail: { mainbar_induced: true } },
      );
      window.dispatchEvent(event);
    },
  };
  const public_interface = {
    addEntry: actions.addEntry,
    calcAmountOfButtons: more.calcAmountOfButtons,
    render: actions.render,
    focusSubentry: actions.focusSubentry,
    focusTopentry: actions.focusTopentry,
    dispatchResizeNotification: actions.dispatchResizeNotification,
  };

  return public_interface;
};

export default renderer;
