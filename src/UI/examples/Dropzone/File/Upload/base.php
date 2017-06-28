<?php
function base() {

	global $DIC;

	if (count($_FILES)) {
//		var_dump($_POST);
		echo json_encode(['success' => true]);
		exit(0);
	}

	$uiFactory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$content = $uiFactory->legacy('My Component');
	$uploadUrl = $_SERVER['REQUEST_URI'];

	$upload = $uiFactory->dropzone()->file()->upload($content, $uploadUrl);

	return $renderer->render($upload);
}