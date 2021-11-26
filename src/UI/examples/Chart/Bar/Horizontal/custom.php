<?php declare(strict_types=1);
namespace ILIAS\UI\examples\Chart\Bar\Horizontal;

function custom()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the horizontal chart
    $bar = $f->chart()->bar()->horizontal(
        "bar321",
        "A horizontal bar chart",
        ["Item 1", "Item 2", "Item 3", "Item 4", "Item 5", "Item 6"],
        "400px",
        "500px"
    );
    $bar = $bar->withTitleVisible(false);
    $bar = $bar->withXLabels(["", "low", "medium", "high"]);
    $bar = $bar->withAdditionalData(
        "Target",
        [[0.99, 1.01], [2.99, 3.01], [2.99, 3.01], [1.99, 2.01], [2.99, 3.01], [-0.01, 0.01]],
        "rgb(0,0,0)",
        1.1,
        ["low", "high", "high", "medium", "high", "-"],
        "bar-y-axis-1"
    );
    $bar = $bar->withAdditionalData("Dataset 1", [1, 1, 0, 2, 1, 0], "#d38000", 0.6, null, "bar-y-axis-2");
    $bar = $bar->withAdditionalData("Dataset 2", [0, 3, 1, null, 1, 3], "#307C88", 0.6, null, "bar-y-axis-2");
    $bar = $bar->withAdditionalData(
        "Dataset 3",
        [1, 1.5, 0.75, 2.5, 1.8, 0.2],
        "#557b2e",
        0.6,
        ["Custom 1", "Custom 2", "Custom 3", "Custom 4", "Custom 5", "Custom 6"],
        "bar-y-axis-2"
    );
    $bar = $bar->withCustomXAxis(true, "bottom", 1.0, true, 0, 3);
    $bar = $bar->withCustomYAxis(true, "left", "bar-y-axis-1");
    $bar = $bar->withCustomYAxis(false, "left", "bar-y-axis-2");

    // render
    return $renderer->render($bar);
}
