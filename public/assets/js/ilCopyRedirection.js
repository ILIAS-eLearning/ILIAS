il.CopyRedirection = {
	
	url: "",
	
	setRedirectUrl: function(url) {
		il.CopyRedirection.url = url;
	},
	checkDone: function() {
		
		var done = setInterval(function() {
			
			var completed = $('span[id^="progress_done_"]');
			var allCompleted = true;
			
			for(i=0;i<completed.length;i++) {
				if($('#' + completed[i].id).is(":visible") == false) {
					allCompleted = false;
				}
				console.log(completed[i].id);
			}
			if(allCompleted == true && (completed.length > 0)) {
				clearInterval(done);
				setTimeout(function() {
					$(location).attr('href', il.CopyRedirection.url);
				},3000);
			}
			
		},1000);
	}
}
