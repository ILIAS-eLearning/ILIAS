//Generic Authoring
let has_tiny = false;
let tiny_settings;

let add_row = function() {
    has_tiny = tinymce.editors.length > 0;

    if (has_tiny) {
        clear_tiny();
    }

    let row = $(this).parents(".aot_row").eq(0);
    let table = $(this).parents(".aot_table").children("tbody");

    let new_row = row.clone();

    new_row = clear_row(new_row);
    row.after(new_row);
    set_input_ids(table);

    if (has_tiny) {
        tinymce.init(tiny_settings);
    }

    return false;
};

let clear_tiny = function() {
    tiny_settings = tinymce.editors[0].settings;

    while (tinymce.editors.length > 0) {
        let editor = tinymce.editors[0];
        let element = $(editor.getElement());

        element.val(editor.getContent());
        element.show();

        tinymce.EditorManager.remove(tinymce.editors[0]);

        element.siblings('.mceEditor').remove();
    }
};

let save_tiny = function() {
    let i;
    for (i = 0; i < tinymce.editors.length; i += 1) {
        let editor = tinymce.editors[i];
        let element = $(editor.getElement());

        element.val(editor.getContent());
    }
};

let remove_row = function() {
    has_tiny = tinymce.editors.length > 0;

    if (has_tiny) {
        clear_tiny();
    }

    let row = $(this).parents(".aot_row");
    let table = $(this).parents(".aot_table").children("tbody");

    if (table.children().length > 1) {
        row.remove();
        set_input_ids(table);
    } else {
        clear_row(row);
    }

    if (has_tiny) {
        tinymce.init(tiny_settings);
    }
};

let clear_row = function(row) {
    row.find('input[type!="Button"], textarea').each(function() {
        let input = $(this);

        if (input.attr('type') === 'radio' ||
                input.attr('type') === 'checkbox') {
            input.attr('checked', false);
        }
        else if (input.attr('type') === 'hidden') {
            // dont clear hidden fields
        }
        else {
            input.val('');
        }
    });

    row.find('span').each(function() {
        let span = $(this);
        if (span.children().length === 0) {
            span.html('');
        }
    });

    return row;
};

let up_row = function() {
    let row = $(this).parents(".aot_row");
    row.prev('.aot_row').before(row);
    set_input_ids(row.parents(".aot_table").children("tbody"));
};

let down_row = function() {
    let row = $(this).parents(".aot_row");
    row.next('.aot_row').after(row);
    set_input_ids(row.parents(".aot_table").children("tbody"));
};

let set_input_ids = function(table) {
    table.parent().siblings(".js_count").val(table.children().length);

    let current_row = 1;

    table.children().each(function() {
        process_row($(this), current_row);
        current_row += 1;
    });
};

let process_row = function(row, current_row) {
    row.find("input[name],textarea[name]").each(function() {
        let input = $(this);
        let new_name = update_input_name(input.attr("name"), current_row);

        // if already an item with the new name exists
        // (when swapping) set the other element name
        // to current oldname to prevent name collision
        // and losing of radio values
        if (input.attr('type') === 'radio') {
            let existing_group = $('[name="' + new_name + '"]');

            if (existing_group.length > 0) {
                let my_name = input.attr("name");
                let my_group = $('[name="' + my_name + '"]');
                my_group.attr("name", "totally_random");
                existing_group.attr("name", my_name);
                my_group.attr("name", new_name);
            }
        }
        else {
            input.attr("name", new_name);
        }

        input.prop("id", update_input_name(input.prop("id"), current_row));
    });
};

let update_input_name = function(old_name, current_row) {
    return current_row + old_name.match(/\D+/);
};

let update_form = function() {
    let initiator = $(this);
    // decode &amp to &
    let decoded_link = $('<div>').html($('#form_part_link').val()).text();

    let url = decoded_link + '&class=' + initiator.children("option:selected").text();

    $.ajax(url)
    .done (function (data) {
        let initiator_row = initiator.parents('.form-group');

        while (initiator_row.next().find("#editor, #presenter, #scoring, #answer_form").length === 0 &&
                !initiator_row.next().hasClass("ilFormFooter")) {
            initiator_row.next().remove();
        }

        initiator_row.after($(data).find('.form-horizontal > .form-group'));
        $('#answer_form').parents('.form-group').remove();
    });
};

$(document).ready(function() {
    // hack to prevent image verification error
    $('[name=ilfilehash]').remove();
});

$(document).on("click", ".js_add", add_row);
$(document).on("click", ".js_remove", remove_row);
$(document).on("click", ".js_up", up_row);
$(document).on("click", ".js_down", down_row);
$(document).on("change", "#editor, #presenter, #scoring", update_form);
$(document).on("submit", "form", save_tiny);

//**********************************************************************************************
//Multiple Choice Authoring
//**********************************************************************************************

let image_header = '';

let show_multiline_editor = function() {
    tinymce.init({
        selector : 'input[id$=mcdd_text]'
    });
    
    $('input[id$=mcdd_image]').each(function(index, item) {
        let td = $(item).parents('td');
        td.children().hide();
        
        if (image_header.length == 0) {
            let th = td.closest('table').find('th').eq(td.index())[0];
            image_header = th.innerHTML;
            th.innerHTML = '';
        }
    });
}

let hide_multiline_editor = function() {
    $('input[id$=mcdd_text]').each(function(index, item) {
        let element = $(item);
        let editor = tinymce.get(element.attr('id'));

        if(!editor) {
            return;
        }
        
        element.val(editor.getContent());
        element.show();

        tinymce.EditorManager.remove(editor);

        element.siblings('.mceEditor').remove();
    });
    
    $('input[id$=mcdd_image').each(function(index, item) {
        let td = $(item).parents('td');
        td.children().show();      
        
        if (image_header.length > 0) {
            let th = td.closest('table').find('th').eq(td.index())[0];
            th.innerHTML = image_header; 
            image_header = '';
                       
        }
    });
}

