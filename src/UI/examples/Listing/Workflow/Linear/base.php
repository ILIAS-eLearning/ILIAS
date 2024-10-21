<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\UI\examples\Listing\Workflow\Linear;

/**
 * ---
 * description: >
 *   Example for rendering a linear workflow.
 *
 * expected output: >
 *   ILIAS shows a linear workflow in five steps: The first two steps are checked, the third step is "in progress" and the
 *   last two steps are not available.
 * ---
 */
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
        $f->step('step 2', 'available, successfully completed')
            ->withAvailability($step::AVAILABLE)->withStatus($step::SUCCESSFULLY),
        $f->step('step 3', 'available, in progress, active ')
            ->withAvailability($step::AVAILABLE)->withStatus($step::IN_PROGRESS),
        $f->step('step 4', 'not available, not started')
            ->withAvailability($step::NOT_AVAILABLE)->withStatus($step::NOT_STARTED),
        $f->step('step 5', 'not available, not started')
            ->withAvailability($step::NOT_AVAILABLE)->withStatus($step::NOT_STARTED)
    ];

    //setup linear workflow
    $wf = $f->linear('Linear Workflow', $steps)
        ->withActive(2);

    //render
    return $renderer->render($wf);
}
