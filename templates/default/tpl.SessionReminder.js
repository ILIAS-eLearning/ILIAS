<script type="text/javascript">
/* <![CDATA[ */
ilAddOnLoad(
	function()
	{
		//console.log('Start countdown: ' + {ILIAS_SESSION_COUNTDOWN} / 1000 + ' seconds ...');
		window.setTimeout('countdown();', {ILIAS_SESSION_COUNTDOWN});
	}
);

String.prototype.sprintfString = function() {
	if (arguments.length < 1) {
		return;
	}
	
	var value = this.valueOf();	
	for (var k = 0; k < arguments.length; k++) {
		switch (typeof(arguments[k])) {
			case 'string':
				value =  value.replace(/%s/, arguments[k]);
			    break;

			default:
				break;
		}
	}

	return new String(value);
};

// Session Checker
var ilSessionCheckerHandler = function(o)
{	
	if(o.responseText !== undefined) 
	{
		//console.log('AJAX response ...');
		//console.log(o.responseText);
		var response = YAHOO.lang.JSON.parse(o.responseText);
		//console.log(response);
		if(response.remind)
		{
			var extend = confirm('{CONFIRM_TXT}'.sprintfString(response.expiresInTimeX, response.currentTime));
			if(extend == true)
			{	
				var t0 = (new Date().getTime());
				new ilSessionExtender('{ILIAS_SESSION_EXTENDER_URL}');	
				var t1 = (new Date().getTime());				
				var dxSeconds = (t1 - t0) / 2 / 1000;				
				
				var newCountdownTime = {ILIAS_SESSION_COUNTDOWN} - dxSeconds;
				//console.log("New check in " + newCountdownTime / 1000 + " seconds ...");
				window.setTimeout('countdown();', newCountdownTime);
			};
		}
	}	
}
var ilSessionCheckerFailureHandler = function(o)
{
}

var ilSessionChecker = function(url)
{
	var ilSessionCheckerCallback =
	{
		success: ilSessionCheckerHandler,
		failure: ilSessionCheckerFailureHandler
	};

	//console.log('AJAX request ...');
	var request = YAHOO.util.Connect.asyncRequest('GET', url, ilSessionCheckerCallback);
};

// Session Checker
var ilSessionExtenderHandler = function(o)
{
}
var ilSessionExtenderFailureHandler = function(o)
{
}

var ilSessionExtender = function(url)
{
	var ilSessionExtenderCallback =
	{
		success: ilSessionExtenderHandler,
		failure: ilSessionExtenderFailureHandler
	};

	//console.log('AJAX request ...');
	var request = YAHOO.util.Connect.asyncRequest('GET', url, ilSessionExtenderCallback);
};

function countdown() {
	new ilSessionChecker('{ILIAS_SESSION_CHECKER_URL}');
}
/* ]]> */
</script>