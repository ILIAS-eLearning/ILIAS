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

        var _default_settings = {
            'upload_url': '',
            'removal_url': '',
            'file_identifier_key': 'file_id',
            'max_files': 1
        };


        var init = function (container_id, settings) {
            var replacer = new RegExp('amp;', 'g');
            console.log(settings);
            settings = Object.assign(JSON.parse(settings), _default_settings);
            settings.upload_url = settings.upload_url.replace(replacer, '');
            settings.removal_url = settings.removal_url.replace(replacer, '');

            console.log(settings);

            var container = '#' + container_id;
            var dropzone = container + ' .il-input-file-dropzone';
            var input_template = $(container + ' .input-template').clone();
            $(container + ' .input-template').remove();
            

            var myDropzone = new Dropzone(dropzone, {
                url: encodeURI(upload_url),
                method: 'post',
                createImageThumbnails: false,
                maxFiles: 10,
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
                    var clone = input_template.clone();
                    clone.val(json.file_identifier);
                    $(container).append(clone);
                }

            };

            myDropzone.on("removedfile", function (file) {
                console.log(file);
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
