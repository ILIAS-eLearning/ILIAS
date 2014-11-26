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
		$(row).find('input:text').attr('value', '');
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

			// answer
			$(this).find('input:text[id*="[answer]"]').each(function() {
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
				that.handleId(this, 'id', rowindex);
				that.handleId(this, 'name', rowindex);
			});

			rowindex++;
		});
	},

	initEvents: function(rootel) {
		var that = this;

		if (typeof tinyMCE == 'undefined' || $(rootel).closest('table').find('textarea').size() == 0) {
			$(rootel).find('button.' + this.tag_button + '_add').click(function(e) {
				that.addRow(e);
			});
			$(rootel).find('button.' + this.tag_button + '_remove').click(function(e) {
				that.removeRow(e);
			});
			$(rootel).find('button.' + this.tag_button + '_up').click(function(e) {
				that.moveRowUp(e);
			});
			$(rootel).find('button.' + this.tag_button + '_down').click(function(e) {
				that.moveRowDown(e);
			});
		} else {
			// skip the javascript functionality if tinyMCE is running
			$(rootel).find('button.' + this.tag_button + '_add').attr("type", "submit");
			$(rootel).find('button.' + this.tag_button + '_remove').attr("type", "submit");
			$(rootel).find('button.' + this.tag_button + '_up').attr("type", "submit");
			$(rootel).find('button.' + this.tag_button + '_down').attr("type", "submit");
		}
	}
};

$(document).ready(function() {
	var ilMultipleChoiceWizardInput = $.extend({}, ilWizardInput, ilMultipleChoiceWizardInputTemplate);
	ilMultipleChoiceWizardInput.init();
});