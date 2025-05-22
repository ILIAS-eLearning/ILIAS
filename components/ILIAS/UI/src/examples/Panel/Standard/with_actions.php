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
 *   Example for rendering a standard panel with actions.
 *
 * expected output: >
 *   ILIAS shows a base panel but it also includes a menu displayed by
 *   an triangle symbol pointing down. You can open the menu which includes links to ilias.de and GitHub.
 * ---
 */
function with_actions()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $actions = $f->dropdown()->standard(array(
        $f->button()->shy("ILIAS", "https://www.ilias.de"),
        $f->button()->shy("GitHub", "https://www.github.com")
    ));

    $panel = $f->panel()->standard(
        "Panel Title",
        $f->legacy()->content("Some Content")
    )->withActions($actions);

    return $renderer->render($panel);
}
