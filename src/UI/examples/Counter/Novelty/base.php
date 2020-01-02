<?php
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render($f->glyph()->mail("#")
        ->withCounter($f->counter()->novelty(3)));
}
