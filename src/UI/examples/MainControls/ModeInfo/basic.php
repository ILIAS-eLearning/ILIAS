<?php

use ILIAS\Data\URI;

function basic()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $info = $factory->mainControls()->modeInfo(
        "This is the Title",
        $factory->button()->close()->withAction(new URI('https://google.ch'))
    );

    return $renderer->render([$info]);
}
