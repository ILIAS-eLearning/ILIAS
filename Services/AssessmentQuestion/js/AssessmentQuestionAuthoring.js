let usingTiny = false;

let add_row = function() {
	usingTiny = false;
    let row = $(this).parents(".aot_table").find(".aot_row").eq(0);
    let table = $(this).parents(".aot_table").children("tbody");

    let new_row = row.clone();

    new_row = clear_row(new_row);
    new_count = table.children().length + 1;
    process_row(new_row, new_count);
    table.append(new_row);
    $(".js_count").val(new_count);
    
    if (usingTiny) {
    	tinymce.init(tinymce.get()[0].settings);
    }
};

let remove_row = function() {
    let row = $(this).parents(".aot_row");
    let table = $(this).parents(".aot_table").children("tbody");

    if (table.children().length > 1) {
        row.remove();
        set_input_ids(table);
    } else {
        clear_row(row);
    }
};

let clear_row = function(row) {
    row.find("input, textarea").each(function() {
    	$input = $(this);
    	
    	if ($input.attr('type') === 'radio' ||
    		$input.attr('type') === 'checkbox') {
    		$input.attr('checked', 'checked');
    	}
    	else {
    		$input.val('');
    	}
        
        if ($input.siblings('.mceEditor').length > 0) {
        	$input.siblings('.mceEditor').remove();
        	$input.show();
        	usingTiny = true;
        }
    });

    return row;
};

let up_row = function() {
	let row = $(this).parents(".aot_row");
	row.prev('.aot_row').before(row);
	set_input_ids(row.parents(".aot_table").children("tbody"));
}

let down_row = function() {
	let row = $(this).parents(".aot_row");
	row.next('.aot_row').after(row);
	set_input_ids(row.parents(".aot_table").children("tbody"));
}

let set_input_ids = function(table) {
    $(".js_count").val(table.children().length);

    let current_row = 1;

    table.children().each(function() {
    	process_row($(this), current_row);
        current_row += 1;
    });
};

let process_row = function(row, current_row) {
    row.find("input[name],textarea[name]").each(function() {
        let input = $(this);
        input.attr("name", update_input_name(input.attr("name"), current_row));
        input.prop("id", update_input_name(input.prop("id"), current_row));
    });	
}

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
}

$(document).ready(function() {
	//TODO hack to prevent image verification error
	$('[name=ilfilehash]').remove();
});

$(document).on("click", ".js_add", add_row);
$(document).on("click", ".js_remove", remove_row);
$(document).on("click", ".js_up", up_row);
$(document).on("click", ".js_down", down_row);
$(document).on("change", "#editor, #presenter, #scoring", update_form);


