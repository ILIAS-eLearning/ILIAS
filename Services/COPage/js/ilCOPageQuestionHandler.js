
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

var ilCOPageQuestionHandlerF = function() {
};
ilCOPageQuestionHandlerF.prototype =
{
	overlays: {},
	callback_url: null,
	
	processAnswer: function (type, id, answer)
	{
		this.sendAnswer(type, id, answer);
	},

	initCallback: function (url)
	{
		this.callback_url = url;
	},

	sendAnswer: function(type, id, answer)
	{
		var cb =
		{
			success: this.asynchSuccess,
			failure: this.asynchFailure,
			argument: {}
		};

		if (this.callback_url != null)
		{
			var request = YAHOO.util.Connect.asyncRequest('POST', this.callback_url, cb,
				"id=" + id + "&type=" + type + "&answer=" + JSON.stringify(answer));
		}

		return false;
	},

	// handle asynchronous request (success)
	asynchSuccess: function(o)
	{
	},

	// Success Handler
	asynchFailure: function(o)
	{
	}

};
var ilCOPageQuestionHandler = new ilCOPageQuestionHandlerF();