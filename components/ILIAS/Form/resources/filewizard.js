var ilFileWizardInputTemplate = {

	tag_container: 'div.wzdcnt',
	tag_row: 'div.wzdrow',
	tag_button: 'imagewizard',

	initEvents: function (rootel) {
		let that = this;
		$(rootel).find('span.' + this.tag_button + '_add').click(function (e) {
			that.addRow(e);
		});
		$(rootel).find('span.' + this.tag_button + '_remove').click(function (e) {
			that.removeRow(e);
		});
		$(rootel).find('span.' + this.tag_button + '_up').click(function (e) {
			that.moveRowUp(e);
		});
		$(rootel).find('span.' + this.tag_button + '_down').click(function (e) {
			that.moveRowDown(e);
		});
	},

	getRowFromEvent: function (e) {
		return $(e.target).closest(this.tag_row);
	},

	getContainerFromEvent: function (e) {
		return $(e.target).closest(this.tag_container);
	},

	cleanRow: function (row) {
		$(row).find('input:file').val('');
		$(row).find('input[type=hidden]').remove();
		$(row).find('img').remove();
	},

	reindexRows: function (rootel) {
		var that = this;
		var rowindex = 0;

		// process all rows
		$(rootel).find(this.tag_row).each(function () {

			// file
			$(this).find('input:file').each(function () {
				that.handleId(this, 'id', rowindex);
				that.handleId(this, 'name', rowindex);
			});

			// hidden
			$(this).find('input:hidden').each(function () {
				that.handleId(this, 'name', rowindex);
			});

			// span with glyph
			$(this).find('> span').each(function () {
				that.handleId(this, 'id', rowindex);
				that.handleId(this, 'data-name', rowindex);
			});

			rowindex++;
		});
	}
};

$(document).ready(function () {
	var ilFileWizardInput = $.extend({}, ilWizardInput, ilFileWizardInputTemplate);
	ilFileWizardInput.init();
});
