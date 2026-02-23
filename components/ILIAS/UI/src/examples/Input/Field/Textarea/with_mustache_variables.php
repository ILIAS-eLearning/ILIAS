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

namespace ILIAS\UI\examples\Input\Field\Textarea;

/**
 * ---
 * description: >
 *   Examples showing how to create and render a basic textarea field with individual clickable mustache variables.
 *   A variable will be inserted into the textarea field after clicking onto it. This example does
 *    not contain any data processing.
 *
 * expected output: >
 *   Three variables in double curly brackets are rendered below the markdown field as shy buttons (clickable).
 *   A click onto a variable will insert the variable into the markdown field.
 * ---
 */
function with_mustache_variables(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $input = $factory->input()->field()->textarea(
        "Create template",
        "You can use Mustache variables here, how fun :).",
    );

    $input = $input->withMustacheVariables(
        [
            'var1' => 'Test Variable 1',
            'var2' => 'Test Variable 2',
            'var3' => 'Test Variable 3',
        ],
        'Also, some more info could be added here as well.'
    );

    $form = $factory->input()->container()->form()->standard('#', [$input]);

    return $renderer->render($form);
}
