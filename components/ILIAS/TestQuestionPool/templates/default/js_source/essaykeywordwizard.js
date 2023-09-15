var ilEssayKeywordWizardInputTemplate = {

	tag_container: 'tbody.esskwzd',
	tag_row: 'tr.esskwzd',
	tag_button: 'essaykeyword',

	getRowFromEvent: function(e) {
		return $(e.target).closest(this.tag_row);
	},

	getContainerFromEvent: function(e) {
		return $(e.target).closest(this.tag_container);
	},

	cleanRow: function(row) {
		$(row).find('input:text').attr('value', '');
	},

	reindexRows: function(tbody) {
		var that = this;
		var rowindex = 0;

		// process all rows
		$(tbody).find(this.tag_row).each(function() {

			// answer
			$(this).find('input:text[id*="[answer]"]').each(function() {
				that.handleId(this, 'id', rowindex);
				that.handleId(this, 'name', rowindex);
			});

			// points
			$(this).find('input:text[id*="[points]"]').each(function() {
				that.handleId(this, 'id', rowindex);
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
	var ilEssayKeywordWizardInput = $.extend({}, ilEssayKeywordWizardInputTemplate, ilWizardInput);
	ilEssayKeywordWizardInput.init();
});