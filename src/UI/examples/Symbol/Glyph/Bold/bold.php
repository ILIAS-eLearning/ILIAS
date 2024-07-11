<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Symbol\Glyph\Bold;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function bold()
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $factory->symbol()->glyph()->bold("#");

    // showcase the various states of this Glyph
    $list = $factory->listing()->descriptive([
        "Active" => $glyph,
        "Inactive" => $glyph->withUnavailableAction(),
        "Highlighted" => $glyph->withHighlight()
    ]);

    return $renderer->render($list);
}
