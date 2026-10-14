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

namespace ILIAS\UI\examples\Symbol\Glyph\Unselect;

/**
 * ---
 * description: >
 *   Example for rendering an unselect glyph.
 *
 * expected output: >
 *   Standard output:
 *   ILIAS shows an empty box.
 *
 *   Highlighted:
 *   ILIAS shows the same symbol, but it's highlighted particularly.
 * ---
 */
function unselect()
{
    global $DIC;

    $f = $DIC->ui()->factory();
    $glyph = $f->symbol()->glyph()->unselect();

    return $DIC->ui()->renderer()->render($f->listing()->descriptive([
        'Standard' => $glyph,
        'Highlighted' => $glyph->withHighlight(),
    ]));
}
