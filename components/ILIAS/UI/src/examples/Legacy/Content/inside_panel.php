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

namespace ILIAS\UI\examples\Legacy\Content;

/**
 * ---
 * description: >
 *   Example for rendering a legacy box.
 *
 * expected output: >
 *   ILIAS shows a box titled "Panel Title" and a grey background. In the lower part of the box the text "Legacy Content"
 *   on a white background is written.
 * ---
 */
function inside_panel()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Init Component
    $legacy = $f->legacy()->content("Legacy Content");
    $panel = $f->panel()->standard("Panel Title", $legacy);

    //Render
    return $renderer->render($panel);
}
