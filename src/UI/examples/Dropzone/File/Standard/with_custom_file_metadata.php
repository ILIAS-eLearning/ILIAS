<?php
function with_custom_file_metadata()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    // Handle a file upload ajax request
    if (isset($_GET['example']) && $_GET['example'] == 2) {
        $upload = $DIC->upload();
        try {
            $upload->process();
            // $upload->moveFilesTo('/myPath/'); // Since we are in an example here, we do not move the files. But this would be the way wou move files using the FileUpload-Service

            // Access the custom file name and description via $_POST parameters:
            // $_POST['customFileName'] and $_POST['fileDescription']

            // The File-Dropzones will expect a valid json-Status (success true or false).
            echo json_encode(['success' => true, 'message' => 'Successfully uploaded file']);
        } catch (Exception $e) {
            // See above
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit();
    }

    $uploadUrl = $_SERVER['REQUEST_URI'] . '&example=2';
    $dropzone = $factory->dropzone()->file()->standard($uploadUrl)
        ->withUserDefinedFileNamesEnabled(true)
        ->withUserDefinedDescriptionEnabled(true)
        ->withUploadButton($factory->button()->standard('Upload', ''));

    return $renderer->render($dropzone);
}
