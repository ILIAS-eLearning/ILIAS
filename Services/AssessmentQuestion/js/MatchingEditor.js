let MATCHING_ONE_TO_ONE = 0;
let MATCHING_MANY_TO_ONE = 1;
let MATCHING_MANY_TO_MANY = 2;
let matching_mode;

let buildDragHelper = function (event) {
    let draggable = $(event.target);

    if (!draggable.hasClass('draggable')) {
        draggable = $(draggable.parents('div.draggable'));
    }

    let helper = $('<div class="draggableHelper" />');
    helper.html(draggable.html());
    helper.css({
        'width' : draggable.css('width'),
        'height' : draggable.css('height'),
        'z-index' : 1035
    });
    return helper;
};

let isValidDroppable = function (droppable, draggable) {
    if (droppable.attr('id') == draggable.parents('.droparea').attr('id')) {
        return false;
    }

    let droppedDraggableId = 
        droppable.attr('data-type') + '_' + 
        droppable.attr('data-id') + '_' + 
        draggable.attr('data-type') + '_' + 
        draggable.attr('data-id');

    if (matching_mode == MATCHING_MANY_TO_MANY && 
        droppable.find('#' + droppedDraggableId).length > 0) {
        return false;
    } else if (matching_mode == MATCHING_ONE_TO_ONE && 
               droppable.find('.draggable').length > 0) {
        return false;
    }

    return true;
};

let startDrag = function (event, ui) {
    let that = $(this);

    that.addClass('draggableDisabled');

    $('.js_definition').each(function (index, item) {
        if (isValidDroppable($(item), that)) {
            $(item).addClass('droppableTarget');
            $(item).droppable('enable');
            $(item).droppable('option', 'hoverClass', 'droppableHover');
        }
    });

    if (that.parents('.droparea').length > 0) {
        let termDroppable = $('#' + that.attr('data-type') + '_'
                + that.attr('data-id'));

        termDroppable.removeClass('draggableDisabled');
        termDroppable.addClass('droppableTarget');
        termDroppable.droppable('enable');
        termDroppable.droppable('option', 'hoverClass', 'droppableHover');
    }
};

let isDraggableToBeReactivated = function (draggable) {
    if ($(draggable).parents('.droparea').length > 0) {
        return true;
    }

    if (matching_mode == MATCHING_MANY_TO_MANY) {
        return true;
    }

    let reactivationRequired = true;

    $('.js_definition').each(
            function (id, item) {
                $(item).find('.draggable').each(
                        function (key, droppedDraggable) {
                            if ($(droppedDraggable).attr('data-id') == $(
                                    draggable).attr('data-id')) {
                                reactivationRequired = false;
                            }
                        });
            });

    return reactivationRequired;
};

let stopDrag = function (event, ui) {
    if (isDraggableToBeReactivated(this)) {
        $(this).removeClass('draggableDisabled');
    }

    $('.js_definition').each(function (index, element) {
        $(element).removeClass('droppableTarget');
        $(element).droppable('disable');
        $(element).droppable('option', 'hoverClass', '');
    });

    if ($(this).parents('.droparea').length > 0) {
        let domSelector = 
            '#' + $(this).attr('data-type') + '_' + $(this).attr('data-id');

        $(domSelector).removeClass('droppableTarget');
        $(domSelector).droppable('disable');
        $(domSelector).droppable('option', 'hoverClass', '');

        if (matching_mode == MATCHING_ONE_TO_ONE ||
            matching_mode == MATCHING_MANY_TO_ONE) {
            $(domSelector).addClass('draggableDisabled');
        }
    }
};

