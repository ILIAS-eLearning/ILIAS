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

namespace ILIAS\UI\examples\Symbol\Glyph\CollapseHorizontal;

/**
 * ---
 * description: >
 *   Example for rendring a collapse horizontal glyph.
 *
 * expected output: >
 *   Active:
 *   ILIAS shows a box with three words listed among each other. Every word has got a "<" arrow functioning as a link but
 *   without any actions. The first arrow is active, the second and third is colored.
 *
 *   Hightlighted:
 *   ILIAS shows the same symbol. But it is higlighted particularly. Moving your cursor over the symbol will darken the
 *   icon's color. Additionally the cursor's form will change and it indicates a linking.
 * ---
 */
function collapsehorizontal()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->collapseHorizontal("#");

    //Showcase the various states of this Glyph
    $list = $f->listing()->descriptive([
        "Active" => $glyph,
        "Highlighted" => $glyph->withHighlight()
    ]);

    return $renderer->render($list);
}
