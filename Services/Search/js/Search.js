
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

// utility functions
il.Search = {
	search_options: false,
	search_area_form: false,
	search_filter_by_type_off: '',
	
	// init
	init: function() {
		il.Search.search_filter_by_type_off = $('#sop_type').html();
		il.Search.search_options = $("#search_options");
		if (il.Search.search_options) {
			il.Overlay.add("search_options", {});
			il.Overlay.addTrigger("search_options_tr", "click", "search_options", "search_options_tr", false, 'tl', 'bl');
			il.Overlay.hide(null, "search_options");
			il.Search.syncOptions();
		}
		il.Search.search_area_form = $("#search_area_form");
		if (il.Search.search_area_form) {
			il.Overlay.add("search_area_form", {});
			il.Overlay.addTrigger("search_area_form_tr", "click", "search_area_form", "search_area_form_tr", false, 'tl', 'bl');
			il.Overlay.hide(null, "search_area_form");
			il.Search.syncOptions();
			/*$('input[name=combination]').change(function () {
				il.Search.syncOptions();
				});*/
		}
		$(':checkbox').change(function () {
			il.Search.syncOptions();
			});
		$('#il_search_toolbar input').change(function () {
			il.Search.syncOptions();
			});
	},
	
	// sync options set in form with options status bar
	syncOptions: function() {
		var cb_id = '', tstr = '';
		/*var comb = $('input[name=combination]:checked').val();
		$('#sop_combination').html($('label[for=combination_' + comb + ']').html());*/
		var type = $('input[name=type]:checked').val();
		if (type) {
			$('#sop_type').html($('label[for=type_' + type + ']').html());
		} else {
			// lucene version
			type = $('input[name=item_filter_enabled]').is(':checked');
			if (!type) {
				$('#sop_type').html(il.Search.search_filter_by_type_off);
			} else {
				tstr = '';
				$('input[name^=filter_type]:checked').each(function (t) {
						if (tstr != '') {
							tstr = tstr + ", ";
						}
						tstr = tstr + $('label[for=' + this.id + ']').html();
					});
				$('#sop_type').html(tstr);
			}
		}
		var area = $('a[name=area_anchor]').html();
		$('#sop_area').html(area);
	}
}
il.Util.addOnLoad(il.Search.init);
