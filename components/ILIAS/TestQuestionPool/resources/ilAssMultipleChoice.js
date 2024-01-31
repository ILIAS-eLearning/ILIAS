/* MC-MR Js Engines */

(function($){

    /* fau: testNav - handle the "none above" mc option in special tests. */
    
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

(function($){

    /* mcSelLim - handle the mc selection limit */
    
    var instances = new Array();
    
    $.fn.ilAssMultipleChoiceEngine = function(questionId, options)
    {
        options = jQuery.extend({}, jQuery.fn.ilAssMultipleChoiceEngine.defaults, options);

        instances[questionId] = new _ilAssMultipleChoiceEngine(questionId, options);

        return instances[questionId];
    };

    $.fn.ilAssMultipleChoiceEngine.defaults = {
        minSelection: null,
        maxSelection: null
    };

    var _ilAssMultipleChoiceEngine = function(questionId, options)
    {
        this.questionId = questionId;
        this.options = options;
    };

    _ilAssMultipleChoiceEngine.prototype = {
        init: function()
        {
            if( this.options.maxSelection )
            {
                // fau: fixMcWithLimit - add check when question is shown again (#26097)
                if( isSelectionLimitReached(this.questionId) )
                {
                  disableUnselectedOptions(this.questionId);
                }
                // fau.
                initSelectionLimitHandler(this.questionId);
            }
        }
    };

    var handleSelectionChange = function()
    {
        var questionId = $(this).attr(getQuestionIdAttributeName());
        
        if( isSelectionLimitReached(questionId) )
        {
            disableUnselectedOptions(questionId);
        }
        else
        {
            enableDisabledOptions(questionId);
        }

        initSelectionLimitHandler(questionId);
    };

    var initSelectionLimitHandler = function(questionId)
    {
        detachSelectionChangeHandler(questionId);
        attachSelectionChangeHandler(questionId);
    };

    var attachSelectionChangeHandler = function(questionId)
    {
        $(buildAllChoiceOptionsSelector(questionId)+':enabled').each(
            function(pos, item)
            {
                $(item).on('change', handleSelectionChange);
            }
        )
    };

    var detachSelectionChangeHandler = function(questionId)
    {
        $(buildAllChoiceOptionsSelector(questionId)).each(
            function(pos, item)
            {
                $(item).off('change');
            }
        );
    };
    
    var enableDisabledOptions = function(questionId)
    {
        $(buildAllChoiceOptionsSelector(questionId)+':disabled').each(
            function(pos, item)
            {
                $(item).removeAttr('disabled');
            }
        );
    };
    
    var disableUnselectedOptions = function(questionId)
    {
        $(buildAllChoiceOptionsSelector(questionId)+':not(:checked)').each(
            function(pos, item)
            {
                $(item).attr('disabled', 'disabled');
            }
        );
    };
    
    var isSelectionLimitReached = function(questionId)
    {
        var numSelected = $(buildAllChoiceOptionsSelector(questionId)+':checked').length;
        var maxSelection = instances[questionId].options.maxSelection;
        
        return  numSelected >= maxSelection;
    };
    
    var buildAllChoiceOptionsSelector = function(questionId)
    {
        return 'input.ilAssMultipleChoiceOption['+getQuestionIdAttributeName()+'='+questionId+']';
    };
    
    var getQuestionIdAttributeName = function()
    {
        return 'data-qst-id';
    }

}(jQuery));
