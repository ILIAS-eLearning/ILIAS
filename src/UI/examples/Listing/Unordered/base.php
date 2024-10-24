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

namespace ILIAS\UI\examples\Listing\Unordered;

/**
 * ---
 * description: >
 *   Example for rendering an unordered list.
 *
 * expected output: >
 *   ILIAS shows a list in following format:
 *
 *   - Point 1
 *   - Point 2
 *   - Point 3
 * ---
 */
function base()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generate List
    $unordered = $f->listing()->unordered(
        ["Point 1","Point 2","Point 3"]
    );

    //Render
    return $renderer->render($unordered);
}
