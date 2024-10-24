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

var AnswerWizardInput = {
	init: function () {
    this.initEvents($(this.tag_container));
  },

  initEvents(rootel) {
    const that = this;
    $(rootel).find(`.${this.tag_button}_add .glyph`).click((e) => {
      that.addRow(e);
    });
    $(rootel).find(`.${this.tag_button}_remove .glyph`).click((e) => {
      that.removeRow(e);
    });
    $(rootel).find(`.${this.tag_button}_up .glyph`).click((e) => {
      that.moveRowUp(e);
    });
    $(rootel).find(`.${this.tag_button}_down .glyph`).click((e) => {
      that.moveRowDown(e);
    });
  },

  onClickHandler(action, e) {
    e.preventDefault();
    const id_tag = e.currentTarget.parentNode.id.split('[');
    const id = id_tag.pop().slice(0, -1);
    const target = id_tag[0].split('_').pop();
    const button = document.createElement('BUTTON');
    button.type = 'submit';
    button.name = `cmd[${action}${target}][${id}]`;
    button.style.display = 'none';
    e.target.insertAdjacentElement('afterend', button);
    button.form.requestSubmit(button);
  },

  addRow(e) {
    // clone row
    const source = this.getRowFromEvent(e);
    const target = $(source).clone();

    // add events
    this.initEvents(target);

    // empty inputs
    this.cleanRow(target);

    $(source).after(target);

    this.reindexRows(this.getContainerFromEvent(e));

    il.Form.registerFileUploadInputEventTrigger();

    const current_upload_fields = $('#files').children().length;
    const max_upload_fields = parseInt(source.children('.imagewizard_add').attr('data-val'));
    if (current_upload_fields === max_upload_fields) {
      $('.imagewizard_add').hide();
    }
  },

  removeRow(e) {
    const source = this.getRowFromEvent(e);
    const tbody = this.getContainerFromEvent(e);

    // do not remove last row
    if ($(tbody).find(this.tag_row).size() > 1) {
      $(source).remove();
    }
    // reset last remaining row
    else {
      this.cleanRow(source);
    }

    this.reindexRows(tbody);

    const current_upload_fields = $('#files').children().length;
    const max_upload_fields = parseInt(source.children('.imagewizard_remove').attr('data-val'));
    if (current_upload_fields <= max_upload_fields) {
      $('.imagewizard_add').show();
    }
  },

  moveRowUp(e) {
    const source = this.getRowFromEvent(e);
    const prev = $(source).prev();
    if (prev[0]) {
      $(prev).before(source);

      this.reindexRows(this.getContainerFromEvent(e));
    }
  },

  moveRowDown(e) {
    const source = this.getRowFromEvent(e);
    const next = $(source).next();
    if (next[0]) {
      $(next).after(source);

      this.reindexRows(this.getContainerFromEvent(e));
    }
  },

  handleId(el, attr, new_idx) {
    const parts = $(el).attr(attr).split('[');
    parts.pop();
    parts.push(`${new_idx}]`);
    $(el).attr(attr, parts.join('['));
  },
};
