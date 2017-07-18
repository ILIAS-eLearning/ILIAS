<?php
function with_custom_message() {

	global $DIC;
	$uiFactory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	if (isset($_GET['example']) && $_GET['example'] == 3 && count($_FILES)) {
		echo json_encode(['success' => true, 'message' => 'Successfully uploaded files']);
		exit(0);
	}

	$uploadUrl = $_SERVER['REQUEST_URI'] . '&example=3';
	$dropzone = $uiFactory->dropzone()->file()->standard($uploadUrl)
		->withMessage("Drag and drop some PDF files over here...")
		->withUploadButton($uiFactory->button()->standard('Upload', ''));

	return $renderer->render($dropzone);
}