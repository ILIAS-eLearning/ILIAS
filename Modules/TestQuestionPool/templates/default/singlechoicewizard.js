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

const ilSingleChoiceWizardInputTemplate = {

  tag_container: 'tbody.scwzd',
  tag_row: 'tr.scwzd',
  tag_button: 'singlechoice',

  getRowFromEvent(e) {
    return $(e.target).closest(this.tag_row);
  },

  getContainerFromEvent(e) {
    return $(e.target).closest(this.tag_container);
  },

  cleanRow(row) {
    $(row).find('input:text').val('');
    $(row).find('textarea').val('');
    $(row).find('div.imagepresentation').remove();
  },

  reindexRows(tbody) {
    const that = this;
    let rowindex = 0;

    // process all rows
    $(tbody).find(this.tag_row).each(function () {
      // hidden
      $(this).find('input:hidden[name*="[imagename]"]').each(function () {
        that.handleId(this, 'name', rowindex);
      });

      // hidden answer id
      $(this).find('input:hidden[name*="[answer_id]"]').each(function () {
        that.handleId(this, 'name', rowindex);
      });

      // answer
      $(this).find('input:text[id*="[answer]"]').each(function () {
        that.handleId(this, 'name', rowindex);
        that.handleId(this, 'id', rowindex);
      });

      $(this).find('textarea[id*="[answer]"]').each(function () {
        that.handleId(this, 'name', rowindex);
        that.handleId(this, 'id', rowindex);
      });

      // points
      $(this).find('input:text[id*="[points]"]').each(function () {
        that.handleId(this, 'name', rowindex);
        that.handleId(this, 'id', rowindex);
      });

      // fileupload
      $(this).find('input:file[id*="[image]"]').each(function () {
        that.handleId(this, 'id', rowindex);
        that.handleId(this, 'name', rowindex);
      });

      // submit upload
      $(this).find('input:submit[name*="[uploadchoice]"]').each(function () {
        that.handleId(this, 'name', rowindex);
      });

      // delete image button
      $(this).find('input:submit[name*="[removeimagechoice]"]').each(function () {
        that.handleId(this, 'name', rowindex);
      });

      // button
      $(this).find('div.btn.btn-link').each(function () {
        that.handleId(this, 'id', rowindex);
      });

      rowindex++;
    });
  },

  initEvents(rootel) {
    const that = this;

    if (typeof tinyMCE === 'undefined' || $(rootel).closest('table').find('textarea').size() == 0) {
      $(rootel).find(`div.${this.tag_button}_add .glyph`).on('click keypress', (e) => {
        if (e.type == 'keypress' && e.key === 'Enter') {
          that.addRow(e);
        }
      });
      $(rootel).find(`div.${this.tag_button}_remove .glyph`).on('click keypress', (e) => {
        if (e.type == 'keypress' && e.key === 'Enter') {
          that.removeRow(e);
        }
      });
      $(rootel).find(`div.${this.tag_button}_up .glyph`).on('click keypress', (e) => {
        if (e.type == 'keypress' && e.key === 'Enter') {
          that.moveRowUp(e);
        }
      });
      $(rootel).find(`div.${this.tag_button}_down .glyph`).on('click keypress', (e) => {
        if (e.type == 'keypress' && e.key === 'Enter') {
          that.moveRowDown(e);
        }
      });
    } else {
      $(rootel).find(`div.${this.tag_button}_add .glyph`).on('click keypress', (e) => {
        if (e.type == 'keypress' && e.key === 'Enter') {
          that.onClickHandler('add', e);
        }
      });
      $(rootel).find(`div.${this.tag_button}_remove .glyph`).on('click keypress', (e) => {
        if (e.type == 'keypress' && e.key === 'Enter') {
          that.onClickHandler('remove', e);
        }
      });
      $(rootel).find(`div.${this.tag_button}_up .glyph`).on('click keypress', (e) => {
        if (e.type == 'keypress' && e.key === 'Enter') {
          that.onClickHandler('up', e);
        }
      });
      $(rootel).find(`div.${this.tag_button}_down .glyph`).on('click keypress', (e) => {
        if (e.type == 'keypress' && e.key === 'Enter') {
          that.onClickHandler('down', e);
        }
      });
    }
  },
};

$(document).ready(() => {
  const ilSingleChoiceWizardInput = $.extend({}, AnswerWizardInput, ilSingleChoiceWizardInputTemplate);
  ilSingleChoiceWizardInput.init();
});
