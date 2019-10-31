<?php
function multiple_glyphs()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $note = $f->symbol()->glyph()->note("#")
        ->withCounter($f->counter()->novelty(100))
        ->withCounter($f->counter()->status(8));

    $tag = $f->symbol()->glyph()->tag("#")
        ->withCounter($f->counter()->novelty(1))
        ->withCounter($f->counter()->status(800));

    $comment = $f->symbol()->glyph()->comment("#")
        ->withCounter($f->counter()->novelty(1))
        ->withCounter($f->counter()->status(8));

    return $renderer->render($note) . $renderer->render($tag) . $renderer->render($comment);
}
