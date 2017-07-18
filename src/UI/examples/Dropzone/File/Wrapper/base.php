<?php
function base() {

	global $DIC;

	if (isset($_GET['example']) && $_GET['example'] == 1 && count($_FILES)) {
		echo json_encode(['success' => true, 'message' => 'Successfully uploaded files']);
		exit(0);
	}

	$uiFactory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$content = $uiFactory->legacy('Hello World, drop some files over me!');
	$uploadUrl = $_SERVER['REQUEST_URI'] . '&example=1';

	$upload = $uiFactory->dropzone()->file()->wrapper($uploadUrl, $content);

	return $renderer->render($upload);
}