/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

il.ExcManagement = {
	ajax_url: '',
	
	init: function (url) {
		this.ajax_url = url;		
		
		$('form[id*="form_excasscomm_"]').submit(function(event) {
			var form_id = $(this).attr("id");
			var form_id_parts = form_id.split("_");
			var ass_id = form_id_parts[2];
			var member_id = form_id_parts[3];					
			var modal_id = form_id_parts[1] + "_" + form_id_parts[2] + "_" + form_id_parts[3];			
			if(ass_id && member_id)	{

				$("#" + modal_id).modal("hide");

				var comment = $('#lcomment_'+ass_id+'_'+member_id).val();

				$.ajax({
					url: il.ExcManagement.ajax_url,
					dataType: 'json',
					type: 'POST',
					data: {
						ass_id: ass_id,
						mem_id: member_id,
						comm: comment
					},
					success: function (response) {		
						$("#"+form_id.substr(5)+"_snip").html(response.snippet);

					}
				}).fail(function() {

				});
			}			

			event.preventDefault();
		});
	},
	
	showComment: function (id) {
		$("#" + id).modal('show');
		return false;	
	}
}