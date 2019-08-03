let answer_selected = function(event) {
    let parent = $(this).parents(".js_multiple_choice");
    let max = parent.children(".js_max_answers").val();
    let current = parent.find(".js_multiple_choice_answer:checkbox:checked").length;

    if (current > max) {
        $(this).prop('checked', false);
        parent.children(".js_limit").css('color', 'red');
    } else {
        parent.children(".js_limit").css('color', '');
    }
};

$(document).on("change", "input[type=checkbox].js_multiple_choice_answer", answer_selected);