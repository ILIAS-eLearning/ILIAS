<?php
function success()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $buttons = [$f->button()->standard("Action", "#"), $f->button()->standard("Cancel", "#")];

    return $renderer->render($f->messageBox()->success("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")->withButtons($buttons));
}
