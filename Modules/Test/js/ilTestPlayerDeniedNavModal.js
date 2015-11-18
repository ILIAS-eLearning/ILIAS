(function($){ $(document).ready( function(){
	
	var show = function() { $('#tst_denied_nav_modal').modal('show'); };
	var hide = function() { $('#tst_denied_nav_modal').modal('hide'); };
	
	$('.ilTstNavElem, .ilToolbar div.navbar-form').click(show);
	$('#tst_cancel_denied_nav_button').click(hide);

}); })
(jQuery);
