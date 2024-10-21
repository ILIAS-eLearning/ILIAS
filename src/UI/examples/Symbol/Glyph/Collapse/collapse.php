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

namespace ILIAS\UI\examples\Symbol\Glyph\Collapse;

/**
 * ---
 * description: >
 *   Example for rendering a collapse glyph.
 *
 * expected output: >
 *   Active:
 *   ILIAS shows a monochrome arrow-pointing-down symbol on a grey background. Moving your cursor over the symbol will
 *   change the symbol's color to a slightly darker color. Additionally the cursor's form will change and the cursor
 *   indicates a linking.
 *
 *   Inactive:
 *   ILIAS shows the same symbol. But it's greyed out which indicates that it is deactivated. Moving the cursor above the
 *   symbol will change nothing.
 *
 *   Hightlighted:
 *   ILIAS shows the same symbol. But it is higlighted particularly. Moving your cursor over the symbol will darken the
 *   icon's color. Additionally the cursor's form will change and it indicates a linking.
 * ---
 */
function collapse()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->collapse("#");

    //Showcase the various states of this Glyph
    $list = $f->listing()->descriptive([
        "Active" => $glyph,
        "Inactive" => $glyph->withUnavailableAction(),
        "Highlighted" => $glyph->withHighlight()
    ]);

    return $renderer->render($list);
}
