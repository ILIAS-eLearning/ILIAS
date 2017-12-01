<?php
/**
 * Max Example for rendering a responsive Speedo when the goal was reached
 */
function user_reached_goal() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the responsive speedo
    $speedo = $f->chart()->speedo()->responsive(array(
        'goal' => 400,
        'score' => 300,
        'minimum' => 250,
    ));

    // render
    return $renderer->render($speedo);
}
