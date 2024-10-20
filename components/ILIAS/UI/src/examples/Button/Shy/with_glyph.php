<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Button\Shy;

/**
 * ---
 * description: >
 *   This example provides buttons with a Glyph in (and as) the label.
 *
 * expected output: >
 *   ILIAS shows a button with the Search Gylph and a label in different states
 *   as well as a Button with _only_ a Glyph, again in different states.
 * ---
 */
function with_glyph()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->search();
    $button = $f->button()->shy('search', "#")
        ->withSymbol($glyph);
    $button2 = $button->withLabel('');

    return $renderer->render([
        $button,
        $button->withEngagedState(true),
        $button->withUnavailableAction(true),
        $f->divider()->vertical(),
        $button2,
        $button2->withEngagedState(true),
        $button2->withUnavailableAction(true),

    ]);
}
