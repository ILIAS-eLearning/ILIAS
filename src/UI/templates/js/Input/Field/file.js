/**
 * This links datetime-pickers together (for duration input)
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
Dropzone.autoDiscover = false;
var il = il || {};
il.UI = il.UI || {};
il.UI.Input = il.UI.Input || {};
(function ($, UI) {

    il.UI.Input.file = (function ($) {


        var init = function (container_id, upload_url) {

            let replacer = new RegExp('amp;', 'g');
            upload_url = upload_url.replace(replacer, '');

            console.log(upload_url);
            let container = '#' + container_id;
            let dropzone = container + ' .il-input-file-dropzone';
            let input_template = $(container + ' .input-template').clone();
            $(container + ' .input-template').remove();
            console.log(input_template);

            var myDropzone = new Dropzone(dropzone, {
                url: encodeURI(upload_url),
                method: 'post',
                createImageThumbnails: false,
                maxFiles: 1,
                dictDefaultMessage: '',
                previewsContainer: container + ' .il-input-file-filelist',
                previewTemplate: document.querySelector(container + ' .il-input-file-template').innerHTML,
                clickable: container + ' .il-input-file-dropzone button',
                autoProcessQueue: true,
                uploadMultiple: false,
                parallelUploads: 1
            });

            myDropzone.on("maxfilesreached", function (file) {
                console.log("max files reached");
                myDropzone.removeEventListeners();
                $(container + ' .il-input-file-dropzone button').attr("disabled", true);
            });
            var success = function (files, response) {
                try {
                    var json = JSON.parse(response);
                } catch (e) {
                    console.log(e);
                    return;
                }
                console.log(json);
                if (json.hasOwnProperty('file_identifier')) {
                    let clone = input_template.clone();
                    clone.val(json.file_identifier);
                    $(container).append(clone);
                }

            };

            myDropzone.on("removedfile", function (file) {
                myDropzone.setupEventListeners();
                myDropzone._updateMaxFilesReachedClass();
                $(container + ' .il-input-file-dropzone button').attr("disabled", false);
            });
            myDropzone.on("successmultiple", function (files, response) {
                success(files, response);
            });
            myDropzone.on("success", function (files, response) {
                success(files, response);
            });
            myDropzone.on("errormultiple", function (files, response) {
                console.log(response);
            });
            myDropzone.on("error", function (files, response) {
                console.log(response);
            });
        };

        return {
            init: init
        };

    })($);
})($, il.UI.Input);
