
/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

var ilCOPageQuestionHandlerF = function() {
};
ilCOPageQuestionHandlerF.prototype =
{
	overlays: {},
	callback_url: null,
	success_handler: null,
	
	processAnswer: function (type, id, answer)
	{
		this.sendAnswer(type, id, answer);
	},

	setSuccessHandler: function (f) {
		this.success_handler = f;
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
	asynchSuccess: function(o){
		if (ilCOPageQuestionHandler.success_handler != null) {
			ilCOPageQuestionHandler.success_handler();
		}
	},

	// Success Handler
	asynchFailure: function(o)
	{
	}

};
var ilCOPageQuestionHandler = new ilCOPageQuestionHandlerF();