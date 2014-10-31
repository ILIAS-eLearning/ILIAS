var ClozeGlobals = {
    clone_active                 : -1,
    active_gap                   : -1,
    cursor_pos                   : '',
    gap_count                    : 0,
    form_class                   : '#form_assclozetest',
    form_class_adjustment        : '#form_adjustment',
    form_footer_class            : '.ilFormFooter',
    form_footer_buttons          : '.col-sm-6.ilFormCmds',
    form_value                   : 'col-sm-9',
    form_value_class             : '.col-sm-9',
    form_header                  : 'ilFormHeader',
    form_header_class            : '.ilFormHeader',
    form_header_value            : 'ilFormCmds',
    form_options                 : 'col-sm-3',
    form_options_class           : '.col-sm-3',
    form_row                     : 'form-group',
    form_error                   : 'form_error',
    form_warning                 : 'form_warning',
    best_combination             : '',
    whitespace_cleaner           : false,
    best_possible_solution_error : false,
    debug                        : false
};

$(document).ready(function ()
{
    'use strict';
    ClozeSettings.gaps_combination = $.map(ClozeSettings.gaps_combination, function(value, index) {
        return [value];
    });
    ClozeSettings.gaps_php = $.map(ClozeSettings.gaps_php, function(value, index) {
        return [value];
    });
    
    if($(ClozeGlobals.form_class).length === 0 && $(ClozeGlobals.form_class_adjustment).length === 1)
    {
        ClozeGlobals.form_class = ClozeGlobals.form_class_adjustment;
    }

    checkJSONArraysOnEntry();
    bindTextareaHandler();
    paintGaps();
    createGapListener();

    function paintGaps()
    {
        if(ClozeSettings.gaps_php[0].length == ClozeGlobals.gap_count)
        {
            var ilform =$(ClozeGlobals.form_class);
            ilform.height(ilform.height());
        }
        else
        {
            $(ClozeGlobals.form_class).height('');
        }
        $('.interactive').remove();
        var c = 0;
        ClozeSettings.gaps_php.forEach(function (obj, counter) {
            obj.forEach(function () {
                var type = obj[c].type;
                var values = obj[c].values;
                var text_field_length = obj[c].text_field_length;
                var shuffle = 0;
                var upper = '';
                var lower = '';
                if (type === 'select') {
                    shuffle = obj[c].shuffle;
                }
                if (type === 'numeric') {
                    upper = obj[c].upper;
                    lower = obj[c].lower;
                }
                //var gap_combination = obj[c].used_in_gap_combination;
                buildFormObject(type, c, values, text_field_length, shuffle, upper, lower);
                c++;
            });
        });
        ClozeGlobals.gap_count = c;
        if(ClozeSettings.gaps_combination.length > 0)
        {
            appendGapCombinationForm();
        }
        moveFooterBelow();
        bindSelectHandler();
        bindInputHandler();
        checkForm();
        if (ClozeGlobals.clone_active != -1) {
            cloneFormPart(ClozeGlobals.clone_active);
        }
        if (typeof(tinyMCE) != 'undefined') {
            if (tinyMCE.activeEditor === null || tinyMCE.activeEditor.isHidden() !== false) {
                ilTinyMceInitCallbackRegistry.addCallback(bindTextareaHandlerTiny);
            }
        }
    }
    var selector =  $('#gaptrigger');
    selector.off('click');
    selector.on('click', function (evt)
    {
        //evt.preventDefault();
        $('#cloze_text').insertGapCodeAtCaret();
        createNewGapCode();
        return false;
    });

    function checkJSONArraysOnEntry()
    {
        if( ClozeSettings.gaps_php === null )
        {
            ClozeSettings.gaps_php = [];
        }

        if( ClozeSettings.gaps_combination  === null )
        {
            ClozeSettings.gaps_combination = [];
        }
    }

    function buildNumericFormObjectHelper(row, type, value)
    {
        $('#numeric_prototype_numeric' + type).clone().attr({
            'id': 'numeric_answers' + type + '_' + row,
            'class': ClozeGlobals.form_row + ' interactive'
        }).appendTo(ClozeGlobals.form_class);
        var form =  $('#numeric_answers' + type + '_' + row);
        appendFormClasses(form);
        form.find('#gap_a_numeric' + type).attr({
            'id': 'gap_' + row + '_numeric' + type,
            'name': 'gap_' + row + '_numeric' +type,
            'value': value,
            'class': 'numeric_gap gap_' + row + '_numeric' +type
        });
    }

    function checkFormula(val)
    {
        var regex = /^-?(\d*)(,|\.|\/){0,1}(\d*)$/;
        return regex.exec(val);
    }
    function buildFormObject(type, counter, values, gap_field_length, shuffle, upper, lower)
    {
        buildTitle(counter);
        buildSelectionField(type, counter);
        if (type === 'text' || type == 'numeric') {
            $('#prototype_gapsize').clone().attr({
                'id': 'gap_' + counter + '_gapsize_row',
                'name': 'gap_' + counter + '_gapsize_row',
                'class': ClozeGlobals.form_row + ' interactive'
            }).appendTo(ClozeGlobals.form_class);
            var gapsize_row = $('#gap_' + counter + '_gapsize_row');
            appendFormClasses(gapsize_row);
            gapsize_row.find('#gap_a_gapsize').attr({
                'id': 'gap_' + counter + '_gapsize',
                'name': 'gap_' + counter + '_gapsize',
                'class' : 'gapsize',
                'value': gap_field_length
            });
        }
        if (type === 'text') {
            changeIdentifierTextField(type, counter, values);
        }
        else if (type === 'select') {
            $('#shuffle_answers').clone().attr({
                'id': 'shuffle_answers_' + counter,
                'class': ClozeGlobals.form_row + ' interactive'
            }).appendTo(ClozeGlobals.form_class);
            appendFormClasses($('#shuffle_answers_' + counter));
            changeIdentifierTextField(type, counter, values);
            if (shuffle === true) {
                $('#shuffle_' + counter).prop('checked', true);
            }
        }
        else if (type === 'numeric') {
            buildNumericFormObjectHelper(counter,'',values[0].answer);
            buildNumericFormObjectHelper(counter,'_lower',values[0].lower);
            buildNumericFormObjectHelper(counter,'_upper',values[0].upper);
            buildNumericFormObjectHelper(counter,'_points',values[0].points);
            $('#numeric_answers_points_' + counter).find('.gap_counter').attr(
                {
                    'id': 'gap[' + counter + ']',
                    'name': 'gap[' + counter + ']'
                });
            $('#numeric_prototype_remove_button').clone().attr({
                'id': 'remove_gap_container_' + counter,
                'name': 'remove_gap_container_' + counter,
                'class': ClozeGlobals.form_row + ' interactive'
            }).appendTo(ClozeGlobals.form_class);
            $('#remove_gap_container_' + counter).find('.btn.btn-default.remove_gap_button').attr(
                {
                    'id': 'remove_gap_' + counter
                });
        }
        $('#error_answer').clone().attr({
            'id': 'gap_error_' + counter,
            'class': ClozeGlobals.form_row + ' interactive'
        }).appendTo(ClozeGlobals.form_class);
        var gap_error =  $('#gap_error_' + counter);
        appendFormClasses(gap_error);
        gap_error.find('#error_answer_val').attr({
            'class': 'error_answer_' + counter,
            'name': 'error_answer_' + counter
        });
        appendGapCombinationButton();
    }
    function highlightRed(selector)
    {
        selector.addClass(ClozeGlobals.form_error);
    }
    function removeHighlight(selector)
    {
        selector.removeClass(ClozeGlobals.form_error);
    }
    function highlightYellow(selector)
    {
        selector.addClass(ClozeGlobals.form_warning);
    }
    function removeHighlightYellow(selector)
    {
        selector.removeClass(ClozeGlobals.form_warning);
    }
    function checkInputElementNotEmpty(selector,value)
    {
        if (value === '' || value === null) {
            highlightRed(selector);
            return 1;
        }
        else {
            removeHighlight(selector);
            return 0;
        }
    }
    function checkInputTextForWhitespaces(id, selector, value)
    {
        var error       = false;
        if (/^\s/.test(value))
        {
            showHidePrototypes(id, 'wsB',true);
            error                           = true;
            ClozeGlobals.whitespace_cleaner = true;
        }
        else if(!error && !ClozeGlobals.whitespace_cleaner)
        {
            showHidePrototypes(id, 'wsB',false);
        }
        if (/\s$/.test(value))
        {
            showHidePrototypes(id, 'wsA',true);
            error                           = true;
            ClozeGlobals.whitespace_cleaner = true;
        }
        else if(!error && !ClozeGlobals.whitespace_cleaner)
        {
            showHidePrototypes(id, 'wsA',false);
        }
        if (/\s{2,}/.test(value))         {
            showHidePrototypes(id, 'wsM',true);
            error                           = true;
            ClozeGlobals.whitespace_cleaner = true;
        }
        else if(!error && !ClozeGlobals.whitespace_cleaner)
        {
            showHidePrototypes(id, 'wsM',false);
        }
        if( error === true )
        {
            highlightYellow(selector);
        }
        else if(!error && !ClozeGlobals.whitespace_cleaner)
        {
            removeHighlightYellow(selector);
        }
        
    }
    function clearInputTextWithWhitespaces(value)
    {
        value = value.replace(/\s{2,}/g,'');
        value = value.replace(/^\s/,'');
        value = value.replace(/\s$/,'');
        return value;
    }
    
    function appendGapCombinationForm()
    {
        var max_points = 0;
        var best_combination_option = $('#select_option_placeholder').html();
        $.each(ClozeSettings.gaps_combination, function(i, obj) {
            var combinationCounter = parseInt(i) +1;
            $('#gap_combination_header').clone().attr({
                'class': ClozeGlobals.form_row + ' interactive',
                'id' : 'gap_combination_header_' + combinationCounter
            }).appendTo(ClozeGlobals.form_class);
            var gapCombinationHeader =  $('#gap_combination_header_' + combinationCounter);
            appendFormHeaderClasses(gapCombinationHeader);
            gapCombinationHeader.find('.text').html(ClozeSettings.combination_text + ' ' + combinationCounter + '');
            gapCombinationHeader.attr('copy','<h3>' + ClozeSettings.combination_text + ' ' + combinationCounter + '</h3>');
            $('#gap_combination_points_prototype').clone().attr({
                'id': 'gap_combination_points_' + i +'_outer',
                'class': ClozeGlobals.form_row + ' interactive'
            }).appendTo(ClozeGlobals.form_class);
            $('#gap_combination').clone().attr({
                'id': 'gap_combination_' + i,
                'class': ClozeGlobals.form_row + ' interactive clear_before_use'
            }).appendTo(ClozeGlobals.form_class);
            $('#gap_combination_' + i).find('.form-group').attr({
                'id':'gap_id_select_append_' + i + '_0'
            });
            var gap_combination_outer =  $('#gap_combination_points_' + i +'_outer');
            appendFormClasses(gap_combination_outer);
            gap_combination_outer.find('#gap_combination_points').attr({
                'id':'gap_combination_points_' + i,
                'name': 'gap_combination[' + i +'][points]'
            });
            gap_combination_outer.find('#best_possible_solution').attr({
                'id':'best_possible_solution_' + i,
                'value': i
            });
            var gapCombinationSelector  = $('#gap_combination_' + i);
            appendFormClasses(gapCombinationSelector);
            var first_row               = true;
            var best_solution           = '';
            $.each(obj, function(r, obj_row) {
                var buildOptionsSelect  = $('#select_option_placeholder').html();
                var buildOptionsValue   = buildOptionsSelect;
                if(!obj_row.points)
                {
                    if(first_row)
                    {
                        gapCombinationSelector.find('#gap_id_select').attr({
                            'id':   'gap_id_select_' + i + '_0',
                            'name': 'gap_combination[' + i + '][0][select]'
                        });
                        gapCombinationSelector.find('#gap_id_value').attr({
                            'id':   'gap_id_value_' + i + '_0',
                            'name': 'gap_combination[' + i + '][0][value]'
                        });
                        first_row = false;
                    }
                    else
                    {
                        $('.gap_combination_spacer').clone().attr({
                            'class':'gap_combination_spacer_applied'
                        }).appendTo('#gap_id_select_append_' + i + '_0');
                        gapCombinationSelector.find('#gap_id_select_' + i + '_0').clone().attr({
                            'id':'gap_id_select_' + i + '_' + r,
                            'name':'gap_combination[' + i + '][' + r +'][select]'
                        }).appendTo('#gap_id_select_append_' + i + '_0');
                        gapCombinationSelector.find('#gap_id_value_' + i + '_0').clone().attr({
                            'id':'gap_id_value_' + i+ '_' + r,
                            'name':'gap_combination[' + i+ '][' + r +'][value]'
                        }).appendTo('#gap_id_select_append_' + i + '_0');

                        $('#gap_id_select_' + i + '_' + r).html('');
                        $('#gap_id_value_' + i + '_' + r).html('');
                    }
                    addCloneButtons(i,r);
                }
                else
                {
                    $('#gap_combination_points_' + i).val(obj_row.points);
                }
                $.each(ClozeSettings.gaps_php[0], function(j, obj_inner_values){
                    var value = parseInt(j, 10) + 1;
                    if(parseInt(obj[r].gap, 10) == parseInt(j, 10) )
                    {
                        buildOptionsSelect += '<option value="' + j + '" selected>Gap ' + value + '</option>';
                        if(obj[r].type == 'numeric' || obj[r].type == 2)
                        {
                            var answer = obj_inner_values.values[0].answer;
                            var inrange, outrange;
                            if(obj[r].answer == answer)
                            {
                                inrange = 'selected';
                            }
                            else if(obj[r].answer == 'OutOfBound')
                            {
                                outrange = 'selected';
                            }
                            buildOptionsValue += '<option value="'+ answer +'" ' + inrange + '>'+ answer +'</option>';
                            buildOptionsValue += '<option value="OutOfBound" ' + outrange + '> ' + ClozeSettings.outofbound_text + '</option>';
                        }
                        else
                        {
                            $.each(obj_inner_values.values, function(k,value){
                                if(obj[r].answer == value.answer)
                                {
                                    buildOptionsValue += '<option value="' + value.answer + '" selected>' + value.answer + '</option>';
                                }
                                else
                                {
                                    buildOptionsValue += '<option value="' + value.answer + '">' + value.answer + '</option>';
                                }
                            });
                        }
                    }
                    else
                    {
                        buildOptionsSelect += '<option value="' + j + '">Gap ' + value + '</option>';
                    }
                });
                $('#gap_id_select_' + i + '_' + r).html(buildOptionsSelect);
                $('#gap_id_value_' + i + '_' + r).html(buildOptionsValue);
                if(obj_row.best_solution == 1)
                {
                    best_solution   = i;
                    max_points      = obj_row.points;
                }
            });
            if(best_solution == i)
            {
                best_combination_option += '<option value="' + i + '"selected>'+ ClozeSettings.combination_text + " " + combinationCounter + '</option>';
            }
            else
            {
                best_combination_option += '<option value="' + i + '">'+ ClozeSettings.combination_text + " " + combinationCounter + '</option>';
            }
        });
        $('#highlander_show').remove();
        $('#best_resultion_header').clone().attr({
            'id':'best_solution_show',
            'name':'',
            'class':ClozeGlobals.form_row + ' interactive'
        }).appendTo(ClozeGlobals.form_class);
        appendFormHeaderClasses($('#best_solution_show'));
        $('#highlander').clone().attr({
            'id':'highlander_show',
            'name':'',
            'class':ClozeGlobals.form_row + ' interactive'
        }).appendTo(ClozeGlobals.form_class);
        appendFormClasses($('#highlander_show'));
        /*$('#max_points').clone().attr({
         'id':'max_points_show',
         'name':'',
         'class':ClozeGlobals.form_row + ' interactive'
         }).appendTo(ClozeGlobals.form_class);
         */
        $('#highlander_show').find('#best_possible_solution').html(best_combination_option);
        // $('#max_points_show').find('#max_points_input').val(max_points);
    }
    function addCloneButtons(i,j)
    {
        $('.add_remove_buttons').clone().attr({
            'class':'add_remove_buttons_gap',
            'name':'gap_combination_' + i + '_' +j
        }).insertAfter('#gap_id_value_' + i + '_' +j);
        var count_gaps = ClozeSettings.gaps_php[0].length;
        var count_combination = parseInt(ClozeSettings.gaps_combination[i].length,10) - 1;
        if( count_combination >= count_gaps )
        {
            $('#gap_combination_' + i).find('.clone_fields_add').remove();
        }
    }
    function appendGapCombinationButton()
    {
        if(! $('#create_gap_combination_in_form').length)
        {
            $('#create_gap_combination').clone().attr({
                'id': 'create_gap_combination_in_form',
                'name': 'create_gap_combination_in_form',
                'class': 'btn btn-default btn-sm'
            }).prependTo(ClozeGlobals.form_footer_buttons);
            $('#create_gap_combination_in_form').live('click', function ()
            {
                var position = ClozeSettings.gaps_combination.length;
                var answer1 = new Object({
                    'gap': '',
                    'answer': '',
                    'type':''
                });
                var answer2 = new Object({
                    'gap': '',
                    'answer': '',
                    'type':''
                });
                var points = new Object({
                    'points': '0',
                    'best_solution': '0'
                });
                var insert = [answer1,answer2,points];

                ClozeSettings.gaps_combination.splice(position, 0, insert);
                paintGaps();
                //return false;
            });
        }
    }
    function checkTextBoxQuick(selector)
    {
        var gap_id      = 0;
        var answer_id   = 0;
        var temp        = '';
        temp = selector.attr('id').split('_')[1].split('[');
        gap_id          = parseInt(temp[0], 10);
        checkInputElementNotEmpty(selector,selector.val());
        checkInputTextForWhitespaces(gap_id,selector,selector.val());
        ClozeGlobals.whitespace_cleaner = false;
    }
    function checkForm()
    {
        var row = 0;
        ClozeSettings.gaps_php[0].forEach(function (entry) {
            var input_failed = 0;
            if (entry.type === 'numeric') {
                input_failed+= checkInputElementNotEmpty($('.gap_' + row + '_numeric'),entry.values[0].answer);
                input_failed+= checkInputElementNotEmpty($('.gap_' + row + '_numeric_upper'),entry.values[0].upper);
                input_failed+= checkInputElementNotEmpty($('.gap_' + row + '_numeric_lower'),entry.values[0].lower);
                if(entry.values[0].error !== false)
                {
                    var obj=entry.values[0].error;
                    if(obj)
                    {
                        Object.keys(obj).forEach(function (key)
                        {
                            if(obj[key]===true)
                            {
                                highlightRed($('#gap_' + row + '_numeric_' + key));
                                showHidePrototypes(row,'formula',true);
                            }
                            else
                            {
                                removeHighlight($('#gap_' + row + '_numeric_' + key));
                            }
                        });
                    }
                }
                if(checkFormula(entry.values[0].lower))
                {
                    removeHighlight($('#gap_' + row + '_numeric_lower'));
                }
                else
                {
                    highlightRed($('#gap_' + row + '_numeric_lower'));
                }
                if(checkFormula(entry.values[0].upper))
                {
                    removeHighlight($('#gap_' + row + '_numeric_upper'));
                }
                else
                {
                    highlightRed($('#gap_' + row + '_numeric_upper'));
                }
                input_failed += checkInputIsNumeric(entry.values[0].points,row,'_points');
                if (input_failed !== 0 ) {
                    showHidePrototypes(row,'number',true);
                }
                else {
                    showHidePrototypes(row,'number',false);
                }
                if (entry.values[0].points === '0') {
                    highlightRed($('#gap_' + row + '_numeric_points'));
                    showHidePrototypes(row,'points',true);
                }
                else {
                    showHidePrototypes(row,'points',false);
                }
            }
            else {
                var points = 0;
                var counter = 0;
                var number = true;
                entry.values.forEach(function (values) {
                    points += parseFloat(values.points);
                    if(isNaN(values.points) || values.points === '' ){
                        highlightRed($('#gap_' + row + '\\[points\\]\\[' + counter + '\\]'));
                        number=false;
                    }
                    else
                    {
                        removeHighlight($('#gap_' + row + '\\[points\\]\\[' + counter + '\\]'));
                    }
                    var failed = checkInputElementNotEmpty($('#gap_' + row + '\\[answer\\]\\[' + counter + '\\]'),values.answer);
                    input_failed += failed;
                    if(entry.type == 'text' && failed === 0)
                    {
                        checkInputTextForWhitespaces(row, $('#gap_' + row + '\\[answer\\]\\[' + counter + '\\]'), values.answer);
                    }
                    counter++;
                });
                if (input_failed > 0) {
                    showHidePrototypes(row,'value',true);
                }
                else {
                    showHidePrototypes(row,'value',false);
                }
                if(number === false){
                    showHidePrototypes(row,'number',true);
                }
                else
                {
                    showHidePrototypes(row,'number',false);
                }
                if (parseFloat(points) === 0) {
                    var gapCheck = checkIfGapIsUsedInCombination(row);
                    if(ClozeSettings.gaps_combination.length > 0 && gapCheck)
                    {
                        removeHighlight($('.gap_points_' + row));
                        showHidePrototypes(row,'points',false);
                    }
                    else
                    {
                        highlightRed($('.gap_points_' + row));
                        showHidePrototypes(row,'points',true);
                    }
                }
                else {
                    if(number === true)
                    {
                        removeHighlight($('.gap_points_' + row));
                        showHidePrototypes(row,'points',false);
                    }
                }
            }
            row++;
            ClozeGlobals.whitespace_cleaner = false;
        });
        $('#gap_json_post').attr('value',JSON.stringify(ClozeSettings.gaps_php));
        if(ClozeSettings.gaps_combination.length > 0)
        {
            ClozeSettings.gaps_combination.forEach(function (entry, i) {
                var checkGaps = [];
                var no_error = true;
                entry.forEach(function (row,j){
                    if( checkGaps.indexOf( parseInt(row.gap, 10)) != -1  || row.answer == ClozeSettings.combination_error || row.answer == 'none_selected_minus_one' )
                    {
                        highlightRed($('#gap_id_select_append_' + i + '_0'));
                        no_error = false;
                    }
                    else if(no_error)
                    {
                        removeHighlight($('#gap_id_select_append_' + i + '_0'));
                    }

                    if(row.points)
                    {
                        if(isNaN(row.points) || row.points === '' || parseFloat(row.points) === 0 )
                        {
                            highlightRed($('#gap_combination_points_' + i));
                        }
                        else
                        {
                            removeHighlight($('#gap_combination_points_' + i ));
                        }
                    }
                    checkGaps[j] = parseInt(row.gap, 10);
                });
            });
        }
        $('#gap_json_combination_post').attr('value',JSON.stringify(ClozeSettings.gaps_combination));
        var higlanderShow = $('#highlander_show');
        if (isNaN(parseInt(higlanderShow.find('#best_possible_solution').val())))
        {
            highlightRed(higlanderShow);
        }
        else
        {
            removeHighlight(higlanderShow);
        }
        checkIfCombinationsUnique();
    }
    function checkIfCombinationsUnique()
    {
        if(ClozeSettings.gaps_combination.length > 1)
        {
            var convertCombinationObject = [];
            ClozeSettings.gaps_combination.forEach(function (entry1,key1){
                var temp_array = new Array(entry1.length -1);
                entry1.forEach(function (entry2,key2){
                    if(typeof(entry2.best_solution) == 'undefined')
                    {
                        temp_array[entry2.gap] = entry2.answer;
                    }
                });
                convertCombinationObject.push(JSON.stringify(temp_array));
            });
            convertCombinationObject.forEach(function (entry,key){
                var result = convertCombinationObject.indexOf(entry, key + 1);
                if(result != -1)
                {
                    var first        = key    + 1;
                    var second       = result + 1;
                    var first_comb   = $('#gap_combination_header_' + first);
                    var second_comb  = $('#gap_combination_header_' + second);
                    var text         = '(' + ClozeSettings.copy_of_combination + ' ';
                    highlightRed(first_comb.find(ClozeGlobals.form_header_class));
                    highlightRed(second_comb.find(ClozeGlobals.form_header_class));
                    first_comb.find('.text').html(first_comb.attr('copy') + text + second  + ')');
                    second_comb.find('.text').html(second_comb.attr('copy')  + text + first   + ')');
                }
            });
        }
    }

    function checkIfGapIsUsedInCombination(id)
    {
        var found = false;
        if(ClozeSettings.gaps_combination.length > 0)
        {
            ClozeSettings.gaps_combination.forEach(function (entry,i) {
                entry.forEach(function (row,j){
                    if( parseInt(id, 10) == parseInt(row.gap, 10))
                    {
                        found = true;
                    }
                });
            });
        }
        return found;
    }

    function checkInputIsNumeric(number,row,field)
    {
        if(isNaN(number) || number === ''){
            highlightRed($('.gap_' + row + '_numeric' + field));
            return 1;
        }
        else{
            removeHighlight($('.gap_' + row + '_numeric' + field));
        }
        return 0;
    }

    function showHidePrototypes(row,type,show)
    {
        if(show)
        {
            $('.error_answer_' + row).find('.' + type).removeClass('prototype');
        }
        else
        {
            $('.error_answer_' + row).find('.' + type).addClass('prototype');
        }
    }

    function buildTitle(counter)
    {
        $('#gap_title').clone().attr({
            'id': 'tile_' + counter,
            'name': 'tile_' + counter,
            'class': ClozeGlobals.form_row + ' interactive'
        }).appendTo(ClozeGlobals.form_class);
        appendFormHeaderClasses($('#tile_' + counter));
        $('#tile_' + counter).find('h3').text('Gap ' + (counter + 1));
    }

    function appendFormClasses(selector)
    {
        selector.children().first().attr('class',ClozeGlobals.form_options);
        selector.children().first().next().attr('class', ClozeGlobals.form_value);
    }

    function appendFormHeaderClasses(selector)
    {
        selector.children().first().attr('class',ClozeGlobals.form_header);
        selector.children().first().next().attr('class', ClozeGlobals.form_header_value);
    }

    function changeIdentifierTextField(type, counter_question, answers)
    {
        var c = 0;
        var text_row_selector;
        answers.forEach(function (s) {
            if (c === 0) {
                $('#answer_text').clone().attr(
                    {
                        'id': 'text_row_' + counter_question + '_' + c,
                        'class': ClozeGlobals.form_row + ' interactive'
                    }).appendTo(ClozeGlobals.form_class);
                text_row_selector =  $('#text_row_' + counter_question + '_' + c);
                appendFormClasses(text_row_selector);
                text_row_selector.find('#table_body').attr(
                    {
                        'id': 'table_body_' + counter_question
                    });
                $('#table_body_' + counter_question).find('tr').attr({
                    'class': ClozeGlobals.form_row + ' interactive'
                });
                text_row_selector.find('.btn.btn-default.remove_gap_button').attr(
                    {
                        'id': 'remove_gap_' + counter_question
                    });
            }
            else {
                $('#inner_text').clone().attr(
                    {
                        'id': 'text_row_' + counter_question + '_' + c,
                        'class': ClozeGlobals.form_row + ' interactive'
                    }).appendTo('#table_body_' + counter_question);
            }
            text_row_selector= $('#text_row_' + counter_question + '_' + c);
            text_row_selector.find('.gap_counter').attr(
                {
                    'id': 'gap[' + counter_question + ']',
                    'name': 'gap[' + counter_question + ']'
                });
            text_row_selector.find('#gap_points').attr(
                {
                    'id': 'gap_' + counter_question + '' + '[points][' + c + ']',
                    'name': 'gap_' + counter_question + '' + '[points][' + c + ']',
                    'class': 'gap_points gap_points_' + counter_question,
                    'value': s.points
                });
            text_row_selector.find('.text_field').attr(
                {
                    'name': 'gap_' + counter_question + '' + '[answer][' + c + ']',
                    'id': 'gap_' + counter_question + '' + '[answer][' + c + ']',
                    'value': s.answer
                });
            $('#shuffle_answers_' + counter_question).find('#shuffle_dummy').attr(
                {
                    'name': 'shuffle_' + counter_question,
                    'class': 'shuffle',
                    'id': 'shuffle_' + counter_question
                });
            text_row_selector.find('.clone_fields_add').attr(
                {
                    'name': 'add_gap_' + counter_question + '_' + c
                });
            text_row_selector.find('.clone_fields_remove').attr(
                {
                    'name': 'remove_gap_' + counter_question + '_' + c
                });
            c++;
        });
    }

    function buildSelectionField(type, counter)
    {
        var prototype_head = $('#select_field');
        prototype_head.clone().attr({
            'id': type + '-gap-r-' + counter,
            'class': ClozeGlobals.form_row + ' interactive'
        }).appendTo(ClozeGlobals.form_class);
        var select_field_selector = $('#' + type + '-gap-r-' + counter);
        appendFormClasses(select_field_selector);
        select_field_selector.children(ClozeGlobals.form_options_class).attr('id', type + '-gap-r-' + counter);
        select_field_selector.children().children('.form-control').attr(
            {
                'id': 'clozetype_' + counter,
                'name': 'clozetype_' + counter
            });
        $('#clozetype_' + counter + ' option').attr('selected', false);
        if (type == 'text') {
            $('#clozetype_' + counter + ' option[value="0"]').attr('selected', true);
        }
        else if (type == 'select') {
            $('#clozetype_' + counter + ' option[value="1"]').attr('selected', true);
        }
        else if (type == 'numeric') {
            $('#clozetype_' + counter + ' option[value="2"]').attr('selected', true);
        }
    }

    // $('.clone_fields_add').off('click');
    $('.clone_fields_add').live('click', function ()
    {
        var getPosition, pos , insert;
        if($(this).attr('class') != 'clone_fields_add combination btn btn-link')
        {
            getPosition = $(this).attr('name');
            pos = getPosition.split('_');
            insert = new Object({
                points: '0',
                answer: ''
            });
            ClozeSettings.gaps_php[0][pos[2]].values.splice(parseInt(pos[3], 10) + 1, 0, insert);
        }
        else
        {
            getPosition = $(this).parent().attr('name');
            pos = getPosition.split('_');
            insert = new Object({
                'gap': '',
                'answer': ''
            });
            ClozeSettings.gaps_combination[pos[2]].splice(parseInt(pos[3], 10) + 1, 0, insert);
        }
        paintGaps();
        return false;
    });
    //  $('.clone_fields_remove').off('click');
    $('.clone_fields_remove').live('click', function ()
    {
        var getPosition, pos;
        if($(this).attr('class') != 'clone_fields_remove combination btn btn-link')
        {
            getPosition = $(this).attr('name');
            pos = getPosition.split('_');
            ClozeSettings.gaps_php[0][pos[2]].values.splice(pos[3], 1);
            editTextarea(pos[2]);
            if (ClozeSettings.gaps_php[0][pos[2]].values.length === 0) {
                ClozeSettings.gaps_php[0].splice(pos[2], 1);
                removeFromTextarea(pos[2]);
            }
        }
        else
        {
            getPosition = $(this).parent().attr('name');
            pos = getPosition.split('_');
            if( ClozeSettings.gaps_combination[pos[2]].length == 3 )
            {
                ClozeSettings.gaps_combination.splice(parseInt(pos[2], 10), 1);
            }
            else
            {
                ClozeSettings.gaps_combination[pos[2]].splice(parseInt(pos[3], 10), 1);
            }
        }
        paintGaps();
        return false;
    });
    //  $('.remove_gap_button').off('click');
    $('.remove_gap_button').live('click', function ()
    {
        var getPosition = $(this).attr('id');
        var whereAmI    = $(this).parents().eq(4).attr('class');
        var pos = getPosition.split('_');
        if (confirm($('#delete_gap_question').text())) {
            ClozeSettings.gaps_php[0].splice(pos[2], 1);
            removeFromTextarea(pos[2]);
            paintGaps();
            if(whereAmI == 'modal-body')
            {
                $('#ilGapModal').modal('hide');
            }
        }
        //return false;
    });

    function bindSelectHandler()
    {
        var selector = $('.form-control') ;
        selector.off('change');
        selector.change(function () {
            var value, id;
            if($(this).attr('class') != 'form-control gap_combination')
            {
                value = parseInt($(this).attr('value'), 10);
                id = $(this).attr('id').split('_');
                if (value === 0) {
                    ClozeSettings.gaps_php[0][id[1]].type = 'text';
                }
                else if (value == 1) {
                    ClozeSettings.gaps_php[0][id[1]].type = 'select';
                }
                else if (value == 2) {
                    ClozeSettings.gaps_php[0][id[1]].values = new Object(new Array({
                        answer: '',
                        lower: '',
                        upper: '',
                        points: 0
                    }));
                    ClozeSettings.gaps_php[0][id[1]].type = 'numeric';
                    editTextarea(id[1]);
                }
                updateGapCombinationFields();
            }
            else
            {
                if($(this).attr('label') == 'select')
                {
                    value   = parseInt($(this).attr('value'), 10);
                    id      = $(this).attr('id').split('_');
                    ClozeSettings.gaps_combination[id[3]][id[4]].gap = value;
                    if(!isNaN(value))
                    {
                        ClozeSettings.gaps_combination[id[3]][id[4]].type = ClozeSettings.gaps_php[0][value].type;
                    }
                }
                else if($(this).attr('label') == 'value')
                {
                    value   = $(this).attr('value');
                    id      = $(this).attr('id').split('_');
                    ClozeSettings.gaps_combination[id[3]][id[4]].answer = value;
                }
            }
            paintGaps();
            //return false;
        });
    }
    
    function updateGapCombinationFields()
    {
        if( $.isArray(ClozeSettings.gaps_combination) ||  ClozeSettings.gaps_combination.length )
        {
            ClozeSettings.gaps_combination.forEach(function (entry, i) {
                entry.forEach(function (row, j) {
                    if(!row.points)
                    {
                        var gap =   ClozeSettings.gaps_combination[i][j].gap;
                        if(gap !== '')
                        {
                            ClozeSettings.gaps_combination[i][j].type = ClozeSettings.gaps_php[0][gap].type;
                        }
                    }
                });
            });
        }
    }
    
    function getPositionFromInputs(selector,single_value)
    {
        var getPosition = selector.attr('name');
        var pos = getPosition.split('_');
        if(single_value)
        {
            return pos;
        }
        else
        {
            pos = pos[1].split('[');
            var answer = pos[2].split(']');
            return [pos[0],answer[0]];
        }
    }
    
    function bindInputHandler()
    {
        var listener = 'blur';
        var selector = $('.text_field');
        selector.off('blur');
        selector.bind(listener, function(event){
            var pos = getPositionFromInputs($(this));
            var temp = ClozeSettings.gaps_php[0][pos[0]].values[pos[1]].answer;
            ClozeSettings.gaps_php[0][pos[0]].values[pos[1]].answer = $(this).val();
            editTextarea(pos[0]);
            if (ClozeGlobals.clone_active != -1) {
                if (event.type == 'blur') {
                    $('.interactive').find('#gap_' + pos[0] + '\\[answer\\]\\[' + pos[1] + '\\]').val($(this).val());
                }
            }
            $.each(ClozeSettings.gaps_combination, function(i, object)
            {
                $.each(object, function(j, row)
                {
                    if(row.gap == pos[0])
                    {
                        if(ClozeSettings.gaps_combination[i][j].answer == temp)
                        {
                            if(ClozeSettings.gaps_combination[i][j].answer != ClozeSettings.gaps_php[0][pos[0]].values[pos[1]].answer)
                            {
                                ClozeSettings.gaps_combination[i][j].answer = ClozeSettings.gaps_php[0][pos[0]].values[pos[1]].answer;
                                paintGaps();
                                //Todo: get focus from tabbing back
                            }
                        }
                    }
                });
            });
            checkForm();
        });
        listener = 'keyup';
        selector.off(listener);
        selector.bind(listener, function(event){
            checkTextBoxQuick($(this));
        });
        selector = $('.gapsize');
        selector.off('blur');
        selector.blur(function () {
            var pos = getPositionFromInputs($(this), true);
            ClozeSettings.gaps_php[0][pos[1]].text_field_length = $(this).val();
            if (ClozeGlobals.clone_active != -1) {
                $('.interactive').find('#gap_' + pos[1] + '_gapsize').val($(this).val());
            }
            checkForm();
        });  
        selector = $('.gap_points');
        selector.off('keyup');
        selector.keyup(function () {
            var pos = getPositionFromInputs($(this));
            ClozeSettings.gaps_php[0][pos[0]].values[pos[1]].points = $(this).val();
            if (ClozeGlobals.clone_active != -1) {
                $('.interactive').find('#gap_' + pos[0] + '\\[points\\]\\[' + pos[1] + '\\]').val($(this).val());
            }
            checkForm();
        });
    
        selector =  $('.points_field');
        selector.off('keyup');
        selector.keyup(function () {
            var pos = $(this).parent().parent().attr('id').split('_');
            var field = parseInt(ClozeSettings.gaps_combination[pos[3]].length, 10) - 1;
            ClozeSettings.gaps_combination[pos[3]][field].points = $(this).val();
            $('#max_points_show').find('#max_points_input').val($(this).val());
            checkForm();
        });
    
        selector =  $('.shuffle');
        selector.off('change');
        selector.change(function () {
            var pos = getPositionFromInputs($(this),true);
            var checked = $(this).is(':checked');
            ClozeSettings.gaps_php[0][pos[1]].shuffle = checked;
            if (ClozeGlobals.clone_active != -1) {
                $('.interactive').find('#shuffle_' + pos[1]).attr('checked', checked);
            }
            checkForm();
        });
    
        selector =   $('.numeric_gap');
        selector.off('blur');
        selector.blur(function () {
            var pos = getPositionFromInputs($(this),true);
            $(this).val($(this).val().replace(/ /g,''));
            if (pos.length == 3) {
                ClozeSettings.gaps_php[0][pos[1]].values[0].answer = $(this).val();
                editTextarea(pos[1]);
                if (ClozeGlobals.clone_active != -1) {
                    $('.interactive').find('#gap_' + pos[1] + '_numeric').val($(this).val());
                }
            }
            else {
                if (pos[3] == 'lower') {
                    ClozeSettings.gaps_php[0][pos[1]].values[0].lower = $(this).val();
                    if (ClozeGlobals.clone_active != -1) {
                        $('.interactive').find('#gap_' + pos[1] + '_numeric_lower').val($(this).val());
                    }
                }
                else if (pos[3] == 'upper') {
                    ClozeSettings.gaps_php[0][pos[1]].values[0].upper = $(this).val();
                    if (ClozeGlobals.clone_active != -1) {
                        $('.interactive').find('#gap_' + pos[1] + '_numeric_upper').val($(this).val());
                    }
                }
                else if (pos[3] == 'points') {
                    ClozeSettings.gaps_php[0][pos[1]].values[0].points = $(this).val();
                    if (ClozeGlobals.clone_active != -1) {
                        $('.interactive').find('#gap_' + pos[1] + '_numeric_points').val($(this).val());
                    }
                }
            }
            checkForm();
        });
    
        selector =   $('.best_possible_solution');
        selector.off('change');
        selector.change(function () {
            var id = parseInt($(this).val(), 10);
            if(!isNaN(id))
            {
                ClozeSettings.gaps_combination.forEach(function (entry1,key1){
                    entry1.forEach(function (entry2,key2){
                        if(typeof(entry2.best_solution) !== 'undefined')
                        {
                            if(key1 == id)
                            {
                                ClozeSettings.gaps_combination[key1][key2].best_solution = 1;
                                $('#max_points_show').find('#max_points_input').val(ClozeSettings.gaps_combination[key1][key2].points);
                            }
                            else
                            {
                                ClozeSettings.gaps_combination[key1][key2].best_solution = 0;
                            }
                        }
                    });
                });
                checkForm();
            }
        });
    }
    function bindTextareaHandler()
    {
        var cloze_text_selector= $('#cloze_text');
        cloze_text_selector.on('keydown', function () {
            var cursorPosition = $('#cloze_text').prop('selectionStart');
            var pos = cursorInGap(cursorPosition);
            ClozeGlobals.cursor_pos = cursorPosition;
            if (pos[1] != -1) {
                setCaretPosition(document.getElementById('cloze_text'), pos[1]);
                focusOnFormular(pos);
            }
        });

        cloze_text_selector.keyup(function(e){
            if(e.keyCode == 8 || e.keyCode == 46)
            {
                checkTextAreaAgainstJson();
            }
        });
        cloze_text_selector.click(function () {
            var cursorPosition = $('#cloze_text').prop('selectionStart');
            var pos = cursorInGap(cursorPosition);
            ClozeGlobals.cursor_pos = cursorPosition;
            if (pos[1] != -1) {
                setCaretPosition(document.getElementById('cloze_text'), pos[1]);
                focusOnFormular(pos);
            }
            return false;
        });
        cloze_text_selector.bind('paste', function (event){
            event.preventDefault();
            var clipboard_text = (event.originalEvent || event).clipboardData.getData('text/plain') || prompt('Paste something..');
            clipboard_text = clipboard_text.replace(/\[gap[\s\S\d]*?\]/g, '[gap]');
            var text = getTextAreaValue();
            var textBefore = text.substring(0,  ClozeGlobals.cursor_pos );
            var textAfter  = text.substring(ClozeGlobals.cursor_pos, text.length );
            setTextAreaValue(textBefore + clipboard_text + textAfter);
            createNewGapCode();
            cleanGapCode();
            paintGaps();
            ClozeGlobals.cursor_pos = parseInt(ClozeGlobals.cursor_pos, 10) + clipboard_text.length;
            setCaretPosition(cloze_text_selector, parseInt(ClozeGlobals.cursor_pos, 10));
        });
    }

    function bindTextareaHandlerTiny()
    {
        var tinymce_iframe_selector =   $('.mceIframeContainer iframe').eq(1).contents().find('body');
        tinymce_iframe_selector.keydown(function () {
            //ToDo: find out why location function breaks keyboard input
            /*var inst = tinyMCE.activeEditor;
            var cursorPosition = getCursorPositionTiny(inst);
            var pos = cursorInGap(cursorPosition);
            console.log(pos)
            g_cursor_pos = cursorPosition;
           if (pos[1] != -1) {
                setCursorPositionTiny(inst, pos[1]);
                focusOnFormular(pos);
            }*/
        });
        tinymce_iframe_selector.keyup(function(e){
            if(e.keyCode == 8 || e.keyCode == 46)
            {
                checkTextAreaAgainstJson();
            }
        });
        tinymce_iframe_selector.click(function () {
            var inst = tinyMCE.activeEditor;
            var cursorPosition = getCursorPositionTiny(inst, false);
            ClozeGlobals.cursor_pos = cursorPosition;
            var pos = cursorInGap(cursorPosition);
            checkTextAreaAgainstJson();
            if (pos[1] != -1) {
                setCursorPositionTiny(inst,pos[1]);
                focusOnFormular(pos);
            }
        });
        tinymce_iframe_selector.blur(function () {
            checkTextAreaAgainstJson();
        });
        tinymce_iframe_selector.bind('paste', function (event){
            event.preventDefault();
            var clipboard_text = (event.originalEvent || event).clipboardData.getData('text/plain') || prompt('Paste something..');
            clipboard_text = clipboard_text.replace(/\[gap[\s\S\d]*?\]/g, '[gap]');
            var text = getTextAreaValue();
            var textBefore = text.substring(0,  ClozeGlobals.cursor_pos );
            var textAfter  = text.substring(ClozeGlobals.cursor_pos, text.length );
            setTextAreaValue(textBefore + clipboard_text + textAfter);
            createNewGapCode();
            cleanGapCode();
            ClozeGlobals.cursor_pos = parseInt(ClozeGlobals.cursor_pos) + clipboard_text.length;
            correctCursorPositionInTextarea();
        });
    }

    function focusOnFormular(pos)
    {
        cloneFormPart(pos[0]);
        //ToDo: fix fokus
        $('#ilGapModal').modal('show');
        var gap                 = parseInt(pos[0], 10) - 1;
        var lightBoxInner       = $('#ilGapModal');
        $('#cloze_text').focus();
        lightBoxInner.find('#gap_' + gap + '\\[answer\\]\\[0\\]').focus();
        lightBoxInner.find('#gap_' + gap + '_numeric').focus();
        $('#ilGapModal').on('hidden.bs.modal', function () {
            checkForm();
        });
    }

    function checkTextAreaAgainstJson()
    {
        var text = getTextAreaValue();
        var text_match = text.match(/\[gap[\s\S\d]*?\](.*?)\[\/gap\]/g);
        var to_be_removed = [];
        if(ClozeSettings.gaps_php[0] !==null && ClozeSettings.gaps_php[0].length!== 0 && text_match !== null && text_match.length !== null)
        {
            var i;
            if(ClozeSettings.gaps_php[0].length != text_match.length)
            {
                var gap_exists_in_txtarea = [];
                for (i = 0; i < text_match.length; i++)
                {
                    var gap_exists = text_match[i].split(']');
                    gap_exists = gap_exists[0].split('[gap ');
                    gap_exists_in_txtarea.push(gap_exists[1]);
                }
                for (i = 0; i < ClozeSettings.gaps_php[0].length; i++)
                {
                    var j = i+1;
                    if(gap_exists_in_txtarea.indexOf(j + '') == -1)
                    {
                        to_be_removed.push(i);
                    }
                }
                var allready_removed = 0;
                for(i = 0; i < to_be_removed.length; i++)
                {
                    var k = to_be_removed[i] - allready_removed;
                    ClozeSettings.gaps_php[0].splice(k,1);
                    allready_removed++;
                }
                cleanGapCode();
                paintGaps();
                correctCursorPositionInTextarea();
            }
        }
        else
        {
            ClozeSettings.gaps_php[0] = [];
            paintGaps();
        }
    }

    function correctCursorPositionInTextarea()
    {
        if (typeof(tinymce) != 'undefined')
        {
            setTimeout(function (){
                var pos = cursorInGap(ClozeGlobals.cursor_pos);
                if (pos[1] != -1)
                {
                    setCursorPositionTiny(tinyMCE.activeEditor, pos[1]);
                }
                else
                {
                    setCursorPositionTiny(tinyMCE.activeEditor, parseInt(ClozeGlobals.cursor_pos, 10));
                }
            }, 0);
        }
        else
        {
            setTimeout(function (){
                var cloze_text_selector = document.getElementById('cloze_text');
                var pos = cursorInGap(ClozeGlobals.cursor_pos);
                if (pos[1] != -1)
                {
                    setCaretPosition(cloze_text_selector, parseInt(pos[1], 10));
                }
                else
                {
                    setCaretPosition(cloze_text_selector, parseInt(ClozeGlobals.cursor_pos, 10));
                }
            }, 0);
        }
    }

    function createGapListener()
    {
        var selector = $('#createGaps');
        selector.off('click');
        selector.on('click', function () {
            if (getTextAreaValue().match(/\[gap\]/g)) {
                createNewGapCode();
            }
            checkTextAreaAgainstJson();
        });
        //return false;
    }

    function editTextarea(gap_count)
    {
        var text = getTextAreaValue();
        gap_count = parseInt(gap_count, 10)  + 1;
        var regexExpression = '\\[gap ' + gap_count + '\\]([\\s\\S]*?)\\[\\/gap\\]';
        var regex = new RegExp(regexExpression, 'i');
        var stringBuild = '';
        ClozeSettings.gaps_php[0][gap_count - 1].values.forEach(function (entry) {
            stringBuild += entry.answer + ',';
        });
        stringBuild = stringBuild.replace(/,+$/, '');
        var newText = text.replace(regex, '[gap ' + gap_count + ']' + stringBuild + '[/gap]');
        setTextAreaValue(newText);
    }

    function insertGapToJson(index, values)
    {
        var newObjects = new Array({
            answer: '',
            points: 0
        });
        if (values !== null) {
            var objects = values.split(',');
            if (objects !== null) {
                for (var i = 0; i < objects.length; i++) {
                    newObjects[i] = ({
                        answer: objects[i],
                        points: 0
                    });
                }
            }
        }
        var insert = new Object({
            type: 'text',
            values: newObjects
        });
        ClozeSettings.gaps_php[0].splice(index, 0, insert);
    }

    function getCursorPositionTiny(editor)
    {
        var bm = editor.selection.getBookmark(0);
        var selector = '[data-mce-type=bookmark]';
        var bmElements = editor.dom.select(selector);
        editor.selection.select(bmElements[0]);
        editor.selection.collapse();
        var elementID = '######cursor######';
        var positionString = '<span id="' + elementID + '"></span>';
        editor.selection.setContent(positionString);
        var content = editor.getContent({format: 'html'});
        var index = content.indexOf(positionString);
        editor.dom.remove(elementID, false);
        editor.selection.moveToBookmark(bm);
        return index;
    }

    function setCursorPositionTiny(editor, index)
    {
        var content = editor.getContent({format: 'html'});
        if( index == '-1')
        {
            index = 0;
        }
        var part1 = content.substr(0, index);
        var part2 = content.substr(index);
        var bookmark = editor.selection.getBookmark(0);
        var positionString = '<span id="' + bookmark.id + '_start" data-mce-type="bookmark" data-mce-style="overflow:hidden;line-height:0px"></span>';
        var contentWithString = part1 + positionString + part2;
        editor.setContent(contentWithString, ({format: 'raw'}));
        editor.selection.moveToBookmark(bookmark);
        return bookmark;
    }

    function setCaretPosition(element, pos)
    {
        if (element.setSelectionRange) {
            element.focus();
            element.setSelectionRange(pos, pos);
        }
        else if (element.createTextRange) {
            var range = element.createTextRange();
            range.collapse(true);
            range.moveEnd('character', pos);
            range.moveStart('character', pos);
            range.select();
        }
    }

    function cursorInGap(position)
    {
        var text = getTextAreaValue();
        var end = 0;
        var inGap = -1;
        var gapNumber;
        for (var i = 0; i < ClozeSettings.gaps_php[0].length; i++) {
            var start = text.indexOf('[gap ', end);
            end = text.indexOf('[/gap]', parseInt(end, 10)) + 5;
            if (start < position && end >= position)
            {
                inGap = parseInt(end, 10) + 1;
                var gapSize = parseInt(end, 10) - parseInt(start, 10);
                var gapContent = text.substr(parseInt(start, 10) + 5, gapSize);
                gapContent = gapContent.split(']');
                gapNumber = gapContent[0];
            }
        }
        return [gapNumber, inGap];
    }

    function removeFromTextarea(gap_count)
    {
        var text = getTextAreaValue();
        var pos = parseInt(gap_count, 10) + 1;
        var regexExpression = '\\[gap ' + pos + '\\](.*?)\\[\\/gap\\]';
        var regex = new RegExp(regexExpression, 'i');
        var newText = text.replace(regex, '');
        setTextAreaValue(newText);
        cleanGapCode();
    }

    function createNewGapCode()
    {
        var newText = getTextAreaValue();
        var iterator = newText.match(/\[gap[\s\S\d]*?\](.*?)\[\/gap\]/g);
        var last = 0;
        for (var i = 0; i < iterator.length; i++) {
            last = i;
            if (iterator[i].match(/\[gap\]/)) {
                var values = iterator[i].replace(/\[gap\]/, '');
                values = values.replace(/\[\/gap\]/, '');
                var gap_id =  parseInt(i, 10) + 1;
                newText = newText.replace(/\[gap\]/, '[gap ' + gap_id + ']');
                insertGapToJson(last, values);
            }
        }
        setTextAreaValue(newText);
        paintGaps();
        cleanGapCode();
    }

    function cleanGapCode()
    {
        var text = getTextAreaValue();
        var newText = text.replace(/\[gap[\s\S\d]*?\]/g, '[temp]');
        newText = newText.replace(/\[\/gap\]/g, '[/temp]');
        for (var i = 0; i < ClozeSettings.gaps_php[0].length; i++) {
            var gap_id =  parseInt(i, 10) + 1;
            newText = newText.replace(/\[temp\]/, '[gap ' + gap_id + ']');
            newText = newText.replace(/\[\/temp\]/, '[/gap]');
        }
        setTextAreaValue(newText);
    }

    function getTextAreaValue()
    {
        var text;
        if (typeof(tinymce) != 'undefined') {
            text = tinymce.get('cloze_text').getContent();
        }
        else {
            var textarea = $('textarea#cloze_text');
            text = textarea.val();
        }
        return text;
    }

    function setTextAreaValue(text)
    {
        var cursor, inGap;
        if (typeof(tinymce) != 'undefined') {
            //ToDo: Bug in tiny steals focus on setContent (tinymce Bug #6423)
            var inst = tinyMCE.activeEditor;
            cursor = getCursorPositionTiny(inst);
            tinymce.get('cloze_text').setContent(text);
            inGap = cursorInGap(cursor);
            if(inGap[1] != '-1' )
            {
                //var newIndex = parseInt(inGap[1], 10);
                //ClozeGlobals.active_gap = newIndex;
                ClozeGlobals.active_gap = parseInt(inGap[1], 10);
            }
            setCursorPositionTiny(inst, ClozeGlobals.active_gap);
        }
        else {
            var textarea = $('textarea#cloze_text');
            cursor = textarea.prop('selectionStart');
            textarea.val(text);
            inGap = cursorInGap(cursor + 1);
            if(inGap != '-1')
            {
                if(ClozeGlobals.active_gap == '-1')
                {
                    setCaretPosition(textarea, cursor);
                }
                else
                {
                    textarea.prop('selectionStart',ClozeGlobals.active_gap);
                    textarea.prop('selectionEnd',ClozeGlobals.active_gap);
                }
                ClozeGlobals.active_gap = parseInt(inGap[1], 10);
            }
        }
    }

    function cloneFormPart(pos)
    {
        ClozeGlobals.clone_active = pos;
        pos = parseInt(pos, 10) - 1;
        if(ClozeSettings.gaps_php[0][pos])
        {
            var clone_type = ClozeSettings.gaps_php[0][pos].type;
            $('.modal-body').html('');
            if (clone_type === '') {
                clone_type = 'text';
            }
            if (clone_type == 'text') {
                $('#text-gap-r-'                + pos).clone(true).removeAttr('id').appendTo('.modal-body');
                $('#gap_'+ pos +'_gapsize_row'       ).clone(true).removeAttr('id').appendTo('.modal-body');
            }
            else if (clone_type == 'select') {
                $('#select-gap-r-'              + pos).clone(true).removeAttr('id').appendTo('.modal-body');
                $('#shuffle_answers_'           + pos).clone(true).removeAttr('id').appendTo('.modal-body');
            }
            else if (clone_type == 'numeric') {
                $('#numeric-gap-r-'             + pos).clone(true).removeAttr('id').appendTo('.modal-body');
                $('#numeric_answers_'           + pos).clone(true).removeAttr('id').appendTo('.modal-body');
                $('#numeric_answers_lower_'     + pos).clone(true).removeAttr('id').appendTo('.modal-body');
                $('#numeric_answers_upper_'     + pos).clone(true).removeAttr('id').appendTo('.modal-body');
                $('#numeric_answers_points_'    + pos).clone(true).removeAttr('id').appendTo('.modal-body');
                $('#remove_gap_container_'      + pos).clone(true).appendTo('.modal-body');
            }
            $('#text_row_'                      + pos + '_0').clone(true).removeAttr('id').appendTo('.modal-body');
            $('.error_answer_'                  + pos).clone(true).removeAttr('id').appendTo('.modal-body');
            var gapName = parseInt(pos, 10) + 1;
            $('.modal-title').html('Gap ' + gapName);
        }
    }
    function moveFooterBelow()
    {
        $('#gap_json_post').appendTo(ClozeGlobals.form_class);
        $('#gap_json_combination_post').appendTo(ClozeGlobals.form_class);
        //$(ClozeGlobals.form_footer_class).parent().appendTo(ClozeGlobals.form_class);
        $(ClozeGlobals.form_footer_class).appendTo(ClozeGlobals.form_class);
    }
    $(function ()
    {
        $('#ilGapModal').draggable();
    });
});
