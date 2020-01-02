<?php

function base()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $back = $f->button()->standard("previous", "http://www.ilias.de");
    $next = $f->button()->standard("next", "http://www.github.com");

    //month button or standard button can be used.
    $button = $f->button()->standard("Today", "");
    $view_control_section = $f->viewControl()->section($back, $button, $next);
    $html = $renderer->render($view_control_section);
    return $html;
}
