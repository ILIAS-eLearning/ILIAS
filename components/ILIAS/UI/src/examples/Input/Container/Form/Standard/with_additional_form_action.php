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

namespace ILIAS\UI\examples\Input\Container\Form\Standard;

/**
 * ---
 * description: >
 *   Examples showing how to create and render a basic form with additional form actions. This example does
 *   not contain any data processing.
 *
 * expected output: >
 *   Three clickable buttons (Action 1, Action 2 and Main Action) are rendered above and below the Input field.
 *   The Main Action button is rendered in a different color than Action 1 and Action 2.
 * ---
 */
function with_additional_form_action(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $input = $factory->input()->field()->text("Pseudo input");

    $form = $factory->input()->container()->form()->standard("#0", [$input]);

    $form = $form->withAdditionalFormAction('#1', 'Action 1');
    $form = $form->withAdditionalFormAction('#2', 'Action 2');
    $form = $form->withSubmitLabel('Main Action');

    return $renderer->render($form);
}
