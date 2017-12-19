<?php
/**
 * Example for rendering a fixed size ProgressMeter with minimum configuration
 */
function base() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the responsive progressmeter
    $progressmeter = $f->chart()->progressmeter()->fixedSize(100, 75);

    // render
    return $renderer->render($progressmeter);
}
