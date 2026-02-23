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

namespace ILIAS\UI\examples\Input\Field\File;

use ILIAS\UI\URLBuilder;

/**
 * ---
 * description: >
 *   Example of how to process passwords.
 *   Note that the value of Password is a Data\Password, not a string-primitive.
 *
 * expected output: >
 *   ILIAS shows a field titled "Upload File" next to a box surrounded by dashed lines. Choose a file by clicking "Select
 *   files" or dragging a file to the box. Save your selection. ILIAS will confirm your action in the following format:
 *
 *   Array
 *   (
 *       [file] => Array
 *       (
 *           [0] => 06f7d8e94a28d102cf35483e346c121c
 *       )
 *   )
 * ---
 */
function in_form()
{
    // Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $http = $DIC->http();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $get_request = $http->wrapper()->query();
    $data_factory = new \ILIAS\Data\Factory();

    $example_uri = $data_factory->uri((string) $http->request()->getUri());
    $url_builder = new URLBuilder($example_uri);
    [$process_form_url_builder, $process_form_parameter] = $url_builder->acquireParameter(explode('\\', __NAMESPACE__), "process_single");

    // Step 1: Define the input field.
    // See the implementation of a UploadHandler in components/ILIAS/UI_/classes/class.ilUIDemoFileUploadHandlerGUI.php
    $file = $ui->input()->field()->file(new \ilUIDemoFileUploadHandlerGUI(), "File Upload", "You can drop your files here");

    // Step 2: Define the form and attach the field.
    $form = $ui->input()->container()->form()->standard(
        (string) $process_form_url_builder->withParameter($process_form_parameter, '1')->buildURI(),
        ['file' => $file]
    );

    // Step 3: Define some data processing.
    $result = '';
    if ($get_request->has($process_form_parameter->getName())) {
        $form = $form->withRequest($request);
        $result = $form->getData();
    }

    // Step 4: Render the form/result.
    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
