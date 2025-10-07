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

/**
 * ---
 * description: >
 *   The example shows how to create and render a Image Field with existing values and attach
 *   it to a Standard Form. The example does not show data processing.
 *
 * expected output: >
 *   ILIAS shows the Image Field inside a Standard Form. Its label and byline are correctly
 *   rendered next to and underneath the actual input, which is displayed as a Shy Button
 *   inside a discernible box. Two file entries above the discernible box are visible. Clicking
 *   the Glyph next to their names will expand the entry further. Both entries will show another
 *   Switchable Group Field, which is required and is already provided with some information
 *   about the corresnponding file.
 * ---
 */
function with_value(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $input = $factory->input()->field()->image(
        new \ilUIDemoFileUploadHandlerGUI(),
        ImagePurpose::USER_DEFINED,
        'Upload Image',
        'Please provide an alternate text if necessary.',
        $factory->input()->field()->text('Additional information')
    )->withMaxFiles(2);

    $input = $input->withValue([
        [
            'file_id_1',
            [
                [ImagePurpose::INFORMATIVE->name, ['alternate text']],
                'additional metadata',
            ],
        ],
        [
            'file_id_2',
            [
                ImagePurpose::DECORATIVE->name,
                'additional metadata',
            ],
        ],
    ]);

    $form = $factory->input()->container()->form()->standard("#", [$input]);

    return $renderer->render($form);
}
