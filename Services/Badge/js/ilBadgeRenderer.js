il.BadgeRenderer = {	
	
	url: "",

	init: function(url) {
		il.BadgeRenderer.url = url;
		
		// add click handler to all badges
		$('[data-id^="badge_"]').click(function() {			
			var id = $(this).data("id").substr(6);
			il.BadgeRenderer.toggleModal(id, this);
			return false;
		});		
	},
	
	toggleModal: function(id, el) {
		// get rid of badge/user id for modal
		var parts = id.split("_");		
		var modal_id = "badge_modal_" + parts[2];		
		
		// existing modal?
		var modal = $("#" + modal_id);	
		if(!modal.length)
		{		
			il.Util.sendAjaxGetRequestToUrl(il.BadgeRenderer.url, {id: id}, {element: el, modal_id: modal_id}, this.initModal);			
		}		
		else
		{
			$(modal).modal('show');
		}
	},
	
	initModal: function(o) {
		if(o.responseText !== undefined)
		{
			if(o.responseText)
			{
				// inject modal html
				$(o.argument.element).after(o.responseText);	
				
				// display the mf
				$("#" + o.argument.modal_id).modal('show');	
			}		
		}
	}
}