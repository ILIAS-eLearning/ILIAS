<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Chart\Bar\Horizontal;

use ILIAS\UI\Component\Chart\Bar\Bar;
use ILIAS\UI\Component\Chart\Bar\BarConfig;
use ILIAS\UI\Component\Chart\Bar\XAxis;

function custom()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $df = new \ILIAS\Data\Factory();
    $renderer = $DIC->ui()->renderer();

    //Generating Dimensions
    $c_dimension = $df->dimension()->cardinal(["", "low", "medium", "high"]);
    $r_dimension = $df->dimension()->range($c_dimension);

    //Generating Dataset with points and tooltips
    $dataset = $df->dataset([
        "Target" => $r_dimension,
        "Dataset 1" => $c_dimension,
        "Dataset 2" => $c_dimension,
        "Dataset 3" => $c_dimension,
    ]);

    $dataset = $dataset->withPoint(
        "Item 1",
        [
            "Target" => [0.99, 1.01],
            "Dataset 1" => 1,
            "Dataset 2" => 0,
            "Dataset 3" => 1
        ]
    );
    $dataset = $dataset->withPoint(
        "Item 2",
        [
            "Target" => [2.99, 3.01],
            "Dataset 1" => 1,
            "Dataset 2" => 3,
            "Dataset 3" => 1.5
        ]
    );
    $dataset = $dataset->withPoint(
        "Item 3",
        [
            "Target" => [2.99, 3.01],
            "Dataset 1" => 0,
            "Dataset 2" => 1,
            "Dataset 3" => 0.75
        ]
    );
    $dataset = $dataset->withPoint(
        "Item 4",
        [
            "Target" => [1.99, 2.01],
            "Dataset 1" => 2,
            "Dataset 2" => null,
            "Dataset 3" => 2.5
        ]
    );
    $dataset = $dataset->withPoint(
        "Item 5",
        [
            "Target" => [2.99, 3.01],
            "Dataset 1" => 1,
            "Dataset 2" => 1,
            "Dataset 3" => 1.8
        ]
    );
    $dataset = $dataset->withPoint(
        "Item 6",
        [
            "Target" => [-0.01, 0.01],
            "Dataset 1" => 0,
            "Dataset 2" => 3,
            "Dataset 3" => 0.2
        ]
    );

    $dataset = $dataset->withAlternativeInformation(
        "Item 1",
        [
            "Target" => "low",
            "Dataset 1" => null,
            "Dataset 2" => null,
            "Dataset 3" => "Custom 1"
        ]
    );
    $dataset = $dataset->withAlternativeInformation(
        "Item 2",
        [
            "Target" => "high",
            "Dataset 1" => null,
            "Dataset 2" => null,
            "Dataset 3" => "Custom 2"
        ]
    );
    $dataset = $dataset->withAlternativeInformation(
        "Item 3",
        [
            "Target" => "high",
            "Dataset 1" => null,
            "Dataset 2" => null,
            "Dataset 3" => "Custom 3"
        ]
    );
    $dataset = $dataset->withAlternativeInformation(
        "Item 4",
        [
            "Target" => "medium",
            "Dataset 1" => null,
            "Dataset 2" => null,
            "Dataset 3" => "Custom 4"
        ]
    );
    $dataset = $dataset->withAlternativeInformation(
        "Item 5",
        [
            "Target" => "high",
            "Dataset 1" => null,
            "Dataset 2" => null,
            "Dataset 3" => "Custom 5"
        ]
    );
    $dataset = $dataset->withAlternativeInformation(
        "Item 6",
        [
            "Target" => "-",
            "Dataset 1" => null,
            "Dataset 2" => null,
            "Dataset 3" => "Custom 6"
        ]
    );

    //Generating Bar Configurations
    $b1 = new BarConfig();
    $b1 = $b1->withRelativeWidth(1.1);
    $b1 = $b1->withColor($df->color("#000000"));
    $b2 = new BarConfig();
    $b2 = $b2->withRelativeWidth(0.6);
    $b2 = $b2->withColor($df->color("#d38000"));
    $b3 = new BarConfig();
    $b3 = $b3->withRelativeWidth(0.6);
    $b3 = $b3->withColor($df->color("#307C88"));
    $b4 = new BarConfig();
    $b4 = $b4->withRelativeWidth(0.6);
    $b4 = $b4->withColor($df->color("#557b2e"));

    $bars = [
        "Target" => $b1,
        "Dataset 1" => $b2,
        "Dataset 2" => $b3,
        "Dataset 3" => $b4
    ];

    //Generating and rendering the horizontal chart
    $bar_chart = $f->chart()->bar()->horizontal(
        "A horizontal bar chart",
        $dataset,
        $bars
    );
    $bar_chart = $bar_chart->withTitleVisible(false);
    $x_axis = new XAxis();
    $x_axis = $x_axis->withMinValue(0);
    $x_axis = $x_axis->withMaxValue(3);
    $bar_chart = $bar_chart->withCustomXAxis($x_axis);

    // render
    return $renderer->render($bar_chart);
}
