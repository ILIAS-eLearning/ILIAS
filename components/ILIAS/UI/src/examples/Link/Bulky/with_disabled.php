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

namespace ILIAS\UI\examples\Link\Bulky;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component, but it is not operable.
 * ---
 */
function with_disabled()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $target = new \ILIAS\Data\URI("https://ilias.de");
    $glyph = $f->symbol()->glyph()->comment();

    $link = $f->link()->bulky($glyph, 'Link to ilias.de with Glyph', $target)
        ->withDisabled(true);

    return $renderer->render([
        $link
    ]);
}
