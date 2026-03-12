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

namespace ILIAS\UI\examples\Symbol\Glyph\Laugh;

/**
 * ---
 * description: >
 *   Example for rendering a laugh glyph.
 *
 * expected output: >
 *   Standard:
 *   ILIAS shows a monochrome laughing-smiley symbol on a grey background.
 *
 *   Highlighted:
 *   ILIAS shows the same symbol, but it's highlighted particularly.
 * ---
 */
function laugh()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->laugh();

    //Showcase the various states of this Glyph
    $list = $f->listing()->descriptive([
        "Standard" => $glyph,
        "Highlighted" => $glyph->withHighlight(),
    ]);

    return $renderer->render($list);
}
