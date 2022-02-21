<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Dropzone\File\Standard;

function server_side_error()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    if ($request_wrapper->has('example') && $request_wrapper->retrieve('example', $refinery->kindlyTo()->int()) == 4) {
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
