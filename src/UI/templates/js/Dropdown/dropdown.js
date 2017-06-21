(function($) {
	$(document).on('shown.bs.dropdown', function(event) {
		var dropdown = $(event.target);

		dropdown.find('.dropdown-toggle').attr('aria-expanded', true);

		// focus first link
		setTimeout(function() {
			dropdown.find('.dropdown-menu li:first-child a').focus();
		}, 10);
	});

	// On dropdown close
	$(document).on('hidden.bs.dropdown', function(event) {
		var dropdown = $(event.target);

		dropdown.find('.dropdown-toggle').attr('aria-expanded', false);
	});
})($);