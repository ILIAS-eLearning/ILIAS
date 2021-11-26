<?php declare(strict_types=1);
namespace ILIAS\UI\examples\Chart\Bar\Vertical;

function custom()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the vertical chart
    $bar = $f->chart()->bar()->vertical(
        "bar321",
        "A vertical bar chart",
        ["Item 1", "Item 2", "Item 3"],
        "400px",
        "500px"
    );
    $bar = $bar->withTitleVisible(false);
    $bar = $bar->withLegendPosition("left");
    $bar = $bar->withAdditionalData("Dataset 1", [75, 45, 50], "#d38000", 0.6);
    $bar = $bar->withAdditionalData("Dataset 2", [80, 30, 100], "#307C88", 0.6);
    $bar = $bar->withAdditionalData("Dataset 3", [100, 90, 65.5], "#557b2e", 0.6, [100, "Custom Tooltip", 65.5]);
    $bar = $bar->withCustomYAxis(true, "right", 10, false);

    // render
    return $renderer->render($bar);
}
