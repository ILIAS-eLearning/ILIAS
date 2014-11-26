var ilTextWizardInputTemplate = {
		
	tag_container: 'div.txtwzd',
	tag_row: 'div.wzdrow',
	tag_button: 'textwizard',
	
	getRowFromEvent: function(e) {
		return $(e.target).closest(this.tag_row);
	},
	
	getContainerFromEvent: function(e) {
		return $(e.target).closest(this.tag_container);
	},
			
	cleanRow: function(row) {
		$(row).find('input:text').attr('value', '');		
		$(row).find('input:checkbox').prop('checked', ''); // ilSelectBuilderInputGUI
	},
		
	reindexRows: function(rootel) {					
		var that = this;
		var rowindex = 0;
	
		// process all rows
		$(rootel).find(this.tag_row).each(function() {
			
			// text
			$(this).find('input:text').each(function() {					
				that.handleId(this, 'id', rowindex);
				that.handleId(this, 'name', rowindex);							
			});
			
			// checkbox (ilSelectBuilderInputGUI)
			$(this).find('input:checkbox').each(function() {					
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
	var ilTextWizardInput = $.extend({}, ilTextWizardInputTemplate, ilWizardInput);
	ilTextWizardInput.init();
});