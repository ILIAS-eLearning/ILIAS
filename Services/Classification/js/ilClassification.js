il.Classification = {		
	ajax_block_id: "",
	ajax_block_url: "",
	ajax_content_id: "",
	ajax_content_url: "",
	setAjax: function(block_id, block_url, content_id, content_url, tabs_html) {
		this.ajax_block_id = block_id;
		this.ajax_block_url = block_url;
		this.ajax_content_id = content_id;
		this.ajax_content_url = content_url;
		this.tabs_html = tabs_html;
		
		$(document).on('il_classification_redraw',  this.redraw);
	},
	toggle: function(args) {
		this.loader(this.ajax_block_id + '_loader');	
		this.loader(this.ajax_content_id);
		if (args.event) {
			event.preventDefault();
			event.stopPropagation();
		}
		il.Util.sendAjaxGetRequestToUrl(this.ajax_block_url, args, {el_id: this.ajax_block_id, content_url: this.ajax_content_url, content_id: this.ajax_content_id}, this.toggleReload)			
	},
	toggleReload: function(o) {				
		$('#' + o.argument.el_id).html(o.responseText);							
		il.Util.sendAjaxGetRequestToUrl(o.argument.content_url, {}, {el_id: o.argument.content_id}, il.Classification.toggleReloadRender);		
	},
	toggleReloadRender: function(o) {	
		if(o.responseText !== "")
		{
			$('#ilSubTab').remove();
			$('#ilTab').remove();
			$('#mainscrolldiv .ilTabsContentOuter').before(il.Classification.tabs_html);
			$('#' + o.argument.el_id).html(o.responseText);			
		}
		else
		{
			// reload parent container (object list)
			location.reload();
		}
	},
	redraw: function() {	
		il.Util.ajaxReplaceInner(il.Classification.ajax_block_url + '&rdrw=1', il.Classification.ajax_block_id);
	},
	loader: function(element_id) {
		var loadergif = document.createElement('img');
		loadergif.src = "./templates/default/images/loader.svg";
		loadergif.border = 0;
		$(loadergif).css("position", "absolute");	
		$('#' + element_id).html(loadergif);
	},

	returnToParent: function() {
		this.loader(this.ajax_block_id + '_loader');
		document.location.reload();
	}

}