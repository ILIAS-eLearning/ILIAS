<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Chart\Bar\Horizontal;

use ILIAS\UI\Component\Chart\Bar\Bar;
use ILIAS\UI\Component\Chart\Bar\BarConfig;
use ILIAS\UI\Component\Chart\Bar\GroupConfig;
use ILIAS\UI\Component\Chart\Bar\XAxis;

function stacked()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $df = new \ILIAS\Data\Factory();
    $renderer = $DIC->ui()->renderer();

    //Generating Dimensions
    $c_dimension = $df->dimension()->cardinal();

    //Generating Dataset with points and tooltips
    $dataset = $df->dataset([
        "Dataset 1.1" => $c_dimension,
        "Dataset 1.2" => $c_dimension,
        "Dataset 1.3" => $c_dimension,
    ], [
        "Grouped Dataset" => $df->dimension()->group("Dataset 1.1", "Dataset 1.2", "Dataset 1.3"),
    ]);

    $dataset = $dataset->withPoint(
        "Item 1",
        [
            "Dataset 1.1" => 3,
            "Dataset 1.2" => 2,
            "Dataset 1.3" => 1
        ]
    );
    $dataset = $dataset->withPoint(
        "Item 2",
        [
            "Dataset 1.1" => 2,
            "Dataset 1.2" => 0,
            "Dataset 1.3" => 3
        ]
    );
    $dataset = $dataset->withPoint(
        "Item 3",
        [
            "Dataset 1.1" => 1,
            "Dataset 1.2" => 3,
            "Dataset 1.3" => 4
        ]
    );

    //Generating Bar Configurations
    $b1 = new BarConfig();
    $b1 = $b1->withColor($df->color("#12436D"));
    $b2 = new BarConfig();
    $b2 = $b2->withColor($df->color("#28A197"));
    $b3 = new BarConfig();
    $b3 = $b3->withColor($df->color("#801650"));
    $g = new GroupConfig();
    $g = $g->withStacked(true);

    $bars = [
        "Dataset 1.1" => $b1,
        "Dataset 1.2" => $b2,
        "Dataset 1.3" => $b3
    ];
    $groups = [
        "Grouped Dataset" => $g
    ];

    //Generating and rendering the vertical chart
    $bar = $f->chart()->bar()->horizontal(
        "A horizontal stacked bar chart",
        $dataset,
        $bars,
        $groups
    );

    // render
    return $renderer->render($bar);
}
