/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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