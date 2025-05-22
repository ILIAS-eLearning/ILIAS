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

namespace ILIAS\UI\examples\Panel\Standard;

/**
 * ---
 * description: >
 *   Example for rendering a standard panel with listings.
 *
 * expected output: >
 *   ILIAS shows a base panel with two lists (numbered and unordered).
 * ---
 */
function with_listings()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $content = array(
        $f->listing()->ordered(array("item 1","item 2","item 3")),
        $f->listing()->unordered(array("item 1","item 2","item 3"))
    );

    $panel = $f->panel()->standard("Panel Title", $content);

    return $renderer->render($panel);
}
