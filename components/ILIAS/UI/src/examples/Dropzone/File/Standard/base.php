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
 *   Example for rendering a file dropzone.
 *
 * expected output: >
 *   ILIAS shows a white box with a dashed border. You can see a text "Upload files" (displayed as a link) on the left
 *   side and on the right side the text "Drag files in here to upload them". Clicking onto the link or dragging a file
 *   into the box opens a small window with the buttons "Save" and "Cancel". If you dragged a file into the box you can
 *   see the file in said small window, too. You can upload only one of file. The window will be closed if you click
 *   onto the "Save" button. An upload doesn't happen in this example. If a file got listed after saving your selection
 *   you can remove the file by clicking the "X" on the right side.
 * ---
 */
function base()
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $wrapper = $DIC->http()->wrapper()->query();

    $submit_flag = 'dropzone_standard_base';
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
