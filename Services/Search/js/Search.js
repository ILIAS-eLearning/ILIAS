
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

// utility functions
il.Search = {
	search_options: false,
	search_area_form: false,
	search_filter_by_type_off: '',
	search_filter_by_cd_off: '',
	
	// init
	init: function() {
		il.Search.search_filter_by_type_off = $('#sop_type').html();
		il.Search.search_filter_by_cd_off = $('#sop_cd').html();
		il.Search.search_options = $("#search_options");
		if (il.Search.search_options) {
			il.Overlay.add("search_options", {});
			il.Overlay.addTrigger("search_options_tr", "click", "search_options", "search_options_tr", false, 'tl', 'bl');
			il.Overlay.addTrigger("search_options_tr", "keypress", "search_options", "search_options_tr", false, 'tl', 'bl');
			il.Overlay.hide(null, "search_options");
			il.Search.syncOptions();
		}
		il.Search.search_area_form = $("#search_area_form");
		if (il.Search.search_area_form) {
			il.Overlay.add("search_area_form", {});
			il.Overlay.addTrigger("search_area_form_tr", "click", "search_area_form", "search_area_form_tr", false, 'tl', 'bl');
			il.Overlay.addTrigger("search_area_form_tr", "keypress", "search_area_form", "search_area_form_tr", false, 'tl', 'bl');
			il.Overlay.hide(null, "search_area_form");
			il.Search.syncOptions();
			/*$('input[name=combination]').change(function () {
				il.Search.syncOptions();
				});*/
		}
		il.Search.search_cdate_form = $("#search_cdate_form");
		if (il.Search.search_cdate_form) {
			il.Overlay.add("search_cdate_form", {});
			il.Overlay.addTrigger("search_cdate_form_tr", "click", "search_cdate_form", "search_cdate_form_tr", false, 'tl', 'bl');
			il.Overlay.addTrigger("search_cdate_form_tr", "keypress", "search_cdate_form", "search_cdate_form_tr", false, 'tl', 'bl');
			il.Overlay.hide(null, "search_cdate_form");
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
		if (type == "1") 
		{
			$('#sop_type').html(il.Search.search_filter_by_type_off);
		} 
		else if(type == "2")
		{
			$('#sop_type').html('<b>On</b>');
		}
		else 
		{
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
				$('#sop_type').html('<b>' + tstr + '</b>');
			}
		}
		
		// cdates
		ctype = $('input[name=screation]').is(':checked');

		$('#sop_cd_on').hide();
		$('#sop_cd_off').hide();
		if(!ctype) {
			$('#sop_cd').html($('#sop_cd_off').html());
		}
		else {
			$('#sop_cd').html($('#sop_cd_on').html());
		}
		
		var area = $('a[name=area_anchor]').html();
		var area_id = $('#area').val();
		
		if((area_id == '') || (area_id == 1))
		{
			$('#sop_area').html(area);
		}
		else
		{
			$('#sop_area').html('<b>' + area + '</b>');
		}

		
		
	}
}
il.Util.addOnLoad(il.Search.init);
