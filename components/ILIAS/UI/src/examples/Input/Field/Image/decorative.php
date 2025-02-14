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
 *   The example shows how to create and render a Image Field for decorative images and attach
 *   it to a Standard Form. The example does not show data processing.
 *
 * expected output: >
 *   ILIAS shows the Image Field inside a Standard Form. Its label and byline are correctly
 *   rendered next to and underneath the actual input, which is displayed as a Shy Button
 *   inside a discernible box. You can choose a file by dragging it onto this box, or by
 *   clicking Shy Button inside it, which opens a file browser window. Once you have choosen
 *   a file, a new file entry above the discernible box will show appear. There is no Glyph
 *   next to the name of your file and the entry cannot be expanded any further.
 * ---
 */
function decorative(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $input = $factory->input()->field()->image(
        new \ilUIDemoFileUploadHandlerGUI(),
        ImagePurpose::DECORATIVE,
        'Upload Image',
        'This image will be purely decorative',
    );

    $form = $factory->input()->container()->form()->standard("#", [$input]);

    return $renderer->render($form);
}
