<?php
/**
 * Example for rendering a standard Gauge with minimum configuration
 */
function base() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the standard gauge
    $gauge = $f->chart()->gauge()->standard(100, 75);

    // render
    return $renderer->render($gauge);
}
