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

namespace ILIAS\UI\examples\Input\Field;

/**
 */
/**
 * ---
 * description: >
 *   Example showing an Input with an optional dedicated name which is used as NAME attribute on the rendered input.
 *   This option is available for all Input/Fields. Inputs without a dedicated name will get an auto-generated name.
 *   Please see the interface of withDedicatedName() for further details on naming.
 *
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function with_dedicated_name()
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $text_input = $ui->input()->field()
        ->text("Username", "A username")
        ->withDedicatedName('username');

    // Inputs with and without dedicated names can be mixed
    $password_input = $ui->input()->field()
         ->password("Password", "A secret password");

    $duration_input = $ui->input()->field()
         ->duration("Valid from/to")
         ->withDedicatedName('valid');

    $form = $ui->input()->container()->form()->standard("", [$text_input, $password_input, $duration_input]);
    return $renderer->render($form);
}
