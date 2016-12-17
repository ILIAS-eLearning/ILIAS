var ilIdentifiedWizardInputExtend = {
	
	tag_row: '.ilWzdRow', // css selector
	tag_button: 'ilWzdBtn', // css classname prefix
	
	newRowKeySequence: null,
	newRowKeyStartValue: -1,
	newRowKeyValueInterval: -1,
	newRowKeyValuePrefix: 'IDENTIFIER~',
	
	getNewRowKey: function()
	{
		return this.getNextRowKey();
	},
	
	getNextRowKey: function()
	{
		if( typeof this.newRowKeySequence === null )
		{
			this.newRowKeySequence = this.newRowKeyStartValue;
		}
		else
		{
			this.newRowKeySequence += this.newRowKeyValueInterval;
		}
		
		return this.newRowKeySequence;
	},
	
	getRowFromEvent: function(e)
	{
		return $(e.target).closest(this.tag_row);
	},
	
	getContainerFromEvent: function(e)
	{
		return $(e.target).closest(this.tag_container);
	},
	
	cleanRow: function(row)
	{
		this.assignNewRowKey(row);
		this.handleRowCleanUp(row);
	},
	
	assignNewRowKey: function(row) // addition
	{
		var wizard = this;
		
		var reg = "^(.*"+wizard.newRowKeyValuePrefix+"?)([\\-|\\w]+)((\\]|__)(\\[|__)\\d+(\\]|__))$";
		var newKey = wizard.getNewRowKey();
		
		$(row).find('input').each(
			function(pos, input)
			{
				wizard.replaceRowKey(input, 'name', reg, newKey);
				wizard.replaceRowKey(input, 'id', reg, newKey);
			}
		);
	},
	
	replaceRowKey: function(input, attr, reg, newKey)
	{
		if( $(input).attr(attr) )
		{
			var regMatch = $(input).attr(attr).match(reg);
			
			if(regMatch)
			{
				$(input).attr(attr, regMatch[1] + newKey + regMatch[3]);
			}
		}
	},
	
	reindexRows: function(container) {
		
		var wizard = this;
		var rowindex = 0;
		
		var that = this;
		
		$(container).find(wizard.tag_row).each(function() {
			
			var item = this;

			$(wizard.getReindexSelectors()).each(function(pos, selector) {
				
				$(item).find(selector).each(function() {
					wizard.fixAttributeIndex(this, 'id', rowindex, wizard);
					wizard.fixAttributeIndex(this, 'name', rowindex, wizard);
				});
			});
			
			rowindex++;
		});
	},

	fixAttributeIndex: function(el, attr, new_idx, wizard)
	{
		if( $(el).attr(attr) && $(el).attr(attr).length )
		{
			if( attr == 'id' )
			{
				this.handleUnderlinedId(el, attr, new_idx, wizard);
			}
			else if( attr == 'name' )
			{
				this.handleId(el, attr, new_idx);
			}
		}
	},
	
	handleUnderlinedId: function(el, attr, new_idx, wizard)
	{
		var reg = "^(.*__"+wizard.newRowKeyValuePrefix+"[\\-|\\w]+____)(\\d+)(__)$";
		
		var regMatch = $(el).attr(attr).match(reg);

		if(regMatch)
		{
			$(el).attr(attr, regMatch[1] + new_idx + regMatch[3]);
		}
	}
};
