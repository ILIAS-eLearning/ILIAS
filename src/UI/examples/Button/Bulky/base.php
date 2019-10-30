<?php
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $ico = $f->icon()
        ->standard('someExample', 'Example')
        ->withAbbreviation('E')
        ->withSize('medium');
    $button = $f->button()->bulky($ico, 'Icon', '#');

    $glyph = $f->glyph()->briefcase();
    $button2 = $f->button()->bulky($glyph, 'Glyph', '#');

    return $renderer->render([
        $button,
        $button->withEngagedState(true),
        $f->divider()->horizontal(),
        $button2,
        $button2->withEngagedState(true),
    ]);
}
