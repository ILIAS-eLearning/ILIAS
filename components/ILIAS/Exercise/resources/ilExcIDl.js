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

il.ExcIDl = {
  ajax_url: '',

  init(url, showSignal, closeSignal) {
    console.log(`init url:${url}`);
    this.ajax_url = url;
    this.showSignal = showSignal;
    this.closeSignal = closeSignal;
    il.ExcIDl.initModal();
  },

  trigger(user_id, ass_id) {
    il.repository.core.fetchHtml(
      il.ExcIDl.ajax_url,
      { idlid: `${ass_id}_${user_id}` },
    ).then((html) => {
      il.ExcIDl.showModal(html);
    });
    return false;
  },

  initModal() {
    console.log('init modal');
    // add form action
    $('form[name="ilExcIDlForm"]').submit(function () {
      const submit_btn = $(document.activeElement).attr('name');
      if (submit_btn) {
        const values = {};
        let cmd = null;
        const sel = [];
        const ids = [];
        $.each($(this).serializeArray(), (i, field) => {
          if (submit_btn == 'select_cmd2' && field.name == 'selected_cmd2') {
            cmd = field.value;
          } else if (submit_btn == 'select_cmd' && field.name == 'selected_cmd') {
            cmd = field.value;
          }
          // extract user/team ids
          if (field.name.substr(0, 12) == 'sel_part_ids') {
            // sel.push(field.name.substr(13, field.name.length-14));
            sel.push(field.value);
          } else if (field.name.substr(0, 3) == 'ass') {
            sel.push(field.name.substr(4, field.name.length - 5));
          } else if (field.name.substr(0, 14) == 'listed_idl_ids' && field.value != '') {
            const sel_value = field.name.substr(15, field.name.length - 16);
            if (sel.indexOf(sel_value) > -1) {
              ids.push(field.value);
            }
          }
        });
        if (cmd == 'setIndividualDeadline' && ids.length) {
          console.log('trigger 2');
          // :TODO: handle preventDoubleSubmission?
          il.repository.core.fetchHtml(
            il.ExcIDl.ajax_url,
            { idlid: ids.join() },
          ).then((html) => {
            il.ExcIDl.showModal(html);
          });
          return false;
        }
      }
    });
    // modal clean-up on close
    $('#ilExcIDlBody').closest('.il-modal-roundtrip').on('hidden.bs.modal', (e) => {
      $('#ilExcIDlBody').html('');
    });
  },

  showModal(html) {
    console.log('show modal');
    if (html !== undefined) {
      $('#ilExcIDlBody').html(html);

      il.ExcIDl.parseForm();

      $(document).trigger(
        this.showSignal,
        {
          id: this.showSignal,
          triggerer: $(this),
          options: JSON.parse('[]'),
        },
      );
      // $("#ilExcIDlBody").closest(".il-modal-roundtrip").modal('show');
    }
  },

  parseForm() {
    $('form[name="ilExcIDlForm"]').submit(function () {
      $.ajax({
        type: 'POST',
        url: il.ExcIDl.ajax_url,
        data: $(this).serializeArray(),
        success: il.ExcIDl.handleForm,
      });
      return false;
    });
  },

  handleForm(responseText) {
    if (responseText !== undefined) {
      if (responseText != 'ok') {
        $('#ilExcIDlBody').html(responseText);
        il.ExcIDl.parseForm();
      } else {
        window.location.replace(`${il.ExcIDl.ajax_url}&dn=1`);
      }
    }
  },
};
