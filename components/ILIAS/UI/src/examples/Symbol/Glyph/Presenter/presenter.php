<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Symbol\Glyph\Presenter;

/**
 * ---
 * description: >
 *   Example for rendering a Presenter Glyph.
 *
 * expected output: >
 *   Standard:
 *   ILIAS shows a monochrome symbol on a grey background.
 *
 *   Highlighted:
 *   ILIAS shows the same symbol, but it's highlighted particularly.
 * ---
 */
function presenter()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->presenter();

    //Showcase the various states of this Glyph
    $list = $f->listing()->descriptive([
        "Active" => $glyph,
        "Highlighted" => $glyph->withHighlight()
    ]);

    return $renderer->render($list);
}
