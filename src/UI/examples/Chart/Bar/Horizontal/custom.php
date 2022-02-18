<?php declare(strict_types=1);
namespace ILIAS\UI\examples\Chart\Bar\Horizontal;

use ILIAS\UI\Component\Chart\Bar\Bar;

function custom()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $df = new \ILIAS\Data\Factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating Dimensions
    $o_dimension = $df->dimension()->ordinal(["", "low", "medium", "high"]);
    $r_dimension = $df->dimension()->range($o_dimension);

    //Genarating Dataset with points and tooltips
    $dataset = $df->dataset([
        "Target" => $r_dimension,
        "Dataset 1" => $o_dimension,
        "Dataset 2" => $o_dimension,
        "Dataset 3" => $o_dimension,
    ]);

    $dataset = $dataset->withPoints(
        "Item 1",
        [
            "Target" => [0.99, 1.01],
            "Dataset 1" => 1,
            "Dataset 2" => 0,
            "Dataset 3" => 1
        ]
    );
    $dataset = $dataset->withPoints(
        "Item 2",
        [
            "Target" => [2.99, 3.01],
            "Dataset 1" => 1,
            "Dataset 2" => 3,
            "Dataset 3" => 1.5
        ]
    );
    $dataset = $dataset->withPoints(
        "Item 3",
        [
            "Target" => [2.99, 3.01],
            "Dataset 1" => 0,
            "Dataset 2" => 1,
            "Dataset 3" => 0.75
        ]
    );
    $dataset = $dataset->withPoints(
        "Item 4",
        [
            "Target" => [1.99, 2.01],
            "Dataset 1" => 2,
            "Dataset 2" => null,
            "Dataset 3" => 2.5
        ]
    );
    $dataset = $dataset->withPoints(
        "Item 5",
        [
            "Target" => [2.99, 3.01],
            "Dataset 1" => 1,
            "Dataset 2" => 1,
            "Dataset 3" => 1.8
        ]
    );
    $dataset = $dataset->withPoints(
        "Item 6",
        [
            "Target" => [-0.01, 0.01],
            "Dataset 1" => 0,
            "Dataset 2" => 3,
            "Dataset 3" => 0.2
        ]
    );

    $dataset = $dataset->withToolTips(
        "Item 1",
        [
            "Target" => "low",
            "Dataset 1" => null,
            "Dataset 2" => null,
            "Dataset 3" => "Custom 1"
        ]
    );
    $dataset = $dataset->withToolTips(
        "Item 2",
        [
            "Target" => "high",
            "Dataset 1" => null,
            "Dataset 2" => null,
            "Dataset 3" => "Custom 2"
        ]
    );
    $dataset = $dataset->withToolTips(
        "Item 3",
        [
            "Target" => "high",
            "Dataset 1" => null,
            "Dataset 2" => null,
            "Dataset 3" => "Custom 3"
        ]
    );
    $dataset = $dataset->withToolTips(
        "Item 4",
        [
            "Target" => "medium",
            "Dataset 1" => null,
            "Dataset 2" => null,
            "Dataset 3" => "Custom 4"
        ]
    );
    $dataset = $dataset->withToolTips(
        "Item 5",
        [
            "Target" => "high",
            "Dataset 1" => null,
            "Dataset 2" => null,
            "Dataset 3" => "Custom 5"
        ]
    );
    $dataset = $dataset->withToolTips(
        "Item 6",
        [
            "Target" => "-",
            "Dataset 1" => null,
            "Dataset 2" => null,
            "Dataset 3" => "Custom 6"
        ]
    );

    //Genarating Bars
    $b1 = $df->bar();
    $b1 = $b1->withSize(1.1);
    $b1 = $b1->withColor($df->color("#000000"));
    $b2 = $df->bar();
    $b2 = $b2->withSize(0.6);
    $b2 = $b2->withColor($df->color("#d38000"));
    $b3 = $df->bar();
    $b3 = $b3->withSize(0.6);
    $b3 = $b3->withColor($df->color("#307C88"));
    $b4 = $df->bar();
    $b4 = $b4->withSize(0.6);
    $b4 = $b4->withColor($df->color("#557b2e"));

    $bars = [
        "Target" => $b1,
        "Dataset 1" => $b2,
        "Dataset 2" => $b3,
        "Dataset 3" => $b4
    ];

    //Genarating and rendering the horizontal chart
    $bar_chart = $f->chart()->bar()->horizontal(
        "chart123",
        "A horizontal bar chart",
        $dataset,
        $bars
    );
    $bar_chart = $bar_chart->withTitleVisible(false);
    $bar_chart = $bar_chart->withCustomXAxis(true, Bar::POSITION_BOTTOM, 1.0, true, 0, 3);

    // render
    return $renderer->render($bar_chart);
}
