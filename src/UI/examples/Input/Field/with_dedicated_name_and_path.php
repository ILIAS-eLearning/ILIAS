<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field;

/**
 * Example showing Inputs with dedicated names that are contained within a named group.
 * The name of the group is added to the 'path' and included in the name of the sub-inputs.
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
