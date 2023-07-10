<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\MainControls\Slate\Legacy;

function legacy()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $icon = $f->symbol()->glyph()->comment();
    $contents = $f->legacy("some <i>html</i>.");

    $slate = $f->maincontrols()->slate()->legacy('legacy_example', $icon, $contents);

    $triggerer = $f->button()->bulky(
        $slate->getSymbol(),
        $slate->getName(),
        '#'
    )
    ->withOnClick($slate->getToggleSignal());

    return $renderer->render([
        $triggerer,
        $slate
    ]);
}
