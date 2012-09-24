il.LearningModule = {
	showContentFrame: function (e) {
		return il.LearningModule.loadContentFrame(e.target.href);
	},
	
	loadContentFrame: function (href) {
		var faqt = $("#bot_center_area");
		if (faqt.length == 0) {
			$('body').append('<div id="bot_center_area" class="ilBotCenterArea"><img class="ilAreaClose" /><iframe /></div>');
		}
		$("img.ilAreaClose").click(function () {
			il.LearningModule.closeContentFrame();
			});
		$("#bot_center_area > iframe").attr("src", href);
		il.UICore.refreshLayout();
		return false;
	},
	
	closeContentFrame: function () {
		//alert("close!");
		$("#bot_center_area").remove();
		il.UICore.refreshLayout();
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
