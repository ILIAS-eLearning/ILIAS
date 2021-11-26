<?php declare(strict_types=1);
namespace ILIAS\UI\examples\Chart\Bar\Horizontal;

function base()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the horizontal chart
    $bar = $f->chart()->bar()->horizontal(
        "bar123",
        "A horizontal bar chart",
        ["Item 1", "Item 2", "Item 3", "Item 4", "Item 5", "Item 6", "Item 7", "Item 8"],
        "300px",
        "300px"
    );
    $bar = $bar->withData("Dataset", [3, 1.5, 0, 2.8, -2, 2.2, 1, -1.75], "#4c6586");

    // render
    return $renderer->render($bar);
}
