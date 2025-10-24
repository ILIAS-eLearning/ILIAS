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

namespace ILIAS\UI\examples\ViewControl\Section;

/**
 * ---
 * expected output: >
 *   ILIAS shows three controls next to each other: A "Back" glyph, a dropdown "Second Section" and a "Next" glyph.
 *   When clicking on "Second Section" a dropdown with three entries "First Section", "Second Section" and "Third Section"
 *   is displayed.
 * ---
 */
function dropdown(): string
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();


    //Here the real magic to draw the controls
    $back = $f->button()->standard("Back", "#");
    $next = $f->button()->standard("Next", "#");
    $middle = $f->dropdown()->standard(
        [
            $f->link()->standard("First Section", "#"),
            $f->link()->standard("Second Section", "#"),
            $f->link()->standard("Third Section", "#")
        ]
    )->withLabel("Second Section");
    $view_control_section = $f->viewControl()->section($back, $middle, $next);
    $html = $renderer->render($view_control_section);
    return $html;
}
