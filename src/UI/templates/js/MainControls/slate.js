il = il || {};
il.UI = il.UI || {};
il.UI.maincontrols = il.UI.maincontrols || {};

(function($, maincontrols) {
	maincontrols.slate = (function($) {
		var _cls_engaged = 'engaged'
			,_cls_disengaged = 'disengaged'
			,_cls_single_slate = 'il-maincontrols-slate'
		;

		var onSignal = function(kind_of_signal, event, signalData, id) {
			var slate = $('#' + id),
				triggerer = signalData.triggerer,
				is_in_metabar_more = triggerer.parents('.il-metabar-more-slate').length > 0;

			switch (kind_of_signal) {
				case 'toggle':
					onToggleSignal(slate, triggerer, is_in_metabar_more);
					break;
				case 'engage':
					engage(slate);
					break;
				case 'replace':
					replaceFromSignal(id, signalData);
					break;
			};
		};

		var onToggleSignal = function(slate, triggerer, is_in_metabar_more) {
			//special case for metabar-more
			if(triggerer.attr('id') === il.UI.maincontrols.metabar._getMoreButton().attr('id')) {
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
				triggerer.addClass(_cls_engaged);
				triggerer.removeClass(_cls_disengaged);
				slate.trigger('in_view');
			} else {
				triggerer.removeClass(_cls_engaged);
				triggerer.addClass(_cls_disengaged);
			}
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

		var replaceFromSignal = function (id, signalData) {
            var url = signalData.options.url;
            il.UI.core.replaceContent(id, url, "content");
        };

		return {
			onSignal: onSignal,
			engage: engage,
			disengage: disengage,
			_cls_single_slate: _cls_single_slate,
			_cls_engaged: _cls_engaged
		}

	})($);
})($, il.UI.maincontrols);
