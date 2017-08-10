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

        form.on('submit', function (event) {
            if (handledUpload) {
                console.log("upload abgeschlossen");
                // event.stopPropagation();
                return;
            }
            if ($(this)[0].checkValidity()) {
                // If we have any files to upload, start uploading process prior to submitting form
                if (il.UI.uploader.getUploads(uploadId).length) {
                    event.preventDefault();

                    var params = {};
                    console.log($(this).serializeArray());
                    $.each($(this).serializeArray(), function (_, kv) {
                        if (params.hasOwnProperty(kv.name)) {
                            params[kv.name] = $.makeArray(params[kv.name]);
                            params[kv.name].push(kv.value);
                        } else {
                            params[kv.name] = kv.value;
                        }
                    });

                    params[buttonName] = true;

                    il.UI.uploader.setUploadParams(uploadId, params);
                    il.UI.uploader.onError(uploadId, function (xmlHttpRequest) {
                        console.log(xmlHttpRequest);
                        // il.UI.uploader.reset(uploadId);
                        if(xmlHttpRequest.response.message) {

                        }
                        handledUpload = true;
                        return false;
                    });
                    il.UI.uploader.onAllUploadCompleted(uploadId, function () {
                        handledUpload = true;
                        return true;
                        // form.trigger('submit');
                    }, function () {
                        handledUpload = true;
                        // form.trigger('submit');
                        return false;
                    });

                    il.UI.uploader.upload(uploadId);
                }
                else {
                    handledUpload = true;
                }
            }
        });
    };

    return {
        init: init
    }

})($);