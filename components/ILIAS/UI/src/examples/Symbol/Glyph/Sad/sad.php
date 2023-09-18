<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Symbol\Glyph\Sad;

function sad()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->sad("#");

    //Showcase the various states of this Glyph
    $list = $f->listing()->descriptive([
            "Active" => $glyph,
            "Inactive" => $glyph->withUnavailableAction(),
            "Highlighted" => $glyph->withHighlight()
            ]);

    return $renderer->render($list);
}
