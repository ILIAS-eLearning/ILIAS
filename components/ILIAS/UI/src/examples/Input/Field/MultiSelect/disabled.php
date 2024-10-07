<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\MultiSelect;

/**
 * ---
 * description: >
 *   This example shows an disabled Multi Select Input.
 * ---
 */
function disabled()
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //define options.
    $options = array(
        "1" => "Pick 1",
        "2" => "Pick 2",
        "3" => "Pick 3",
        "4" => "Pick 4",
    );

    $multi = $ui->input()->field()->multiselect("Take your picks", $options, "This is the byline text")
        ->withValue(['2','4'])
        ->withDisabled(true);

    $form = $ui->input()->container()->form()->standard('#', ['multi' => $multi]);
    return $renderer->render($form);
}
