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
        $f->step('available, successfully completed', '(1)')
            ->withAvailability($step::AVAILABLE)->withStatus($step::SUCCESSFULLY),
        $f->step('available, unsuccessfully completed', '(2)')
            ->withAvailability($step::AVAILABLE)->withStatus($step::UNSUCCESSFULLY),
        $f->step('available, not started', '(3)')
            ->withAvailability($step::AVAILABLE)->withStatus($step::NOT_STARTED),
        $f->step('available, in progress', '(4)')
            ->withAvailability($step::AVAILABLE)->withStatus($step::IN_PROGRESS),
        $f->step('available, in progress, active (by workflow)', '(5)')
            ->withAvailability($step::AVAILABLE)->withStatus($step::IN_PROGRESS),
        $f->step('not available, not started', '(6)')
            ->withAvailability($step::NOT_AVAILABLE)->withStatus($step::NOT_STARTED),
        $f->step('not available, in progress', '(7)')
            ->withAvailability($step::NOT_AVAILABLE)->withStatus($step::IN_PROGRESS),
        $f->step('not available, successfully completed', '(8)')
            ->withAvailability($step::NOT_AVAILABLE)->withStatus($step::SUCCESSFULLY),
        $f->step('not available, unsuccessfully completed', '(9)')
            ->withAvailability($step::NOT_AVAILABLE)->withStatus($step::UNSUCCESSFULLY),
        $f->step('not available anymore, not started', '(10)')
            ->withAvailability($step::NOT_ANYMORE)->withStatus($step::NOT_STARTED),
        $f->step('not available anymore, in progress', '(11)')
            ->withAvailability($step::NOT_ANYMORE)->withStatus($step::IN_PROGRESS),
        $f->step('not available anymore, successfully completed', '(12)')
            ->withAvailability($step::NOT_ANYMORE)->withStatus($step::SUCCESSFULLY),
        $f->step('not available anymore, unsuccessfully completed', '(13)')
            ->withAvailability($step::NOT_ANYMORE)->withStatus($step::UNSUCCESSFULLY),
    ];

    //setup linear workflow
    $wf = $f->linear('Linear Workflow', $steps)
        ->withActive(4);

    //render
    return $renderer->render($wf);
}
