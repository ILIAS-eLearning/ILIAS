<?php declare(strict_types=1);
namespace ILIAS\UI\examples\Chart\Bar\Vertical;

use ILIAS\UI\Component\Chart\Bar\Bar;

function custom()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $df = new \ILIAS\Data\Factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating Dimensions
    $o_dimension = $df->dimension()->ordinal();

    //Genarating Dataset with points and tooltips
    $dataset = $df->dataset([
       "Dataset 1" => $o_dimension,
        "Dataset 2" => $o_dimension,
        "Dataset 3" => $o_dimension,
    ]);

    $dataset = $dataset->withPoints(
        "Item 1",
        [
            "Dataset 1" => 75,
            "Dataset 2" => 80,
            "Dataset 3" => 100
        ]
    );
    $dataset = $dataset->withPoints(
        "Item 2",
        [
            "Dataset 1" => 45,
            "Dataset 2" => 30,
            "Dataset 3" => 90
        ]
    );
    $dataset = $dataset->withPoints(
        "Item 3",
        [
            "Dataset 1" => 50,
            "Dataset 2" => 100,
            "Dataset 3" => 65.5
        ]
    );

    //Genarating Bars
    $b1 = $df->bar();
    $b1 = $b1->withColor($df->color("#d38000"));
    $b2 = $df->bar();
    $b2 = $b2->withColor($df->color("#307C88"));
    $b3 = $df->bar();
    $b3 = $b3->withColor($df->color("#557b2e"));

    $bars = [
        "Dataset 1" => $b1,
        "Dataset 2" => $b2,
        "Dataset 3" => $b3
    ];

    //Genarating and rendering the vertical chart
    $bar = $f->chart()->bar()->vertical(
        "chart123",
        "A vertical bar chart",
        $dataset,
        $bars
    );
    $bar = $bar->withTitleVisible(false);
    $bar = $bar->withLegendPosition(Bar::POSITION_LEFT);
    $bar = $bar->withCustomYAxis(true, Bar::POSITION_RIGHT, 10, false);

    // render
    return $renderer->render($bar);
}
