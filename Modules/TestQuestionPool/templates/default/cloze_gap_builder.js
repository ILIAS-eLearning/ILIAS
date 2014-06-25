$(document).ready(function ()
{
    "use strict";
    var clone_active = -1;

    bindTextareaHandler();
    paintGaps();
    createGapListener();

    function paintGaps()
    {
        $(".interactive").remove();
        var c = 0;
        gaps_php.forEach(function (obj, counter) {
            obj.forEach(function () {
                var type = obj[c].type;
                var values = obj[c].values;
                var text_field_length = obj[c].text_field_length;
                var shuffle = 0;
                var upper = '';
                var lower = '';
                if (type === "select") {
                    shuffle = obj[c].shuffle;
                }
                if (type === "numeric") {
                    upper = obj[c].upper;
                    lower = obj[c].lower;
                }
                buildFormObject(type, c, values, text_field_length, shuffle, upper, lower);
                c++;
            });
        });

        bindSelectHandler();
        bindInputHandler();
        checkForm();
        if (clone_active != -1) {
            cloneFormPart(clone_active);
        }
        if (typeof(tinyMCE) != "undefined") {
            if (tinyMCE.activeEditor === null || tinyMCE.activeEditor.isHidden() !== false) {
                ilTinyMceInitCallbackRegistry.addCallback(bindTextareaHandlerTiny);
             }
        }
    }

    $('#gaptrigger').on('click', function (evt)
    {
        //evt.preventDefault();
        $('#cloze_text').insertGapCodeAtCaret();
        createNewGapCode();
        return false;
    });

    function buildNumericFormObjectHelper(row, type, value)
    {
        $('#numeric_prototype_numeric' + type).clone().attr({
            'id': 'numeric_answers' + type + '_' + row,
            'class': 'ilFormRow interactive'
        }).appendTo(".ilForm");
        $('#numeric_answers' + type + '_' + row).find('#gap_a_numeric' + type).attr({
            'id': 'gap_' + row + '_numeric' + type,
            'name': 'gap_' + row + '_numeric' +type,
            'value': value,
            'class': 'numeric_gap gap_' + row + '_numeric' +type
        });

    }

    function buildFormObject(type, counter, values, gap_field_length, shuffle, upper, lower)
    {
        buildTitle(counter);
        buildSelectionField(type, counter);

        if (type === 'text' || type == 'numeric') {
            $('#prototype_gapsize').clone().attr({
                'id': 'gap_' + counter + '_gapsize_row',
                'name': 'gap_' + counter + '_gapsize_row',
                'class': 'ilFormRow interactive'
            }).appendTo(".ilForm");
            $('#gap_' + counter + '_gapsize_row').find('#gap_a_gapsize').attr({
                'id': 'gap_' + counter + '_gapsize',
                'name': 'gap_' + counter + '_gapsize',
                'value': gap_field_length
            });
        }
        if (type === "text") {
            changeIdentifierTextField(type, counter, values);
        }
        else if (type === "select") {
            $('#shuffle_answers').clone().attr({
                'id': 'shuffle_answers_' + counter,
                'class': 'ilFormRow interactive'
            }).appendTo(".ilForm");
            changeIdentifierTextField(type, counter, values);
            if (shuffle === true) {
                $('#shuffle_' + counter).prop('checked', true);
            }
        }
        else if (type === "numeric") {
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
                'class': 'ilFormRow interactive'
            }).appendTo(".ilForm");
        }
        $('#error_answer').clone().attr({
            'id': 'gap_error_' + counter,
            'class': 'ilFormRow interactive'
        }).appendTo(".ilForm");
        $('#gap_error_' + counter).find('#error_answer_val').attr({
            'class': 'error_answer_' + counter,
            'name': 'error_answer_' + counter
        });

        moveFooterBelow();
    }
    function highlightRed(selector)
    {
        selector.css('background-color', 'rgba(255,0,0,0.4)');
    }
    function removeHighlight(selector)
    {
        selector.css('background-color', '');
    }
    function checkInputElementNotEmpty(selector,value)
    {
        if (value === "" || value === null) {
            highlightRed(selector);
            return 1;
        }
        else {
            removeHighlight(selector);
            return 0;
        }
    }
    function checkForm()
    {
        var row = 0;
        gaps_php[0].forEach(function (entry) {
            if (entry.type === 'numeric') {
                var input_failed = 0;
                input_failed+= checkInputElementNotEmpty($('.gap_' + row + '_numeric'),entry.values[0].answer);
                input_failed+= checkInputElementNotEmpty($('.gap_' + row + '_numeric_upper'),entry.values[0].upper);
                input_failed+= checkInputElementNotEmpty($('.gap_' + row + '_numeric_lower'),entry.values[0].lower);
                if(entry.values[0].error != false)
                {
                    var obj=entry.values[0].error;
                    if(obj)
                    {
                        Object.keys(obj).forEach(function (key)
                        {
                            console.log(obj[key],key)
                            if(obj[key]===true)
                            {
                                highlightRed($('#gap_' + row + '_numeric_' + key));
                                showHidePrototypes(row,'formula',true);
                                console.log('#gap_' + row + '_numeric_' + key)
                            }
                            else
                            {
                                removeHighlight($('#gap_' + row + '_numeric_' + key));
                            }
                        });
                    }

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
                var input_failed = 0;
                entry.values.forEach(function (values) {
                    points += values.points;
                    if(isNaN(values.points) || values.points === '' ){
                        highlightRed($('#gap_' + row + '\\[points\\]\\[' + counter + '\\]'));
                        number=false;
                    }
                    else
                    {
                        removeHighlight($('#gap_' + row + '\\[points\\]\\[' + counter + '\\]'));
                    }
                    input_failed += checkInputElementNotEmpty($('#gap_' + row + '\\[answer\\]\\[' + counter + '\\]'),values.answer);
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
                if (points == 0) {
                    highlightRed($('.gap_points_' + row));
                    showHidePrototypes(row,'points',true);
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
        });
        $('#gap_json_post').attr('value',JSON.stringify(gaps_php));
    }

    function checkInputIsNumeric(number,row,field)
    {
        if(isNaN(number) || number === ""){
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
            if(type == "value")
            {
                $('.error_answer_' + row).find('.value').attr('class', 'value');
            }
            else if(type == "points")
            {
                $('.error_answer_' + row).find('.points').attr('class', 'points');
            }
            else if(type == "number")
            {
                $('.error_answer_' + row).find('.number').attr('class', 'number');
            }
            else if(type == "formula")
            {
                $('.error_answer_' + row).find('.formula').attr('class', 'formula');
            }
        }
        else
        {
            if(type == "value")
            {
                $('.error_answer_' + row).find('.value').attr('class', 'prototype value');
            }
            else if(type == "points")
            {
                $('.error_answer_' + row).find('.points').attr('class', 'prototype points');
            }
            else if(type == "number")
            {
                $('.error_answer_' + row).find('.number').attr('class', 'prototype number');
            }
            else if(type == "formula")
            {
                $('.error_answer_' + row).find('.formula').attr('class', 'prototype formula');
            }
        }
    }

    function buildTitle(counter)
    {
        $('#gap_title').clone().attr({
            'id': 'tile_' + counter,
            'name': 'tile_' + counter,
            'class': 'ilFormRow interactive'
        }).appendTo('.ilForm');
        $('#tile_' + counter).find('h3').text('Gap ' + (counter + 1));
    }

    function changeIdentifierTextField(type, counter_question, answers)
    {
        var c = 0;
        answers.forEach(function (s) {
            if (c == 0) {
                $('#answer_text').clone().attr(
                    {
                        'id': 'text_row_' + counter_question + '_' + c,
                        'class': 'ilFormRow interactive'
                    }).appendTo('.ilForm');
                $('#text_row_' + counter_question + '_' + c).find('#table_body').attr(
                    {
                        'id': 'table_body_' + counter_question
                    });
            }
            else {
                $('#inner_text').clone().attr(
                    {
                        'id': 'text_row_' + counter_question + '_' + c,
                        'class': 'ilFormRow interactive'
                    }).appendTo('#table_body_' + counter_question);
            }
            var text_row_selector= $('#text_row_' + counter_question + '_' + c);
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
            'class': 'ilFormRow interactive'
        }).appendTo('.ilForm');
        var select_field_selector = $('#' + type + '-gap-r-' + counter);
        select_field_selector.children('.ilFormOption').attr('id', type + '-gap-r-' + counter);
        select_field_selector.children().children('.select_type').attr(
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

    $('.clone_fields_add').live('click', function ()
    {
        var getPosition = $(this).attr('name');
        var pos = getPosition.split('_');
        var insert = new Object({
            points: '0',
            answer: ''
        });
        
        gaps_php[0][pos[2]].values.splice(parseInt(pos[3]) + 1, 0, insert);
        paintGaps();
    });

    $('.clone_fields_remove').live('click', function ()
    {
        var getPosition = $(this).attr('name');
        var pos = getPosition.split('_');
        gaps_php[0][pos[2]].values.splice(pos[3], 1);
        editTextarea(pos[2]);
        if (gaps_php[0][pos[2]].values.length == 0) {
            gaps_php[0].splice(pos[2], 1);
            removeFromTextarea(pos[2]);
        }
        paintGaps();
    });

    $('.remove_gap_button').live('click', function ()
    {
        var getPosition = $(this).closest('.interactive').attr('id');
        var pos = getPosition.split('_');
        var position = pos[2];
        if (position == "container") {
            position = pos[3];
        }
        if (confirm($('#delete_gap_question').text())) {

            gaps_php[0].splice(position, 1);
            removeFromTextarea(position);
            paintGaps();
        }
    });

    function bindSelectHandler()
    {
        $('.select_type').change(function () {

            var value = $(this).attr('value');
            var id = $(this).attr('id').split('_');
            if (value == 0) {
                gaps_php[0][id[1]].type = 'text';
            }
            else if (value == 1) {
                gaps_php[0][id[1]].type = 'select';
            }
            else if (value == 2) {
                gaps_php[0][id[1]].values = new Object(new Array({
                    answer: '',
                    lower: '',
                    upper: '',
                    points: 0
                }));
                gaps_php[0][id[1]].type = 'numeric';
                editTextarea(id[1]);
            }
            paintGaps();
        });
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
        $('.text_field').bind('blur keyup', function(event){
            var pos = getPositionFromInputs($(this));
            gaps_php[0][pos[0]].values[pos[1]].answer = $(this).val();
            editTextarea(pos[0]);
            if (clone_active != -1) {
                if (event.type == 'blur') {
                    $('.interactive').find('#gap_' + pos[0] + '\\[answer\\]\\[' + pos[1] + '\\]').val($(this).val());
                }
            }
            checkForm();
        });
        $('.gap_points').keyup(function () {
            var pos = getPositionFromInputs($(this));
            gaps_php[0][pos[0]].values[pos[1]].points = $(this).val();
            if (clone_active != -1) {
                $('.interactive').find('#gap_' + pos[0] + '\\[points\\]\\[' + pos[1] + '\\]').val($(this).val());
            }
            checkForm();
        });
        $('.shuffle').change(function () {
            var pos = getPositionFromInputs($(this),true);
            var checked = $(this).is(":checked");
            gaps_php[0][pos[1]].shuffle = checked;
            if (clone_active != -1) {
                $('.interactive').find('#shuffle_' + pos[1]).attr('checked', checked);
            }
            checkForm();
        });
        $('.numeric_gap').keyup(function () {
            var pos = getPositionFromInputs($(this),true);
            if (pos.length == 3) {
                gaps_php[0][pos[1]].values[0].answer = $(this).val();
                editTextarea(pos[1]);
                if (clone_active != -1) {
                    $('.interactive').find('#gap_' + pos[1] + '_numeric').val($(this).val());
                }
            }
            else {
                if (pos[3] == 'lower') {
                    gaps_php[0][pos[1]].values[0].lower = $(this).val();
                    if (clone_active != -1) {
                        $('.interactive').find('#gap_' + pos[1] + '_numeric_lower').val($(this).val());
                    }
                }
                else if (pos[3] == 'upper') {
                    gaps_php[0][pos[1]].values[0].upper = $(this).val();
                    if (clone_active != -1) {
                        $('.interactive').find('#gap_' + pos[1] + '_numeric_upper').val($(this).val());
                    }
                }
                else if (pos[3] == 'points') {
                    gaps_php[0][pos[1]].values[0].points = $(this).val();
                    if (clone_active != -1) {
                        $('.interactive').find('#gap_' + pos[1] + '_numeric_points').val($(this).val());
                    }
                }
            }
            checkForm();
        });
    }

    function bindTextareaHandler()
    {
        var cloze_text_selector= $('#cloze_text');
        cloze_text_selector.on('keydown', function () {
            var cursorPosition = $('#cloze_text').prop('selectionStart');
            var pos = cursorInGap(cursorPosition);
            if (pos[1] != -1) {
                setCaretPosition(document.getElementById('cloze_text'), pos[1]);
                cloneFormPart(pos[0]);
                window.location.hash = 'lightbox';
                return false;
            }
        });
        cloze_text_selector.click(function () {
            var cursorPosition = $('#cloze_text').prop('selectionStart');
            var pos = cursorInGap(cursorPosition);
            if (pos[1] != -1) {
                setCaretPosition(document.getElementById('cloze_text'), pos[1]);
                cloneFormPart(pos[0]);
                window.location.hash = 'lightbox';
                return false;
            }
        });
    }

    function bindTextareaHandlerTiny()
    {
        var tinymce_iframe_selector =   $('.mceIframeContainer iframe').eq(1).contents().find('body');
        tinymce_iframe_selector.keydown(function () {
            var inst = tinyMCE.activeEditor;
            var cursorPosition = getCursorPositionTiny(inst);
            var pos = cursorInGap(cursorPosition);
            if (pos[1] != -1) {
                setCursorPositionTiny(inst, pos[1]);
                cloneFormPart(pos[0]);
                window.location.hash = 'lightbox';
                return false;
            }
        });
        tinymce_iframe_selector.click(function () {
            var inst = tinyMCE.activeEditor;
            var cursorPosition = getCursorPositionTiny(inst);
            var pos = cursorInGap(cursorPosition);
            if (pos[1] != -1) {
                //setCursorPositionTiny(inst,pos[1]);
                cloneFormPart(pos[0]);
                window.location.hash = 'lightbox';
                return false;
            }
        });
    }

    function checkTextAreaAgainstJson()
    {
        var fail = false;
        var text = getTextAreaValue();
        var text_match = text.match(/\[gap[\s\S\d]*?\](.*?)\[\/gap\]/g);
        if (text_match !== null) {
            for (var i = 0; i < text_match.length; i++) {
                var pos = parseInt(i) + 1;
                var string_match = '[gap ' + pos + ']';
                for (var j = 0; j < gaps_php[0][i].values.length; j++) {
                    string_match += gaps_php[0][i].values[j].answer + ',';
                }
                string_match = string_match.replace(/,+$/, '');
                string_match += '[/gap]';
                if (text_match[i] != string_match) {
                    console.log('BROKEN', text_match[i], string_match, 'text_row_' + i + '_0');
                    $('#text_row_' + i + '_0').css('background-color', 'orange');
                    fail = true;
                }
            }
        }
        if (fail === true) {
            $('textarea#cloze_text').css('background-color', 'orange');
        }
        else {
            $('.interactive').css('background-color', '');
            $('textarea#cloze_text').css('background-color', '');
            $('#createGaps').css('color', '');
        }
    }

    function createGapListener()
    {
        $('#createGaps').on('click', function () {
            if (getTextAreaValue().match(/\[gap\]/g)) {
                createNewGapCode();
            }
            checkTextAreaAgainstJson();
            return false;
        });
    }

    function editTextarea(gap_count)
    {
        var text = getTextAreaValue();
        gap_count = parseInt(gap_count) + 1;
        var regexExpression = '\\[gap ' + gap_count + '\\]([\\s\\S]*?)\\[\\/gap\\]';
        var regex = new RegExp(regexExpression, 'i');
        var stringBuild = '';
        gaps_php[0][gap_count - 1].values.forEach(function (entry) {
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
            var objects = values.split(",");
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

        gaps_php[0].splice(index, 0, insert);
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
        for (var i = 0; i < gaps_php[0].length; i++) {
            var start = text.indexOf('[gap ', end);
            end = text.indexOf('[/gap]', parseInt(end)) + 5;
            if (start < position && end >= position) {
                inGap = start;
                gapNumber = text.substr(parseInt(start) + 5, 1);
            }
        }
        return [gapNumber, inGap];
    }

    function removeFromTextarea(gap_count)
    {
        var text = getTextAreaValue();
        var pos = parseInt(gap_count) + 1;
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
                newText = newText.replace(/\[gap\]/, '[gap ' + parseInt(i + 1) + ']');
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
        for (var i = 0; i < gaps_php[0].length; i++) {
            newText = newText.replace(/\[temp\]/, '[gap ' + parseInt(i + 1) + ']');
            newText = newText.replace(/\[\/temp\]/, '[/gap]');
        }
        setTextAreaValue(newText);
    }

    function getTextAreaValue()
    {
        var text;
        
        if (typeof(tinymce) != "undefined") {
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
        if (typeof(tinymce) != "undefined") {
            //ToDo: Bug in tiny steals focus on setContent (tinymce Bug #6423)
            tinymce.get('cloze_text').setContent(text);
        }
        else {
            var textarea = $('textarea#cloze_text');
            textarea.val(text);
        }
    }

    function moveFooterBelow()
    {
        $('#gap_json_post').appendTo('.ilForm');
        $('.ilFormFooter').parent().appendTo('.ilForm');
    }

    function cloneFormPart(pos)
    {
        if(($('#ilCharSelectorPanel').css('display'))=='block')
        {
            $('.lightbox-target').css('top','200px');
        }
        clone_active = pos;
        pos = parseInt(pos) - 1;
        var clone_type = gaps_php[0][pos].type;
        $('#lightbox_content').html('');
        if (clone_type === '') {
            clone_type = 'text';
        }
        if (clone_type == 'text') {
            $('#text-gap-r-' + pos).clone(true).removeAttr("id").appendTo('#lightbox_content');
        }
        else if (clone_type == 'select') {
            $('#select-gap-r-' + pos).clone(true).removeAttr("id").appendTo('#lightbox_content');
            $('#shuffle_answers_' + pos).clone(true).removeAttr("id").appendTo('#lightbox_content');
        }
        else if (clone_type == 'numeric') {
            $('#numeric-gap-r-' + pos).clone(true).removeAttr("id").appendTo('#lightbox_content');
            $('#numeric_answers_' + pos).clone(true).removeAttr("id").appendTo('#lightbox_content');
            $('#numeric_answers_lower_' + pos).clone(true).removeAttr("id").appendTo('#lightbox_content');
            $('#numeric_answers_upper_' + pos).clone(true).removeAttr("id").appendTo('#lightbox_content');
            $('#numeric_answers_points_' + pos).clone(true).removeAttr("id").appendTo('#lightbox_content');
        }
        $('#text_row_' + pos + '_0').clone(true).removeAttr("id").appendTo('#lightbox_content');
        $('.error_answer_' + pos).clone(true).removeAttr("id").appendTo('#lightbox_content');
        var gapName = parseInt(pos) + 1;
        $('#lightbox_title_top').find('h3').html('Gap ' + gapName);
        $('#lightbox_title_bottom').find('h3').html('Gap ' + gapName);
    }

    $(function ()
    {
        $("#lightbox_inner").draggable();
    });
});
