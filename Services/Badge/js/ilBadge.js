il.Badge = {
	
	url: "",

	setUrl: function(url) {
		il.Badge.url = url;
	},

	publish: function(id) {				
		il.Util.sendAjaxGetRequestToUrl(il.Badge.url, {id: id}, {}, this.prepared);
		return false;
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
					il.Badge.publishMulti([url]);									
				}														
			}
		}
	},
	
	publishMulti: function(urls) {		
		// console.log(urls);
		
		OpenBadges.issue(urls, function(errors, successes) {												
			// console.log(errors);
			// console.log(successes);

			/* see https://github.com/mozilla/openbadges-backpack/wiki/using-the-issuer-api
			DENIED - The user denied permission to add the badge.
			EXISTS - The badge is already in the earner's backpack.
			INACCESSIBLE - The assertion provided could not be retrieved.
				e.g. The assertion URL itself may be malformed, or attempting to access the 
				assertion may have resulted in 404 Not Found or 403 Forbidden.
			MALFORMED - The assertion URL provided exists but was malformed.
			INVALID - The assertion URL provided exists and is well-formed, but is not valid.
				e.g. The recipient of the assertion may not be the currently logged-in user.
			*/

		});
	}
}