<?php

/**
 * Class ilFileStandardDropzoneInputGUI
 *
 * Wrapper around the standard dropzone to use its functionality in a legacy form.
 *
 * Please note:
 *
 * - Each upload request includes all form data
 * - Uploads are only processed if the form is valid on the client side
 * - After all uploads have been processed successfully, the form is submitted again
 * - If any file fails to upload, the form is not submitted
 */
class ilFileStandardDropzoneInputGUI extends ilFileInputGUI {

	/**
	 * @var int
	 */
	static $count = 0;
	/**
	 * @var string
	 */
	protected $uploadUrl = '';
	/**
	 * @var int
	 */
	protected $maxFiles = 1;
	/**
	 * @var \ILIAS\Data\DataSize
	 */
	protected $maxFileSize;
	/**
	 * @var string
	 */
	protected $dropzoneMessage = '';


	/**
	 * @return string
	 */
	public function getUploadUrl() {
		return $this->uploadUrl;
	}


	/**
	 * @param string $uploadUrl
	 *
	 * @return $this
	 */
	public function setUploadUrl($uploadUrl) {
		$this->uploadUrl = $uploadUrl;

		return $this;
	}


	/**
	 * @return int
	 */
	public function getMaxFiles() {
		return $this->maxFiles;
	}


	/**
	 * @param int $maxFiles
	 */
	public function setMaxFiles($maxFiles) {
		$this->maxFiles = $maxFiles;
	}


	/**
	 * @return \ILIAS\Data\DataSize
	 */
	public function getMaxFileSize() {
		return $this->maxFileSize;
	}


	/**
	 * @param \ILIAS\Data\DataSize $maxFileSize
	 */
	public function setMaxFileSize(\ILIAS\Data\DataSize $maxFileSize) {
		$this->maxFileSize = $maxFileSize;
	}


	/**
	 * @return string
	 */
	public function getDropzoneMessage() {
		return $this->dropzoneMessage;
	}


	/**
	 * @param string $dropzoneMessage
	 */
	public function setDropzoneMessage($dropzoneMessage) {
		$this->dropzoneMessage = $dropzoneMessage;
	}


	function render($a_mode = "") {
		global $DIC;
		$factory = $DIC->ui()->factory();
		$renderer = $DIC->ui()->renderer();

		$dropzone = $factory->dropzone()->file()->standard($this->getUploadUrl())->withIdentifier($this->getPostVar())->withMaxFiles($this->getMaxFiles())->withMessage($this->getDropzoneMessage())->withAllowedFileTypes($this->getSuffixes());
		if ($this->getMaxFileSize()) {
			$dropzone = $dropzone->withFileSizeLimit($this->getMaxFileSize());
		}
		$n = ++ self::$count;
		$out = "<div id='ilFileStandardDropzoneInputGUIWrapper{$n}'>" . $renderer->render($dropzone)
		       . '</div>';
		// We need some javascript magic
		/** @var ilTemplate $tpl */
		$tpl = $DIC['tpl'];
		$tpl->addJavaScript('./src/UI/examples/Dropzone/File/Standard/ilFileStandardDropzoneInputGUI.js');
		$tpl->addOnLoadCode("ilFileStandardDropzoneInputGUI.init('ilFileStandardDropzoneInputGUIWrapper{$n}');");

		return $out;
	}


	function checkInput() {
		if ($this->getRequired() && !isset($_FILES[$this->getPostVar()])) {
			return false;
		}

		return true;
	}
}

function with_usage_in_legacy_form() {
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
	$item = new ilFileStandardDropzoneInputGUI('Files', 'files');
	$item->setUploadUrl($form->getFormAction());
	$item->setSuffixes([ 'jpg', 'gif', 'png', 'pdf' ]);
	$item->setInfo('Allowed file types: ' . implode(', ', $item->getSuffixes()));
	$item->setDropzoneMessage('For the purpose of this demo, any PDF file will fail to upload');
	$form->addItem($item);
	$form->addCommandButton('save', 'Save');

	// Check for submission
	global $DIC;
	if (isset($_POST['submitted']) && $_POST['submitted']) {
		if ($form->checkInput()) {
			// We might also want to process and save other form data here
			$upload = $DIC->upload();
			// Check if this is a request to upload a file
			if ($upload->hasUploads()) {
				try {
					$upload->process();
					// We simulate a failing response for any uploaded PDF file
					$uploadedPDFs = array_filter($upload->getResults(), function ($uploadResult) {
						/** @var $uploadResult \ILIAS\FileUpload\DTO\UploadResult */
						return ($uploadResult->getMimeType() == 'application/pdf');
					});
					$uploadResult = count($uploadedPDFs) == 0;
					echo json_encode(array( 'success' => $uploadResult ));
				} catch (Exception $e) {
					echo json_encode(array( 'success' => false ));
				}
				exit();
			}
		} else {
			$form->setValuesByPost();
		}
		ilUtil::sendSuccess('Form processed successfully');
	}

	return $form->getHTML();
}