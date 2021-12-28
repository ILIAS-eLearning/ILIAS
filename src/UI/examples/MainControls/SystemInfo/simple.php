<?php

use ILIAS\Data\URI;

function simple()
{
    //
    // This example show how the UI-Elements itself looks like. For a full
    // example use the example of the UI-Component Layout\Page\Standard.
    //

    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $systemInfo = $f->mainControls()
        ->systemInfo('This is an neutral Message!', 'read it, understand it, dismiss it...')
        ->withDismissAction(new URI($_SERVER['HTTP_REFERER']));

    return $renderer->render([$systemInfo]);
}
