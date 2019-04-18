il = il || {};
il.UI = il.UI || {};
il.UI.maincontrols = il.UI.maincontrols || {};

(function($, maincontrols) {
	maincontrols.metabar = (function($) {

		var id
			,_cls_btn_engaged = 'engaged'
			,_cls_entry = 'il-metabar-entry'
			,_cls_single_slate = false //class of one single slate, will be set on registerSignals
			,_cls_slate_engaged = false //engaged class of a slate, will be set on registerSignals
		;

		var registerSignals = function (
			component_id,
			entry_signal
		) {
			id = component_id;
			_cls_single_slate = il.UI.maincontrols.slate._cls_single_slate;
			_cls_slate_engaged = il.UI.maincontrols.slate._cls_engaged;

			$(document).on(entry_signal, function(event, signalData) {
				onClickEntry(event, signalData);
				return false;
			});
		};

		var onClickEntry = function(event, signalData) {
			var btn = signalData.triggerer;
			if(_isEngaged(btn)) {
				_disengageButton(btn);
			} else {
				//disengage others:
				_disengageAllSlates();
				_disengageAllButtons();
				_engageButton(btn);
			}
		};

		var _engageButton = function(btn) {
			btn.addClass(_cls_btn_engaged);
			btn.attr('aria-pressed', true);
		};

		var _disengageButton = function(btn) {
			btn.removeClass(_cls_btn_engaged);
			btn.attr('aria-pressed', false);
		};

		var _isEngaged = function(btn) {
			return btn.hasClass(_cls_btn_engaged);
		};

		var _disengageAllButtons = function() {
			$('#' + id +' .' + _cls_entry)
			.children('.btn.engaged')
			.each(
				function(i, btn) {
					_disengageButton($(btn));
				}
			)
		};

		var _disengageAllSlates = function() {
			var search = '#' + id
				+ ' .' + _cls_single_slate
				+ '.' + _cls_slate_engaged;

			$(search).each(
				function(i, slate) {
					il.UI.maincontrols.slate.disengage($(slate));
				}
			)
		};

		return {
			registerSignals: registerSignals
		}

	})($);
})($, il.UI.maincontrols);

