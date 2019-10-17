<?php
function with_novelty()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render(
        $f->glyph()->mail("#")
        ->withCounter($f->counter()->novelty(1))
        ->withCounter($f->counter()->status(8))
    );
}
