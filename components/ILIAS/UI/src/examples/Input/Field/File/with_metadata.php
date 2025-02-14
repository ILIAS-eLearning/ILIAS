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

/**
 * ---
 * description: >
 *   Example of how to create and render a File Field with ab additional metadata Field
 *   and attach it to a form. This example does not show any processing.
 *
 * expected output: >
 *   ILIAS shows the File Field inside a Standard Form. Its label and byline are correctly
 *   rendered next to and underneath the actual input, which is displayed as a Shy Button
 *   inside a discernible box. You can choose a file by dragging it onto this box, or by
 *   clicking Shy Button inside it, which opens a file browser window. Once you have choosen
 *   a file, a new file entry above the discernible box will show appear. Clicking the Glyph
 *   next to the name of your file will expand the entry further. Another Field becomes
 *   visible, which can be used to provide more information about the file. Submitting the
 *   Standard Form has no effect because the example does not implement any processing.
 * ---
 */
function with_metadata(): string
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $file_input = $factory->input()->field()->file(
        new \ilUIDemoFileUploadHandlerGUI(),
        "Upload File",
        "After choosing the file you can add additional metadata.",
        $factory->input()->field()->switchableGroup([
            $factory->input()->field()->group([$factory->input()->field()->text('New filename')], 'Yes'),
            $factory->input()->field()->group([], 'No'),
        ], 'Change filename?'),
    );

    $form = $factory->input()->container()->form()->standard("#", [$file_input]);

    return $renderer->render($form);
}
