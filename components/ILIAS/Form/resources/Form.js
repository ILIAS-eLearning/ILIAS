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

/* eslint-disable */
il.Form = {

  duration: 150,

  items: {},

  escapeSelector(str) {
    return str.replace(/([;&,\.\+\*\~':"\!\^#$%@\[\]\(\)=>\|])/g, '\\$1');
  },

  sub_active: [],	// active sub forms for each context

  initItem(id, cfg) {
    il.Form.items[id] = cfg;
  },

  // ad
  // General functions
  //

  // init
  init() {
    $(() => {
      il.Form.initLinkInput();
      il.Form.registerFileUploadInputEventTrigger();
    });
  },

  registerFileUploadInputEventTrigger(selectorPrefix = '') {
    /* experimental: bootstrap'ed file upload */

    // see http://www.abeautifulsite.net/whipping-file-inputs-into-shape-with-bootstrap-3/

    // trigger event on fileselect
    $(document).on('change', `${selectorPrefix}.btn-file :file`, function (e) {
      const input = $(this);
      const dt = new DataTransfer();
      let label = '';

      input.parents('.input-group').next().addClass('help-block').removeClass('ui-input-file-input-error-msg');
      input.get(0).files.forEach(
        (item) => {
          if (item.size <= input.attr('data-maxsize')) {
            dt.items.add(item);
          } else {
            label = input.attr('data-maxsize-warning');
            input.parents('.input-group').next().removeClass('help-block').addClass('ui-input-file-input-error-msg');
            e.stopImmediatePropagation();
          }
        }
      );
      input.get(0).files = dt.files;

      const numFiles = input.get(0).files ? input.get(0).files.length : 1;
      label += input.val().replace(/\\/g, '/').replace(/.*\//, '');
      input.trigger('fileselect', [numFiles, label]);
    });

    // display selected file name
    $(`${selectorPrefix}.btn-file :file`).on('fileselect', function (event, numFiles, label) {
      const input = $(this).parents('.input-group').find(':text');
      if (input.length) {
        input.val(label);
      }
    });
  },

  // hide sub forms
  hideSubForm(id) {
    id = il.Form.escapeSelector(id);
    $(`#${id}`)./* css('overflow', 'hidden'). */css('height', 'auto').css('display', 'none');
  },

  // show Subform
  showSubForm(id, cont_id, cb) {
    let nh; let obj; let k; let
      m;

    id = il.Form.escapeSelector(id);
    cont_id = il.Form.escapeSelector(cont_id);

    console.log(id);
    console.log(cont_id);

    if (cb == null) {
      il.Form.sub_active[cont_id] = id;
    } else if (cb.checked) {
      il.Form.sub_active[cont_id] = id;
    } else {
      il.Form.sub_active[cont_id] = null;
    }

    console.log(il.Form.sub_active);

    const parent_subform = $(`#${cont_id}`).parents('.ilSubForm')[0];

    console.log('close...');
    $(`#${cont_id} div.ilSubForm[id!='${id}']`).each(function () {
      console.log(this.id);

      // #18482 - check if subform is on same level as parent
      if (parent_subform == $(this).parents('.ilSubForm')[0]) {
        $(this).animate({
          height: 0,
        }, il.Form.duration, function () {
          $(this).css('display', 'none');

          // activated in the meantime?
          for (m = 0; m < il.Form.sub_active.length; m++) {
            if (il.Form.escapeSelector(this.id) == il.Form.sub_active[m]) {
              $(this).css('display', '');
            }
          }
          $(this).css('height', 'auto');
        });
      }
    });
    console.log('...close');

    // activate subform
    obj = $(`#${id}`).get(0);
    if (obj && obj.style.display == 'none' && (cb == null || cb.checked == true)) {
      obj.style.display = '';
      obj.style.position = 'relative';
      obj.style.left = '-1000px';
      obj.style.display = 'block';
      nh = obj.scrollHeight;
      obj.style.height = '0px';
      obj.style.position = '';
      obj.style.left = '';
      // obj.style.overflow = 'hidden';

      obj.style.display = '';
      $(obj).animate({
        height: nh,
      }, il.Form.duration, function () {
        $(this).css('height', 'auto');
      });

      // needed for google maps
      $(obj).closest('form').trigger('subform_activated');
    }

    // deactivate subform of checkbox
    if (obj && (cb != null && cb.checked == false)) {
      // obj.style.overflow = 'hidden';

      $(obj).animate({
        height: 0,
      }, il.Form.duration, function () {
        $(this).css('display', 'none');
        // activated in the meantime?
        for (k = 0; k < il.Form.sub_active.length; k++) {
          if (il.Form.escapeSelector(this.id) == il.Form.sub_active[k]) {
            $(this).css('display', '');
          }
        }
        $(this).css('height', 'auto');
      });
    }
  },

  //
  // ilLinkInputGUI
  //

  initLinkInput() {
    $('a.ilLinkInputRemove').click(function (e) {
      let { id } = this.parentNode;
      id = id.substr(0, id.length - 4);
      $(`input[name=${il.Form.escapeSelector(id)}_ajax_type]`).val('');
      $(`input[name=${il.Form.escapeSelector(id)}_ajax_id]`).val('');
      $(`input[name=${il.Form.escapeSelector(id)}_ajax_target]`).val('');
      $(`#${il.Form.escapeSelector(id)}_value`).html('');
      $(this.parentNode).css('display', 'none');
      console.log(id);
    });
  },

  // set internal link in form item
  addInternalLink(link, title, input_id, ev, c) {
    let type; let id; let part; let
      target = '';

    input_id = il.Form.escapeSelector(input_id);

    // #10543 - IE[8]
    const etarget = ev.target || ev.srcElement;

    $(`#${input_id}_value`).html($(etarget).html());

    link = link.split(' ');
    part = link[1].split('="');
    type = part[0];
    id = part[1].split('"')[0];
    if (link[2] !== undefined) {
      target = link[2].split('="');
      target = target[1].split('"')[0];
    }
    $(`input[name=${input_id}_ajax_type]`).val(type);
    $(`input[name=${input_id}_ajax_id]`).val(id);
    $(`input[name=${input_id}_ajax_target]`).val(target);

    $(`#${input_id}_rem`).css('display', 'block');
  },

  //
  // ilNumberInputGUI
  //

  // initialisation for number fields
  initNumericCheck(id, decimals_allowed) {
    let current;

    $(`#${il.Form.escapeSelector(id)}`).keydown((event) => {
      // #10562
      const kcode = event.which;
      const is_shift = event.shiftKey;
      const is_ctrl = event.ctrlKey;

      if (kcode == 190 || kcode == 188) {
        // decimals are not allowed
        if (decimals_allowed == undefined || decimals_allowed == 0) {
          event.preventDefault();
        } else {
          // decimal point is only allowed once
          current = $(`#${id}`).val();
          if (
            current.indexOf('.') > -1
						|| current.indexOf(',') > -1
          ) {
            event.preventDefault();
          }
        }
        // Allow: backspace, delete, tab, escape, and enter
      } else if (kcode == 46 || kcode == 8 || kcode == 9 || kcode == 27 || kcode == 13
					 // Allow: Ctrl+A
					|| (kcode == 65 && is_ctrl === true)
					 // Allow: home, end, left, right (up [38] does not matter)
					|| (kcode >= 35 && kcode <= 39)
					 // Allow: negative values (#10652)
					|| kcode == 173) {
        // let it happen, don't do anything

      } else {
        // Ensure that it is a number and stop the keypress (2nd block: num pad)
        if (is_shift || (kcode < 48 || kcode > 57) && (kcode < 96 || kcode > 105)) {
          event.preventDefault();
        }
      }
    });
  },

  //
  // ilDateDurationInputGUI
  //

  initDateDurationPicker(picker_id, picker2_id, toggle_id) {
    const dp = document.querySelector(`#${picker_id} input[type=datetime-local]`);
    const dp2 = document.querySelector(`#${picker2_id} input[type=datetime-local]`);

    // init

    // set limit by current date of other picker
    if (dp.value) {
      dp2.min = dp.value;

      // store current value for diff magic
      dp.dataset.current_value = dp.value;
    }

    // onchange

    dp.addEventListener('change', (e) => {
      // limit to value of end picker
      dp2.min = dp.value;

      // keep diff the same
      const old_dp = dp.dataset.current_value;

      if (old_dp && dp2.value && dp.value) {
        const old_dp_timestamp = Date.parse(`${old_dp}Z`);
        const dp_timestamp = Date.parse(`${dp.value}Z`);
        const dp2_timestamp = Date.parse(`${dp2.value}Z`);

        const new_dp2_date = new Date(dp2_timestamp - old_dp_timestamp + dp_timestamp);
        if (dp2.type == 'datetime-local') {
          dp2.value = new_dp2_date.toISOString().slice(0, 16);
        } else if (dp2.type == 'date') {
          dp2.value = new_dp2_date.toISOString().slice(0, 10);
        }
      }

      // keep current date for diff parsing (see above);
      dp.dataset.current_value = dp.value;
    });

    // toggle

    if (toggle_id) {
      const toggle = document.querySelector(`#${toggle_id}`);

      // init

      if (toggle.checked) {
        il.Form.removeTimeFromDatetimeInput(dp);
        il.Form.removeTimeFromDatetimeInput(dp2);
      }

      // onchange

      toggle.addEventListener('change', (e) => {
        if (!toggle.checked) {
          il.Form.addTimeToDatetimeInput(dp);
          il.Form.addTimeToDatetimeInput(dp2);
        } else {
          il.Form.removeTimeFromDatetimeInput(dp);
          il.Form.removeTimeFromDatetimeInput(dp2);
        }
        // update current date for diff parsing (see above);
        dp.dataset.current_value = dp.value;
      });
    }
  },

  addTimeToDatetimeInput(input) {
    // read out relevant values before changing type
    // toggleable duration inputs always start with time, so dataset should be filled
    const date_value = input.value + input.dataset.valuetime;
    const min_value = input.min + input.dataset.mintime;

    // change type
    input.type = 'datetime-local';

    // restore relevant attributes
    input.step = 60;
    input.value = date_value;
    input.min = min_value;
  },

  removeTimeFromDatetimeInput(input) {
    // read out relevant values before changing type
    const date_value = input.value.slice(0, 10);
    const min_value = input.min.slice(0, 10);

    // store relevant attributes so they can be restored on retoggle
    input.dataset.valuetime = input.value.slice(-6);
    input.dataset.mintime = input.min.slice(-6);

    // change type
    input.type = 'date';

    // set attributes so they apply to date input
    input.step = 1;
    input.value = date_value;
    input.min = min_value;
  },

  // Tiny textarea char. counter
  showCharCounterTinymce(ed) {
    // var content_raw = ed.getContent({ format: 'raw' }); // whitespaces and br issues. (first whitespace creates br etc.)
    const content_raw = ed.getContent({ format: 'raw' });
    let content = content_raw.replace(/<\/?[^>]+(>|$)/g, '');
    // #20630, #20674
    content = content.replace(/&nbsp;/g, ' ');
    content = content.replace(/&lt;/g, '<');
    content = content.replace(/&gt;/g, '>');
    content = content.replace(/&amp;/g, '&');
    console.log(content);
    const text_length = content.length;

    const max_limit = $(`#textarea_feedback_${ed.id}`).data('maxchars');
    if (max_limit > 0) {
      const text_remaining = max_limit - text_length;
      $(`#textarea_feedback_${ed.id}`).html(`${il.Language.txt('form_chars_remaining')} ${text_remaining}`);
    }
  },
  // normal textarea char. counter
  showCharCounterTextarea(textarea_id, feedback_id, min_limit, max_limit) {
    const text_length = $(`#${textarea_id}`).val().length;
    if (max_limit > 0) {
      const text_remaining = max_limit - text_length;
      $(`#${feedback_id}`).html(`${il.Language.txt('form_chars_remaining')} ${text_remaining}`);
      return true;
    }
  },

};

// init forms
il.Util.addOnLoad(il.Form.init);

// see #27281
$(document).on('dp.show', (event) => {
  il.UI.page.fit($('.bootstrap-datetimepicker-widget'));
});
