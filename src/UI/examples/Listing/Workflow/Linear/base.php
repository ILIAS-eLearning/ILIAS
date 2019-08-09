<?php

function base()
{
    //init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory()->listing()->workflow();
    $renderer = $DIC->ui()->renderer();

    //setup steps
    $step = $f->step('', '');
    $steps = [
        $f->step('step 1', 'available, successfully completed')
            ->withAvailability($step::AVAILABLE)->withStatus($step::SUCCESSFULLY),
        $f->step('step 2', 'available, unsuccessfully completed')
            ->withAvailability($step::AVAILABLE)->withStatus($step::UNSUCCESSFULLY),
        $f->step('step 3', 'available, not started')
            ->withAvailability($step::AVAILABLE)->withStatus($step::NOT_STARTED),
        $f->step('step 4', 'available, in progress')
            ->withAvailability($step::AVAILABLE)->withStatus($step::IN_PROGRESS),
        $f->step('active step', 'available, in progress, active (by workflow)')
            ->withAvailability($step::AVAILABLE)->withStatus($step::IN_PROGRESS),
        $f->step('step 6', 'not available, not started')
            ->withAvailability($step::NOT_AVAILABLE)->withStatus($step::NOT_STARTED),
        $f->step('step 7', 'not available, in progress')
            ->withAvailability($step::NOT_AVAILABLE)->withStatus($step::IN_PROGRESS),
        $f->step('step 8', 'not available, successfully completed')
            ->withAvailability($step::NOT_AVAILABLE)->withStatus($step::SUCCESSFULLY),
        $f->step('step 9', 'not available, unsuccessfully completed')
            ->withAvailability($step::NOT_AVAILABLE)->withStatus($step::UNSUCCESSFULLY),
        $f->step('step 10', 'not available anymore, not started')
            ->withAvailability($step::NOT_ANYMORE)->withStatus($step::NOT_STARTED),
        $f->step('step 11', 'not available anymore, in progress')
            ->withAvailability($step::NOT_ANYMORE)->withStatus($step::IN_PROGRESS),
        $f->step('step 12', 'not available anymore, successfully completed')
            ->withAvailability($step::NOT_ANYMORE)->withStatus($step::SUCCESSFULLY),
        $f->step('step 13', 'not available anymore, unsuccessfully completed')
            ->withAvailability($step::NOT_ANYMORE)->withStatus($step::UNSUCCESSFULLY),
    ];

    //setup linear workflow
    $wf = $f->linear('Linear Workflow', $steps)
        ->withActive(4);

    //render
    return $renderer->render($wf);
}
