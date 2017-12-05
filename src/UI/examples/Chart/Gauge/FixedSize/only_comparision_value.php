<?php
/**
 * Example for rendering a fixed size Gauge with an diagnostic score only
 */
function only_comparision_value() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the responsive gauge
    $gauge = $f->chart()->gauge()->fixedSize(100, 0, 75, 50);

    // render
    return $renderer->render($gauge);
}
