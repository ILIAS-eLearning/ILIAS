(function($){

    var instance;

    $.fn.ilMatchingQuestionEngine = function(options)
    {
        options = jQuery.extend({}, jQuery.fn.ilMatchingQuestionEngine.defaults, options);

        instance = new _ilMatchingQuestionEngine(options);

        return instance;
    };

    $.fn.ilMatchingQuestionEngine.defaults = {
        matchingContainer: 'body',
        matchingMode: '1:1',
        resetButtonId: null
    };

    var _ilMatchingQuestionEngine = function(options)
    {
        this.options = options;

        this.questionId = null;

        this.definitions = [];
        this.terms = [];
        this.matchings = [];
    };

    _ilMatchingQuestionEngine.prototype = {

        setQuestionId: function(questionId)
        {
            instance.questionId = questionId;
        },

        addDefinition: function(definitionId)
        {
            instance.definitions.push(definitionId);
        },

        addTerm: function(termId)
        {
            instance.terms.push(termId);
        },

        addMatching: function(definitionId, termId)
        {
            instance.matchings.push({
                term: termId,
                definition: definitionId
            });
        },

        init: function()
        {
            initDroppables();
            initDraggables();
            restoreMatches();

            if( $(instance.options.resetButtonId) )
            {
                $(instance.options.resetButtonId).click(resetMatchings);
            }
        }
    };

    var initDroppables = function()
    {
        $(instance.definitions).each(
            function(key, definitionId)
            {
                var domSelector = '#definition_'+definitionId;

                makeDroppable(domSelector);
            }
        );
    };

    var initDraggables = function()
    {
        $(instance.terms).each(
            function(key, termId)
            {
                var domSelector = '#term_'+termId;
                makeDraggable(domSelector);
                makeDroppable(domSelector);
            }
        );
    };

    var buildDragHelper = function(event)
    {
        var draggable = $(event.target);

        if( !draggable.hasClass('draggable') )
        {
            draggable = $(draggable.parents('div.draggable'));
        }

        draggable.addClass('draggableDisabled');

        var helper = $('<div class="draggableHelper" />');
        helper.html(draggable.html());
        helper.css('width', draggable.css('width'));
        helper.css('height', draggable.css('height'));

        return helper;
    };

    var makeDroppable = function(domSelector)
    {
        $(domSelector).droppable({
            drop: dropElementHandler,
            disabled: true,
            tolerance: 'pointer'
        });
    };

    var makeDraggable = function(domSelector)
    {
        $(domSelector).draggable({
            helper: buildDragHelper,
            start: startDrag,
            stop: stopDrag,
            //cursor: 'move',
            revert: true,
            scroll: true,
            containment: instance.options.matchingContainer
        });
    };

    var isValidDroppable = function(droppable, draggable)
    {
        if( droppable.attr('id') == draggable.parents('.droparea').attr('id') )
        {
            return false;
        }

        var droppedDraggableId = droppable.attr('data-type')+'_'+droppable.attr('data-id');
        droppedDraggableId += '_'+draggable.attr('data-type')+'_'+draggable.attr('data-id');

        if( instance.options.matchingMode == 'n:n' && droppable.find('#'+droppedDraggableId).length > 0 )
        {
            return false;
        }
        else if( instance.options.matchingMode == '1:1' && droppable.find('.draggable').length > 0 )
        {
            return false;
        }

        return true;
    }

    var startDrag = function(event, ui)
    {
        var that = $(this);

        $(instance.definitions).each(
            function(key, definitionId)
            {
                var domSelector = '#definition_'+definitionId;

                if( isValidDroppable($(domSelector), that) )
                {
                    $(domSelector).addClass('droppableTarget');
                    $(domSelector).droppable('enable');
                    $(domSelector).droppable('option', 'hoverClass', 'droppableHover');
                }
            }
        );

        if( that.parents('.droparea').length > 0 )
        {
            var termDroppable = $('#'+that.attr('data-type')+'_'+that.attr('data-id'));

            termDroppable.removeClass('draggableDisabled');
            termDroppable.addClass('droppableTarget');
            termDroppable.droppable('enable');
            termDroppable.droppable('option', 'hoverClass', 'droppableHover');
        }
    };

    var isDraggableToBeReactivated = function(draggable)
    {
        if( $(draggable).parents('.droparea').length > 0 )
        {
            return true;
        }

        if( instance.options.matchingMode == 'n:n' )
        {
            return true;
        }

        var reactivationRequired = true;

        $(instance.definitions).each(
            function(key, definitionId)
            {
                var domSelector = '#definition_'+definitionId;

                $(domSelector).find('.draggable').each(
                    function(key, droppedDraggable)
                    {
                        if( $(droppedDraggable).attr('data-id') == $(draggable).attr('data-id') )
                        {
                            reactivationRequired = false;
                        }
                    }
                );
            }
        );

        return reactivationRequired;
    };

    var stopDrag = function(event, ui)
    {
        if( isDraggableToBeReactivated(this) )
        {
            $(this).removeClass('draggableDisabled');
        }

        $(instance.definitions).each(
            function(key, definitionId)
            {
                var domSelector = '#definition_'+definitionId;

                $(domSelector).removeClass('droppableTarget');
                $(domSelector).droppable('disable');
                $(domSelector).droppable('option', 'hoverClass', '');
            }
        );

        if( $(this).parents('.droparea').length > 0 )
        {
            var domSelector = '#'+$(this).attr('data-type')+'_'+$(this).attr('data-id');

            $(domSelector).removeClass('droppableTarget');
            $(domSelector).droppable('disable');
            $(domSelector).droppable('option', 'hoverClass', '');

            if( instance.options.matchingMode == '1:1' )
            {
                $(domSelector).addClass('draggableDisabled')
            }
        }
    };

    var dropElementHandler = function(event, ui)
    {
        ui.helper.remove();

        if( ui.draggable.parents('.droparea').length > 0 )
        {
            removeTermInputFromDefinition(ui.draggable, ui.draggable.parents('.droparea'));
            ui.draggable.remove();
        }
        else if( instance.options.matchingMode == '1:1' )
        {
            ui.draggable.draggable('disable');
        }

        var draggableOriginalSelector = '#'+$(ui.draggable).attr('data-type')+'_'+$(ui.draggable).attr('data-id');
        $(draggableOriginalSelector).removeClass('droppableTarget');

        if( $(this).hasClass('droparea') )
        {
            if( instance.options.matchingMode == 'n:n' )
            {
                $(draggableOriginalSelector).removeClass('draggableDisabled');
            }
            else if( instance.options.matchingMode == '1:1' )
            {
                $(draggableOriginalSelector).addClass('draggableDisabled');
            }
        }

        if( $(this).attr('data-type') == 'definition' )
        {
            var cloneId = buildDroppedDraggableCloneId(ui.draggable, $(this));

            var droppedDraggableClone = ui.draggable.clone();
            droppedDraggableClone.attr('id', cloneId);
            droppedDraggableClone.removeClass('draggableDisabled');
            droppedDraggableClone.addClass('droppedDraggable');

            $(this).find('.ilMatchingQuestionTerm').append(droppedDraggableClone);

            makeDraggable('#'+droppedDraggableClone.attr('id'));
        }
        else if( $(this).attr('data-type') == 'term' && instance.options.matchingMode == '1:1' )
        {
            $(draggableOriginalSelector).draggable('enable');
        }

        if( $(this).hasClass('droparea') )
        {
            appendTermInputToDefinition(droppedDraggableClone, $(this));
        }

        $(instance.definitions).each(
            function(key, definitionId)
            {
                var domSelector = '#definition_'+definitionId;

                $(domSelector).removeClass('droppableTarget');
            }
        );
    };

    var removeDefinitionDroppablesTargetClass = function(droppable)
    {
        if( droppable.parents('.droparea').length > 0 )
        {
            var domSelector = '#'+$(this).attr('data-type')+'_'+$(this).attr('data-id');

            $(domSelector).removeClass('droppableTarget');
            $(domSelector).droppable('disable');
            $(domSelector).droppable('option', 'hoverClass', '');

            if( instance.options.matchingMode == '1:1' )
            {
                $(domSelector).addClass('draggableDisabled')
            }
        }
    };

    var buildDroppedDraggableCloneId = function(draggable, droppable)
    {
        var cloneId = droppable.attr('data-type')+'_'+droppable.attr('data-id');
        cloneId += '_'+draggable.attr('data-type')+'_'+draggable.attr('data-id');

        return cloneId;
    };

    var appendTermInputToDefinition = function(draggable, droppable)
    {
        var input = $('<input type="hidden" />');

        input.attr('id', 'data_'+draggable.attr('id'));
        input.attr('name', 'matching['+instance.questionId+']['+droppable.attr('data-id')+']['+draggable.attr('data-id')+']');
        input.attr('value', draggable.attr('data-id'));

        droppable.append(input);
    };

    var removeTermInputFromDefinition = function(draggable, droppable)
    {
        var inputId = 'data_'+droppable.attr('data-type')+'_'+droppable.attr('data-id');
        inputId += '_'+draggable.attr('data-type')+'_'+draggable.attr('data-id');

        $('#'+inputId).remove();
    };

    var restoreMatches = function()
    {
        $(instance.matchings).each(
            function(key, matching)
            {
                var definitionDroppable = $('#definition_'+matching.definition);
                var termDraggable = $('#term_'+matching.term);

                var cloneId = buildDroppedDraggableCloneId(termDraggable, definitionDroppable);

                var droppedDraggableClone = termDraggable.clone();
                droppedDraggableClone.attr('id', cloneId);
                droppedDraggableClone.removeClass('draggableDisabled');

                definitionDroppable.find('.ilMatchingQuestionTerm').append(droppedDraggableClone);
                appendTermInputToDefinition(droppedDraggableClone, definitionDroppable);

                makeDraggable('#'+droppedDraggableClone.attr('id'));


                if( instance.options.matchingMode == '1:1' )
                {
                    termDraggable.draggable('disable');
                    termDraggable.addClass('draggableDisabled');
                }
            }
        );
    };

    var resetMatchings = function()
    {
        $(instance.definitions).each(
            function(key, definitionId)
            {
                var definitionDroppable = $('#definition_'+definitionId);
                definitionDroppable.find('[data-type="term"]').remove();
                definitionDroppable.find('input[type="hidden"]').remove();
            }
        );

        $(instance.terms).each(
            function(key, termId)
            {
                var term = $('#term_'+termId);
                term.removeClass('draggableDisabled');
                term.draggable('enable');
            }
        );
    }

}(jQuery));
