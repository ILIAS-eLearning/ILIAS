<?php declare(strict_types=1);

namespace ILIAS\UI\examples\ViewControl\Section;

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
