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
 *   Example for rendering a dropdown with aria labels
 *
 * expected output: >
 *   ILIAS shows base dropdown button without a title. Clicking the button will open a
 *   dropdown menu with two entries rendered as shy buttons. Clicking the entries will open the
 *   appropriate website in the same browser window.
 * ---
 */
function with_aria_label()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $items = array(
        $f->button()->shy("GitHub", "https://www.github.com"),
        $f->button()->shy("Bugs", "https://mantis.ilias.de"),
    );
    return $renderer->render($f->dropdown()->standard($items)->withAriaLabel("MyLabel"));
}
