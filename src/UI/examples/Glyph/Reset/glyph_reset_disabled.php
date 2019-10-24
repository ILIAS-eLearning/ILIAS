<?php
function glyph_reset_disabled()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render($f->glyph()->reset("#")->withUnavailableAction());
}
