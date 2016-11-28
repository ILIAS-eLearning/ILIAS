var ilIdentifiedWizardInputExtend = {
	
	tag_row: '.ilWzdRow', // css selector
	tag_button: 'ilWzdBtn', // css classname prefix
	
	newRowKey: -1,
	
	getNewRowKey: function()
	{
		return this.newRowKey--;
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
		var newKey = this.getNewRowKey();
		
		$(row).find('input').each(
			function(pos, input)
			{
				var oldPostVar = $(input).attr('name');
				
				if( !oldPostVar )
				{
					return;
				}
				
				var regMatch = $(input).attr('name').match(
					/^(\w+[\w|\-]*.*\[)([\-|\w]+)(\]\[\d+\])$/
				);
				
				if(!regMatch)
				{
					return;
				}
				
				$(input).attr('name', regMatch[1] + newKey + regMatch[3]);
			}
		);
	},
	
	reindexRows: function(container) {
		
		var wizard = this;
		var rowindex = 0;
		
		$(container).find(wizard.tag_row).each(function() {
			
			var item = this;

			$(wizard.getReindexSelectors()).each(function(pos, selector) {
				
				$(item).find(selector).each(function() {
					wizard.fixAttributeIndex(this, 'id', rowindex);
					wizard.fixAttributeIndex(this, 'name', rowindex);
				});
			});
			
			rowindex++;
		});
	},
	
	fixAttributeIndex: function(el, attr, new_idx)
	{
		if( $(el).attr(attr) && $(el).attr(attr).length )
		{
			if( attr == 'id' )
			{
				this.handleUnderlinedId(el, attr, new_idx);
			}
			else if( attr == 'name' )
			{
				this.handleId(el, attr, new_idx);
			}
		}
	},
	
	handleUnderlinedId: function(el, attr, new_idx)
	{
		var regMatch = $(el).attr(attr).match(
			/^(.*__[\-|\w]+____)(\d+)(__)$/
		);
		
		if(regMatch)
		{
			$(el).attr(attr, regMatch[1] + new_idx + regMatch[3]);
		}
	}
};
