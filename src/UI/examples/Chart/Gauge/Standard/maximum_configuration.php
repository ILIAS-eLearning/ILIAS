<?php
/**
 * Example for rendering a standard Gauge with maximum configuration
 */
function maximum_configuration() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the standard gauge
    $gauge = $f->chart()->gauge()->standard(100, 75, 80, 50);

    // add score text
    $gauge = $gauge->withMainText('Your Score');

    // add required text
    $gauge = $gauge->withRequiredText('Required Score');

    // render
    return $renderer->render($gauge);
}
