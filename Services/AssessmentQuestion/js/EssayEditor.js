let text_length;
let max_length;
let has_max_length = false;

let update_counts = function (e) {
    if (!tinymce) {
        return;
    }

    let body = tinymce.editors[0].getBody();
    let text = tinymce.trim(body.innerText || body.textContent);
    text_length = text.length;

    $('.js_letter_count').html(text_length);
};

let check_values = function () {
    if (has_max_length) {
        if (text_length > max_length) {
            // TODO use ilias modalpopup
            alert($('.js_error').val());
            return false;
        }
    }
};

$(document).on('keyup', '#ilAsqQuestionView', update_counts);
$(document).on('submit', 'main form', check_values);

$(document).ready(function () {
    tinymce.init({
        selector : 'textarea',
        init_instance_callback : function (editor) {
            editor.onKeyUp.add(update_counts);
            update_counts();
        }
    });

    if ($('.js_maxlength').length > 0) {
        max_length = parseInt($('.js_maxlength').val());
        has_max_length = true;
    }
});