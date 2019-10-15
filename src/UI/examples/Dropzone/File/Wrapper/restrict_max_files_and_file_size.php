<?php
function restrict_max_files_and_file_size()
{
    global $DIC;

    // Handle a file upload ajax request
    if (isset($_GET['example']) && $_GET['example'] == 2) {
        $upload = $DIC->upload();
        try {
            $upload->process();
            // $upload->moveFilesTo('/myPath/');  // Since we are in an example here, we do not move the files. But this would be the way wou move files using the FileUpload-Service

            // The File-Dropzones will expect a valid json-Status (success true or false).
            echo json_encode(['success' => true, 'message' => 'Successfully uploaded file']);
        } catch (Exception $e) {
            // See above
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    $uiFactory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $content = $uiFactory->legacy('You are not allowed to upload more than 2 files, max file size is 300kB');
    $uploadUrl = $_SERVER['REQUEST_URI'] . '&example=2';
    $upload = $uiFactory->dropzone()->file()->wrapper($uploadUrl, $content)
        ->withMaxFiles(2)
        ->withFileSizeLimit(new \ILIAS\Data\DataSize(300 * 1000, \ILIAS\Data\DataSize::KB));

    return $renderer->render($upload);
}
