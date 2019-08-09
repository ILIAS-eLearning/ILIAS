<?php
function notification_highlighted()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render(
        $f->symbol()->glyph()->notification("#")
            ->withHighlight()
    );
}
