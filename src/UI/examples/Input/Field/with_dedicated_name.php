<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field;

/**
 * Example showing an Input with an optional dedicated name which is used as NAME attribute on the rendered input.
 * This option is available for all Input/Fields. Inputs without a dedicated name will get an auto-generated name.
 * Please see the interface of withDedicatedName() for further details on naming.
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
    return $renderer->render([$form, $ui->dropdown()->standard([
        $ui->button()->shy('lbl', '#'),
    ])]);
}
