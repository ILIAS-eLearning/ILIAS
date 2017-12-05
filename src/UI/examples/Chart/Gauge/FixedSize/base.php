<?php
/**
 * Example for rendering a fixed size Gauge with minimum configuration
 */
function base() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the responsive gauge
    $gauge = $f->chart()->gauge()->fixedSize(100, 75);

    // render
    return $renderer->render($gauge);
}
