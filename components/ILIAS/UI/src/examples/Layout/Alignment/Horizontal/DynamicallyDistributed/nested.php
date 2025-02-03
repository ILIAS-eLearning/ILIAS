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

namespace ILIAS\UI\examples\Layout\Alignment\Horizontal\DynamicallyDistributed;

/**
 * ---
 * expected output: >
 *   ILIAS shows colored text-blocks labeld A to F.
 *   The blocks are distributed across the available width and differ in size.
 *   Because A, B and C form a "virtual" block in itself, D, E and F will
 *   consecutively break into a new row first when shrinking the browser's window.
 *   Then, A, B and C will break "internally", resulting in three rows
 *   of A/B, C and D/E/F, respectively in four rows of A, B, C and D/E/F.
 *   Finally (with really! little space), all blocks are shown vertically under each other.
 * ---
 */
function nested()
{
    global $DIC;
    $ui_factory = $DIC['ui.factory'];
    $renderer = $DIC['ui.renderer'];
    $tpl = $DIC['tpl'];
    $tpl->addCss('assets/ui-examples/css/alignment_examples.css');


    $blocks = [
        $ui_factory->legacy()->content('<div class="example_block fullheight blue">D</div>'),
        $ui_factory->legacy()->content('<div class="example_block fullheight green">E</div>'),
        $ui_factory->legacy()->content('<div class="example_block fullheight yellow">F</div>')
    ];

    $aligned = $ui_factory->layout()->alignment()->horizontal()->dynamicallyDistributed(
        $ui_factory->legacy()->content('<div class="example_block bluedark">A</div>'),
        $ui_factory->legacy()->content('<div class="example_block greendark">B</div>'),
        $ui_factory->legacy()->content('<div class="example_block yellowdark">C</div>')
    );

    return $renderer->render(
        $ui_factory->layout()->alignment()->horizontal()
            ->dynamicallyDistributed(
                $aligned,
                ...$blocks
            )
    );
}
