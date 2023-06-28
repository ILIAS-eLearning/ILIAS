<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Layout\Alignment\Horizontal\DynamicallyDistributed;

function nested()
{
    global $DIC;
    $ui_factory = $DIC['ui.factory'];
    $renderer = $DIC['ui.renderer'];
    $tpl = $DIC['tpl'];
    $tpl->addCss('src/UI/examples/Layout/Alignment/alignment_examples.css');


    $blocks = [
        $ui_factory->legacy('<div class="example_block fullheight blue">D</div>'),
        $ui_factory->legacy('<div class="example_block fullheight green">E</div>'),
        $ui_factory->legacy('<div class="example_block fullheight yellow">F</div>')
    ];

    $aligned = $ui_factory->layout()->alignment()->horizontal()->dynamicallyDistributed(
        $ui_factory->legacy('<div class="example_block bluedark">A</div>'),
        $ui_factory->legacy('<div class="example_block greendark">B</div>'),
        $ui_factory->legacy('<div class="example_block yellowdark">C</div>')
    );

    return $renderer->render(
        $ui_factory->layout()->alignment()->horizontal()
            ->dynamicallyDistributed(
                $aligned,
                ...$blocks
            )
    );
}
