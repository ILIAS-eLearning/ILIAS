<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Link\Bulky;

//The Bulky Links in this example point to ilias.de
//Note the exact look of the Bulky Links is mostly defined by the
//surrounding container.
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $target = new \ILIAS\Data\URI("https://ilias.de");

    $ico = $f->symbol()->icon()
             ->standard('someExample', 'Example')
             ->withAbbreviation('E')
             ->withSize('medium');
    $link = $f->link()->bulky($ico, 'Link to ilias.de with Icon', $target);

    $glyph = $f->symbol()->glyph()->briefcase();
    $link2 = $f->link()->bulky($glyph, 'Link to ilias.de with Glyph', $target);

    return $renderer->render([
        $link,
        $f->divider()->horizontal(),
        $link2,
    ]);
}
