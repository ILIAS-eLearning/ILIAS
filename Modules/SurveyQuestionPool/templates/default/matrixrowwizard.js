var ilMatrixRowWizardInput = {
	
	init: function() {			
		this.initEvents($('tr.mtxwzd').parent());
	},
	
	initEvents: function(rootel) {			
		$(rootel).find('button.matrixrowwizard_add').click(function(e) {
			ilMatrixRowWizardInput.addRow(e);
		});	
		$(rootel).find('button.matrixrowwizard_remove').click(function(e) {
			ilMatrixRowWizardInput.removeRow(e);
		});	
		$(rootel).find('button.matrixrowwizard_up').click(function(e) {
			ilMatrixRowWizardInput.moveRowUp(e);
		});	
		$(rootel).find('button.matrixrowwizard_down').click(function(e) {
			ilMatrixRowWizardInput.moveRowDown(e);
		});			
	},
	
	addRow: function(e) {				
		// clone row
		var source = $(e.target).parents('tr');				
		var target = $(source).clone();		
		
		// add events
		this.initEvents(target);
		
		// empty inputs
		this.cleanRow(target);
		
		$(source).after(target);	
					
		this.reindexRows($(e.target).parents('tbody'));		
	},
	
	removeRow: function(e) {		
		var source = $(e.target).parents('tr');			
		var tbody = $(e.target).parents('tbody');
		
		// do not remove last row
		if($(tbody).find('tr').size() > 1) {
			$(source).remove();
		}
		// reset last remaining row
		else {
			this.cleanRow(source);
		}
			
		this.reindexRows(tbody);		
	},
	
	moveRowUp: function(e) {		
		var source = $(e.target).parents('tr');					
		var prev = $(source).prev();
		if(prev[0])
		{
			$(prev).before(source);
		}		
	},
	
	moveRowDown: function(e) {		
		var source = $(e.target).parents('tr');		
		var next = $(source).next();
		if(next[0])
		{
			$(next).after(source);
		}
	},
	
	cleanRow: function(row) {
		$(row).find('input:text').attr('value', '');
		$(row).find('input:checkbox').prop('checked', false);
	},
	
	reindexRows: function(tbody) {		
		var postvar = $(tbody).parents('div').attr('id');
		var rowindex = 0;
	
		// process all rows
		$(tbody).find('tr').each(function() {
			
			// answer
			$(this).find('input:text[id*="[answer]"]').each(function() {				
				$(this).attr('id', postvar + '[answer][' + rowindex + ']');
				$(this).attr('name', postvar + '[answer][' + rowindex + ']');								
			});
			
			// scale
			$(this).find('input:text[id*="[label]"]').each(function() {				
				$(this).attr('id', postvar + '[label][' + rowindex + ']');
				$(this).attr('name', postvar + '[label][' + rowindex + ']');											
			});
			
			// other
			$(this).find('input:checkbox').each(function() {				
				$(this).attr('id', postvar + '[other][' + rowindex + ']');
				$(this).attr('name', postvar + '[other][' + rowindex + ']');												
			});
								
			rowindex++;
		});				
	}
};

$(document).ready(function() {
	ilMatrixRowWizardInput.init();
});