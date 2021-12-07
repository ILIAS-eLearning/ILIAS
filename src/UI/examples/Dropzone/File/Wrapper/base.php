<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Dropzone\File\Wrapper;

use Exception;

function base()
{
    global $DIC;
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    // Handle a file upload ajax request
    if ($request_wrapper->has('example') && $request_wrapper->retrieve('example', $refinery->kindlyTo()->int()) == 1) {
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
