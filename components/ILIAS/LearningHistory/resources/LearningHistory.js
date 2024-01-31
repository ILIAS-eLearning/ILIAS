il = il || {};
il.LearningHistory = il.LearningHistory || {};
(function($, il) {
	il.LearningHistory = (function($) {
		var initShowMore = function (id, url) {
			$("#" + id).on("click", function(e) {
				il.Util.sendAjaxPostRequestToUrl(url, {}, function(o) {
					o = JSON.parse(o);
					$(".ilLearningHistoryShowMore").html($(o.more));
					$(".ilTimeline").append($(o.timeline));
					il.Timeline.removeRedundantBadges();
				})
			});
		};
		return {
			initShowMore: initShowMore
		};
	})($);
})($, il);