/**
 * Form
 *
 * @author <killing@leifos.com>
 */

var il = il || {};
il.UI = il.UI || {};

il.UI.form = (function ($) {

	/**
	 *
	 * @param event
	 * @param signalData
	 */
	var onInputUpdate = function (event, signalData) {
		console.log(signalData.options.string_value);
	};


	/**
	 * Public interface
	 */
	return {
		onInputUpdate: onInputUpdate
	};

})($);