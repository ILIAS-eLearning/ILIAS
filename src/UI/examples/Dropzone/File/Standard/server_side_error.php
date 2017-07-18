<?php
function server_side_error() {

	global $DIC;
	$uiFactory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	if (isset($_GET['example']) && $_GET['example'] == 4 && count($_FILES)) {
		echo json_encode(['success' => false, 'message' => 'Unable to store file on server']);
		exit(0);
	}

	$uploadUrl = $_SERVER['REQUEST_URI'] . '&example=4';
	$dropzone = $uiFactory->dropzone()->file()->standard($uploadUrl)
		->withMessage("Drag and drop some PDF files over here...")
		->withUploadButton($uiFactory->button()->standard('Upload', ''));

	return $renderer->render($dropzone);
}