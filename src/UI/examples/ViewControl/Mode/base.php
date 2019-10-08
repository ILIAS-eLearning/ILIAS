<?php
/**
 * Only serving as Example
 */
function base()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //ViewControl element
    $actions = array(
        "Using ILIAS" => "http://www.ilias.de/docu/goto_docu_cat_580.html",
        "ILIAS Development" => "http://www.ilias.de/docu/goto.php?target=cat_582&client_id=docu",
        "ILIAS Community" => "http://www.ilias.de/docu/goto.php?target=cat_1444&client_id=docu"
    );

    $aria_label = "change_the_currently_displayed_mode";
    $view_control = $f->viewControl()->mode($actions, $aria_label)->withActive("ILIAS Development");
    $html = $renderer->render($view_control);

    return $html;
}
