<?php
/**
 * Example for rendering a responsive Speedo with maximum configuration
 */
function maximum_configuration() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the responsive speedo
    $speedo = $f->chart()->speedo()->responsive(100, 75, 80, 50);

    // add score text
    $speedo = $speedo->withTxtScore('Your Score');

    // add goal text
    $speedo = $speedo->withTxtGoal('Minimum Goal');

    // render
    return $renderer->render($speedo);
}
