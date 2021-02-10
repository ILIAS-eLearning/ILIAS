var ilFileStandardDropzoneInputGUI = (function ($) {

  var init = function (wrapper_id, cancel_command) {
    var cancelButtonName = "cmd[" + cancel_command + "]";
    var wrapper = $('#' + wrapper_id);
    var uploadId = wrapper.find('.il-dropzone-base').attr('id');
    var form = wrapper.closest('form');
    var handledUpload = false;
    var buttonName = form.find('input[type=submit]:first').attr('name');
    var formAdded = false;

    // bugfix mantis 0025881:
    // Don't add the form to the uploader when cancel was clicked as this leads to a faulty validation or even worse an unwanted upload.
    form.find('input[type=submit]').on("click", function (e) {
      buttonName = $(this).attr('name');
      if (buttonName !== cancelButtonName) {
        if (formAdded === false) {
          il.UI.uploader.addForm(uploadId, form.attr('id'));
          formAdded = true;
        }
      } else {
        e.preventDefault();
        var preCancelFormAction = form.attr('action');
        var newFormAction = preCancelFormAction.replace(/cmd=.+?(?=&)/, ("cmd=" + cancel_command));
        location.assign(newFormAction);
      }
    });
    var params = {};
    params[buttonName] = true;
    il.UI.uploader.setUploadParams(uploadId, params);
  };

  return {
    init: init
  }

})($);
