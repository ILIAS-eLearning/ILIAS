<?php
/**
 * Example for rendering a fixed size Progress Meter with an diagnostic score only
 */
function only_comparison_value()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the responsive progressmeter
    $progressmeter = $f->chart()->progressMeter()->fixedSize(100, 0, 75, 50);

    // render
    return $renderer->render($progressmeter);
}
