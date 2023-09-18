<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\MainControls\SystemInfo;

use ILIAS\UI\Component\MainControls\ModeInfo;

function long_text()
{
    //
    // This example show how the UI-Elements itself looks like. For a full
    // example use the example of the UI-Component Layout\Page\Standard.
    //

    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $long_text = $f->mainControls()->systemInfo(
        'This Message has a long body',
        <<<TEXT
Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod
tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At
vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren,
no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet,
consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et
dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo
dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem
ipsum dolor sit amet.
TEXT
    );

    return $renderer->render([$long_text]);
}
