il = il || {};
il.UI = il.UI || {};
il.UI.maincontrols = il.UI.maincontrols || {};

(function($, maincontrols) {
	maincontrols.slate = (function($) {
		var _cls_engaged = 'engaged'
			,_cls_disengaged = 'disengaged'
			,_cls_single_slate = 'il-maincontrols-slate'
		;

		var onToggleSignal = function(event, signalData, id) {
			var slate = $('#' + id);
			toggle(slate);
			if(_isEngaged(slate)) {
				signalData.triggerer.addClass(_cls_engaged);
				signalData.triggerer.removeClass(_cls_disengaged);
			} else {
				signalData.triggerer.removeClass(_cls_engaged);
				signalData.triggerer.addClass(_cls_disengaged);
			}
		};

		var onShowSignal = function(event, signalData, id) {
			var slate = $('#' + id);
			engage(slate);
		};

		var toggle = function(slate) {
			_isEngaged(slate) ? disengage(slate) : engage(slate);
		};

		var _isEngaged = function(slate) {
			return slate.hasClass(_cls_engaged);
		};

		var engage = function(slate) {
			slate.removeClass(_cls_disengaged);
			slate.addClass(_cls_engaged);
			slate.attr("aria-expanded", "true");
			slate.attr("aria-hidden", "false");
		};

		var disengage = function(slate) {
			slate.removeClass(_cls_engaged);
			slate.addClass(_cls_disengaged);
			slate.attr("aria-expanded", "false");
			slate.attr("aria-hidden", "true");
		};

		return {
			onToggleSignal: onToggleSignal,
			onShowSignal: onShowSignal,
			engage: engage,
			disengage: disengage,
			_cls_single_slate: _cls_single_slate,
			_cls_engaged: _cls_engaged
		}

	})($);
})($, il.UI.maincontrols);
