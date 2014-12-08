il.Classification = {		
	ajax_block_id: "",
	ajax_block_url: "",
	ajax_content_id: "",
	ajax_content_url: "",
	setAjax: function(block_id, block_url, content_id, content_url) {
		this.ajax_block_id = block_id;
		this.ajax_block_url = block_url;
		this.ajax_content_id = content_id;
		this.ajax_content_url = content_url;
		
		$(document).bind('il_classification_redraw',  this.redraw);
	},
	toggle: function(args) {
		console.log(this);
		console.log(this.ajax_block_url);
		this.loader(this.ajax_block_id + '_loader');	
		this.loader(this.ajax_content_id);		
		il.Util.sendAjaxGetRequestToUrl(this.ajax_block_url, args, {el_id: this.ajax_block_id, content_url: this.ajax_content_url, content_id: this.ajax_content_id}, this.toggleReload)			
	},
	toggleReload: function(o) {				
		$('#' + o.argument.el_id).html(o.responseText);							
		il.Util.sendAjaxGetRequestToUrl(o.argument.content_url, {}, {el_id: o.argument.content_id}, il.Classification.toggleReloadRender);		
	},
	toggleReloadRender: function(o) {	
		if(o.responseText !== "")
		{			
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
	}
}