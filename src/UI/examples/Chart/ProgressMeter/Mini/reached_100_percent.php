<?php
/**
 * Example for rendering a mini Progress Meter when 100% are reached
 */
function reached_100_percent()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the mini progressmeter
    $progressmeter = $f->chart()->progressMeter()->mini(100, 100);

    // render
    return $renderer->render($progressmeter);
}
