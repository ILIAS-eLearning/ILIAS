<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Symbol\Glyph\Date;

/**
 * ---
 * description: >
 *   Example for rendering a Date Glyph.
 *
 * expected output: >
 *   Active:
 *   ILIAS shows a monochrome symbol on a grey background. If you move your cursor onto the symbol it's
 *   color darkens a little bit. Additionaly the cursor symbol changes it's form and indicates a linking.
 *
 *   Inactive:
 *   ILIAS shows the same symbol. But it's greyed out. Moving the cursor above the symbol will not change the presentation.
 *
 *   Highlighted:
 *   ILIAS shows the same symbol. But it's highlighted particularly. The presentation will darken if you move your cursor
 *   above the symbol. Additionally the cursor symbol will change it's form and indicates a linking.
 * ---
 */
function date()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->date("#");

    //Showcase the various states of this Glyph
    $list = $f->listing()->descriptive([
        "Active" => $glyph,
        "Inactive" => $glyph->withUnavailableAction(),
        "Highlighted" => $glyph->withHighlight()
    ]);

    return $renderer->render($list);
}
