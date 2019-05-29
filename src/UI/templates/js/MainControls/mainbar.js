il = il || {};
il.UI = il.UI || {};
il.UI.maincontrols = il.UI.maincontrols || {};

(function($, maincontrols) {
	maincontrols.mainbar = (function($) {

		var id
			,_cls_btn_engaged = 'engaged'
			,_cls_page_active_slates = 'with-mainbar-slates-engaged' //set on _page_div
			,_cls_entries_wrapper = 'il-mainbar-entries' //encapsulating div of all entries
			,_cls_toolentries_wrapper = 'il-mainbar-tools-entries' //tools (within  _cls_entries_wrapper)
			,_cls_tools_wrapper = 'il-mainbar-tools-entries-bg' //encapsulating div of all tool-entries
			,_cls_tools_btn = 'il-mainbar-tools-button' //encapsulating div of the tools-button
			,_cls_page_div = 'il-layout-page' //encapsulating div of the page
			,_cls_slates_wrapper = 'il-mainbar-slates' //encapsulating div of mainbar's slates
			,_cls_single_slate = false //class of one single slate, will be set on registerSignals
			,_cls_slate_engaged = false //engaged class of a slate, will be set on registerSignals
			,_cls_mainbar = 'il-mainbar';
		;

		var registerSignals = function (
			component_id,
			entry_signal,
			close_slates_signal,
			tools_signal,
			tools_removal_signal
		) {
			id = component_id;
			_cls_single_slate = il.UI.maincontrols.slate._cls_single_slate;
			_cls_slate_engaged = il.UI.maincontrols.slate._cls_engaged;

			$(document).on(entry_signal, function(event, signalData) {
				onClickEntry(event, signalData);
				return false;
			});
			$(document).on(close_slates_signal, function(event, signalData) {
				onClickDisengageAll(event, signalData);
				return false;
			});
			$(document).on(tools_signal, function(event, signalData) {
				onClickToolsEntry(event, signalData);
				return false;
			});
			$(document).on(tools_removal_signal, function(event, signalData) {
				onClickToolRemoval(event, signalData);
				initMore();
				return false;
			});
		};

		var initActive = function(component_id) {
			id = component_id;
			var btn = _getAllButtons()
				.filter('.' + _cls_btn_engaged);
			_disengageButton(btn);

			if(!il.UI.page.isSmallScreen()) {
				btn.click();
			}

		}

		var onClickEntry = function(event, signalData) {
			var btn = signalData.triggerer;
			if(_isEngaged(btn)) {
				if(! _isToolButton(btn)) {
					_disengageButton(btn);
					_setPageSlatesActive(false);
				}
			} else {
				_disengageAllButtons(); //reset, so that only _one_ is active
				if(_isToolButton(btn)) {
					_disengageAllToolButtons();
				}
				_disengageAllSlates();
				_engageButton(btn);
				_setPageSlatesActive(true);
				if(_isToolButton(btn)) {
					_setToolsActive(true);
					_engageButton(_getToolsButton());
				} else {
					_setToolsActive(false);
					_disengageButton(_getToolsButton());
				}
			}
		};

		var onClickDisengageAll = function(event, signalData) {
			_disengageAllButtons();
			_disengageAllSlates();
			_setPageSlatesActive(false);
			_setToolsActive(false);
		};

		var onClickToolsEntry = function(event, signalData) {
			var btn = signalData.triggerer,
				active_tool_button;

			if(_isEngaged(btn)) {
				_setPageSlatesActive(false);
				_setToolsActive(false);
				_disengageButton(btn);
			} else {
				_disengageAllButtons();
				_setPageSlatesActive(true);
				_setToolsActive(true);
				_engageButton(btn);
				_disengageAllSlates();

				if(_isAnyToolActive()) {
					active_tool_btn = _getAllToolButtons()
						.filter('.' + _cls_btn_engaged)[0];
				} else {
					active_tool_btn = _getAllToolButtons()[0];
				}
				active_tool_btn.click();
			}
		};

		var onClickToolRemoval = function(event, signalData) {
			var inst_id = '#' + id,
				search = [inst_id, _cls_toolentries_wrapper, 'btn'].join(' .'),
				active_tool_btn = $(search).filter(' .' + _cls_btn_engaged),
				remaining;

			active_tool_btn.remove();
			remaining = $(search);

			if(remaining.length > 0) {
				$(remaining[0]).click();
			} else {
				_disengageAllSlates();
				_setPageSlatesActive(false);
				_setToolsActive(false);
				_getToolsButton().remove();
			}
		};

		var _getToolsButton = function() {
			return $('#' + id + ' .' + _cls_tools_btn + ' .btn');
		};

		var _isToolButton = function(btn) {
			return btn.parent().hasClass(_cls_tools_wrapper);
		};

		var _isAnyToolActive = function(btn) {
			return _getAllToolButtons()
				.filter('.' + _cls_btn_engaged)
				.length > 0;
		};

		var _getAllToolButtons = function() {
			var search = '#' + id + ' .' + _cls_tools_wrapper + ' .btn';
			return $(search);
		};

		var _getAllButtons = function() {
			var search = '#' + id + ' .' + _cls_entries_wrapper + ' .btn';
			return $(search);
		};

		/**
		 * If any slates are active in the mainbar,
		 * the overall template has to be alerted
		 * in order to make room for the slate.
		 */
		var _setPageSlatesActive = function(active) {
			var page_div = $('.' + _cls_page_div);
			if(active) {
				page_div.addClass(_cls_page_active_slates);
			} else {
				page_div.removeClass(_cls_page_active_slates);
			}
		};

		var _setToolsActive = function(active) {
			var tools_area = $('#' + id +' .' + _cls_toolentries_wrapper);
			if(active) {
				tools_area.addClass(_cls_btn_engaged);
			} else {
				tools_area.removeClass(_cls_btn_engaged);
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

		var _toggleButton = function(btn) {
			_isEngaged(btn)? _disengageButton(btn) : _engageButton(btn);
		};

		var _disengageAllButtons = function() {
			var f = function(i, btn) {
					_disengageButton($(btn));
				}
			_getAllButtons().filter('.' + _cls_btn_engaged).each(f);
			_getMoreSlate().find('.' + _cls_btn_engaged).each(f);

		};

		var _disengageAllToolButtons = function() {
			_getAllToolButtons().filter('.' + _cls_btn_engaged).each(
				function(i, btn) {
					_disengageButton($(btn));
				}
			);
		};

		var _disengageAllSlates = function() {
			$('#' + id + ' .' + _cls_slates_wrapper)
			.children('.' + _cls_single_slate + '.' + _cls_slate_engaged)
			.each(
				function(i, slate) {
					il.UI.maincontrols.slate.disengage($(slate));
				}
			);
		};


		/**
		  * "more" button
		  */
		var _isCompletelyOnScreen = function(btn) {
			var window_height = $(window).height(),
				window_width = $(window).width(),
				btn_offset_top = $(btn).offset().top,
				btn_offset_left = $(btn).offset().left,
				btn_height = $(btn).height(),
				btn_width = $(btn).width(),
				vertically_visible = (btn_offset_top + btn_height) <= window_height,
				horizontally_visible = (btn_offset_left + btn_width) <= window_width;
			return (vertically_visible && horizontally_visible);
		};

		var _getInvisibleButtons = function() {
			var all_buttons = _getAllButtons(),
				buttons = all_buttons.slice(0, -1),
				last_visible = buttons.length,
				invisible_buttons;

			buttons.each(
				function(index, btn) {
					if(!_isCompletelyOnScreen(btn)) {
						last_visible = index - 1; //make room for the more-button
						return false;
					}
				}
			);

			if(last_visible == buttons.length) {
				return [];
			}
			return buttons.slice(last_visible);
		};

		var _getMoreButton = function() {
			var buttons = _getAllButtons();
			return $(buttons[buttons.length - 1]);
		}

		var _getMoreSlate = function() {
			var slates = $('#' + id + ' .' + _cls_slates_wrapper)
				.children('.' + _cls_single_slate);
			return $(slates[slates.length - 1]);
		}

		var initMore = function() {
			var more_button = _getMoreButton(),
				more_slate = _getMoreSlate(),
				invisible;

			//reset:
			more_slate.find('.btn-bulky').insertBefore(more_button);

			invisible = _getInvisibleButtons();
			if(invisible.length > 0) {
				invisible.appendTo(more_slate.children('.il-maincontrols-slate-content'));
				more_button.show();
			} else {
				more_button.hide();
				if(_isEngaged(more_slate)) {
					_setPageSlatesActive(false);
					_disengageButton(more_button);
					il.UI.maincontrols.slate.disengage(more_slate);
				}
			}
		}

		return {
			registerSignals: registerSignals,
			initActive: initActive,
			initMore: initMore
		}

	})($);
})($, il.UI.maincontrols);

