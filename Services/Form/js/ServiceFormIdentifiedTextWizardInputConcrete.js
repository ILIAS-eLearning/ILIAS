ilIdentifiedTextWizardInputConcrete = {
	
	// css selector
	tag_container: '.ilWzdContainerText',
	
	// array of css selectors
	getReindexSelectors: function()
	{
		return ['input:text', 'input:checkbox', 'button'];
	},
	
	// prepare a cloned row for use as new one
	handleRowCleanUp: function(row)
	{
		$(row).find('input:text').val('');
	}
};