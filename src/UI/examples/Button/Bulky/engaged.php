<?php
function engaged()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->briefcase();
    $button = $f->button()->bulky($glyph, 'Engaged Button', '#')
                          ->withEngagedState(true);

    return $renderer->render($button);
}
