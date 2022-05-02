<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Dropzone\File\Wrapper;

function base()
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $dropzone = $factory
        ->dropzone()->file()->wrapper(
            (new \ilUIAsyncDemoFileUploadHandlerGUI()),
            '#',
            $factory->messageBox()->info('Drag and drop files onto me!')
        )
        ->withTitle('Upload your files here');

    return $renderer->render($dropzone);
}
