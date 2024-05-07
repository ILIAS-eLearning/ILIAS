<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Dropzone\File\Wrapper;

function with_clear_button()
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    $submit_flag = 'dropzone_wrapper_with_clear_button';
    $post_url = "{$request->getUri()}&$submit_flag";

    $dropzone = $factory
        ->dropzone()->file()->wrapper(
            'Upload your files here',
            $post_url,
            $factory->messageBox()->info('Drag and drop files onto me!'),
            $factory->input()->field()->file(
                new \ilUIAsyncDemoFileUploadHandlerGUI(),
                'Your files'
            )
        );

    $dropzone = $dropzone->withActionButtons([
        $factory->button()->standard('Clear files!', '#')->withOnClick($dropzone->getClearSignal())
    ]);

    return $renderer->render($dropzone);
}
