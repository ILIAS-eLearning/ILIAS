var ilFileStandardDropzoneInputGUI = (function ($) {

	var init = function (wrapper_id) {
		var $wrapper = $('#' + wrapper_id);
		var $form = $wrapper.closest('form');
		var uploadId = $wrapper.find('.il-upload-file-list').attr('data-upload-id');
		console.log(uploadId);
		var handledUpload = false;
		$form.on('submit', function (event) {
			if (handledUpload) return;
			if ($(this)[0].checkValidity()) {
				// If we have any files to upload, start uploading process prior to submitting form
				if (il.UI.uploader.getUploads(uploadId).length) {
					event.preventDefault();
					// Include all form data in the upload request
					var params = {};
					$.each($(this).serializeArray(), function (_, kv) {
						if (params.hasOwnProperty(kv.name)) {
							params[kv.name] = $.makeArray(params[kv.name]);
							params[kv.name].push(kv.value);
						} else {
							params[kv.name] = kv.value;
						}
					});
					il.UI.uploader.setUploadParams(uploadId, params);
					il.UI.uploader.upload(uploadId);
					il.UI.uploader.onAllUploadCompleted(uploadId, function () {
						handledUpload = true;
						$form.trigger('submit');
					});
				}
			}
		});
	};

	return {
		init: init
	}

})($);