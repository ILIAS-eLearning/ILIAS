<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Panel\Secondary\Listing;

function base()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $actions = $factory->dropdown()->standard(array(
        $factory->button()->shy("ILIAS", "https://www.ilias.de"),
        $factory->button()->shy("GitHub", "https://www.github.com")
    ));

    $list_item1 = $factory->item()->standard("Item Title")
        ->withActions($actions)
        ->withProperties(array(
            "Origin" => "Course Title 1",
            "Last Update" => "24.11.2011",
            "Location" => "Room 123, Main Street 44, 3012 Bern"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

    $list_item2 = $factory->item()->standard("Item 2 Title")
        ->withActions($actions)
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

    $list_item3 = $factory->item()->standard("Item 3 Title")
        ->withActions($actions)
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

    $items = array(
        $factory->item()->group("Listing Subtitle 1", array(
            $list_item1,
            $list_item2
        )),
        $factory->item()->group("Listing Subtitle 2", array(
            $list_item3
        )));

    $panel = $factory->panel()->secondary()->listing("Listing panel Title", $items)->withActions($actions);

    return $renderer->render($panel);
}
