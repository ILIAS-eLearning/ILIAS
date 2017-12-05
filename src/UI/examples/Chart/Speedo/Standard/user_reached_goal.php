<?php
/**
 * Example for rendering a standard Speedo when a specific score was reached
 */
function user_reached_goal() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the standard speedo
    $speedo = $f->chart()->speedo()->standard(100, 80, 75);

    // render
    return $renderer->render($speedo);
}
