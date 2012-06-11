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
