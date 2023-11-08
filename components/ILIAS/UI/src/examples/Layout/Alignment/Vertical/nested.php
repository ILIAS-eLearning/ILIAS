<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Layout\Alignment\Vertical;

function nested()
{
    global $DIC;
    $ui_factory = $DIC['ui.factory'];
    $renderer = $DIC['ui.renderer'];
    $tpl = $DIC['tpl'];
    $tpl->addCss('components/ILIAS/UI/src/examples/Layout/Alignment/alignment_examples.css');

    $icon = $ui_factory->image()->standard("templates/default/images/logo/HeaderIconResponsive.svg", "ilias");
    $blocks = [
        $ui_factory->legacy('<div class="example_block fullheight blue">Example Block</div>'),
        $icon,
        $ui_factory->legacy('<div class="example_block fullheight green">Another Example Block</div>'),
        $icon,
        $ui_factory->legacy('<div class="example_block fullheight yellow">And a third block is also part of this group</div>')
    ];

    $dynamic = $ui_factory->layout()->alignment()->horizontal()->dynamicallyDistributed(...$blocks);
    $evenly = $ui_factory->layout()->alignment()->horizontal()->evenlyDistributed(
        $icon,
        $icon,
        $dynamic
    );


    $vertical = $ui_factory->layout()->alignment()->vertical(
        $ui_factory->legacy('<div class="example_block fullheight red">The block above.</div>'),
        $evenly,
        $ui_factory->legacy('<div class="example_block fullheight red">The block below.</div>')
    );


    return $renderer->render($vertical);
}
