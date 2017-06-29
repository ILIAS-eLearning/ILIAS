<?php
function restrict_max_files_and_file_size() {

	global $DIC;

	if (isset($_GET['example']) && $_GET['example'] == 3 && count($_FILES)) {
		echo json_encode(['success' => true, 'message' => 'Successfully uploaded files']);
		exit(0);
	}

	$uiFactory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$content = $uiFactory->legacy('You are not allowed to upload more than 2 files, max file size is 500kB');
	$uploadUrl = $_SERVER['REQUEST_URI'] . '&example=3';

	$upload = $uiFactory->dropzone()->file()->upload($content, $uploadUrl)
		->withMaxFiles(2)
		->withFileSizeLimit(500 * 1000);

	return $renderer->render($upload);
}