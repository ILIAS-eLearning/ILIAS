<?php
/**
 * Base
 */
function with_actions()
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

    $group_actions = $f->dropdown()->standard(array(
        $f->button()->shy("Features", "https://feature.ilias.de"),
        $f->button()->shy("Bugs", "https://www.ilias.de/mantis/")
    ));

    $group = $f->item()->group("Subtitle 1", array(
        $list_item1,
        $list_item2
    ))->withActions($group_actions);

    return $renderer->render($group);
}
