<?php

declare(strict_types=1);

namespace ILIAS\UI\Examples\Panel\Sub;

function with_secondary_panel()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $actions = $f->dropdown()->standard(array(
        $f->button()->shy("ILIAS", "https://www.ilias.de"),
        $f->button()->shy("GitHub", "https://www.github.com")
    ));

    $list_item1 = $f->item()->standard("Item Title")
                          ->withActions($actions)
                          ->withProperties(array(
                              "Origin" => "Course Title 1",
                              "Last Update" => "24.11.2011",
                              "Location" => "Room 123, Main Street 44, 3012 Bern"))
                          ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

    $list_item2 = $f->item()->standard("Item 2 Title")
                          ->withActions($actions)
                          ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

    $list_item3 = $f->item()->standard("Item 3 Title")
                          ->withActions($actions)
                          ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

    $items = array(
        $f->item()->group("Listing Subtitle 1", array(
            $list_item1,
            $list_item2
        )),
        $f->item()->group("Listing Subtitle 2", array(
            $list_item3
        )));

    $panel = $f->panel()->secondary()->listing("Listing panel Title", $items)->withActions($actions);

    $block = $f->panel()->standard(
        "Panel Title",
        $f->panel()->sub("Sub Panel Title", $f->legacy("Some Content"))
          ->withFurtherInformation($panel)
    );

    return $renderer->render($block);
}
