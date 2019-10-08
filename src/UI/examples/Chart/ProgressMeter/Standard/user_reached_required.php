<?php
/**
 * Example for rendering a standard Progress Meter when the required value was reached
 */
function user_reached_required()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the standard progressmeter
    $progressmeter = $f->chart()->progressMeter()->standard(100, 80, 75);

    // render
    return $renderer->render($progressmeter);
}