let dropElementHandler = function (event, ui) {
    ui.helper.remove();

    if (ui.draggable.parents('.droparea').length > 0) {
        removeTermInputFromDefinition(ui.draggable, ui.draggable
                .parents('.droparea'));
        ui.draggable.remove();
    } else if (matching_mode == MATCHING_ONE_TO_ONE || 
               matching_mode == MATCHING_MANY_TO_ONE) {
        ui.draggable.draggable('disable');
    }

    let draggableOriginalSelector = 
        '#' + $(ui.draggable).attr('data-type') + '_' + $(ui.draggable).attr('data-id');
    
    $(draggableOriginalSelector).removeClass('droppableTarget');

    if ($(this).hasClass('droparea')) {
        if (matching_mode == MATCHING_MANY_TO_MANY) {
            $(draggableOriginalSelector).removeClass('draggableDisabled');
        } else if (matching_mode == MATCHING_ONE_TO_ONE
                || matching_mode == MATCHING_MANY_TO_ONE) {
            $(draggableOriginalSelector).addClass('draggableDisabled');
        }
    }

    let droppedDraggableClone = ui.draggable.clone();
    
    if ($(this).attr('data-type') == 'definition') {
        let cloneId = buildDroppedDraggableCloneId(ui.draggable, $(this));

        
        droppedDraggableClone.attr('id', cloneId);
        droppedDraggableClone.removeClass('draggableDisabled');
        droppedDraggableClone.addClass('droppedDraggable');

        $(this).find('.ilMatchingQuestionTerm').append(droppedDraggableClone);

        $('#' + droppedDraggableClone.attr('id')).draggable(
                {
                    helper : buildDragHelper,
                    start : startDrag,
                    stop : stopDrag,
                    revert : true,
                    scroll : true,
                    containment : $('#' + droppedDraggableClone.attr('id'))
                            .parents('.ilc_question_standard').eq(0)
                });
    } else if ($(this).attr('data-type') == 'term' && 
                  (matching_mode == MATCHING_ONE_TO_ONE || 
                   matching_mode == MATCHING_MANY_TO_ONE)) {
        $(draggableOriginalSelector).draggable('enable');
    }

    if ($(this).hasClass('droparea')) {
        appendTermInputToDefinition(droppedDraggableClone, $(this));
    }

    $('.js_definition').removeClass('droppableTarget');
};

let getAnswerItem = function (item) {
    return item.parents('.answers').find('input[type=hidden].answer').eq(0);
};

let appendTermInputToDefinition = function (draggable, droppable) {
    let input = $('<input type="hidden" />');

    input.attr('id', 'data_' + draggable.attr('id'));
    input.attr('value', draggable.attr('data-id'));

    droppable.append(input);

    let answer_item = getAnswerItem(draggable);
    let current_answers = answer_item.val().split(';');
    current_answers.push(droppable.attr('data-id') + '-'+ draggable.attr('data-id'));
    
    answer_item.val(current_answers.join(';'));
};

let removeTermInputFromDefinition = function (draggable, droppable) {
    let inputId = 
        'data_' + droppable.attr('data-type') + '_' + 
                  droppable.attr('data-id') + '_' + 
                  draggable.attr('data-type') + '_' + 
                  draggable.attr('data-id');

    $('#' + inputId).remove();

    let answer_item = getAnswerItem(draggable);
    let current_answers = answer_item.val().split(';');
    let old_match = droppable.attr('data-id') + '-' + draggable.attr('data-id');
    let old_index = current_answers.indexOf(old_match);
    if (old_index > -1) {
        current_answers.splice(old_index, 1);
    }
    answer_item.val(current_answers.join(';'));
};

let buildDroppedDraggableCloneId = function (draggable, droppable) {
    let cloneId = 
        droppable.attr('data-type') + '_' + 
        droppable.attr('data-id')+ '_' + 
        draggable.attr('data-type') + '_' + 
        draggable.attr('data-id');

    return cloneId;
};

let restoreMatches = function () {
    $('input[type=hidden].answer').eq(0).val().split(';').forEach(
            function (match) {
                let raw = match.split('-');
                let definition = raw[0];
                let term = raw[1];

                let definitionDroppable = $('#definition_' + definition);
                let termDraggable = $('#term_' + term);

                let cloneId = buildDroppedDraggableCloneId(termDraggable,
                        definitionDroppable);

                let droppedDraggableClone = termDraggable.clone();
                droppedDraggableClone.attr('id', cloneId);
                droppedDraggableClone.removeClass('draggableDisabled');

                definitionDroppable.find('.ilMatchingQuestionTerm').append(
                        droppedDraggableClone);

                $('#' + droppedDraggableClone.attr('id')).draggable(
                        {
                            helper : buildDragHelper,
                            start : startDrag,
                            stop : stopDrag,
                            revert : true,
                            scroll : true,
                            containment : $(
                                    '#' + droppedDraggableClone.attr('id'))
                                    .parents('.ilc_question_standard').eq(0)
                        });

                if (matching_mode == MATCHING_ONE_TO_ONE) {
                    termDraggable.draggable('disable');
                    termDraggable.addClass('draggableDisabled');
                }
            });
};

$(document).ready(function () {
    $('.js_definition, .js_term').each(function (i, droppable) {
        $(droppable).droppable({
            drop : dropElementHandler,
            disabled : true,
            tolerance : 'pointer'
        });
    });

    $('.js_term').each(function (i, draggable) {
        $(draggable).draggable({
            helper : buildDragHelper,
            start : startDrag,
            stop : stopDrag,
            revert : true,
            scroll : true,
            containment : $(draggable).parents('.ilc_question_standard').eq(0)
        });
    });

    matching_mode = $('.js_matching_type').val();
    restoreMatches();
});
