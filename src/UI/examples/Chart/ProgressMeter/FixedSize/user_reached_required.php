<?php
/**
 * Example for rendering a fixed size Progress Meter when a specific score was reached
 */
function user_reached_required()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the responsive progressmeter
    $progressmeter = $f->chart()->progressMeter()->fixedSize(100, 80, 75);

    // render
    return $renderer->render($progressmeter);
}
