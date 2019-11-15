<?php
function base()
{
    global $DIC;

    // Handle a file upload ajax request
    if (isset($_GET['example']) && $_GET['example'] == 1) {
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

    $content = $uiFactory->panel()->standard('Panel', $uiFactory->legacy('Hello World, drag some files over me!'));
    $uploadUrl = $_SERVER['REQUEST_URI'] . '&example=1';

    $upload = $uiFactory->dropzone()->file()->wrapper($uploadUrl, $content);

    return $renderer->render($upload);
}
