var ilMatchingPairWizardInputTemplate = {

	tag_container: 'tbody.mpwzd',
	tag_row: 'tr.mpwzd',
	tag_button: 'matchingpair',

	getRowFromEvent: function(e) {
		return $(e.target).closest(this.tag_row);
	},

	getContainerFromEvent: function(e) {
		return $(e.target).closest(this.tag_container);
	},

	cleanRow: function(row) {
		$(row).find('input:text').attr('value', '');
		$(row).find('select').prop('selectedIndex', 0);
	},

	reindexRows: function(tbody) {
		var that = this;
		var rowindex = 0;

		// process all rows
		$(tbody).find(this.tag_row).each(function() {

			// definition
			$(this).find('select[name*="[definition]"]').each(function() {
				that.handleId(this, 'name', rowindex);
			});

			// term
			$(this).find('select[name*="[term]"]').each(function() {
				that.handleId(this, 'name', rowindex);
			});

			// points
			$(this).find('input:text[name*="[points]"]').each(function() {
				that.handleId(this, 'name', rowindex);
			});

			// button
			$(this).find('div.btn.btn-link').each(function() {
				that.handleId(this, 'id', rowindex);
			});

			rowindex++;
		});
	}
};

$(document).ready(function() {
	var ilMatchingPairWizardInput = $.extend({}, ilMatchingPairWizardInputTemplate, AnswerWizardInput);
	ilMatchingPairWizardInput.init();
});