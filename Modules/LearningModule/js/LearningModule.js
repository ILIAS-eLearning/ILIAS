il.LearningModule = {
	
	save_url: '',
	init_frame: '',
	last_frame_url: '',
	
	setSaveUrl: function (url) {
		il.LearningModule.save_url = url;
	},
	
	showContentFrame: function (e) {
		return il.LearningModule.loadContentFrame(e.target.href);
	},
	
	initContentFrame: function (href) {
		il.LearningModule.init_frame = href;
	},
	
	setLastFrameUrl: function (href) {
		il.LearningModule.last_frame_url = href;
	},
	
	openInitFrames: function () {
		if (il.LearningModule.init_frame != "") {
			il.LearningModule.loadContentFrame(il.LearningModule.init_frame);
		} else if (il.LearningModule.last_frame_url != "") {
			il.LearningModule.loadContentFrame(il.LearningModule.last_frame_url);
		}
	},
	
	loadContentFrame: function (href) {
		var faqt = $("#bot_center_area");
		if (faqt.length == 0) {
			$('body').append('<div id="bot_center_area" class="ilBotCenterArea"><div id="bot_center_area_drag"></div><img class="ilAreaClose" /><iframe /></div>');
		}
		$("img.ilAreaClose").click(function () {
			il.LearningModule.closeContentFrame();
			});
		$("#bot_center_area > iframe").attr("src", href);
		
		il.UICore.initLayoutDrag();
		
		il.UICore.refreshLayout();
		if (il.LearningModule.save_url != '') {
			il.Util.sendAjaxGetRequestToUrl(il.LearningModule.save_url + "&url=" + encodeURIComponent(href),
				{}, {}, il.LearningModule.handleSuccess);
		}
		
		return false;
	},
	
	handleSuccess: function (o) {
		//
	},
	
	closeContentFrame: function () {
		//alert("close!");
		$("#bot_center_area").remove();
		il.UICore.refreshLayout();
		if (il.LearningModule.save_url != '') {
			il.Util.sendAjaxGetRequestToUrl(il.LearningModule.save_url + "&url=",
				{}, {}, il.LearningModule.handleSuccess);
		}
	}
	
	
}

$(function() {
	$('body').focus();
	$(document).keydown(function(e) {
	if (e.target.tagName != "TEXTAREA" &&
		e.target.tagName != "INPUT") {
		// right
		if (e.keyCode == 39) {
			var a = $('.ilc_page_rnavlink_RightNavigationLink').first().attr('href');
			if (a) {
				top.location.href = a;
			}
			return false;
		}
		// left
		if (e.keyCode == 37) {
			var a = $('.ilc_page_lnavlink_LeftNavigationLink').first().attr('href');
			if (a) {
				top.location.href = a;
			}
			return false;
		}
		return true;
	}
})});
