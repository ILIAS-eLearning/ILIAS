(function($) {
	$(document).on('shown.bs.dropdown', function(event) {
		var dropdown = $(event.target);
		dropdown.find('.dropdown-toggle').attr('aria-expanded', true);
		il.UI.page.fit(dropdown.find('.dropdown-menu'));
	});

	// on close
	$(document).on('hidden.bs.dropdown', function(event) {
		var dropdown = $(event.target);
		dropdown.find('.dropdown-toggle').attr('aria-expanded', false);
	});
})($);