<?php
/**
 * Example for rendering a standard Gauge with an comparision value only
 */
function only_comparision_value() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the standard gauge
    $gauge = $f->chart()->gauge()->standard(100, 0, 75, 50);

    // render
    return $renderer->render($gauge);
}
