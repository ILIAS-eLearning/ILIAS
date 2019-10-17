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
        var init = function (container_id) {
            let container = '#' + container_id;
            let dropzone = container + ' .il-input-file-dropzone';
            let input_template = $(container+' .input-template').clone();
            $(container+' .input-template').remove();
            console.log(input_template);

            var myDropzone = new Dropzone(dropzone, {
                url: './src/UI/examples/Input/Field/File/handler/upload.php',
                method: 'post',
                createImageThumbnails: false,
                maxFiles: 1,
                dictDefaultMessage: '',
                previewsContainer: container + ' .il-input-file-filelist',
                previewTemplate: document.querySelector(container + ' .il-input-file-template').innerHTML,
                clickable: container + ' .il-input-file-dropzone button',
                autoProcessQueue: true,
                uploadMultiple: true,
                parallelUploads: 100
            });

            myDropzone.on("maxfilesreached", function (file) {
                console.log("max files reached");
                myDropzone.removeEventListeners();
                $(container + ' .il-input-file-dropzone button').attr("disabled", true);
            });
            myDropzone.on("removedfile", function (file) {
                myDropzone.setupEventListeners();
                myDropzone._updateMaxFilesReachedClass();
                $(container + ' .il-input-file-dropzone button').attr("disabled", false);
            });
            myDropzone.on("successmultiple", function (files, response) {
                console.log(response);
                let file_ids = JSON.parse(response);
                console.log(file_ids);
                for(let id of file_ids) {
                    let clone = input_template.clone();
                    clone.val(id);
                    $(container).append(clone);
                }
            });
            myDropzone.on("errormultiple", function (files, response) {
                console.log(response);
            });
        };

        return {
            init: init
        };

    })($);
})($, il.UI.Input);
