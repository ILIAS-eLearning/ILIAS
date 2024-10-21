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

namespace ILIAS\UI\examples\Button\Bulky;

/**
 * ---
 * description: >
 *   Example for rendering a bulky button.
 *
 * note: >
 *   The exact look of the Bulky Buttons is mostly defined by the surrounding container.
 *
 * expected output: >
 *   ILIAS shows a button with an icon and titled "Icon". The button's size is almost as wide as the width of the box
 *   in the background. Clicking the button won't activate any actions.
 *   Additionally ILIAS shows a button with a glyph and the title "Glyph". The button's size is also almost as wide as
 *   the widht of the box in the background. Clicking the button won't activate any actions.
 * ---
 */
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $ico = $f->symbol()->icon()
        ->standard('someExample', 'Example')
        ->withAbbreviation('E')
        ->withSize('medium');
    $button = $f->button()->bulky($ico, 'Icon', '#');

    $glyph = $f->symbol()->glyph()->briefcase();
    $button2 = $f->button()->bulky($glyph, 'Glyph', '#');

    return $renderer->render([
        $button,
        $f->divider()->horizontal(),
        $button2
    ]);
}
