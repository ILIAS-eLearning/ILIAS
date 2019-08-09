<?php

function with_actions()
{
    //init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory()->listing()->workflow();
    $renderer = $DIC->ui()->renderer();

    //setup steps
    $step = $f->step('', '');
    $steps = [
        $f->step('step 1', 'available, successfully completed', '#')
            ->withAvailability($step::AVAILABLE)->withStatus($step::SUCCESSFULLY),
        $f->step('step 2', 'not available anymore, unsuccessfully completed', '#')
            ->withAvailability($step::NOT_ANYMORE)->withStatus($step::SUCCESSFULLY),
        $f->step('active step', 'available, in progress, active (by workflow)', '#')
            ->withAvailability($step::AVAILABLE)->withStatus($step::IN_PROGRESS),
        $f->step('step 4', 'available, not started', '#')
            ->withAvailability($step::AVAILABLE)->withStatus($step::NOT_STARTED),
        $f->step('step 5', 'not available, not started', '#')
            ->withAvailability($step::NOT_AVAILABLE)->withStatus($step::NOT_STARTED),
    ];

    //setup linear workflow
    $wf = $f->linear('Linear Workflow', $steps)
        ->withActive(2);

    //render
    return $renderer->render($wf);
}
