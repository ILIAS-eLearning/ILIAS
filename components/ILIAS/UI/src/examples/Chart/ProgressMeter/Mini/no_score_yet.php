<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Chart\ProgressMeter\Mini;

/**
 * Example for rendering a mini Progress Meter when no score is given
 */
function no_score_yet()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generating and rendering the mini progressmeter
    $progressmeter = $f->chart()->progressMeter()->mini(100, 0);

    // render
    return $renderer->render($progressmeter);
}
