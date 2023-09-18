<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Counter\Status;

function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render($f->symbol()->glyph()->mail("#")
        ->withCounter($f->counter()->status(3)));
}
