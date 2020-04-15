/**
 * This wraps Drozone.js for FileInputs
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
Dropzone.autoDiscover = false;
var il = il || {};
il.UI = il.UI || {};
il.UI.Input = il.UI.Input || {};
(function ($, UI) {

    il.UI.Input.file = (function ($) {

        var _default_settings = {
            upload_url: '',
            removal_url: '',
            info_url: '',
            file_identifier_key: 'file_id',
            max_files: 1,
            accepted_files: '',
            existing_file_ids: [],
            existing_files: [],
            get_file_info_async: false
        };


        var init = function (container_id, settings) {
            var replacer = new RegExp('amp;', 'g');
            settings = Object.assign(_default_settings, JSON.parse(settings));
            settings.upload_url = settings.upload_url.replace(replacer, '');
            settings.removal_url = settings.removal_url.replace(replacer, '');

            var container = '#' + container_id;
            var dropzone = container + ' .il-input-file-dropzone';
            var input_template = $(container + ' .input-template').clone();
            $(container + ' .input-template').remove();

            var myDropzone = new Dropzone(dropzone, {
                url: encodeURI(settings.upload_url),
                method: 'post',
                createImageThumbnails: false,
                maxFiles: settings.max_files,
                dictDefaultMessage: '',
                previewsContainer: container + ' .il-input-file-filelist',
                previewTemplate: document.querySelector(container + ' .il-input-file-template').innerHTML,
                clickable: container + ' .il-input-file-dropzone button',
                autoProcessQueue: true,
                uploadMultiple: false,
                parallelUploads: 1,
                acceptedFiles: settings.accepted_files
            });

            myDropzone.on("maxfilesreached", function (file) {
                myDropzone.removeEventListeners();
                $(container + ' .il-input-file-dropzone button').attr("disabled", true);
            });

            var success = function (files, new_file_id) {
                var clone = input_template.clone();
                clone.val(new_file_id);
                clone.attr('data-file-id', new_file_id);

                files.file_id = new_file_id;

                $(container).append(clone);
            };

            var successFromResponse = function (files, response) {
                try {
                    var json = JSON.parse(response);
                } catch (e) {
                    return;
                }
                if (json.hasOwnProperty(settings.file_identifier_key)) {
                    var file_id = json[settings.file_identifier_key];
                    success(files, file_id);
                }
            };
            var disableForm = function () {
                $(container).closest('form').find('button').each(function (e) {
                    $(this).prop('disabled', true);
                });
            };
            var enableForm = function () {
                $(container).closest('form').find('button').each(function (e) {
                    $(this).prop('disabled', false);
                });
            };


            myDropzone.on('sending', function () {
                disableForm();
            });

            myDropzone.on("removedfile", function (file) {

                myDropzone.setupEventListeners();
                myDropzone._updateMaxFilesReachedClass();
                $(container + ' .il-input-file-dropzone button').attr("disabled", false);
                // remove input
                var file_id = file.file_id;
                $(container + ' *[data-file-id="' + file_id + '"]').remove();

                // Call removal-URL
                if (file.hasOwnProperty('is_existing') && file.is_existing === true) {
                    disableForm();
                    var data = {};
                    data[settings.file_identifier_key] = file_id;
                    $.get(settings.removal_url, data, function (response) {
                        enableForm();
                    });
                }
            });
            myDropzone.on("success", function (files, response) {
                successFromResponse(files, response);
                enableForm();
            });
            myDropzone.on("errormultiple", function (files, response) {

            });
            myDropzone.on("error", function (files, response) {
                enableForm();
            });

            // existing files
            var addExisting = function (mockFile, response) {
                mockFile.accepted = true;
                mockFile.is_existing = true;
                myDropzone.files.push(mockFile);
                myDropzone.emit("success", mockFile, response);
                myDropzone.emit("complete", mockFile);
                myDropzone.emit("addedfile", mockFile);
                myDropzone._updateMaxFilesReachedClass();
            };
            if (settings.get_file_info_async) {
                var data = {};
                for (var i in settings.existing_file_ids) {
                    data[settings.file_identifier_key] = settings.existing_file_ids[i];
                    $.get(settings.info_url, data, function (response) {
                        var mockFile = JSON.parse(response);
                        addExisting(mockFile, response);
                    });
                }
            } else {
                for (var i in settings.existing_files) {
                    addExisting(settings.existing_files[i], {});
                }
            }

        };

        return {
            init: init
        };

    })($);
})($, il.UI.Input);
