<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\UI\examples\Dropzone\File\Standard;

/**
 * ---
 * description: >
 *   Example for rendering a standard file dropzone with additional input.
 *
 * expected output: >
 *   ILIAS shows a base file upload/dropzone box. Clicking onto the link or dragging a file
 *   into the box opens a small window with the buttons "Save" and "Cancel" and an additional input field. The upload
 *   process works as in the base file upload/dropzone example.
 * ---
 */
function with_additional_input()
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $wrapper = $DIC->http()->wrapper()->query();

    $submit_flag = 'dropzone_standard_with_additional_input';
    $post_url = "{$request->getUri()}&$submit_flag";

    $dropzone = $factory
        ->dropzone()->file()->standard(
            'Upload your files here',
            'Drag files in here to upload them!',
            $post_url,
            $factory->input()->field()->file(
                new \ilUIAsyncDemoFileUploadHandlerGUI(),
                'your files'
            ),
            $factory->input()->field()->text(
                'Additional Input',
                'Additional input which affects all files of this upload.'
            )
        )->withUploadButton(
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
