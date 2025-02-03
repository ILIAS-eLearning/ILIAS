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

namespace ILIAS\UI\examples\Panel\Secondary\Legacy;

/**
 * ---
 * description: >
 *   Example for rendering a secondary legacy listing panel with a footer.
 *
 * expected output: >
 *   ILIAS shows a panel titled "Panel Title". It includes five tag buttons and a link "Edit Keywords". Clicking the link
 *   will not activate any actions.
 * ---
 */
function with_footer()
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $tags = ["PHP", "ILIAS", "Sofware", "SOLID", "Domain Driven"];

    $html = "";
    foreach ($tags as $tag) {
        $html .= $renderer->render($factory->button()->tag($tag, ""));
    }

    $legacy = $factory->legacy()->content($html);
    $link = $factory->button()->Shy("Edit Keywords", "");

    $panel = $factory->panel()->secondary()->legacy("panel title", $legacy)->withFooter($link);

    return $renderer->render($panel);
}
