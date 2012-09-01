il.LearningModule = {
	showContentFrame: function (e) {
		return il.LearningModule.loadContentFrame(e.target.href);
	},
	
	loadContentFrame: function (href) {
		var faqt = $("#bot_left_area");
		if (faqt.length == 0) {
			$('body').append('<div id="bot_left_area" class="ilBotLeftArea"><iframe /></div>');
		}
		$("#bot_left_area > iframe").attr("src", href);
		il.UICore.refreshLayout();
		return false;
	}
}

$(function() {
	$('body').focus();
	$(document).keypress(function(e) {
	if (e.target.tagName != "TEXTAREA" &&
		e.target.tagName != "INPUT") {
		// right
		if (e.keyCode == 39) {
			var a = $('.ilc_page_rnavlink_RightNavigationLink').first().attr('href');
			if (a != "") {
				top.location.href = a;
			}
		}
		// left
		if (e.keyCode == 37) {
			var a = $('.ilc_page_lnavlink_LeftNavigationLink').first().attr('href');
			if (a != "") {
				top.location.href = a;
			}
		}
		return false;
	}
})});
