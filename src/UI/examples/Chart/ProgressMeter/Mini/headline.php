<?php
/**
 * Example for rendering a mini Progress Meter as part of a headline
 */
function headline()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the mini progressmeter
    $progressmeter = $f->chart()->progressMeter()->mini(100, 75);

    // render
    return '<h3>Your Progress: <span style="display: inline-block; height: 19px">' .
        $renderer->render($progressmeter) .
        '</span></h3>';
}
