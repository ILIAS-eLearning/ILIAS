<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Symbol\Glyph\Launch;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function launch()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->launch("#");

    //Showcase the various states of this Glyph
    $list = $f->listing()->descriptive([
        "Active" => $glyph,
        "Inactive" => $glyph->withUnavailableAction(),
        "Highlighted" => $glyph->withHighlight()
    ]);

    return $renderer->render($list);
}
