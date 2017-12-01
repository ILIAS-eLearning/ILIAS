<?php
/**
 * Base Example for rendering a responsive Speedo with minimum configuration
 */
function base() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the responsive speedo
    $speedo = $f->chart()->speedo()->responsive(array(
        'goal' => 400,
        'score' => 250,
    ));

    // render
    return $renderer->render($speedo);
}
