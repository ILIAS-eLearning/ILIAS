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

namespace ILIAS\UI\examples\MainControls\Slate\Combined;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function combined()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $icon = $f->symbol()->glyph()->comment();
    $contents = $f->legacy()->content("some contents.");
    $slate1 = $f->maincontrols()->slate()->legacy('legacy1', $icon, $contents);
    $slate2 = $f->maincontrols()->slate()->legacy('legacy2', $icon, $contents);
    $divider = $f->divider()->horizontal()->withLabel('Horizontal Divider with Text');

    $glyph = $f->symbol()->glyph()->briefcase();
    $button = $f->button()->bulky($glyph, 'Button', '#');

    $slate = $f->maincontrols()->slate()
        ->combined('combined_example', $f->symbol()->glyph()->briefcase())
        ->withAdditionalEntry($slate1)
        ->withAdditionalEntry($button)
        ->withAdditionalEntry($divider)
        ->withAdditionalEntry($slate2);


    $triggerer = $f->button()->bulky(
        $slate->getSymbol(),
        $slate->getName(),
        '#'
    )
    ->withOnClick($slate->getToggleSignal());

    return $renderer->render([
        $triggerer,
        $slate
    ]);
}
