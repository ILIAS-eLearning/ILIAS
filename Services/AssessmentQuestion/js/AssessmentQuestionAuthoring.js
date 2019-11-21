// Generic Authoring
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
	for (let i = 0; i < tinymce.editors.length; i++) {
		let editor = tinymce.editors[i];
		let element = $(editor.getElement());
		
		element.val(editor.getContent());		
	}
}

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
	// TODO hack to prevent image verification error
	$('[name=ilfilehash]').remove();
});

$(document).on("click", ".js_add", add_row);
$(document).on("click", ".js_remove", remove_row);
$(document).on("click", ".js_up", up_row);
$(document).on("click", ".js_down", down_row);
$(document).on("change", "#editor, #presenter, #scoring", update_form);
$(document).on("submit", "form", save_tiny);

// **********************************************************************************************
// ErrorTextQuestion Authoring
// **********************************************************************************************

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
}

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
}

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
}

let find_errors = function(text) {
	let errors = [];
	
	let multiword = false;
	let multilength = 0;
	
	for (let i = 0; i < text.length; i++) {
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
}

$(document).on('click', '#process_error_text', process_error_text);

//**********************************************************************************************
//ErrorTextQuestion Authoring
//**********************************************************************************************

let update_definitions = function() {
	update_values('me_definition_text', 'me_match_definition');
}

let update_terms = function() {
	update_values('me_term_text', 'me_match_term');
}

let update_values = function(source, destination) {
	let values = [];
	
	$('input[id$="' + source + '"]').each(function() {
		values.push($(this).val());
	});
	
	$('select[id$="' + destination + '"]').each(function() {
		let that = $(this);
		let selected = that.val();
		that.empty();
		
		for (let i = 1; i <= values.length; i++) {
			that.append(new Option(values[i - 1], i));
		}
		
		if (selected <= values.length) {
			that.val(selected);
		}
	});
}

$(document).on('change', 'input[id$="me_definition_text"]', update_definitions);
$(document).on('click', '#il_prop_cont_me_definitions .js_remove', update_definitions);
$(document).on('change', 'input[id$="me_term_text"]', update_terms);
$(document).on('click', '#il_prop_cont_me_terms .js_remove', update_terms);
