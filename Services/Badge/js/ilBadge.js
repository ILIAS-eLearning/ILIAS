il.Badge = {
	
	url: "",

	setUrl: function(url) {
		self.url = url;
	},

	publish: function(id) {				
		il.Util.sendAjaxGetRequestToUrl(self.url, {id: id}, {}, this.prepared);
	},
	
	prepared: function(o) {		
		if(o.responseText !== undefined)
		{
			if(o.responseText)
			{
				var result = JSON.parse(o.responseText);
				if(result.error === false)
				{
					var url = result.url;
					console.log(url);
					
					// :TOOD:
					return;					
					OpenBadges.issue([url], function(errors, successes) {
						console.log(errors);
						console.log(successes);
					});
				}														
			}
		}
	}
}