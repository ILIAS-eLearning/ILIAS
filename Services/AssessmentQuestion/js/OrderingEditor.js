$(document).ready(function() {
	$('.js_list').sortable({
		placeholder: "placeholder",
		start: function(e, ui){
	        ui.placeholder.height(ui.item.height());
	        ui.placeholder.width(ui.item.width());
	    },
	    stop: function() {
	    	let items = $(this).find('[data-id]');
	    	
	    	let ids = [];
	    	items.each(function() {
	    		ids.push($(this).attr('data-id'));
	    	});
	    	
	    	$(this).siblings('input').val(ids.join(','));
	    }
	});
});