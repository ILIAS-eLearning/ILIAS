<!-- BEGIN tinymce -->
<!-- BEGIN obj_id -->
var obj_id = '{OBJ_ID}';
var obj_type = '{OBJ_TYPE}';
window.obj_id = obj_id;
window.obj_type = obj_type;
<!-- END obj_id -->
var client_id = '{CLIENT_ID}';
var session_id = '{SESSION_ID}';
var image_update = 0;
window.image_update = image_update;

window.client_id = client_id;
window.session_id = session_id;

//helper function to translate formats
function ilTinyMCETranslateFormats() {
    var block_formats = "{BLOCKFORMATS}";
    var block_array = block_formats.split(',');
    var translated_formats = [];
    for (var format in block_array) {
        var title = "";
        switch (block_array[format]) {
            case "p":
                title = 'Paragraph';
                break;
            case "div":
                title = 'Div';
                break;
            case "pre":
                title = 'Preformatted';
                break;
            case "code":
                title = 'Code';
                break;
            case "h1":
                title = 'Heading 1';
                break;
            case "h2":
                title = 'Heading 2';
                break;
            case "h3":
                title = 'Heading 3';
                break;
            case "h4":
                title = 'Heading 4';
                break;
            case "h5":
                title = 'Heading 5';
                break;
            case "h6":
                title = 'Heading 6';
                break
            case "":
                continue;
            default:
                //Do nothing
        }
        var title_translation = tinymce.translate(title);
        if (title_translation === undefined) {
            title_translation = title;
        }
        translated_formats.push(title_translation + "=" + block_array[format]);
    }
    var result = translated_formats.join(';');
    return result;

}

function ilTinyMceInitCallback(ed) {
    <!-- BEGIN remove_img_context_menu_item -->
    <!-- END remove_img_context_menu_item -->
}

tinymce.init({
    license_key: 'gpl',
    selector: "textarea.RTEditor",
    branding: false,
    language: "{LANG}",
    block_formats: ilTinyMCETranslateFormats(),
    plugins: "{ADDITIONAL_PLUGINS}",
    menubar: false,
    toolbar: "{BUTTONS_1} {BUTTONS_2} {BUTTONS_3}",
    toolbar_sticky: true,
    toolbar_persist: true,
    toolbar_mode: 'wrap',
    image_advtab: true,
    image_title: true,
    images_file_types: "gif, jpg, jpeg, png",
    file_picker_types: "image",
    automatic_uploads: true,
    importcss_append: true,
    plugin_insertdate_dateFormat: "%d.%m.%Y",
    plugin_insertdate_timeFormat: "%H:%M:%S",
    image_caption: true,
    quickbars_selection_toolbar: '',
    noneditable_noneditable_class: 'mceNonEditable',
    contextmenu: 'cut copy paste link unlink image imagetools table ilimgupload table',
    skin: 'oxide',
    entities: "60,lt,62,gt,38,amp",
    content_css: "{STYLESHEET_LOCATION}",
    content_style: 'html { overflow: initial; }',
    fix_list_elements: true,
    valid_elements: "{VALID_ELEMENTS}",
    <!-- BEGIN formelements -->
    extended_valid_elements: "form[name|id|action|method|enctype|accept-charset|onsubmit|onreset|target],input[id|name|type|value|size|maxlength|checked|accept|s rc|width|height|disabled|readonly|tabindex|accessk ey|onfocus|onblur|onchange|onselect],textarea[id|name|rows|cols|disabled|readonly|tabindex|acces skey|onfocus|onblur|onchange|onselect],option[name|id|value],select[id|name|type|value|size|maxlength|checked|accept|s rc|width|height|disabled|readonly|tabindex|accessk ey|onfocus|onblur|onchange|onselect|length|options |selectedIndex]",
    <!-- END formelements -->
    <!-- BEGIN forced_root_block -->forced_root_block : '{FORCED_ROOT_BLOCK}',<!-- END forced_root_block -->
    setup: function(ed) {
        ed.on('init', ilTinyMceInitCallback);

    },

});
<!-- END tinymce -->