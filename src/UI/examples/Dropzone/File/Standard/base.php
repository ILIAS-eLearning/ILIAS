<?php
function base() {

	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	// Handle a file upload ajax request
	if (isset($_GET['example']) && $_GET['example'] == 1) {
		$upload = $DIC->upload();
		try {
			$upload->process();
			// $upload->moveFilesTo('/myPath/');
			echo json_encode(['success' => true, 'message' => 'Successfully uploaded file']);
		} catch (Exception $e) {
			echo json_encode(['success' => false, 'message' => $e->getMessage()]);
		}
		exit();
	}

	$uploadUrl = $_SERVER['REQUEST_URI'] . '&example=1';
	$dropzone = $factory->dropzone()->file()->standard($uploadUrl)
		->withUploadButton($factory->button()->standard('Upload', ''));

	return $renderer->render($dropzone);
}