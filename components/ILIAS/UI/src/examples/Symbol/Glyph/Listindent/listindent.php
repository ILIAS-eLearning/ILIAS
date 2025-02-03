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

namespace ILIAS\UI\examples\Symbol\Glyph\Listindent;

/**
 * ---
 * description: >
 *   Example for rendring a list indent glyph.
 *
 * expected output: >
 *   Active:
 *   ILIAS shows a monochrome indent-list symbol on a grey background. Moving the cursor above the symbol will darken it's
 *   color slightly. Additionally the cursor's form will change and it indicates a linking.
 *
 *   Inactive:
 *   ILIAS shows the same symbol, but it's greyed out. Moving the cursor will not change the presentation.
 *
 *   Highlighted:
 *   ILIAS shows the same symbol but it's highlighted particularly. Moving the cursor above the symbol will darken it's
 *   color slightly. Additionally the cursor's form will change and it indicates a linking.
 * ---
 */
function listindent()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->listindent("#");

    //Showcase the various states of this Glyph
    $list = $f->listing()->descriptive([
        "Active" => $glyph,
        "Inactive" => $glyph->withUnavailableAction(),
        "Highlighted" => $glyph->withHighlight()
    ]);

    return $renderer->render($list);
}
