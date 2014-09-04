(function($){

	$( document ).ready(
		function()
		{
			$('input[name=option_label]').change(
				function(elem)
				{
					var translations = $('[data-id='+$(this).val()+']');
					
					if(translations.length)
					{
						
						$('.kprimchoicewizard .optionLabel.true').html(
							$(translations).find('[data-var=true]').attr('data-val')
						);
						
						$('.kprimchoicewizard .optionLabel.false').html(
							$(translations).find('[data-var=false]').attr('data-val')
						);
					}
					else
					{
						$('.kprimchoicewizard .optionLabel.true').html($('#option_label_custom_true').val());
						$('.kprimchoicewizard .optionLabel.false').html($('#option_label_custom_false').val());
					}
				}
			);

			$('input[name=option_label_custom_true]').change(
				function(elem)
				{
					$('.kprimchoicewizard .optionLabel.true').html($(this).val());
				}
			);

			$('input[name=option_label_custom_false]').change(
				function(elem)
				{
					$('.kprimchoicewizard .optionLabel.false').html($(this).val());
				}
			);
		}
	);
	
}(jQuery));
