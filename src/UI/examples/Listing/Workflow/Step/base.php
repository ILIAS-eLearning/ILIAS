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

namespace ILIAS\UI\examples\Listing\Workflow\Step;

/**
 * ---
 * description: >
 *   Example for rendering a workflow list with steps.
 *
 * expected output: >
 *   ILIAS shows all possible status in the workflow:
 *
 *   - available, successfully completed (with Check-Glyph)
 *   - available, unsuccessfully completed (with X-Glyph)
 *   - available, not started (empty circle)
 *   - available, in progress (filled circle)
 *   - available, in progress, active (by workflow) (filled circle, big)
 *   - not available, not started (Key-Glyph, greyed out)
 *   - not available, in progress (Key-Glyph, highlighted blue, medium grey)
 *   - not available, successfully completed (Check-Glyph, greyed out)
 *   - not available, unsuccessfully completed (X-Glyph, greyed out)
 *   - not available anymore, not started (Clock-Glyph, greyed out)
 *   - not available anymore, in progress (Clock-Glyph highlighted blue, medium grey)
 *   - not available anymore, successfully completed (Check-Glyph, greyed out)
 *   - not available anymore, unsuccessfully completed (X-Glyph, greyed out)
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
