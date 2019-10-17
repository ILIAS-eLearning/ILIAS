<?php
function apply_disabled()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render($f->glyph()->apply("#")->withUnavailableAction());
}
