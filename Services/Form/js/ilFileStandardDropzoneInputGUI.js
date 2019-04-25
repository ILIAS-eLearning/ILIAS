var ilFileStandardDropzoneInputGUI = (function ($) {

    var init = function (wrapper_id) {
        var wrapper = $('#' + wrapper_id);
        var uploadId = wrapper.find('.il-dropzone-base').attr('id');
        var form = wrapper.closest('form');
        var handledUpload = false;
        var buttonName = form.find('input[type=submit]:first').attr('name');
        form.find('input[type=submit]').on("click", function () {
            buttonName = $(this).attr('name');
        });

        var params = {};
        params[buttonName] = true;
        il.UI.uploader.addForm(uploadId, form.attr('id'));
        il.UI.uploader.setUploadParams(uploadId, params);
    };

    return {
        init: init
    }

})($);