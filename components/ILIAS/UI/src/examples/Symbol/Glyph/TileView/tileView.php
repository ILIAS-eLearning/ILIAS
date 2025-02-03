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

namespace ILIAS\UI\examples\Symbol\Glyph\TileView;

/**
 * ---
 * description: >
 *   Example for rendering a Tile View Glyph.
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
function tileView()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->tileView("#");

    //Showcase the various states of this Glyph
    $list = $f->listing()->descriptive([
        "Active" => $glyph,
        "Inactive" => $glyph->withUnavailableAction(),
        "Highlighted" => $glyph->withHighlight()
    ]);

    return $renderer->render($list);
}
