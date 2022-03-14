<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Dropzone\File\Standard;

function restrict_max_files_and_file_size()
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $dropzone = $factory
        ->dropzone()->file()->standard(
            (new \ilUIAsyncDemoFileUploadHandlerGUI()),
            '#'
        )
        ->withMaxFiles(2)
        ->withMaxFileSize(2048)
        ->withUploadButton(
            $factory->button()->shy('Upload files', '#')
        )
        ->withMessage('Drag files in here to upload them!')
        ->withTitle('Upload your files here');

    return $renderer->render($dropzone);
}
