<?php
function base() {

	global $DIC;
	$uiFactory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	if (isset($_GET['example']) && $_GET['example'] == 1 && count($_FILES)) {
		echo json_encode(['success' => true, 'message' => 'Successfully uploaded files']);
		exit(0);
	}

	$uploadUrl = $_SERVER['REQUEST_URI'] . '&example=1';
	$standardDropzone = $uiFactory->dropzone()->file()->standard($uploadUrl)
		->withUploadButton($uiFactory->button()->standard('Upload', ''));

	return $renderer->render($standardDropzone);
}