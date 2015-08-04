/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

il.ExcPeerReview = {
	ajax_url: '',
	
	setAjax: function (url) {
		this.ajax_url = url;
	},

	saveCrit: function (node, rating_peer_id, rating_crit_id, value) {			
		$.ajax({
			url: this.ajax_url,
			dataType: 'text',
			type: 'POST',
			data: {
				peer_id: rating_peer_id,
				crit_id: rating_crit_id,
				value: value
			}			
		}).done(function(data) {			
			 $(node).closest("div.crit_widget").html(data);
		});
	}		
};