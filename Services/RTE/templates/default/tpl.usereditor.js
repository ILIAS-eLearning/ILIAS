<!-- BEGIN tinymce -->
function ilTinyMceInitCallback(ed) { // Add hook for onContextMenu so that Insert Image can be removed
    <!-- BEGIN remove_img_context_menu_item -->
    <!-- END remove_img_context_menu_item -->
}

tinymce.init({
    mode: "textareas",
    menubar: false,
    branding: false,
    editor_selector: "{SELECTOR}",
    language: "{LANG}",
    plugins: "save",
    fix_list_elements: true,
    block_formats: "{BLOCKFORMATS}",
    toolbar_location: "top",
    toolbar_align: "left",
    path_location: "bottom",
    toolbar: "save {BUTTONS}",
    valid_elements: "{VALID_ELEMENTS}",
    entities: "60,lt,62,gt,38,amp",
    content_css: "{STYLESHEET_LOCATION}",
    content_style: 'html { overflow: initial; }',
    plugin_insertdate_dateFormat: "%d.%m.%Y",
    plugin_insertdate_timeFormat: "%H:%M:%S",
    save_enablewhendirty: false,
    save_onsavecallback: "saveTextarea",
    resize: 'true',
    font_formats: "Arial=sans-serif;Courier=monospace;Times Roman=serif",
    fontsize_formats: "8pt,10pt,12pt,14pt,18pt,24pt,36pt",
    setup: function(ed) { 
        ed.on('init', ilTinyMceInitCallback);
        ed.on('keyup', charCounter);
    }
});
<!-- END tinymce -->