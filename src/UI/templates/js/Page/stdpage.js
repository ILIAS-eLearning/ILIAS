il = il || {};
il.UI = il.UI || {};
(function($, ui) {
	ui.page = (function($) {

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

		var fitContainerToContent = function(target_container) {
			var content_container = $('.il-layout-page-content'),
				margin = resized_poppers_margin,
				max_width = content_container.width() - 2 * margin,
				target_left = content_container.offset().left - target_container.parent().offset().left + margin;

			if( target_container.width() < max_width &&
				target_container.offset().left > content_container.offset().left
			) {
				return;
			}
			window.setTimeout(function(){
				target_container.css({
					'left': target_left,
					'max-width': max_width
				});
			}, 100)
		}

		return {
			isSmallScreen: isSmallScreen,
			getOrientation: getOrientation,
			isPortrait: isPortrait,
			isLandscape: isLandscape,
			fit: fitContainerToContent
		};

	})($);
})($, il.UI);
