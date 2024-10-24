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

namespace ILIAS\UI\examples\Counter\Status;

/**
 * ---
 * description: >
 *   Base example for rendering a status counter with novelty
 *
 * expected output: >
 *   ILIAS shows a glyph with two counters. The counter's numbers are white and highlighted red resp. grey.
 * ---
 */
function with_novelty()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render(
        $f->symbol()->glyph()->mail("#")
        ->withCounter($f->counter()->novelty(1))
        ->withCounter($f->counter()->status(8))
    );
}
