<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Dropzone\File\Wrapper;

function restrict_max_files_and_file_size()
{
    global $DIC;
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    // Handle a file upload ajax request
    if ($request_wrapper->has('example') && $request_wrapper->retrieve('example', $refinery->kindlyTo()->int()) == 2) {
        $upload = $DIC->upload();
        try {
            $upload->process();
            // $upload->moveFilesTo('/myPath/');  // Since we are in an example here, we do not move the files. But this would be the way wou move files using the FileUpload-Service

            // The File-Dropzones will expect a valid json-Status (success true or false).
            echo json_encode(['success' => true, 'message' => 'Successfully uploaded file']);
        } catch (\Exception $e) {
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
