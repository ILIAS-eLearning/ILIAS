<?php
/**
 * Example for rendering a standard Progress Meter with maximum configuration
 */
function maximum_configuration()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the standard progressmeter
    $progressmeter = $f->chart()->progressMeter()->standard(100, 75, 80, 50);

    // add score text
    $progressmeter = $progressmeter->withMainText('Your Score');

    // add required text
    $progressmeter = $progressmeter->withRequiredText('Required Score');

    // render
    return $renderer->render($progressmeter);
}
