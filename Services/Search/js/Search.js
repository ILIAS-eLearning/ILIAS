
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

// utility functions
il.Search = {
	search_options: false,
	
	// init
	init: function() {
		il.Search.search_options = $("#search_options");
		if (il.Search.search_options) {
			il.Overlay.add("search_options", {});
			il.Overlay.addTrigger("search_options_tr", "click", "search_options", "search_options_tr", false, 'tl', 'bl');
			il.Overlay.hide(null, "search_options");
			il.Search.syncOptions();
			$('input[name=combination]').change(function () {
				il.Search.syncOptions();
				});
			$('input[name=type]').change(function () {
				il.Search.syncOptions();
				});
		}
	},
	
	// sync options set in form with options status bar
	syncOptions: function() {
		var comb = $('input[name=combination]:checked').val();
		$('#sop_combination').html($('label[for=combination_' + comb + ']').html());
		var type = $('input[name=type]:checked').val();
		$('#sop_type').html($('label[for=type_' + type + ']').html());
		var area = $('a[name=area_anchor]').html();
		$('#sop_area').html(area);
	}
}
il.Util.addOnLoad(il.Search.init);
