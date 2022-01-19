<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Dropzone\File\Standard;

function with_custom_file_metadata()
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $dropzone = $factory
        ->dropzone()->file()->standard(
            (new \ilUIAsyncDemoFileUploadHandlerGUI()),
            '#'
        )
        ->withUploadButton(
            $factory->button()->shy('Upload files', '#')
        )
        ->withMessage('Drag files in here to upload them!')
        ->withTitle('Upload your files here')
        ->withMetadataFields(true);

    return $renderer->render($dropzone);
}
