<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Chart\Bar\Horizontal;

function base()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $df = new \ILIAS\Data\Factory();
    $renderer = $DIC->ui()->renderer();

    //Generating Dimensions
    $c_dimension = $df->dimension()->cardinal();

    //Generating Dataset with points
    $dataset = $df->dataset(["Dataset" => $c_dimension]);

    $dataset = $dataset->withPoint("Item 1", ["Dataset" => 3]);
    $dataset = $dataset->withPoint("Item 2", ["Dataset" => 1.5]);
    $dataset = $dataset->withPoint("Item 3", ["Dataset" => 0]);
    $dataset = $dataset->withPoint("Item 4", ["Dataset" => 2.8]);
    $dataset = $dataset->withPoint("Item 5", ["Dataset" => -2]);
    $dataset = $dataset->withPoint("Item 6", ["Dataset" => 2.2]);
    $dataset = $dataset->withPoint("Item 7", ["Dataset" => 1]);
    $dataset = $dataset->withPoint("Item 8", ["Dataset" => -1.75]);

    //Generating and rendering the horizontal chart
    $bar_chart = $f->chart()->bar()->horizontal(
        "A horizontal bar chart",
        $dataset
    );

    // render
    return $renderer->render($bar_chart);
}
