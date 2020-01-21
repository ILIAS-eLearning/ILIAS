/**
 * Input
 *
 * @author Thomas Famula <famula@leifos.de>
 */

var il = il || {};
il.UI = il.UI || {};

il.UI.input = (function ($) {

	var signals_per_id = {};

	var setSignalsForId = function (id, signals) {
		signals_per_id[id] = signals;
	};

	var onFieldUpdate = function (event, id, val) {
		var input = $("#" + id);
		var signals = signals_per_id[id];
		for (var i = 0; i < signals.length; i++) {
			var s = signals[i];
			var options = s.options;
			options.string_value = val;
			if (s.event === "update") {
				$(input).trigger(s.signal_id, {
					'id': s.signal_id,
					'event': s.event,
					'triggerer': input,
					'options': options
				});
			}
		}
	};

	/**
	 * Public interface
	 */
	return {
		setSignalsForId: setSignalsForId,
		onFieldUpdate: onFieldUpdate
	};

})($);
