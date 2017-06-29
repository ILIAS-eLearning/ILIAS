<?php
function server_side_error() {

	global $DIC;

	if (isset($_GET['example']) && $_GET['example'] == 4 && count($_FILES)) {
		echo json_encode(['success' => false, 'error' => 'Some error happened, we are sorry']);
		exit(0);
	}

	$uiFactory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$content = $uiFactory->legacy('Upload will fail for any file you drop here');
	$uploadUrl = $_SERVER['REQUEST_URI'] . '&example=4';

	$upload = $uiFactory->dropzone()->file()->upload($content, $uploadUrl);

	return $renderer->render($upload);
}