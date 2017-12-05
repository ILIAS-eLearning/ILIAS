<?php
/**
 * Example for rendering a fixed size Gauge when a specific score was reached
 */
function user_reached_required() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the responsive gauge
    $gauge = $f->chart()->gauge()->fixedSize(100, 80, 75);

    // render
    return $renderer->render($gauge);
}
