<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Symbol\Glyph\Link;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function link()
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $factory->symbol()->glyph()->link("#");

    // showcase the various states of this Glyph
    $list = $factory->listing()->descriptive([
        "Active" => $glyph,
        "Inactive" => $glyph->withUnavailableAction(),
        "Highlighted" => $glyph->withHighlight()
    ]);

    return $renderer->render($list);
}
