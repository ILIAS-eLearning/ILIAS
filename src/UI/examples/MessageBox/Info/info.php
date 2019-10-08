<?php
function info()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $buttons = [$f->button()->standard("Action", "#"), $f->button()->standard("Cancel", "#")];

    $links = [
        $f->link()->standard("Open Exercise Assignment", "#"),
        $f->link()->standard("Open other screen", "#")
    ];

    return $renderer->render($f->messageBox()->info("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
        ->withButtons($buttons)
        ->withLinks($links));
}
