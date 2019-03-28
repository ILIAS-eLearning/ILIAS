<?php
function play_highlighted() {
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render($f->glyph()->play("#")
        ->withHighlight()
    );
}
