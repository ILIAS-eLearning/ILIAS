<?php

/**
 * Class ilFileStandardDropzoneInputGUI
 */
class ilFileStandardDropzoneInputGUI extends ilFileInputGUI {

	static $count = 0;

	protected $uploadUrl = '';

	/**
	 * @return string
	 */
	public function getUploadUrl() {
		return $this->uploadUrl;
	}

	/**
	 * @param string $uploadUrl
	 * @return $this
	 */
	public function setUploadUrl($uploadUrl) {
		$this->uploadUrl = $uploadUrl;
		return $this;
	}

	function render($a_mode = "") {
		global $DIC;
		$uiFactory = $DIC->ui()->factory();
		$renderer = $DIC->ui()->renderer();

		$n = ++self::$count;
		$dropzone = $uiFactory->dropzone()->file()->standard($this->getUploadUrl())
			->withIdentifier($this->getPostVar())
			->withAllowedFileTypes($this->getSuffixes());
		$out = "<div id='ilFileStandardDropzoneInputGUIWrapper{$n}'>" . $renderer->render($dropzone) . '</div>';

		self::$count++;
		// We need some javascript magic
		$tpl = $DIC['tpl'];
		$tpl->addOnLoadCode("
		var \$wrapper = $('#ilFileStandardDropzoneInputGUIWrapper{$n}');
		var \$form = \$wrapper.closest('form');
		var uploadId = \$wrapper.find('.il-upload-file-list').attr('data-upload-id');
		var handledUpload = false;
		\$form.on('submit', function(event) {
		   if (handledUpload) return;
		   if ($(this)[0].checkValidity()) {
		     // If we have any files to upload, start uploading process prior to submitting form
		     if (il.UI.uploader.getUploads(uploadId).length) {
		        event.preventDefault();
		        // Include all form data in the upload request
		        var params = {};
				$.each($(this).serializeArray(), function(_, kv) {
				  if (params.hasOwnProperty(kv.name)) {
				    params[kv.name] = $.makeArray(params[kv.name]);
				    params[kv.name].push(kv.value);
				  } else {
				    params[kv.name] = kv.value;
				  }
				});
		       il.UI.uploader.setUploadParams(uploadId, params); 
		       il.UI.uploader.upload(uploadId);		       
		       il.UI.uploader.onAllUploadCompleted(uploadId, function() {
		           handledUpload = true;
		           \$form.trigger('submit');		          
		       });
		     }
		   }
		});
		");
		return $out;
	}

	function checkInput() {
		if ($this->getRequired() && !isset($_FILES[$this->getPostVar()])) {
			return false;
		}
		return true;
	}

}

function usage_in_legacy_form() {

	// Build our form
	$form = new ilPropertyFormGUI();
	$form->setId('myUniqueFormId');
	$form->setTitle('Form');
	$form->setFormAction($_SERVER['REQUEST_URI'] . '&example=6');
	$form->setPreventDoubleSubmission(false);
	$flag = new ilHiddenInputGUI('submitted');
	$flag->setValue('1');
	$form->addItem($flag);
	$item = new ilTextInputGUI('Title', 'title');
	$item->setRequired(true);
	$form->addItem($item);
	$item = new ilTextareaInputGUI('Description', 'description');
	$item->setRequired(true);
	$form->addItem($item);
	$item = new ilFileStandardDropzoneInputGUI('Images', 'images');
	$item->setUploadUrl($form->getFormAction());
	$item->setSuffixes(['jpg', 'gif', 'png']);
	$item->setInfo('Allowed file types: ' . implode(', ', $item->getSuffixes()));
	$form->addItem($item);
	$form->addCommandButton('save', 'Save');

	// Check for submission
	if (isset($_POST['submitted']) && $_POST['submitted']) {
		if ($form->checkInput()) {
			// Process and save data from $_POST
			// ....
			if (count($_FILES) && isset($_FILES['images']) && $_FILES['images']['name']) {
				// Also process a file upload
				// ....
				echo json_encode(array('success' => true));
				exit();
			}
		} else {
			$form->setValuesByPost();
		}
		ilUtil::sendSuccess('Form processed successfully');
	}

	return $form->getHTML();
}