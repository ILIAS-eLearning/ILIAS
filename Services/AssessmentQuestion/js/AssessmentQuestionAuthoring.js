let add_row = function() {
    let row = $(this).parents(".aot_table").find(".aot_row").eq(0);
    let table = $(this).parents(".aot_table").children("tbody");

    let new_row = row.clone();

    new_row = clear_row(new_row);
    new_count = table.children().length + 1;
    process_row(new_row, new_count);
    table.append(new_row);
    $(".js_count").val(new_count);
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
    row.find("input").each(function() {
        $(this).val('');
    });

    return row;
};

let set_input_ids = function(table) {
    $(".js_count").val(table.children().length);

    let current_row = 1;

    table.children().each(function() {
    	process_row($(this), current_row);
        current_row += 1;
    });
};

let process_row = function(row, current_row) {
    row.find("input[name]").each(function() {
        let input = $(this);
        input.attr("name", update_input_name(input.attr("name"), current_row));
        input.prop("id", update_input_name(input.prop("id"), current_row));
    });	
}

let update_input_name = function(old_name, current_row) {
    return current_row + old_name.match(/\D+/);
};

$(document).on("click", ".js_add", add_row);
$(document).on("click", ".js_remove", remove_row);