<?php
/**
 * Max Example for rendering a responsive Speedo with maximum configuration
 */
function maximum_configuration() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the responsive speedo
    $speedo = $f->chart()->speedo()->responsive(array(
        'goal' => 400,
        'score' => 250,
        'minimum' => 300,
        'diagnostic' => 200,
    ));

    // add score text
    $speedo = $speedo->withTxtScore('Your Score');

    // add goal text
    $speedo = $speedo->withTxtGoal('Minimum Goal');

    // render
    return $renderer->render($speedo);
}
