<?php declare(strict_types=1);
namespace ILIAS\UI\examples\Chart\Bar\Vertical;

function base()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the vertical chart
    $bar = $f->chart()->bar()->vertical(
        "bar123",
        "A vertical bar chart",
        ["Item 1", "Item 2", "Item 3", "Item 4"],
        "300px",
        "300px"
    );
    $bar = $bar->withData("Dataset", [80, 0, 18, 55], "#4c6586");

    // render
    return $renderer->render($bar);
}
