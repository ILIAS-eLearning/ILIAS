<?php
/**
 * Base example performing a page reload if active view is changed.
 */
function base()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Some Target magic to get a behaviour closer to some real use case
    $target = $DIC->http()->request()->getRequestTarget();
    $param = "Mode";

    $active = 1;
    if ($_GET[$param]) {
        $active = $_GET[$param];
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
