(function($) {
	$(document).on('shown.bs.dropdown', function(event) {
		var dropdown = $(event.target);
		dropdown.find('.dropdown-toggle').attr('aria-expanded', true);
		//Fit Dropdowns correctly to page, omit for legacy component, add new Item, see #30856
		il.UI.page.fit(dropdown.find('.dropdown-menu:not(#il-add-new-item-gl)'));
	});

	// on close
	$(document).on('hidden.bs.dropdown', function(event) {
		var dropdown = $(event.target);
		dropdown.find('.dropdown-toggle').attr('aria-expanded', false);
	});
})($);