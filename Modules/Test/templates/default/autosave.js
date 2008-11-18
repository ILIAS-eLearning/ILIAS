var handleSuccess = function(o)
{ 
	if (o.responseText !== undefined)
	{
		if ((o.responseText.indexOf("http") == 0) || (o.responseText.indexOf("ilias.php") == 0))
		{
			window.location.href = o.responseText;
		}
		else
		{
			document.getElementById("autosavemessage").innerHTML = o.responseText;
			var stay = new YAHOO.util.Anim("autosavemessage", { opacity: { from: 1, to: 1 } }, 4);
			var fadeOut = new YAHOO.util.Anim("autosavemessage", { opacity: { from: 1, to: 0 } }, 1);
			stay.onComplete.subscribe(function() { fadeOut.animate(); });
			stay.animate();
		}
	} 
};

var handleFailure = function(o)
{
	if (o.responseText !== undefined)
	{ 
		document.getElementById("autosavemessage").innerHTML = o.responseText;
		var stay = new YAHOO.util.Anim("autosavemessage", { opacity: { from: 1, to: 1 } }, 4);
		var fadeOut = new YAHOO.util.Anim("autosavemessage", { opacity: { from: 1, to: 0 } }, 1);
		stay.onComplete.subscribe(function() { fadeOut.animate(); });
		stay.animate();
	} 
};

var callback =
{
	success:handleSuccess,
	failure:handleFailure
};

function autosave(sUrl)
{
	if (typeof tinyMCE != 'undefined')
	{
		if (tinyMCE) tinyMCE.triggerSave();
	}
	formObject = document.getElementById('taForm'); 
	YAHOO.util.Connect.setForm(formObject); 
	YAHOO.util.Connect.asyncRequest('POST', sUrl, callback);
}
