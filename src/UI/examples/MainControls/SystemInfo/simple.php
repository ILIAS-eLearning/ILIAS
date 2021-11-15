<?php declare(strict_types=1);

namespace ILIAS\UI\examples\MainControls\SystemInfo;

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

    $dismiss_action = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "http://localhost";
    $systemInfo = $f->mainControls()
        ->systemInfo('This is an neutral Message!', 'read it, understand it, dismiss it...')
        ->withDismissAction(new URI($dismiss_action));

    return $renderer->render([$systemInfo]);
}
