<?php
/**
 * Max Example for rendering a standard Speedo when the goal was reached
 */
function user_reached_goal() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the standard speedo
    $speedo = $f->chart()->speedo()->standard(array(
        'goal' => 400,
        'score' => 300,
        'minimum' => 250,
    ));

    // render
    return $renderer->render($speedo);
}
