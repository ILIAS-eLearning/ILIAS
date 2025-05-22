<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\UI\examples\Button\Standard;

/**
 * ---
 * description: >
 *   This example provides buttons with a Glyph in (and as) the label.
 *
 * expected output: >
 *   ILIAS shows a button with the Search Gylph and in some cases a label in different states. Clicking the button
 *   won't activate any actions.
 * ---
 */
function with_glyph()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->search();
    $button = $f->button()->standard('search', '#')
        ->withSymbol($glyph);
    $button2 = $button->withLabel('')
        ->withAriaLabel('search');

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
