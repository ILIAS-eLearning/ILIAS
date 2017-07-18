<?php
function with_custom_file_metadata() {

	global $DIC;
	$uiFactory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	if (isset($_GET['example']) && $_GET['example'] == 2 && count($_FILES)) {
		echo json_encode(['success' => true, 'message' => 'Successfully uploaded files']);
		exit(0);
	}

	$uploadUrl = $_SERVER['REQUEST_URI'] . '&example=2';
	$dropzone = $uiFactory->dropzone()->file()->standard($uploadUrl)
		->withCustomFileNames(true)
		->withFileDescriptions(true)
		->withUploadButton($uiFactory->button()->standard('Upload', ''));

	return $renderer->render($dropzone);
}