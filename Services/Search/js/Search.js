
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

// utility functions
il.Search = {
	search_options: false,
	
	// init
	init: function() {
		this.search_options = $("#search_options");
		if (this.search_options) {
			il.Overlay.add("search_options", {});
			il.Overlay.addTrigger("search_options_tr", "click", "search_options", "search_options_tr", false, 'tl', 'bl');
			il.Overlay.hide(null, "search_options");
		}
	}
}
il.Util.addOnLoad(il.Search.init);
