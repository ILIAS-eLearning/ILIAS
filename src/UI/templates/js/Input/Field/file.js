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
        var init = function (id) {
            //Dropzone Configuration

            id = '#' + id;
	        console.log(id);
	        $(id).dropzone({
                url: './src/UI/examples/Input/Field/File/handler/upload.php',
                method: 'post',
		        createImageThumbnails: false,
		        maxFiles:1,
		        dictDefaultMessage:'',
                previewTemplate: "<div class=\"il-upload-file-item clearfix row standard\" >" +
                    "<div class=\"col-xs-12 col-no-padding\">" +
                    "<span class='file-info filename' data-dz-name></span>" +
                    "<span class=\"file-info filesize\" data-dz-size></span>" +
                    "<!--<img data-dz-thumbnail />-->" +
                    "</div>" +
                    "<!--<div class=\"dz-progress\"><span class=\"dz-upload\" data-dz-uploadprogress></span></div>-->" +
                    "<!--<div class=\"dz-success-mark\"><span>✔</span></div>-->" +
                    "<!--<div class=\"dz-error-mark\"><span>✘</span></div>-->" +
                    "<!--<div class=\"dz-error-message\"><span data-dz-errormessage></span></div>-->" +
                    "</div>"
            });
            $(id).on("addedfile", function (file) {
                /* Maybe display some more file information on your page */
                console.log(file);
            });

        };

        return {
            init: init
        };

    })($);
})($, il.UI.Input);
