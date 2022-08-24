<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Button\Bulky;

//Note the exact look of the Bulky Buttons is mostly defined by the
//surrounding container.
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $ico = $f->symbol()->icon()
        ->standard('someExample', 'Example')
        ->withAbbreviation('E')
        ->withSize('medium');
    $button = $f->button()->bulky($ico, 'Icon', '#');

    $glyph = $f->symbol()->glyph()->briefcase();
    $button2 = $f->button()->bulky($glyph, 'Glyph', '#');

    return $renderer->render([
        $button,
        $f->divider()->horizontal(),
        $button2
    ]);
}
