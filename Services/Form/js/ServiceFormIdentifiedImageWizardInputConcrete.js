ilIdentifiedImageWizardInputConcrete = {
	
	// css selector
	tag_container: '.ilWzdContainerImage',
	
	// array of css selectors
	getReindexSelectors: function() {
		return [
			'input:hidden[name*="[count]"]', 'input:hidden[name*="[imagename]"]', 'input:file[id*="__image__"]',
			'input:submit[name*="[uploadanswers]"]', 'input:submit[name*="[removeimageanswers]"]', 'button'
		];
	},
	
	// prepare a cloned row for use as new one
	handleRowCleanUp: function(row)
	{
		$(row).find('div.imagepresentation').remove();
	}
	
};