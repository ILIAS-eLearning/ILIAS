<?php
function engaged()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $button = $f->button()->standard("Engaged Button", "#")
                                  ->withEngagedState(true);
    return $renderer->render($button);
}
