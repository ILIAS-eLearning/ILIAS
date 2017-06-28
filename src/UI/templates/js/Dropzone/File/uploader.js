var il = il || {};
il.UI = il.UI || {};
(function ($, UI) {

    UI.uploader = (function ($) {

        var defaultOptions = {
            allowedFileTypes: [], // Allowed file types
            uploadUrl: '', // URL where files are uploaded to
            maxFiles: 0 // Max number of files to upload, 0 = infinity
        };

        var instances = {};
        var filesCount = {};

        // Private

        var renderAddFile = function (uploadId, file, fileId) {
            var $container = $('#' + uploadId);
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

        var renderRemoveFile = function (uploadId, fileId) {
            var $container = $('#' + uploadId);
            var $fileItem = $container.find("[data-file-id='"+ fileId +"']");
            if ($fileItem.length) {
                $fileItem.fadeOut();
            }
        };

        var renderProgress = function (uploadId, fileId, progress) {
            var $container = $('#' + uploadId);
            var $fileItem = $container.find("[data-file-id='"+ fileId +"']");
            var $progress = $fileItem.find('.progress').show();
            $progress.find('.progress-bar')
                .css('width', progress + '%')
                .attr('aria-valuenow', progress);
        };

        var renderComplete = function (uploadId, fileId) {
            var $container = $('#' + uploadId);
            var $fileItem = $container.find("[data-file-id='"+ fileId +"']");
            var $progressBar = $fileItem.find('.progress-bar');
            $progressBar.removeClass('active')
                // .addClass('progress-bar-success')
                .text('Completed');
            $fileItem.find('.btn-group').fadeOut();
            $fileItem.find('.metadata').hide();
        };

        var renderClear = function (uploadId) {
            var $container = $('#' + uploadId);
            $container.find('.il-upload-file-items').children().remove();
        };

        var humanFileSize = function (size) {
            if (size === 0) {
                return '0 kB';
            }
            var i = Math.floor(Math.log(size) / Math.log(1024));
            return ( size / Math.pow(1024, i) ).toFixed(2) * 1 + ' ' + ['B', 'kB', 'MB', 'GB', 'TB'][i];
        };

        var getFilesCount = function (id) {
            return (id in filesCount) ? filesCount[id] : 0;
        };

        var incrementFilesCount = function (id) {
            if (id in filesCount) {
                filesCount[id]++;
            } else {
                filesCount[id] = 1;
            }
        };

        var decrementFilesCount = function (id) {
            if (id in filesCount && filesCount[id] > 0) {
                filesCount[id]--;
            } else {
                filesCount[id] = 0;
            }
        };

        // Public

        var init = function (uploadId, options) {
            options = $.extend({}, defaultOptions, options);
            var uploader = new qq.FineUploaderBasic({
                autoUpload: false,
                debug: true,
                request: {
                    endpoint: options.uploadUrl
                },
                callbacks: {
                    onComplete: function (fileId, fileName, response, xmlHttpRequest) {
                        console.log('complete ' + fileName);
                        renderComplete(uploadId, fileId);
                    },
                    onAllComplete: function (succeeded, failed) {
                        console.log(succeeded);
                        var files = succeeded.map(function(fileId) {
                           return uploader.getFile(fileId).name;
                        });
                        console.log('Successfuly uploaded files: ' + files.join(', '));
                    },
                    onError: function (fileId, fileName, errorReason, xmlHttpRequest) {
                        console.log('Error: ' + errorReason);
                    },
                    onProgress: function (fileId, fileName, uploadedBytes, totalBytes) {
                        console.log('progress for ' + fileId + ': ' + uploadedBytes + '/' + totalBytes);
                        var progress = Math.round(100 / totalBytes * uploadedBytes);
                        renderProgress(uploadId, fileId, progress);
                    }
                }
            });
            instances[uploadId] = uploader;
        };

        var addFile = function (uploadId, file) {
            var uploader = instances[uploadId];
            uploader.addFiles([file]);
            var fileId = getFilesCount(uploadId);
            renderAddFile(uploadId, file, fileId);
            incrementFilesCount(uploadId);
        };


        var removeFile = function (uploadId, fileId) {
            var uploader = instances[uploadId];
            uploader.cancel(fileId);
            decrementFilesCount(uploadId);
            renderRemoveFile(uploadId, fileId);
        };

        var upload = function (uploadId) {
            var uploader = instances[uploadId];
            uploader.uploadStoredFiles();
        };

        var clear = function (uploadId) {
            var uploader = instances[uploadId];
            uploader.clearStoredFiles();
            renderClear(uploadId);
        };

        return {
            init: init,
            addFile: addFile,
            removeFile: removeFile,
            upload: upload,
            clear: clear
        }
    })($);

})($, il.UI);

$(function () {
    $(document).on('click', '.edit-file-metadata', function () {
        $(this).parents('.il-upload-file-item').find('.metadata').toggle();
    });
    $(document).on('click', '.delete-file', function () {
        var uploadId = $(this).parents('.il-upload-file-list').attr('id');
        var fileId = parseInt($(this).parents('.il-upload-file-item').attr('data-file-id'));
        il.UI.uploader.removeFile(uploadId, fileId);
    });
});