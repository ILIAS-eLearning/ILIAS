(function($){

	function updateWizardInputsTrueColumnLabel(trueLabel)
	{
		$('.kprimchoicewizard .optionLabel.true').html(trueLabel);
	}

	function updateWizardInputsFalseColumnLabel(falseLabel)
	{
		$('.kprimchoicewizard .optionLabel.false').html(falseLabel);
	}

	function updateWizardInputsColumnLabelsWithPreset(hiddenTranslationElements)
	{
		updateWizardInputsTrueColumnLabel( $(hiddenTranslationElements).find('[data-var=true]').attr('data-val') );
		updateWizardInputsFalseColumnLabel( $(hiddenTranslationElements).find('[data-var=false]').attr('data-val') );
	}

	function updateWizardInputsColumnLabelsWithCustomValues()
	{
		updateWizardInputsTrueColumnLabel( $('#option_label_custom_true').val() );
		updateWizardInputsFalseColumnLabel( $('#option_label_custom_false').val() );
	}

	function updateWizardInputsColumnLabels(selectedLabelOption)
	{
		var hiddenTranslationElements = $('[data-id='+selectedLabelOption+']');

		if(hiddenTranslationElements.length)
		{
			updateWizardInputsColumnLabelsWithPreset(hiddenTranslationElements);
		}
		else
		{
			updateWizardInputsColumnLabelsWithCustomValues();
		}
	}

	function initWizardInputsColumnLabelsValues()
	{
		updateWizardInputsColumnLabels( $('input[name=option_label]:checked').val() );
	}

	function labelOptionSettingChangedHandler(elem)
	{
		updateWizardInputsColumnLabels( $(this).val() );
	}
	
	function customTrueLabelValueChangedHandler(elem)
	{
		updateWizardInputsTrueColumnLabel( $(this).val() );
	}
	
	function customFalseLabelValueChangedHandler(elem)
	{
		updateWizardInputsFalseColumnLabel( $(this).val() );
	}
	
	function registerWizardInputsColumnLabelsUpdater()
	{
		$('input[name=option_label]').change(labelOptionSettingChangedHandler);
		$('input[name=option_label_custom_true]').change(customTrueLabelValueChangedHandler);
		$('input[name=option_label_custom_false]').change(customFalseLabelValueChangedHandler);
	}
	
	$( document ).ready(
		function()
		{
			registerWizardInputsColumnLabelsUpdater();
			initWizardInputsColumnLabelsValues();
		}
	);
	
}(jQuery));
