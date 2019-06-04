il = il || {};
il.UI = il.UI || {};
(function($, ui) {
	ui.page = (function($) {

		var breakpoint_max_width = 768, //this corresponds to @grid-float-breakpoint-max, see mainbar.less/metabar.less
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

		var init = function() {
			mq_orientation.addListener(function(mq) {
				window.location.reload();
			});
		};

		return {
			init: init,
			isSmallScreen: isSmallScreen,
			getOrientation: getOrientation,
			isPortrait: isPortrait,
			isLandscape: isLandscape,
		};

	})($);
})($, il.UI);
