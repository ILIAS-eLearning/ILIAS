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

namespace ILIAS\UI\examples\Layout\Alignment\Vertical;

/**
 * ---
 * expected output: >
 *   ILIAS shows three colored sections with text.
 *   The sections cover the whole width of the content area
 *   and are aligned vertically under each other.
 *   When changing the width of the content area, the sections keep their
 *   alignment - one per line.
 * ---
 */
function base()
{
    global $DIC;
    $ui_factory = $DIC['ui.factory'];
    $renderer = $DIC['ui.renderer'];
    $tpl = $DIC['tpl'];
    $tpl->addCss('assets/ui-examples/css/alignment_examples.css');

    $blocks = [
        $ui_factory->legacy()->content('<div class="example_block fullheight blue">Example Block</div>'),
        $ui_factory->legacy()->content('<div class="example_block fullheight green">Another Example Block</div>'),
        $ui_factory->legacy()->content('<div class="example_block fullheight yellow">And a third block is also part of this group</div>')
    ];

    $vertical = $ui_factory->layout()->alignment()->vertical(...$blocks);
    return $renderer->render($vertical);
}
