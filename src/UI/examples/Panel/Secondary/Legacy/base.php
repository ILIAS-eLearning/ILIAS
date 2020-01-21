<?php

function base()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $actions = $factory->dropdown()->standard(array(
        $factory->button()->shy("ILIAS", "https://www.ilias.de"),
        $factory->button()->shy("GitHub", "https://www.github.com")
    ));

    $legacy = $factory->legacy("Legacy content");

    $panel = $factory->panel()->secondary()->legacy(
        "Legacy panel title",
        $legacy
    )->withActions($actions);

    return $renderer->render($panel);
}
