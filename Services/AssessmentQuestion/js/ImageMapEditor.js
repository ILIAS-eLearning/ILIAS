let shape_click = function(e) {
	let shape = $(this);
	
	shape.toggleClass('selected');
	
	let selected = [];
	
	shape.parents('.imagemap_editor').find('svg .selected').each(function(index, item) {
		selected.push($(item).attr('data-value'));
	});
	
	shape.parents('svg').siblings('input[type="hidden"]').val(selected.join(','));
}

$(document).on("click", ".imagemap_editor rect, .imagemap_editor circle, .imagemap_editor polygon", shape_click);