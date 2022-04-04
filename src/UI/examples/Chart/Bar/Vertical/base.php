<?php declare(strict_types=1);
namespace ILIAS\UI\examples\Chart\Bar\Vertical;

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

    $dataset = $dataset->withPoint("Item 1", ["Dataset" => 80]);
    $dataset = $dataset->withPoint("Item 2", ["Dataset" => 0]);
    $dataset = $dataset->withPoint("Item 3", ["Dataset" => 18]);
    $dataset = $dataset->withPoint("Item 4", ["Dataset" => 55]);

    //Generating and rendering the vertical chart
    $bar_chart = $f->chart()->bar()->vertical(
        "A vertical bar chart",
        $dataset
    );

    // render
    return $renderer->render($bar_chart);
}
