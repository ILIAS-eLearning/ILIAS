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
			var slate = $('#' + id),
				is_in_metabar_more = signalData.triggerer.parents('.il-metabar-more-slate').length > 0;

			//special case for metabar-more
			if(signalData.triggerer.attr('id') === il.UI.maincontrols.metabar._getMoreButton().attr('id')) {
				if(il.UI.maincontrols.metabar.getEngagedSlates().length > 0){
					il.UI.maincontrols.metabar._disengageAllSlates();
				} else {
					toggle(slate);
				}
				return;
			}

			toggle(slate);
			if(is_in_metabar_more) {
				return;
			}
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
