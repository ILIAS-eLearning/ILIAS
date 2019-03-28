<?php
function pause_highlighted() {
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render($f->glyph()->pause("#")
        ->withHighlight()
    );
}