let update_editor = function() {
    if ($('#singleline').val() == 'true') {
        hide_multiline_editor();
    }
    else {
        show_multiline_editor();
    }
}

$(document).ready(function() {
    update_editor();
});


$(document).on("change", "#singleline", update_editor);

//**********************************************************************************************
//ErrorTextQuestion Authoring
//**********************************************************************************************

class ErrorDefinition {
    constructor(start, length) {
        this.start = start;
        this.length = length;
    }
}

let process_error_text = function() {
    let text = $('#ete_error_text').val().split(' ');

    let errors = find_errors(text);

    if (errors.length > 0) {
        prepare_table(errors.length);
    }
    else {
        $('.aot_table').hide();
    }

    display_errors(errors, text);
};

let display_errors = function(errors, text) {
    $('.aot_table tbody').children().each(function (i, rrow) {
        let error = errors[i];
        let row = $(rrow);
        let label = text.slice(error.start, error.start + error.length).join(' ');
        label = label.replace('((', '').replace('))', '').replace('#', '');

        row.find('.etsd_wrong_text').text(label);
        row.find('#' + (i + 1) + 'etsd_word_index').val(error.start);
        row.find('#' + (i + 1) + 'etsd_word_length').val(error.length);
    });
};

let prepare_table = function(length) {
    $('#answer_form').show();
    let table = $('.aot_table tbody');
    let row = table.children().eq(0);

    row.siblings().remove();

    clear_row(row);

    while (length > table.children().length) {
        table.append(row.clone());
    }

    set_input_ids(table);
};

let find_errors = function(text) {
    let errors = [];

    let multiword = false;
    let multilength = 0;

    let i;
    for (i = 0; i < text.length; i += 1) {
        if (text[i].startsWith('#')) {
            errors.push(new ErrorDefinition(i, 1));
        }
        else if (text[i].startsWith('((')) {
            multiword = true;
            multilength = 0;
        }

        if (multiword) {
            multilength += 1;
        }

        if (multiword && text[i].endsWith('))')) {
            errors.push(new ErrorDefinition(i - (multilength - 1), multilength));
            multiword = false;
        }
    }

    return errors;
};

$(document).on('click', '#process_error_text', process_error_text);

//**********************************************************************************************
//Matching Authoring
//**********************************************************************************************

let MATCHING_ONE_TO_ONE = '0';
let MATCHING_MANY_TO_ONE = '1';
let MATCHING_MANY_TO_MANY = '2';
let matching_mode;

let used_terms = [];
let used_definitions = [];

let update_definitions = function() {
    update_values('me_definition_text', 'me_match_definition', used_definitions);
};

let update_terms = function() {
    update_values('me_term_text', 'me_match_term', used_terms);
};

let update_values = function(source, destination, useds) {
    let values = {};

    let i = 1;
    $('input[id$="' + source + '"]').each(function() {
        let val = $(this).val();


        if (!useds.includes(i.toString())) {
            values[i] = val;
        }

        i += 1;
    });

    $('select[id$="' + destination + '"]').each(function() {
        let that = $(this);
        let selected_val = that.val();
        let selected_text = that.children('option:selected').text();
        that.empty();

        Object.keys(values).forEach(function(key) {
            that.append(new Option(values[key], key));
        });

        if (useds.includes(selected_val)) {
            let found = false;

            that.children().each(function(index, child) {
                let child_val = parseInt($(child).val());

                if (child_val > parseInt(selected_val) && !found) {
                    $(child).before(new Option(selected_text, selected_val));
                    found = true;
                }
            });

            if(!found) {
                that.append(new Option(selected_text, selected_val));
            }
        }

        that.val(selected_val);
    });
};

let update_used_definitions = function() {
    if (matching_mode === MATCHING_ONE_TO_ONE) {
        update_used('me_match_definition', used_definitions);
    }
    else {
        used_definitions = [];
    }

    update_values('me_definition_text', 'me_match_definition', used_definitions);
};

let update_used_terms = function() {
    if (matching_mode === MATCHING_ONE_TO_ONE ||
            matching_mode === MATCHING_MANY_TO_ONE)
    {
        update_used('me_match_term', used_terms);
    } else {
        used_terms = [];
    }

    update_values('me_term_text', 'me_match_term', used_terms);
};

let update_used = function(selects, useds) {
    useds.splice(0, useds.length);

    $('select[id$="' + selects + '"]').each(function() {
        let val = $(this).val();
        if (val !== null) {
            useds.push(val);
        }
    });
};

let clean_added_row = function() {
    $(this).parents('.aot_table').find('tr').last().find('select').each(function() {
        $(this).empty();
    });

    update_definitions();
    update_terms();
};

$(document).ready(function() {
    if ($('input[name=me_matching]').length > 0) {
        matching_mode = $('input[name=me_matching]:checked').val();
        update_used_definitions();
        update_used_terms();
    }
});

$(document).on('change', 'input[name=me_matching]', function() {
    matching_mode = $(this).val();
    update_used_definitions();
    update_used_terms();
});

$(document).on('change', 'input[id$="me_definition_text"]', update_definitions);
$(document).on('click', '#il_prop_cont_me_definitions .js_remove', update_definitions);
$(document).on('change', 'input[id$="me_term_text"]', update_terms);
$(document).on('click', '#il_prop_cont_me_terms .js_remove', update_terms);
$(document).on('change', 'select[id$=me_match_definition]', update_used_definitions);
$(document).on('change', 'select[id$=me_match_term]', update_used_terms);
$(document).on("click", "#il_prop_cont_me_matches .js_add", clean_added_row);
