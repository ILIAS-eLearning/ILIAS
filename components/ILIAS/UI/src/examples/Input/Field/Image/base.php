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
 */

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\Image;

use ILIAS\Data\ImagePurpose;
use ILIAS\UI\URLBuilder;

/**
 * ---
 * description: >
 *   The example shows how to create and render a Image Field and attach it to a Standard Form.
 *   The example also shows data processing.
 *
 * expected output: >
 *   ILIAS shows the Image Field inside a Standard Form. Its label and byline are correctly
 *   rendered next to and underneath the actual input, which is displayed as a Shy Button
 *   inside a discernible box. You can choose a file by dragging it onto this box, or by
 *   clicking Shy Button inside it, which opens a file browser window. Once you have choosen
 *   a file, a new file entry above the discernible box will show appear. Clicking the Glyph
 *   next to the name of your file will expand the entry further. Another Switchable Group Field
 *   becomes visible, which can be used to provide more information about the image. Once
 *   information has been filled out and the Form is submitted, the output should become
 *   visible above the Standard Form.
 * ---
 */
function base(): string
{
    global $DIC;

    $http = $DIC->http();
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $get_request = $http->wrapper()->query();
    $data_factory = new \ILIAS\Data\Factory();
    $refinery_factory = new \ILIAS\Refinery\Factory($data_factory, $DIC->language());

    $example_uri = $data_factory->uri((string) $http->request()->getUri());
    $url_builder = new URLBuilder($example_uri);
    [$url_builder, $token] = $url_builder->acquireParameter(explode('\\', __NAMESPACE__), "process");

    $input = $factory->input()->field()->image(
        new \ilUIDemoFileUploadHandlerGUI(),
        ImagePurpose::USER_DEFINED,
        'Upload Image',
        'Please provide an alternate text if necessary.',
    );

    $form = $factory->input()->container()->form()->standard(
        (string) $url_builder->withParameter($token, '1')->buildURI(),
        [$input]
    );

    // simulates a form processing endpoint:
    if ($get_request->has($token->getName())) {
        $form = $form->withRequest($http->request());
        $data = $form->getData();
    } else {
        $data = 'No submitted data yet.';
    }

    return '<pre>' . print_r($data, true) . '</pre>' . $renderer->render($form);
}
