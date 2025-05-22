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
 *   ILIAS shows several sections.
 *   The first and last row spread over the entire width.
 *   The second row consists of logos and text-blocks.
 *   When space is available, all elements are shown horizontally next to each other.
 *   Upon decreasing the available width (shrink the browser window),
 *   the text-blocks and logos on the right will start breaking lines first,
 *   while the logos on the left will remain horizontally next to each other and
 *   the now breaking sections.
 *   Finally, when the space gets smaller, _all_ sections and logos will be
 *   displayed vertically, one element per row.
 * ---
 */
function nested()
{
    global $DIC;
    $ui_factory = $DIC['ui.factory'];
    $renderer = $DIC['ui.renderer'];
    $tpl = $DIC['tpl'];
    $tpl->addCss('assets/ui-examples/css/alignment_examples.css');

    $icon = $ui_factory->image()->standard("assets/images/logo/HeaderIconResponsive.svg", "ilias");
    $blocks = [
        $ui_factory->legacy()->content('<div class="example_block fullheight blue">Example Block</div>'),
        $icon,
        $ui_factory->legacy()->content('<div class="example_block fullheight green">Another Example Block</div>'),
        $icon,
        $ui_factory->legacy()->content('<div class="example_block fullheight yellow">And a third block is also part of this group</div>')
    ];

    $dynamic = $ui_factory->layout()->alignment()->horizontal()->dynamicallyDistributed(...$blocks);
    $evenly = $ui_factory->layout()->alignment()->horizontal()->evenlyDistributed(
        $icon,
        $icon,
        $dynamic
    );


    $vertical = $ui_factory->layout()->alignment()->vertical(
        $ui_factory->legacy()->content('<div class="example_block fullheight red">The block above.</div>'),
        $evenly,
        $ui_factory->legacy()->content('<div class="example_block fullheight red">The block below.</div>')
    );


    return $renderer->render($vertical);
}
