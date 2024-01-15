<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Button\Shy;

function with_glyph()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->search();
    $button = $f->button()->shy($glyph, "#");

    return $renderer->render([
        $button,
        $button->withEngagedState(true),
        $button->withUnavailableAction(true),
    ]);
}
