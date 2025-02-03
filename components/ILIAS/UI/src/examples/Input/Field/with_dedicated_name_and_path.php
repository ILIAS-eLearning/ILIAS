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
 * ---
 * description: >
 *   Example showing Inputs with dedicated names that are contained within a named group.
 *   The name of the group is added to the 'path' and included in the name of the sub-inputs.
 *
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function with_dedicated_name_and_path()
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $street = $ui->input()->field()
        ->text("Street", "Street and No.")
        ->withDedicatedName('street');

    $city = $ui->input()->field()
       ->text("City")
       ->withDedicatedName('city');

    // This creates inputs named 'address/street' and 'address/city'
    $address = $ui->input()->field()
         ->group([$street, $city], "Address")
         ->withDedicatedName('address');

    $form = $ui->input()->container()->form()->standard("", [$address]);
    return $renderer->render($form);
}
