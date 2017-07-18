<?php
function restrict_file_types() {

	global $DIC;

	if (isset($_GET['example']) && $_GET['example'] == 2 && count($_FILES)) {
		echo json_encode(['success' => true, 'message' => 'Successfully uploaded files']);
		exit(0);
	}

	$uiFactory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$content = $uiFactory->legacy('You are only allowed to upload JPGs, GIFs or PNGs');
	$uploadUrl = $_SERVER['REQUEST_URI'] . '&example=2';

	$upload = $uiFactory->dropzone()->file()->wrapper($uploadUrl, $content)
		->withAllowedFileTypes(['jpg', 'png', 'gif'])
		->withCustomFileNames(true)
		->withFileDescriptions(true);

	return $renderer->render($upload);
}