<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Link\Bulky;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component, but it is not operable.
 * ---
 */
function with_disabled()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $target = new \ILIAS\Data\URI("https://ilias.de");
    $glyph = $f->symbol()->glyph()->comment();

    $link = $f->link()->bulky($glyph, 'Link to ilias.de with Glyph', $target)
        ->withDisabled(true);

    return $renderer->render([
        $link
    ]);
}
