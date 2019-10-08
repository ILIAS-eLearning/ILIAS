<?php
function server_side_error()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    if (isset($_GET['example']) && $_GET['example'] == 4) {
        // The File-Dropzones will expect a valid json-Status (success true or false).
        echo json_encode(['success' => false, 'message' => 'Unable to store file on server']);
        exit(0);
    }

    $uploadUrl = $_SERVER['REQUEST_URI'] . '&example=4';
    $dropzone = $factory->dropzone()->file()->standard($uploadUrl)
        ->withMessage('Drag and drop your files here. Note that any upload will be failing!')
        ->withUploadButton($factory->button()->standard('Upload', ''));
    return $renderer->render($dropzone);
}
