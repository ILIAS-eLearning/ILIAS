let shape_click = function(e) {
	let shape = $(this);
	
	if (shape.hasClass('multiple_choice')) {
		shape.toggleClass('selected');
	}
	else {
		shape.parents('.imagemap_editor').find('svg .selected').removeClass('selected');
		shape.addClass('selected');
	}
	
	let selected = [];
	
	shape.parents('.imagemap_editor').find('svg .selected').each(function(index, item) {
		selected.push($(item).attr('data-value'));
	});
	
	shape.parents('svg').siblings('input[type="hidden"]').val(selected.join(','));
}

$(document).on("click", ".imagemap_editor rect, .imagemap_editor ellipse, .imagemap_editor polygon", shape_click);