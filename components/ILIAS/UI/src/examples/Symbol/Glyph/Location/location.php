<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Symbol\Glyph\Location;

/**
 * ---
 * description: >
 *   Example for rendering a Owner Glyph.
 *
 * expected output: >
 *   Standard:
 *   ILIAS shows a monochrome symbol on a grey background.
 *
 *   Highlighted:
 *   ILIAS shows the same symbol, but it's highlighted particularly.
 * ---
 */
function location()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->location();

    //Showcase the various states of this Glyph
    $list = $f->listing()->descriptive([
        "Active" => $glyph,
        "Highlighted" => $glyph->withHighlight()
    ]);

    return $renderer->render($list);
}
