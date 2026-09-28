<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Symbol\Glyph\Date;

/**
 * ---
 * description: >
 *   Example for rendering a Date Glyph.
 *
 * expected output: >
 *   Standard:
 *   ILIAS shows a monochrome symbol on a grey background.
 *
 *   Highlighted:
 *   ILIAS shows the same symbol, but it's highlighted particularly.
 * ---
 */
function date()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->date();

    //Showcase the various states of this Glyph
    $list = $f->listing()->descriptive([
        "Active" => $glyph,
        "Highlighted" => $glyph->withHighlight()
    ]);

    return $renderer->render($list);
}
