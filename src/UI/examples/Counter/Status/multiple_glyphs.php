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
 *   Example for rendering multiple glyphs with status counters
 *
 * expected output: >
 *   ILIAS shows three rendered glyphs with two counters each in a row.
 *   The counters consist of a white number each and are highlighted red resp. grey
 * ---
 */
function multiple_glyphs()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $note = $f->symbol()->glyph()->note("#")
        ->withCounter($f->counter()->novelty(100))
        ->withCounter($f->counter()->status(8));

    $tag = $f->symbol()->glyph()->tag("#")
        ->withCounter($f->counter()->novelty(1))
        ->withCounter($f->counter()->status(800));

    $comment = $f->symbol()->glyph()->comment("#")
        ->withCounter($f->counter()->novelty(1))
        ->withCounter($f->counter()->status(8));

    return $renderer->render($note) . $renderer->render($tag) . $renderer->render($comment);
}
