<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Dropzone\File\Standard;

function large()
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $dropzone = $factory
        ->dropzone()->file()->standard(
            (new \ilUIAsyncDemoFileUploadHandlerGUI()),
            '#'
        )
        ->withLargeZone()
        ->withUploadButton(
            $factory->button()->shy('Upload files', '#')
        )
        ->withMessage('Drag files in here to upload them!')
        ->withTitle('Upload your files here');

    return $renderer->render($dropzone);
}
