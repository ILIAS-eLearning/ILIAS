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
 * description: >
 *   Example performing a page reload if switching between sections of some data.
 *
 * expected output: >
 *   ILIAS shows three controls next to each other: A "Back" glyph, a label "Engaged Section" and a "Next" glyph.
 *   Clicking "Engaged Section" will reload the website. Clicking the other two controls will result in the button in the
 *   middle being disenganged and showing the label "Go to Engaged Section (Current Section: 1)". The counter increases with
 *   each click onto the "Next" glyph and decreases with each click onto the "Back" glyph. Clicking the button in the middle
 *   with the "Goto..." label will revert the buttons to the original status.
 * ---
 */
function base()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    //Some Target magic to get a behaviour closer to some real use case
    $target = $DIC->http()->request()->getRequestTarget();
    $param = "Section";
    $active = 0;
    if ($request_wrapper->has($param) && $request_wrapper->retrieve($param, $refinery->kindlyTo()->int())) {
        $active = $request_wrapper->retrieve($param, $refinery->kindlyTo()->int());
    }

    //Here the real magic to draw the controls
    $back = $f->button()->standard("Back", "$target&$param=" . ($active - 1));
    $next = $f->button()->standard("Next" . " " . ($active - 1), "$target&$param=" . ($active + 1));
    $middle = $f->button()->standard("Go to Engaged Section (Current Section: $active)", "$target&$param=0");
    //Note that the if the middle button needs to be engaged by the surrounding component, as so, if need to be drawn
    //as engaged. This can e.g. be the case for rendering a button labeled "Today" or similar.
    if ($active == 0) {
        $middle = $middle->withLabel("Engaged Section")->withEngagedState(true);
    }
    $view_control_section = $f->viewControl()->section($back, $middle, $next);
    $html = $renderer->render($view_control_section);
    return $html;
}
