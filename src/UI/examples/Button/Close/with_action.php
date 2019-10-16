<?php

use ILIAS\Data\URI;

/**
 * This third example shows a scenario in which the Close Button is used with
 * a URI as close action.
 */
function with_action()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $close_button = $factory->button()->close()->withAction(new URI("https://google.ch"));

    return $renderer->render([$close_button]);
}
