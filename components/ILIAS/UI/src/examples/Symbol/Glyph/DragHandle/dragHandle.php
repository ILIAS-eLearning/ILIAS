<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Symbol\Glyph\DragHandle;

/**
 * ---
 * description: >
 *   Example for rendering a Drag Handle Glyph.
 *
 * expected output: >
 *   Active:
 *   ILIAS shows a monochrome symbol on a grey background. If you move your cursor onto the symbol it's
 *   color darkens a little bit. Additionally, the cursor symbol changes its form and indicates that an element is draggable.
 *
 *   Inactive:
 *   ILIAS shows the same symbol. But it's greyed out. Moving the cursor above the symbol will not change the presentation.
 *
 *   Highlighted:
 *   ILIAS shows the same symbol. But it's highlighted particularly. The presentation will darken if you move your cursor
 *   above the symbol. Additionally, the cursor symbol will change its form and indicates that an element is draggable.
 * ---
 */
function dragHandle()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->dragHandle("#");

    //Showcase the various states of this Glyph
    $list = $f->listing()->descriptive([
        "Active" => $glyph,
        "Inactive" => $glyph->withUnavailableAction(),
        "Highlighted" => $glyph->withHighlight()
    ]);

    return $renderer->render($list);
}
