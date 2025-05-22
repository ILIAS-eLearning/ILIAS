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

namespace ILIAS\UI\examples\Dropdown\Standard;

/**
 * ---
 * description: >
 *   Example for rendering a dropdown with buttons and links
 *
 * expected output: >
 *   ILIAS shows a base dropdown button. Clicking the button will open a
 *   dropdown menu with the entries "ILIAS" rendered as a link and "GitHub" rendered as a shy button. Clicking the
 *   shy button will open the appropriate website in the same browser window while clicking the link will open the
 *   appropriate website in a new browser tab.
 * ---
 */
function with_buttons_and_links()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $items = array(
        $f->button()->shy("Github", "https://www.github.com"),
        $f->link()->standard("ILIAS", "https://www.ilias.de")->withOpenInNewViewport(true)
    );
    return $renderer->render($f->dropdown()->standard($items)->withLabel("Actions"));
}
