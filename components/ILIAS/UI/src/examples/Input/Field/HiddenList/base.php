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

namespace ILIAS\UI\examples\Input\Field\HiddenList;

/**
 * ---
 * description: >
 *   The example shows how to create and render a basic Hidden List Input and
 *   attach it to a form. This example does not contain any data processing.
 *
 * expected output: >
 *   User doesn't see a input field, ILIAS shows an empty form.
 * ---
 */
function base(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $input = $factory->input()->field()->hiddenList()->withValue(['some_id_1', 'some_id_2', '...']);

    $form = $factory->input()->container()->form()->standard("#", [$input]);

    return $renderer->render($form);
}
