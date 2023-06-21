var ilSingleChoiceWizardInputTemplate = {

	tag_container: 'tbody.scwzd',
	tag_row: 'tr.scwzd',
	tag_button: 'singlechoice',

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
		$(tbody).find(this.tag_row).each(function() {
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
			$(this).find('div.btn.btn-link').each(function() {
				that.handleId(this, 'id', rowindex);
			});

			rowindex++;
		});
	},

	initEvents: function(rootel) {
		var that = this;

		if (typeof tinyMCE == 'undefined' || $(rootel).closest('table').find('textarea').size() == 0) {
			$(rootel).find('div.' + this.tag_button + '_add .glyph').click(function(e) {
				that.addRow(e);
			});
			$(rootel).find('div.' + this.tag_button + '_remove .glyph').click(function(e) {
				that.removeRow(e);
			});
			$(rootel).find('div.' + this.tag_button + '_up .glyph').click(function(e) {
				that.moveRowUp(e);
			});
			$(rootel).find('div.' + this.tag_button + '_down .glyph').click(function(e) {
				that.moveRowDown(e);
			});
		} else {
			$(rootel).find('div.' + this.tag_button + '_add .glyph').click((e) => {
				that.onClickHandler('add', e);
			});
			$(rootel).find('div.' + this.tag_button + '_remove .glyph').click((e) => {
				that.onClickHandler('remove', e);
			});
			$(rootel).find('div.' + this.tag_button + '_up .glyph').click((e) => {
				that.onClickHandler('up', e);
			});
			$(rootel).find('div.' + this.tag_button + '_down .glyph').click((e) => {
				that.onClickHandler('down', e);
			});
		}
	}
};

$(document).ready(function() {
	var ilSingleChoiceWizardInput = $.extend({}, AnswerWizardInput, ilSingleChoiceWizardInputTemplate);
	ilSingleChoiceWizardInput.init();
});