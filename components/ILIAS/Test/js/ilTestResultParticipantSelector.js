(function($){ $(document).ready(

	function()
	{
		var jumpToActive = function(activeId)
		{
			location.href = '#participant_active_'+activeId;
		};
		
		$('#tst_results_toolbar select[name=active_id]').change(
			function(event)
			{
				var activeId = event.target.options[event.target.selectedIndex].value;
				
				jumpToActive(activeId);
			}
		);

		$('#tst_results_toolbar #ilTestResultParticipantJumper').click(
			function(event)
			{
				event.stopPropagation();
				event.preventDefault();

				var activeId = $('#tst_results_toolbar select[name=active_id]').val();
				
				jumpToActive(activeId);
			}
		);
	}

); })(jQuery)