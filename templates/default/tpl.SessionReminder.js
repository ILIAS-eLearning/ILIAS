<script type="text/javascript">
/* <![CDATA[ */
ilAddOnLoad(
	function()
	{
		window.setTimeout('countdown();', ({TIME_LEFT} - {REMEMBER_TIME}));
	}
);

function countdown() 
{
	var check = confirm('{ALERT}');
	if(check == true)
	{
		new ilSessionExtender('{URL}');
	}
	
	return check;
}

// Success Handler
var ilSessionExtenderHandler = function(o)
{	
	// perform block modification
	if(o.responseText !== undefined) 
	{
		alert('{SESSION_REMINDER_EXTENDED}');
		window.setTimeout('countdown();', ({TIME_LEFT} - {REMEMBER_TIME}));
	}
}

// Failure Handler
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

	var request = YAHOO.util.Connect.asyncRequest('GET', url, ilSessionExtenderCallback);
};
/* ]]> */
</script>