/* fau: testNav - this script is not included anymore and can be deleted. */
(function($){

	var show = function(e)
	{
		e.stopPropagation();
		e.preventDefault();

		// href of invoked link/button
		var nextHref = $(this).attr('href');

		// next command forwarded to submitSolution
		var nextCommand = $(this).attr('data-nextcmd');

		// possibly the next sequence element for forwarding
		var nextSequence = $(this).attr('data-nextseq');
		
		$('a#nextCmdLink').attr('href', nextHref);
		$('input[name=nextcmd]').val(nextCommand);
		if( typeof nextSequence != 'undefined' )
		{
			$('input[name=nextseq]').val(nextSequence);
		}

		$('#tst_nav_while_edit_modal').modal('show');
	};

	var hide = function(e)
	{
		$('a#nextCmdLink').attr('href', '');
		$('input[name=nextcmd]').val('');
		$('input[name=nextseq]').val('');
		
		$('#tst_nav_while_edit_modal').modal('hide');
	};
	
	$(document).ready(
		function()
		{
			$('.ilTstNavElem').click(show);
			$('#tst_cancel_nav_while_edit_button').click(hide);
		}
	);

})(jQuery);
