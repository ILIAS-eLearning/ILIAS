<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Container\Form\Standard;

/**
 * Example showing a Form with an optional dedicated name which is used as NAME attribute on the rendered form.
 */
function with_dedicated_name()
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $text_input = $ui->input()->field()
        ->text("Just Another Input", "I'm just another input");

    $form = $ui->input()->container()->form()->standard("", [$text_input]);
    $form = $form->withDedicatedName('userform');
    return $renderer->render($form);
}
