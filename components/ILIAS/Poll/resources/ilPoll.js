ilPoll = {	
	redraw_url: "",				
	setUrl: function(url)
	{
		this.redraw_url = url;
	},
	redrawComments: function(ref_id)
	{
		var url = this.redraw_url+"&poll_id="+ref_id;
		il.Util.ajaxReplaceInner(url, "poll_comments_counter_"+ref_id);
	}
};