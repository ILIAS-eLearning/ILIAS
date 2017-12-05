<?php
/**
 * Example for rendering a mini Gauge with minimum configuration
 */
function base() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the mini gauge
    $gauge = $f->chart()->gauge()->mini(100, 75);

    // render
    return $renderer->render($gauge);
}
