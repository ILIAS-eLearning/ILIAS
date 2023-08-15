<!-- BEGIN tinymce -->
<!-- BEGIN obj_id -->
var obj_id = '{OBJ_ID}';
var obj_type = '{OBJ_TYPE}';
window.obj_id = obj_id;
window.obj_type = obj_type;
<!-- END obj_id -->
var client_id = '{CLIENT_ID}';
var session_id = '{SESSION_ID}';

window.client_id = client_id;
window.session_id = session_id;

var image_update = 0;
window.image_update = image_update;
window.translations = "";
//helper function
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
    // Add hook for onContextMenu so that Insert Image can be removed
    <!-- BEGIN remove_img_context_menu_item -->
    <!-- END remove_img_context_menu_item -->
}


var _ilTinyMceInitCallbackRegistry = function() {
    this.callbacks = [];
};

_ilTinyMceInitCallbackRegistry.prototype = {

    addCallback: function(callback) {
        this.callbacks.push(callback);
    },

    callCallbacks: function(ed) {
        for (var i = 0; i < this.callbacks.length; i++) {
            this.callbacks[i](ed);
        }
    }
};

var ilTinyMceInitCallbackRegistry = new _ilTinyMceInitCallbackRegistry();

window.ilTinyMceInitCallbackRegistry = ilTinyMceInitCallbackRegistry;

function ilTinyMCEImagePickerCallback(cb, value, meta) {
    var input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.setAttribute('name', 'img_file');
    input.setAttribute('id', 'img_file');


    /*
      Note: In modern browsers input[type="file"] is functional without
      even adding it to the DOM, but that might not be the case in some older
      or quirky browsers like IE, so you might want to add it to the DOM
      just in case, and visually hide it. And do not forget do remove it
      once you do not need it anymore.
    */

    input.onchange = function() {
        var file = this.files[0];

        var reader = new FileReader();
        reader.onload = function() {
            /*
              Note: Now we need to register the blob in TinyMCEs image blob
              registry. In the next release this part hopefully won't be
              necessary, as we are looking to handle it internally.
            */
            var id = 'blobid' + (new Date()).getTime();
            var blobCache = tinymce.activeEditor.editorUpload.blobCache;
            var base64 = reader.result.split(',')[1];
            var blobInfo = blobCache.create(id, file, base64);
            blobCache.add(blobInfo);

            /* call the callback and populate the Title field with the file name */
            cb(blobInfo.blobUri(), { title: file.name });
        };
        reader.readAsDataURL(file);
    };
    input.click();
}

function UploadHandler(blobInfo, success, failure, progress) {
    var xhr, formData;
    var uploadUrl = './node_modules/tinymce/plugins/ilimgupload/imgupload.php?obj_id=' + obj_id + '&obj_type=' + obj_type + '&update=' + image_update;
    xhr = new XMLHttpRequest();
    xhr.open('POST', uploadUrl);
    xhr.setRequestHeader('Accept', 'application/json');
    //xhr.withCredentials = settings.credentials;
    xhr.upload.onprogress = function(e) {
        progress(e.loaded / e.total * 100);
    };
    xhr.onerror = function() {
        failure('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
    };
    xhr.onload = function() {
        if (xhr.status < 200 || xhr.status >= 300) {
            failure('HTTP Error: ' + xhr.status);
            return;
        }
        var json = JSON.parse(xhr.responseText);
        if (!json || typeof json.response !== 'object') {

            failure('Invalid JSON: ' + xhr.responseText);
            return;
        }
        var location = json.response.uploaded_file.location;
        success(location);
    };
    var formData = new FormData();
    formData.append('img_file', blobInfo.blob(), blobInfo.filename());
    xhr.send(formData);
};

tinymce.init({
    mode: "textareas",
    editor_deselector: "noRTEditor",
    branding: false,
    language: "{LANG}",
    //blockformats has changed it's definition in 5.x. please fix the translations
    block_formats: ilTinyMCETranslateFormats(),
    plugins: "{ADDITIONAL_PLUGINS}",
    //Maybe a bug: initial width is a null
    <!-- BEGIN initial_width -->
    //width : "{INITIAL_WIDTH}",
    <!-- END initial_width -->
    menubar: false,
    toolbar: "{BUTTONS_1} {BUTTONS_2} {BUTTONS_3}",
    toolbar_sticky: true,
    toolbar_mode: 'wrap',
    valid_elements: "{VALID_ELEMENTS}",
    image_advtab: false,
    image_title: true,
    images_file_types: "gif, jpg, jpeg, png",
    file_picker_types: "image",
    automatic_uploads: true,
    images_upload_handler: UploadHandler,
    images_upload_url: './node_modules/tinymce/plugins/ilimgupload/imgupload.php?obj_id=' + obj_id + '&obj_type=' + obj_type + '&update=' + image_update,
    importcss_append: true,
    file_picker_callback: ilTinyMCEImagePickerCallback,
    plugin_insertdate_dateFormat: "%d.%m.%Y",
    plugin_insertdate_timeFormat: "%H:%M:%S",
    image_caption: true,
    quickbars_selection_toolbar: '',
    noneditable_noneditable_class: 'mceNonEditable',
    contextmenu: '{CONTEXT_MENU_ITEMS}',
    skin: 'oxide',
    entities: "60,lt,62,gt,38,amp",
    content_css: "{STYLESHEET_LOCATION}",
    content_style: 'html { overflow: initial; }',
    fix_list_elements: true,
    <!-- BEGIN formelements -->
    extended_valid_elements: "form[name|id|action|method|enctype|accept-charset|onsubmit|onreset|target],input[id|name|type|value|size|maxlength|checked|accept|s rc|width|height|disabled|readonly|tabindex|accessk ey|onfocus|onblur|onchange|onselect],textarea[id|name|rows|cols|disabled|readonly|tabindex|acces skey|onfocus|onblur|onchange|onselect],option[name|id|value],select[id|name|type|value|size|maxlength|checked|accept|s rc|width|height|disabled|readonly|tabindex|accessk ey|onfocus|onblur|onchange|onselect|length|options |selectedIndex]",
    <!-- END formelements -->
    <!-- BEGIN forced_root_block -->forced_root_block : '{FORCED_ROOT_BLOCK}',<!-- END forced_root_block -->

    ilimgupload_file_extensions: "{TXT_ALLOWED_FILE_EXTENSIONS}",
    ilimgupload_maxsize: "{TXT_MAX_SIZE}",
    setup: function(ed) {
        ed.on('init', ilTinyMceInitCallback);
        ed.on('init', function() {
            if (il.Form) {
                il.Form.showCharCounterTinymce(ed);
            }

        });

        ed.on('keyup', function() {
            if (il.Form) {
                il.Form.showCharCounterTinymce(ed);
            }
        });
    },
    init_instance_callback: function(ed) {
        ilTinyMceInitCallbackRegistry.callCallbacks(ed);
    }
});
<!-- END tinymce -->