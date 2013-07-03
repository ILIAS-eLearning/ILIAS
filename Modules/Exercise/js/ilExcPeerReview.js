/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

il.ExcPeerReview = {
	ajax_url: '',
	
	setAjax: function (url) {
		this.ajax_url = url;
	},

	/**
	 * Save comments on rating redirect
	 */
	saveComments: function () {
		
		var pcomm = {};
		
		$('textarea[id*="excpr_"]').each(function() {						
			
			var parts = $(this).attr("name").split("__");
			/* var giver_id = parts[0].substr(3); */
			var peer_id = parseInt(parts[1].substr(0, parts[1].length-1));
			
			pcomm[peer_id] = $(this).val();							
		});
		
		$.ajax({
			url: this.ajax_url,
			dataType: 'text',
			type: 'POST',
			data: {
				pc: pcomm
			}			
		});
	}
};