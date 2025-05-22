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

namespace ILIAS\UI\examples\Button\Standard;

/**
 * ---
 * description: >
 *   Example for rendering a standard button with JS binding
 *
 * expected output: >
 *   ILIAS shows an active button with a title. Clicking the button opens a dialog with a click-ID.
 * ---
 */
function with_js_binding()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render(
        $f->button()->standard("Goto ILIAS", "#")
            ->withOnLoadCode(function ($id) {
                return
                    "$(\"#$id\").click(function() { alert(\"Clicked: $id\"); return false;});";
            })
    );
}
