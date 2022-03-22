il = il || {};
il.UI = il.UI || {};
il.UI.maincontrols = il.UI.maincontrols || {};

(function($, maincontrols) {
	maincontrols.metabar = (function($) {

		var id
			,_cls_btn_engaged = 'engaged'
			,_cls_entries = 'il-maincontrols-metabar'
			,_cls_slates = 'il-metabar-slates'
			,_cls_slate = 'il-maincontrols-slate'
			,_cls_more_btn = 'il-metabar-more-button'
			,_cls_more_slate = 'il-metabar-more-slate'
			,_cls_single_slate = false //class of one single slate, will be set on registerSignals
			,_cls_slate_engaged = false //engaged class of a slate, will be set on registerSignals
		;

		var propagation_stopped;

		var registerSignals = function (
			component_id,
			entry_signal,
			close_slates_signal
		) {
			id = component_id;
			_cls_single_slate = il.UI.maincontrols.slate._cls_single_slate;
			_cls_slate_engaged = il.UI.maincontrols.slate._cls_engaged;

			$(document).on(entry_signal, function(event, signalData) {
				onClickEntry(event, signalData);
				if(il.UI.page.isSmallScreen() && il.UI.maincontrols.mainbar) {
					il.UI.maincontrols.mainbar.disengageAll();
				}
				return false;
			});
			$(document).on(close_slates_signal, function(event, signalData) {
				onClickDisengageAll();
				return false;
			});

			//close metabar when user clicks anywhere
			$('.'+_cls_entries).on('click', function(event) {
				propagation_stopped = true;

			});
			$('body').on('click', function(event) {
				if(propagation_stopped) {
					propagation_stopped = false
				} else {
					onClickDisengageAll();
				}
			});

			//close metabar slate when focus moves out
			$('.'+_cls_slates+' > .'+_cls_slate).on('focusout', function(event) {
				if(!il.UI.page.isSmallScreen()) {
					let next_focus_target = event.relatedTarget;
					let current_slate = event.currentTarget;
					if (!$.contains(current_slate, next_focus_target)) {
						onClickDisengageAll();
					}
				}
			});
		};

		var onClickEntry = function(event, signalData) {
			var btn = signalData.triggerer;

			if(btn.attr('id') === _getMoreButton().attr('id')) {
				return;
			}

			if(_isEngaged(btn)) {
				_disengageButton(btn);
			} else {
				_disengageAllSlates();
				_disengageAllButtons();
				if(btn.parents('.' + _cls_more_slate).length == 0) {
					_engageButton(btn);
				}
			}
		};
		var onClickDisengageAll = function() {
			_disengageAllButtons();
			_disengageAllSlates();
		};
		var _engageButton = function(btn) {
			btn.addClass(_cls_btn_engaged);
			btn.attr('aria-expanded', true);
		};
		var _disengageButton = function(btn) {
			btn.removeClass(_cls_btn_engaged);
			btn.attr('aria-expanded', false);
		};
		var _isEngaged = function(btn) {
			return btn.hasClass(_cls_btn_engaged);
		};
		var _disengageAllButtons = function() {
			$('#' + id +'.' + _cls_entries)
			.children('li').children('.btn.' + _cls_btn_engaged)
			.each(
				function(i, btn) {
					_disengageButton($(btn));
				}
			)
		};
		var _disengageAllSlates = function() {
			getEngagedSlates().each(
				function(i, slate) {
					il.UI.maincontrols.slate.disengage($(slate));
				}
			)
		};
		var disengageAll = function() {
			_disengageAllSlates();
			_disengageAllButtons();
		};
		var getEngagedSlates = function() {
			var search = '#' + id
				+ ' .' + _cls_single_slate
				+ '.' + _cls_slate_engaged;

			return $(search);
		};

		/**
		  * decide and init condensed/wide version
		  */
		var init = function () {
			_tagMoreButton();
			_tagMoreSlate();
			il.UI.page.isSmallScreen() ? _initCondensed() : _initWide();
			//unfortunately, this does not work properly via a class
			$('.' + _cls_entries).css("visibility","visible");
			$('#' + id +' .' + _cls_slates).children('.' + _cls_single_slate)
				.attr('aria-hidden', true)
		};

		var _initCondensed = function () {
			_initMoreSlate();
			_getMetabarEntries().hide();
			_getMoreButton().show();
			collectCounters();
		};

		var _initWide = function () {
			_getMoreButton().hide();
			_getMetabarEntries().show();
		};

		var _tagMoreButton = function() {
			if(_getMoreButton().length === 0) {
				var entries = $('#' + id +'.' + _cls_entries).find('.btn'),
					more = entries.last();
				$(more).addClass(_cls_more_btn);
			}
		}

		var _tagMoreSlate = function() {
			if(_getMoreSlate().length === 0) {
				var slates = $('#' + id +' .' + _cls_slates).children('.' + _cls_single_slate),
					more = slates.last();
				$(more).addClass(_cls_more_slate);
			}
		}

		var _getMoreButton = function() {
			return $('.' + _cls_more_btn);
		}

		var _getMoreSlate = function() {
			return $('.' + _cls_more_slate);
		}

		var _getMetabarEntries = function() {
			return $('#' + id +'.' + _cls_entries)
				.children('li').children('.btn')
				.not('.' + _cls_more_btn);
		}

		var _initMoreSlate = function() {
			var content = _getMoreSlate().children('.il-maincontrols-slate-content');
			if(content.children().length == 0) {
				_getMetabarEntries().clone(true, true)
					.appendTo(content);
			}
		}

		var _getAllSlates = function() {
			return $('#' + id + ' .' + _cls_single_slate)
				.not('.' + _cls_more_slate);
		}

		var collectCounters = function() {
			var $more_slate_counter = il.UI.counter.getCounterObjectOrNull(_getMoreSlate());
			if($more_slate_counter){
				il.UI.counter.getCounterObject(_getMoreButton())
					.setNoveltyTo($more_slate_counter.getNoveltyCount())
					.setStatusTo($more_slate_counter.getStatusCount())
			};
		}

		return {
			registerSignals: registerSignals,
			init: init,
			collectCounters: collectCounters,
			_getMoreButton: _getMoreButton,
			getEngagedSlates: getEngagedSlates,
			_disengageAllSlates: _disengageAllSlates,
			disengageAll: disengageAll
		}

	})($);
})($, il.UI.maincontrols);

