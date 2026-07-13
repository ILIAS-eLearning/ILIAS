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

var ilMultipleChoiceWizardInputTemplate = {

	tag_container: 'tbody.mcwzd',
	tag_row: 'tr.mcwzd',
	tag_button: 'multiplechoice',

	getRowFromEvent: function(e) {
		return $(e.target).closest(this.tag_row);
	},

	getContainerFromEvent: function(e) {
		return $(e.target).closest(this.tag_container);
	},

	cleanRow: function(row) {
		$(row).find('input:text').val('');
		$(row).find('textarea').val('');
		$(row).find('div.imagepresentation').remove();
	},

	reindexRows: function(tbody) {
		var that = this;
		var rowindex = 0;

		// process all rows
		$(tbody).find(that.tag_row).each(function() {

			// hidden
			$(this).find('input:hidden[name*="[imagename]"]').each(function() {
				that.handleId(this, 'name', rowindex);
			});

			// hidden answer id
			$(this).find('input:hidden[name*="[answer_id]"]').each(function() {
				that.handleId(this, 'name', rowindex);
			});

			// answer
			$(this).find('input:text[id*="[answer]"]').each(function() {
				that.handleId(this, 'name', rowindex);
				that.handleId(this, 'id', rowindex);
			});

			$(this).find('textarea[id*="[answer]"]').each(function() {
				that.handleId(this, 'name', rowindex);
				that.handleId(this, 'id', rowindex);
			});

			// points
			$(this).find('input:text[id*="[points]"]').each(function() {
				that.handleId(this, 'name', rowindex);
				that.handleId(this, 'id', rowindex);
			});

			// points unchecked
			$(this).find('input:text[id*="[points_unchecked]"]').each(function() {
				that.handleId(this, 'name', rowindex);
				that.handleId(this, 'id', rowindex);
			});

			// fileupload
			$(this).find('input:file[id*="[image]"]').each(function() {
				that.handleId(this, 'id', rowindex);
				that.handleId(this, 'name', rowindex);
			});

			// submit upload
			$(this).find('input:submit[name*="[uploadchoice]"]').each(function() {
				that.handleId(this, 'name', rowindex);
			});

			// delete image button
			$(this).find('input:submit[name*="[removeimagechoice]"]').each(function() {
				that.handleId(this, 'name', rowindex);
			});

			// button
			$(this).find('button').each(function() {
				that.handleId($(this).parent(), 'id', rowindex);
			});

			rowindex++;
		});
	},

	initEvents: function(rootel) {
		var that = this;

		if (typeof tinyMCE === 'undefined' || $(rootel).closest('table').find('textarea').length === 0) {
			$(rootel).find('div.' + this.tag_button + '_add').off('click');
			$(rootel).find('div.' + this.tag_button + '_add .btn').click(function(e) {
				e.preventDefault();
				that.addRow(e);
			});
			$(rootel).find('div.' + this.tag_button + '_remove').off('click');
			$(rootel).find('div.' + this.tag_button + '_remove .btn').click((e) => {
				e.preventDefault();
				that.removeRow(e);
			});
			$(rootel).find('div.' + this.tag_button + '_up').off('click');
			$(rootel).find('div.' + this.tag_button + '_up .btn').click((e) => {
				e.preventDefault();
				that.moveRowUp(e);
			});
			$(rootel).find('div.' + this.tag_button + '_down').off('click');
			$(rootel).find('div.' + this.tag_button + '_down .btn').click((e) => {
				e.preventDefault();
				that.moveRowDown(e);
			});
		} else {
			$(rootel).find('div.' + this.tag_button + '_add').off('click');
			$(rootel).find('div.' + this.tag_button + '_add .btn').click((e) => {
				e.preventDefault();
				that.onClickHandler('add', e);
			});
			$(rootel).find('div.' + this.tag_button + '_remove').off('click');
			$(rootel).find('div.' + this.tag_button + '_remove .btn').click((e) => {
				e.preventDefault();
				that.onClickHandler('remove', e);
			});
			$(rootel).find('div.' + this.tag_button + '_up').off('click');
			$(rootel).find('div.' + this.tag_button + '_up .btn').click((e) => {
				e.preventDefault();
				that.onClickHandler('up', e);
			});
			$(rootel).find('div.' + this.tag_button + '_down').off('click');
			$(rootel).find('div.' + this.tag_button + '_down .btn').click((e) => {
				e.preventDefault();
				that.onClickHandler('down', e);
			});
		}
	}
};

$(document).ready(function() {
	var ilMultipleChoiceWizardInput = $.extend({}, AnswerWizardInput, ilMultipleChoiceWizardInputTemplate);
	ilMultipleChoiceWizardInput.init();
});