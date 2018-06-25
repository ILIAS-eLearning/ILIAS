<?php
function dropzone_in_dropzone() {

	global $DIC;

//	// Handle a file upload ajax request
//	if (isset($_GET['example']) && $_GET['example'] == 2) {
//		$upload = $DIC->upload();
//		try {
//			$upload->process();
//			// $upload->moveFilesTo('/myPath/');  // Since we are in an example here, we do not move the files. But this would be the way wou move files using the FileUpload-Service
//
//			// The File-Dropzones will expect a valid json-Status (success true or false).
//			echo json_encode(['success' => true, 'message' => 'Successfully uploaded file']);
//		} catch (Exception $e) {
//			// See above
//			echo json_encode(['success' => false, 'message' => $e->getMessage()]);
//		}
//		exit();
//	}

	$uiFactory = $DIC->ui()->factory();
	$renderer = $DIC->ui()->renderer();

	$uploadUrl = $_SERVER['REQUEST_URI'] . '&example=2';

	$content = $uiFactory->dropzone()->file()->standard($uploadUrl);
	$panel = $uiFactory->panel()->standard("Panel Titel", $content);

	$upload = $uiFactory->dropzone()->file()->wrapper($uploadUrl, $panel)
		->withMaxFiles(2)
		->withFileSizeLimit(new \ILIAS\Data\DataSize(300 * 1000, \ILIAS\Data\DataSize::KB));

	return $renderer->render($upload);
}