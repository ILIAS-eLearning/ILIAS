$().ready(function() {
	$('div.errortext a').on('click', function(e) {
		var $elm = $(this);

		if($elm.hasClass('ilc_qetitem_ErrorTextItem'))
		{
            $elm.removeClass('ilc_qetitem_ErrorTextItem');
            $elm.addClass('ilc_qetitem_ErrorTextSelected');
		}
		else if($elm.hasClass('ilc_qetitem_ErrorTextSelected'))
		{
            $elm.removeClass('ilc_qetitem_ErrorTextSelected');
            $elm.addClass('ilc_qetitem_ErrorTextItem');
			
		}

		var context  = $elm.closest('.errortext');
		var selected = [];
		context.find('a').each(function(i) {
			if ($(this).hasClass('ilc_qetitem_ErrorTextSelected')) {
				selected.push(i);
			}
		});
		context.find('input[type=hidden]').val(selected.join(','));

		e.preventDefault();
		e.stopPropagation();
	});
});