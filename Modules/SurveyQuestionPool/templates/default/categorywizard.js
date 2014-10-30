var ilCategoryWizardInputTemplate = {
	
	tag_container: 'tbody.catwzd',
	tag_row: 'tr.catwzd',
	tag_button: 'categorywizard',
	
	getRowFromEvent: function(e) {
		return $(e.target).parents(this.tag_row);
	},
	
	getContainerFromEvent: function(e) {
		return $(e.target).parents(this.tag_container);
	},	
	
	cleanRow: function(row) {
		$(row).find('input:text').attr('value', '');
		$(row).find('input:checkbox').prop('checked', false);
	},
	
	reindexRows: function(container) {				
		var that = this;		
		var rowindex = 0;
		var maxscale = 0;
		
		// process all rows
		$(container).find(this.tag_row).each(function() {
								
			// answer
			$(this).find('input:text[id*="[answer]"]').each(function() {					
				that.handleId(this, 'id', rowindex);
				that.handleId(this, 'name', rowindex);											
			});
			
			// scale
			$(this).find('input:text[id*="[scale]"]').each(function() {		
				that.handleId(this, 'id', rowindex);
				that.handleId(this, 'name', rowindex);			
				
				// find current max scale
				var value = $(this).attr('value');
				if (!isNaN(value) && parseInt(value) > maxscale) {
					maxscale = parseInt(value);
				}
			});
			
			// other
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
		
		// redo scale values
		$(container).find('input:text[id*="[scale]"]').each(function() {	
			var value = $(this).attr('value');		
			if(isNaN(value) || value === '') {
				maxscale++;				
				$(this).attr('value', maxscale);
			}		
		});			
		
		// fix neutral
		var postvar = $(container).parents('div').attr('id');
		var neutral = $('#' + postvar + '_neutral_scale').attr('value');
		if (neutral !== null)
		{
			if (parseInt(neutral) <= maxscale) {
				$('#' + postvar + '_neutral_scale').attr('value', maxscale+1);
			}
		}
	}
};

$(document).ready(function() {
	var ilCategoryWizardInput = $.extend({}, ilCategoryWizardInputTemplate, ilWizardInput);
	ilCategoryWizardInput.init();
});