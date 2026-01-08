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
		if (!signals || !signals.length) {
			return;
		}
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
