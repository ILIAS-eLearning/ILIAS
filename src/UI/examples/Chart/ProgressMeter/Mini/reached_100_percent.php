<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Chart\ProgressMeter\Mini;

/**
 * ---
 * description: >
 *   Example for rendering a mini Progress Meter when 100% are reached
 *
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function reached_100_percent()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generating and rendering the mini progressmeter
    $progressmeter = $f->chart()->progressMeter()->mini(100, 100);

    // render
    return $renderer->render($progressmeter);
}
