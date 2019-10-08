<?php

function with_actions()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $actions = $f->dropdown()->standard(array(
        $f->button()->shy("ILIAS", "https://www.ilias.de"),
        $f->button()->shy("GitHub", "https://www.github.com")
    ));

    $panel = $f->panel()->standard(
        "Panel Title",
        $f->legacy("Some Content")
    )->withActions($actions);

    return $renderer->render($panel);
}
