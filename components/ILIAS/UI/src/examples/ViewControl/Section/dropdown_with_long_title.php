<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\ViewControl\Section;

function dropdown_with_long_title()
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
            $f->link()->standard("Second section with a very long title to check the responsive behaviour", "#"),
            $f->link()->standard("Third Section", "#")
        ]
    )->withLabel("Second section with a very long title to check the responsive behaviour");
    $view_control_section = $f->viewControl()->section($back, $middle, $next);
    $html = $renderer->render($view_control_section);
    return $html;
}
