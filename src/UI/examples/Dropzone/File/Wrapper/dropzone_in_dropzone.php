<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Dropzone\File\Wrapper;

function dropzone_in_dropzone()
{
    // This example shows how the wrapper-dropzone will be "unusable" when another Dropzone is in it.
    // Dropping a file on the outer wrapper dropzone won't open a modal.
    // The innermost dropzone will be the working one. This example does not proceed the file, it's
    // only purpose is tho show stacking dropzones.

    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $dropzone = $factory
        ->dropzone()->file()->wrapper(
            (new \ilUIAsyncDemoFileUploadHandlerGUI()),
            '#',
            $factory->dropzone()->file()->standard(
                (new \ilUIAsyncDemoFileUploadHandlerGUI()),
                '#'
            )
                    ->withUploadButton(
                        $factory->button()->shy('Upload Files', '#')
                    )
                    ->withMessage('This dropzone is only usable via button')
                    ->withTitle('Subordinate Dropzone')
        )
        ->withTitle('Upload your files here');

    return $renderer->render($dropzone);
}
