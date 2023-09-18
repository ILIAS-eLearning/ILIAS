<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Dropzone\File\Standard;

function base()
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    $dropzone = $factory
        ->dropzone()->file()->standard(
            'Upload your files here',
            'Drag files in here to upload them!',
            '#',
            $factory->input()->field()->file(
                new \ilUIAsyncDemoFileUploadHandlerGUI(),
                'your files'
            )
        )->withUploadButton(
            $factory->button()->shy('Upload files', '#')
        );

    // please use ilCtrl to generate an appropriate link target
    // and check it's command instead of this.
    if ('POST' === $request->getMethod()) {
        $dropzone = $dropzone->withRequest($request);
        $data = $dropzone->getData();
    } else {
        $data = 'no results yet.';
    }

    return '<pre>' . print_r($data, true) . '</pre>' .
        $renderer->render($dropzone);
}
