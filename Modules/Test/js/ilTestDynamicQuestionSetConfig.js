(function($){

	$(document).ready(function(){

		var form = $('form#form_tst_form_dynamic_question_set_config');
		
		var qplSelect = form.find('#source_qpl_id');
		var taxSelect = form.find('#ordering_tax');
		var fetchUrl = form.find('input[name=taxSelectOptAsyncUrl]').val();
		
		var loaderIcon = $('<img style="display:none;" src="Modules/Test/templates/default/images/loading.gif" />');
		
		taxSelect.after(loaderIcon);

		qplSelect.change(
			function(e)
			{
				taxSelect.hide();
				loaderIcon.show();
				
				var questionPoolId = $(this).val();

				$.ajax({
					type: "POST",
					url: fetchUrl,
					data: { question_pool_id: questionPoolId },
					dataType: 'json',
					success: function(selectOptions)
					{
						taxSelect.html('');

						$(selectOptions).each(
							function(pos, item)
							{
								taxSelect.append('<option value="'+item.value+'">'+item.label+'</option>');
							}
						);

						loaderIcon.hide();
						taxSelect.show();
					}
				});
			}
		);

	});

}(jQuery));
