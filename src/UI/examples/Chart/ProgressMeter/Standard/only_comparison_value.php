<?php
/**
 * Example for rendering a standard Progress Meter with an comparison value only
 */
function only_comparison_value()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the standard progressmeter
    $progressmeter = $f->chart()->progressMeter()->standard(100, 0, 75, 50);

    // render
    return $renderer->render($progressmeter);
}
