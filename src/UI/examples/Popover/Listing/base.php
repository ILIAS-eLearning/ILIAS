<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Popover\Listing;

function base()
{
    global $DIC;

    // This example shows how to render a popover containing a list
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generate some List Items
    $actions = $factory->dropdown()->standard(array(
        $factory->button()->shy("ILIAS", "https://www.ilias.de"),
        $factory->button()->shy("GitHub", "https://www.github.com")
    ));

    $list_item1 = $factory->item()->standard("Item Title")
                          ->withProperties(array(
                              "Origin" => "Course Title 1",
                              "Last Update" => "24.11.2011",
                              "Location" => "Room 123, Main Street 44, 3012 Bern"))
                          ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

    $list_item2 = $factory->item()->standard("Item 2 Title")
                          ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

    $list_item3 = $factory->item()->standard("Item 3 Title")
                          ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");


    //Put the List Items Into the Listing Popover
    $popover = $factory->popover()->listing([
        $factory->item()->group("Subtitle 1", [$list_item1, $list_item2]),
        $factory->item()->group("Subtitle 2", [$list_item3])
    ])->withTitle('Listing');

    //Add a Button opening the Listing Popover on Click
    $button = $factory->button()->standard('Show Listing', '#')
                      ->withOnClick($popover->getShowSignal());

    //Render the Listing Popover
    return $renderer->render([$popover, $button]);
}
