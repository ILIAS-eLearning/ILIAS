$().ready(function() {
	$('div.errortext a').on('click', function(e) {
		var $elm = $(this);

		$elm.toggleClass('sel');

		var context  = $elm.closest('.errortext');
		var selected = [];
		context.find('a').each(function(i) {
			if ($(this).hasClass('sel')) {
				selected.push(i);
			}
		});
		context.find('input[type=hidden]').val(selected.join(','));

		e.preventDefault();
		e.stopPropagation();
	});
});