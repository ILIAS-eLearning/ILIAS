var ilImageWizardInputTemplate = {

	tag_container: 'tbody.imgwzd',
	tag_row: 'tr.imgwzd',
	tag_button: 'imagewizard',

	getRowFromEvent: function(e) {
		return $(e.target).closest(this.tag_row);
	},

	getContainerFromEvent: function(e) {
		return $(e.target).closest(this.tag_container);
	},

	cleanRow: function(row) {
		$(row).find('div.imagepresentation').remove();
	},

	reindexRows: function(tbody) {
		var that = this;
		var rowindex = 0;

		// process all rows
		$(tbody).find(this.tag_row).each(function() {

			// hidden count
			$(this).find('input:hidden[name*="[count]"]').each(function() {
				that.handleId(this, 'name', rowindex);
			});

			// hidden imagename
			$(this).find('input:hidden[name*="[imagename]"]').each(function() {
				that.handleId(this, 'name', rowindex);
			});

			// fileupload
			$(this).find('input:file[id*="[image]"]').each(function() {
				that.handleId(this, 'id', rowindex);
				that.handleId(this, 'name', rowindex);
			});

			// submit upload
			$(this).find('input:submit[name*="[uploadanswers]"]').each(function() {
				that.handleId(this, 'name', rowindex);
			});

			// submit remove
			$(this).find('input:submit[name*="[removeimageanswers]"]').each(function() {
				that.handleId(this, 'name', rowindex);
			});

			// button
			$(this).find('button').each(function() {
				that.handleId(this, 'id', rowindex);
				that.handleId(this, 'name', rowindex);
			});

			rowindex++;
		});
	}
};

$(document).ready(function() {
	var ilImageWizardInput = $.extend({}, ilImageWizardInputTemplate, ilWizardInput);
	ilImageWizardInput.init();
});