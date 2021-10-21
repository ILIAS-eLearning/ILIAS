var il = il || {};
il.UI = il.UI || {};
(function ($, UI) {

    UI.uploader = (function ($) {

        // Default options when creating an uploader instance via the il.UI.uploader.init function
        var defaultOptions = {
            fileListContainer: null, // JQuery object representing the container listing the files
            autoUpload: false, // Any selected/dropped file is getting uploaded automatically, e.g. NOT on button click
            allowedFileTypes: [], // Allowed file types
            uploadUrl: '', // URL where files are uploaded to
            maxFiles: 0, // Max number of files to upload, 0 = infinity
            fileSizeLimit: 0, // Max file size in bytes
            identifier: 'files', // Input name used when sending files back to the server. Corresponds to the key in $_FILES array
            selectFilesButton: null, // A JQuery object acting as select files button. Cannot be a <button>
            interceptSubmit: true
        };

        // Stores all the different upload instances with a unique uploadId
        var instances = {};

        // Private
        // ********************************************

        /**
         * Get fileListContainer aka JQuery object showing the uploaded files.
         * @param uploadId
         */
        var getFileListContainer = function (uploadId) {
            return instances[uploadId].options['fileListContainer'];
        };

        /**
         * Get the FineUploader instance identified by the given uploadId.
         * @param uploadId
         */
        var getUploader = function (uploadId) {
            return instances[uploadId].uploader;
        };

        /**
         * Rendering if a new file is added.
         * @param uploadId
         * @param file
         * @param fileId
         */
        var renderAddFile = function (uploadId, file, fileId) {
            // console.log("Append new fle to list");
            var $container = getFileListContainer(uploadId);
            var $items = $container.find('.il-upload-file-items');
            var $template = $container.find('.il-upload-file-item-template');
            var $item = $template.clone()
                .removeClass('il-upload-file-item-template')
                .removeClass('hidden')
                .attr('data-file-id', fileId);
            $item.find('.filename').text(file.name);
            $item.find('.filename-input').val(file.name);
            $item.find('.filesize').text(humanFileSize(file.size));
            $items.append($item);
        };

        /**
         * Add any custom file metadata (title + description) to the upload request.
         * Note: Parameters are only attached if it is possible to add a custom filename or file description.
         * @param uploadId
         * @param fileId
         */
        var addAdditionalParametersToUploadRequest = function (uploadId, fileId) {
            var $container = getFileListContainer(uploadId);
            var $fileList = $container.find('.il-upload-file-item[data-file-id="' + fileId + '"]');
            var $metadata = $fileList.find('.metadata');
            if (!$metadata.length) return;
            var $filenameInput = $metadata.find('.filename-input');
            var $descriptionInput = $metadata.find('.description-input');
            var uploader = getUploader(uploadId);
            var params = {};
            if ($filenameInput.length) {
                params['customFileName'] = $filenameInput.val();
            }
            if ($descriptionInput.length) {
                params['fileDescription'] = $descriptionInput.val();
            }
            uploader.setParams(params, fileId);
        };

        /**
         * Rendering if a file is removed.
         * @param uploadId
         * @param fileId
         */
        var renderRemoveFile = function (uploadId, fileId) {
            var $container = getFileListContainer(uploadId);
            var $fileItem = $container.find("[data-file-id='" + fileId + "']");
            if ($fileItem.length) {
                $fileItem.fadeOut();
            }
        };

        /**
         * Render the progress bar during uploading.
         * @param uploadId
         * @param fileId
         * @param progress
         */
        var renderProgress = function (uploadId, fileId, progress) {
            var $container = getFileListContainer(uploadId);
            var $fileItem = $container.find("[data-file-id='" + fileId + "']");
            var $progress = $fileItem.find('.progress').show();
            $progress.find('.progress-bar')
                .css('width', progress + '%')
                .attr('aria-valuenow', progress);
        };

        /**
         * Render an error specific to a file.
         * @param uploadId
         * @param fileId
         * @param errorReason
         */
        var renderFileError = function (uploadId, fileId, errorReason) {
            var $container = getFileListContainer(uploadId);
            var $fileItem = $container.find("[data-file-id='" + fileId + "']");
            $fileItem.find('.file-error-message').text(errorReason).fadeIn();
            $fileItem.find('.progress-bar').removeClass('active');
            $fileItem.find('.delete-file').fadeOut();
        };

        /**
         * Render a global error.
         * @param uploadId
         * @param errorReason
         */
        var renderError = function (uploadId, errorReason) {
            // console.log('renderError ' + errorReason);
            var $container = getFileListContainer(uploadId);
            var $alert = $container.find('.error-messages')
                .fadeIn()
                .children('.alert');
            $alert.append($alert.text().trim() ? '<br>' + errorReason : errorReason);
        };

        /**
         * Render a successful upload of a file.
         * @param uploadId
         * @param fileId
         */
        var renderFileSuccess = function (uploadId, fileId) {
            var $container = getFileListContainer(uploadId);
            var $fileItem = $container.find("[data-file-id='" + fileId + "']");
            var $progressBar = $fileItem.find('.progress-bar');
            $progressBar.removeClass('active')
            // .addClass('progress-bar-success')
                .text('Completed');
            $fileItem.find('.btn-group').fadeOut();
            $fileItem.find('.metadata').hide();
        };

        /**
         * Clear any rendering, resulting in the initial DOM of the file list container.
         * @param uploadId
         */
        var renderClear = function (uploadId) {
            var $container = getFileListContainer(uploadId);
            $container.find('.error-messages').hide().children('.alert').text('');
            $container.find('.il-upload-file-items')
                .children('.il-upload-file-item')
                .remove();
        };

        /**
         * Format a file size into a human readable form.
         * @param size
         * @returns {*}
         */
        var humanFileSize = function (size) {
            if (size === 0) {
                return '0 kB';
            }
            var i = Math.floor(Math.log(size) / Math.log(1024));
            return (size / Math.pow(1024, i)).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
        };

        /**
         * Enable or disable any upload buttons
         * @param uploadId
         * @param state
         */
        var toggleBoundUploadButtons = function (uploadId, state) {
            var $uploadButtons = instances[uploadId].uploadButtons;
            $.each($uploadButtons, function (index, $uploadButton) {
                if (state) {
                    $uploadButton.removeClass('disabled');
                } else {
                    $uploadButton.addClass('disabled');
                }
            });
        };

        // Public
        // ********************************************

        /**
         * Initialize a new uploader instance
         * @param uploadId
         * @param options
         */
        var init = function (uploadId, options) {
            options = $.extend({}, defaultOptions, options);
            var uploader = new qq.FineUploaderBasic({
                autoUpload: options.autoUpload,
                button: options.selectFilesButton ? options.selectFilesButton[0] : null,
                debug: false,
                request: {
                    endpoint: options.uploadUrl,
                    inputName: options.identifier
                },
                validation: {
                    allowedExtensions: options.allowedFileTypes,
                    sizeLimit: options.fileSizeLimit,
                    itemLimit: options.maxFiles
                },
                messages: {
                    typeError: options.typeError,
                    tooManyItemsError: options.tooManyItemsError,
                    noFilesError: options.noFilesError,
                },
                callbacks: {
                    onUpload: function (fileId, name) {
                        // Register additional name + description parameters for the upload request
                        addAdditionalParametersToUploadRequest(uploadId, fileId);
                    },
                    onComplete: function (fileId, fileName, response, xmlHttpRequest) {
                        if (response.success && response.redirect_url) {
                            instances[uploadId].lastRedirect = response.redirect_url;
                        }
                        // Errors are rendered in the onError callback
                        if (response.success) {
                            // console.log('Successfully uploaded file ' + fileName);
                            renderFileSuccess(uploadId, fileId);
                        }
                    },
                    onAllComplete: function (succeeded, failed) {
                        if (typeof instances[uploadId].lastRedirect !== 'undefined') {
                            window.location.replace(instances[uploadId].lastRedirect);
                        }
                        var succeededFiles = succeeded.map(function (fileId) {
                            //return uploader.getFile(fileId).name;
                        });
                        var failedFiles = failed.map(function (fileId) {
                            //return uploader.getFile(fileId).name;
                        });


                        // console.log('Successfully uploaded files: ' + succeededFiles.join(', '));
                        // console.log('Failed to upload files: ' + failedFiles.join(', '));
                        // Execute and custom callbacks if all files were uploaded successfully
                        if (!failed.length) {
                            toggleBoundUploadButtons(uploadId, false);
                            var callbacks = instances[uploadId].callbacks['onAllUploadCompleted'];
                            $.each(callbacks, function (index, callback) {
                                callback();
                            });
                        } else {
                            toggleBoundUploadButtons(uploadId, false);
                            var failedCallbacks = instances[uploadId].callbacks['onAllUploadCompletedFailed'];
                            $.each(failedCallbacks, function (index, callback) {
                                callback();
                            });
                        }
                    },
                    onError: function (fileId, fileName, errorReason, xmlHttpRequest) {
                        var response;
                        try {
                            response = JSON.parse(xmlHttpRequest.response);
                        } catch (e) {
                            // console.log(xmlHttpRequest);
                            response = {};
                            response.message = null;
                        }
                        if (fileId !== null) {
                            errorReason = response.message || errorReason;
                            renderFileError(uploadId, fileId, errorReason);
                        } else {
                            renderError(uploadId, errorReason);
                        }
                        // Calling Callbacks
                        var callbacks = instances[uploadId].callbacks['onError'];
                        $.each(callbacks, function (index, callback) {
                            callback(xmlHttpRequest);
                        });
                        return false;
                    },
                    onCancel: function (fileId, fileName) {
                        var nonCanceledFiles = uploader.getUploads().filter(function (file) {
                            return (file.status !== 'canceled');
                        });
                        if (nonCanceledFiles.length <= 1) {
                            toggleBoundUploadButtons(uploadId, false);
                        }
                    },
                    onProgress: function (fileId, fileName, uploadedBytes, totalBytes) {
                        // console.log('progress for ' + fileId + ': ' + uploadedBytes + '/' + totalBytes);
                        var progress = (totalBytes > 0 && uploadedBytes > 0) ? Math.round(100 / totalBytes * uploadedBytes) : 0;
                        renderProgress(uploadId, fileId, progress);
                    },
                    onStatusChange: function (fileId, oldStatus, newStatus) {
                        // console.log('status changed' + fileId + '; old=' + oldStatus + ', new=' + newStatus);
                    },
                    onSubmitted: function (fileId, name) {
                        var file = uploader.getFile(fileId);
                        renderAddFile(uploadId, file, fileId);
                        // Set any bound upload button to active, as we now have at least one valid file to upload
                        toggleBoundUploadButtons(uploadId, true);
                        var callbacks = instances[uploadId].callbacks['onSubmitFile'];
                        $.each(callbacks, function (index, callback) {
                            callback(file);
                        });
                    }
                }
            });

            instances[uploadId] = {
                'uploader': uploader,
                'options': options,
                'callbacks': {
                    'onAllUploadCompleted': [],
                    'onAllUploadCompletedFailed': [],
                    'onSubmitFile': [],
                    'onError': []
                },
                'uploadButtons': [],
                'lastRedirectURL': null
            };

            if (options.uploadButton !== null) {
                bindUploadButton(uploadId, options.uploadButton);
            }
        };

        /**
         * Add a new file to the uploader.
         * @param uploadId
         * @param file
         */
        var addFile = function (uploadId, file) {
            var uploader = getUploader(uploadId);
            uploader.addFiles([file]);
        };

        /**
         * Remove a file with a given ID from the uploader.
         * @param uploadId
         * @param fileId
         */
        var removeFile = function (uploadId, fileId) {
            var uploader = getUploader(uploadId);
            uploader.cancel(fileId);
            renderRemoveFile(uploadId, fileId);
        };

        /**
         * Attach any custom parameters included with the upload request for each file.
         * @param uploadId
         * @param params
         */
        var setUploadParams = function (uploadId, params) {
            var uploader = getUploader(uploadId);
            uploader.setParams(params);
        };

        /**
         * Get all uploads of the uploader identified by uploadId.
         * @param uploadId
         */
        var getUploads = function (uploadId) {
            var uploader = getUploader(uploadId);
            var files = uploader.getUploads();
            return files.filter(function (file) {
                return (file.status !== 'canceled');
            });
        };

        /**
         * Start the upload process for the given uploadId.
         * @param uploadId
         */
        var upload = function (uploadId) {
            var uploader = getUploader(uploadId);
            uploader.uploadStoredFiles();
        };

        /**
         * Reset the uploader instance by clearing all queued files. Also resets processed rendering.
         * @param uploadId
         */
        var reset = function (uploadId) {
            var uploader = getUploader(uploadId);
            uploader.clearStoredFiles();
            uploader.reset();
            toggleBoundUploadButtons(uploadId, false);
            renderClear(uploadId);
        };

        /**
         * Checks if the uploader is currently uploading any files.
         * @param uploadId
         * @returns {boolean}
         */
        var isUploading = function (uploadId) {
            var uploader = getUploader(uploadId);
            return (uploader.getInProgress > 0);
        };

        /**
         * Bind an upload button starting the upload process.
         * @param uploadId
         * @param $uploadButton JQuery object
         */
        var bindUploadButton = function (uploadId, $uploadButton) {
            instances[uploadId]['uploadButtons'].push($uploadButton);
            $uploadButton.on('click', function (event) {
                event.preventDefault();
                upload(uploadId);
            });
        };

        /**
         * Attach a callback function when all files have been successfully uploaded.
         * Note: The callback is only executed if all files in the queue succeeded
         * @param uploadId
         * @param success callback
         * @param failed callback
         */
        var onAllUploadCompleted = function (uploadId, success, failed) {
            instances[uploadId].callbacks['onAllUploadCompleted'].push(success);
            instances[uploadId].callbacks['onAllUploadCompletedFailed'].push(failed);
        };
        /**
         * Attach a callback function when any Error occurs.
         * @param uploadId
         * @param callback
         */
        var onError = function (uploadId, callback) {
            instances[uploadId].callbacks['onError'].push(callback);
        };

        /**
         * Attach a callback function when the uploader receives a new file to be uploaded.
         * Note: Only executed for valid files!
         * @param uploadId
         * @param callback
         */
        var onSubmitFile = function (uploadId, callback) {
            instances[uploadId].callbacks['onSubmitFile'].push(callback);
        };

        /**
         * Attach a callback function when the uploader receives a new file to be uploaded.
         * Note: Only executed for valid files!
         * @param uploadId
         * @param callback
         */
        var onFileAdded = function (uploadId, callback) {
            instances[uploadId].callbacks['onFileAdded'].push(callback);
        };

        var addForm = function (uploadId, formElementId) {
            instances[uploadId].uploader.setForm(formElementId);
        };

        return {
            init: init,
            addFile: addFile,
            removeFile: removeFile,
            setUploadParams: setUploadParams,
            getUploads: getUploads,
            isUploading: isUploading,
            upload: upload,
            reset: reset,
            bindUploadButton: bindUploadButton,
            onAllUploadCompleted: onAllUploadCompleted,
            onError: onError,
            onSubmitFile: onSubmitFile,
            addForm: addForm
        }
    })($);

})($, il.UI);

$(function () {
    var $uploadFileLists = $('.il-upload-file-list');
    $uploadFileLists.find('span.toggle .glyph:first').hide();
    $uploadFileLists.on('click', 'span.toggle .glyph', function () {
        $(this).parent().find(".glyph").each(function () {
            $(this).toggle();
        });
        $(this).parents('.il-upload-file-item').find('.metadata').toggle();
        return false;
    });
    $uploadFileLists.on('click', 'span.remove button.close', function () {
        var dz = $(this).closest('.il-dropzone-base').find(".il-dropzone");
        var uploadId = dz.data('upload-id');
        var fileId = parseInt($(this).parents('.il-upload-file-item').data('file-id'));
        il.UI.uploader.removeFile(uploadId, fileId);
        return false;
    });
});
