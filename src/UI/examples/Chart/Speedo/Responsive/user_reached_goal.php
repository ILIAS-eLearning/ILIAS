<?php
/**
 * Example for rendering a responsive Speedo when a specific score was reached
 */
function user_reached_goal() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the responsive speedo
    $speedo = $f->chart()->speedo()->responsive(100, 80, 75);

    // render
    return $renderer->render($speedo);
}
