<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\ViewControl\Mode;

/**
 * Base example performing a page reload if active view is changed.
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
