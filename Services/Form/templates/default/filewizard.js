var ilFileWizardInputTemplate = {
		
	tag_container: 'div.wzdcnt',
	tag_row: 'div.wzdrow',
	tag_button: 'imagewizard',
	
	getRowFromEvent: function(e) {
		return $(e.target).parent(this.tag_row);
	},
	
	getContainerFromEvent: function(e) {
		return $(e.target).parents(this.tag_container);
	},
			
	cleanRow: function(row) {
		$(row).find('input:file').attr('value', '');
		$(row).find('input[type=hidden]').remove();
		$(row).find('img').remove();
	},
		
	reindexRows: function(rootel) {					
		var that = this;
		var rowindex = 0;
	
		// process all rows
		$(rootel).find(this.tag_row).each(function() {
			
			// file
			$(this).find('input:file').each(function() {					
				that.handleId(this, 'id', rowindex);
				that.handleId(this, 'name', rowindex);							
			});
			
			// hidden
			$(this).find('input:hidden').each(function() {		
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
	var ilFileWizardInput = $.extend({}, ilFileWizardInputTemplate, ilWizardInput);
	ilFileWizardInput.init();
});