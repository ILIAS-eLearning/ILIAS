<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Symbol\Glyph\Unchecked;

/**
 * ---
 * description: >
 *   Example for rendering a Unchecked Glyph.
 *
 * expected output: >
 *   Standard:
 *   ILIAS shows a monochrome heart symbol on a grey background.
 *
 *   Highlighted:
 *   ILIAS shows the same symbol, but it's highlighted particularly.
 * ---
 */
function unchecked()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->unchecked();

    //Showcase the various states of this Glyph
    $list = $f->listing()->descriptive([
        "Standard" => $glyph,
        "Highlighted" => $glyph->withHighlight(),
    ]);

    return $renderer->render($list);
}
