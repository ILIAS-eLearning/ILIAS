<?php
/**
 * Example for rendering a mini Progress Meter with minimum configuration
 */
function maximum_configuration()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the mini progressmeter
    $progressmeter = $f->chart()->progressMeter()->mini(100, 50, 75);

    // render
    return $renderer->render($progressmeter);
}
