<?php declare(strict_types=1);

namespace ILIAS\UI\examples\MainControls\ModeInfo;

use ILIAS\Data\URI;

function modeinfo()
{
    //
    // This example show how the UI-Elements itself looks like. For a full
    // example use the example of the UI-Component Layout\Page\Standard.
    //

    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render($f->mainControls()->modeInfo('a small step for a man', new URI('http://a_giant_leap_for_mankind_meaning')));
}
