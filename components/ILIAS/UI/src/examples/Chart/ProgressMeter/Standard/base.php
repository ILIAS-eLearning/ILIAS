<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Chart\ProgressMeter\Standard;

/**
 * Example for rendering a standard Progress Meter with minimum configuration
 */
function base()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generating and rendering the standard progressmeter
    $progressmeter = $f->chart()->progressMeter()->standard(100, 75);

    // render
    return $renderer->render($progressmeter);
}
