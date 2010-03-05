ilCheckboxListOverlayFunc = function() {};
ilCheckboxListOverlayFunc.prototype =
{
	save: function(url, chb)
	{
		var callb =
		{
			success: function(o) {},
			failure: function(o) {}
		};
console.log(url);
console.log(chb);
//		var request = YAHOO.util.Connect.asyncRequest('GET', url, callb);
		
		return false;
	}
}
var ilCheckboxListOverlay = new ilCheckboxListOverlayFunc();