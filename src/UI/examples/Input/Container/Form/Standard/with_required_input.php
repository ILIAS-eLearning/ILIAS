<?php

declare(strict_types=1);

/**
 * Example showing a Form with required fields. An explaining hint is displayed below the Form.
 */
function with_required_input()
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $text_input = $ui->input()->field()
        ->text("Required Input", "User needs to fill this field")
        ->withRequired(true);

    $section = $ui->input()->field()->section(
        [$text_input], 
        "Section with required field", 
        "The Form should show an explaining hint at the bottom"
    );

    $form = $ui->input()->container()->form()->standard("", [$section]);
    return $renderer->render($form);
}
