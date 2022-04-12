il = il || {};
il.UI = il.UI || {};
(function($, ui) {
	ui.page = (function($) {
		var _cls_page_content = '.il-layout-page-content',
			_id_right_col = '#il_right_col';

		var breakpoint_max_width = 768, //this corresponds to @grid-float-breakpoint-max, see mainbar.less/metabar.less
			resized_poppers_margin = 25, //dropdown, date-picker
			mq_orientation = window.matchMedia("(orientation: portrait)");

		var isSmallScreen = function() {
			var media_query = "only screen"
				+ " and (max-width: " + breakpoint_max_width + "px)";
			return window.matchMedia(media_query).matches;
		};

		var isLandscape = function() {
			return mq_orientation.matches === false;
		};
		var isPortrait = function() {
			return mq_orientation.matches;
		};
		var getOrientation = function() {
			return isPortrait() ? 'portrait' : 'landscape';
		};

		var fitContainerToPageContent = function(target_container) {
			var content_container = $(_cls_page_content)
				right_column = $(_id_right_col);

			if(!content_container.length || 
				!isContainerInPageContent(target_container)){
				return;
			}

			var	margin = resized_poppers_margin,
				max_width = content_container.width() - 2 * margin,
				target_left = content_container.offset().left - target_container.parent().offset().left + margin;

			if(right_column.length > 0) {
				max_width = max_width - right_column.width();
			}

			if( (target_container.width() < max_width && target_container.offset().left > content_container.offset().left)
				|| max_width < 0
			) {
				return;
			}

			window.setTimeout(function(){
				target_container.css({
					'left': target_left,
					'max-width': max_width
				});
			}, 100)
		};

		var isContainerInPageContent = function(container){
			return container.parents(_cls_page_content).length
		};

		return {
			isSmallScreen: isSmallScreen,
			getOrientation: getOrientation,
			isPortrait: isPortrait,
			isLandscape: isLandscape,
			fit: fitContainerToPageContent
		};

	})($);
})($, il.UI);
il.Util.addOnLoad(function () {
	window.setTimeout(
		function () {
			if (il.UI.page.isSmallScreen() === false) {
				$("main").attr("tabindex", -1).focus();
			}
		}, 10
	);
});