<?php
/**
 * Base Example for rendering a standard Speedo with minimum configuration
 */
function base() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the standard speedo
    $speedo = $f->chart()->speedo()->standard(array(
        'goal' => 400,
        'score' => 250,
    ));

    // render
    return $renderer->render($speedo);
}
