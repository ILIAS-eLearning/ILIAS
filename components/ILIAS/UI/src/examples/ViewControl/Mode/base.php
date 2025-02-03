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

namespace ILIAS\UI\examples\ViewControl\Mode;

/**
 * ---
 * description: >
 *   Base example performing a page reload if active view is changed.
 *
 * expected output: >
 *   ILIAS shows three controls next to each other. The first control is highlighted especially ("active/engaged").
 *   Clicking the first control won't activate any actions. Clicking the other controls activates/engages the
 *   appropriate control while the other control will be deactived/disengaged.
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
    $param = "Mode";

    $active = 1;
    if ($request_wrapper->has($param) && $request_wrapper->retrieve($param, $refinery->kindlyTo()->int())) {
        $active = $request_wrapper->retrieve($param, $refinery->kindlyTo()->int());
    }

    //Here the real magic to draw the controls
    $actions = array(
        "$param 1" => "$target&$param=1",
        "$param 2" => "$target&$param=2",
        "$param 3" => "$target&$param=3",
    );

    $aria_label = "change_the_currently_displayed_mode";
    $view_control = $f->viewControl()->mode($actions, $aria_label)->withActive("$param $active");
    $html = $renderer->render($view_control);

    return $html;
}
