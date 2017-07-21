<?php
function with_custom_file_metadata() {

	global $DIC;
	$factory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	// Handle a file upload ajax request
	if (isset($_GET['example']) && $_GET['example'] == 2) {
		$upload = $DIC->upload();
		try {
			$upload->process();
			// $upload->moveFilesTo('/myPath/');
			// Access the custom file name and description via $_POST parameters:
			// $_POST['customFileName'] and $_POST['fileDescription']
			echo json_encode(['success' => true, 'message' => 'Successfully uploaded file']);
		} catch (Exception $e) {
			echo json_encode(['success' => false, 'message' => $e->getMessage()]);
		}
		exit();
	}

	$uploadUrl = $_SERVER['REQUEST_URI'] . '&example=2';
	$dropzone = $factory->dropzone()->file()->standard($uploadUrl)
		->withCustomFileNames(true)
		->withFileDescriptions(true)
		->withUploadButton($factory->button()->standard('Upload', ''));

	return $renderer->render($dropzone);
}