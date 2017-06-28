(function($) {
	$(document).on('shown.bs.dropdown', function(event) {
		var dropdown = $(event.target);

		dropdown.find('.dropdown-toggle').attr('aria-expanded', true);
	});

	// on close
	$(document).on('hidden.bs.dropdown', function(event) {
		var dropdown = $(event.target);

		dropdown.find('.dropdown-toggle').attr('aria-expanded', false);
	});
})($);