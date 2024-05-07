<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Dropzone\File\Standard;

function bulky()
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $wrapper = $DIC->http()->wrapper()->query();

    $submit_flag = 'dropzone_standard_bulky';
    $post_url = "{$request->getUri()}&$submit_flag";

    $dropzone = $factory
        ->dropzone()->file()->standard(
            'Upload your files here',
            'Drag files in here to upload them!',
            $post_url,
            $factory->input()->field()->file(
                new \ilUIAsyncDemoFileUploadHandlerGUI(),
                'your files'
            )
        )
        ->withBulky(true)
        ->withUploadButton(
            $factory->button()->shy('Upload files', '#')
        );

    // please use ilCtrl to generate an appropriate link target
    // and check it's command instead of this.
    if ($wrapper->has($submit_flag)) {
        $dropzone = $dropzone->withRequest($request);
        $data = $dropzone->getData();
    } else {
        $data = 'no results yet.';
    }

    return '<pre>' . print_r($data, true) . '</pre>' .
        $renderer->render($dropzone);
}
