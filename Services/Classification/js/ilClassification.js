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
	},
	toggle: function(args) {							
		il.Util.sendAjaxGetRequestToUrl (this.ajax_block_url, args, {el_id: this.ajax_block_id, content_url: this.ajax_content_url, content_id: this.ajax_content_id}, this.toggleReload)			
	},
	toggleReload: function(o) {				
		$('#' + o.argument.el_id).html(o.responseText);				
		il.Util.ajaxReplaceInner(o.argument.content_url, o.argument.content_id);
	}	
}