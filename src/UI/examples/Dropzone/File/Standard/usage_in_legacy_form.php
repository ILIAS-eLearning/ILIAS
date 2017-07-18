<?php

/**
 * Class ilFileStandardDropzoneInputGUI
 */
class ilFileStandardDropzoneInputGUI extends ilFileInputGUI {

	static $count = 0;

	function render($a_mode = "") {
		global $DIC;
		$uiFactory = $DIC->ui()->factory();
		$renderer = $DIC->ui()->renderer();

		$n = ++self::$count;
		$dropzone = $uiFactory->dropzone()->file()->standard('')
			->withIdentifier($this->getPostVar())
			->withAllowedFileTypes($this->getSuffixes());
		$out = "<div id='ilFileStandardDropzoneInputGUIWrapper{$n}'>" . $renderer->render($dropzone) . '</div>';

		self::$count++;
		// We need some javascript magic
		$tpl = $DIC['tpl'];
		$tpl->addOnLoadCode("
		var \$wrapper = $('#ilFileStandardDropzoneInputGUIWrapper{$n}');
		var \$form = \$wrapper.closest('form');
		var uploadId = \$wrapper.find('.il-upload-file-list').attr('id');
		il.UI.uploader.setForm(uploadId, \$form.attr('id'));
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
	$item->setSuffixes(['jpg', 'gif', 'png']);
	$item->setInfo('Allowed file types: ' . implode(', ', $item->getSuffixes()));
	$form->addItem($item);
	$form->addCommandButton('save', 'Save');

	// Check for submission
	if (isset($_POST['submitted']) && $_POST['submitted']) {
		if ($form->checkInput()) {
			// Process and save data from $_POST
			// ....
			if (count($_FILES) && isset($_FILES['images'])) {
				// Also process a file upload
				// ....
				echo json_encode(array('success' => true));
				exit();
			}
		} else {
			$form->setValuesByPost();
		}
	}

	return $form->getHTML();
}