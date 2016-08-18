/* fau: testNav - optional script to handle the "none above" mc option in special tests. */

(function($){

    function handleMultipleChoiceResult()
    {
        if ($('.ilAssMultipleChoiceResult:checked').length > 0)
        {
            $('.ilAssMultipleChoiceNone').removeAttr('checked');
        }
        else
        {
            $('.ilAssMultipleChoiceNone').attr('checked','checked');
        }
    }

    function handleMultipleChoiceNone()
    {
        if ($('.ilAssMultipleChoiceNone:checked').length > 0)
        {
            $('.ilAssMultipleChoiceResult').removeAttr('checked');
        }
    }
	
	$( document ).ready(
		function()
		{
			$('.ilAssMultipleChoiceResult').change(handleMultipleChoiceResult);
            $('.ilAssMultipleChoiceNone').change(handleMultipleChoiceNone);
		}
	);
	
}(jQuery));
