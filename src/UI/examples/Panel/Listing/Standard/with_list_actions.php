<?php
/**
 * With list actions
 */
function with_list_actions()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $actions = $f->dropdown()->standard(array(
        $f->button()->shy("ILIAS", "https://www.ilias.de"),
        $f->button()->shy("GitHub", "https://www.github.com")
    ));
    $list_item1 = $f->item()->standard("Item Title")
        ->withProperties(array(
            "Origin" => "Course Title 1",
            "Last Update" => "24.11.2011",
            "Location" => "Room 123, Main Street 44, 3012 Bern"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

    $list_item2 = $f->item()->standard("Item 2 Title")
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

    $list_item3 = $f->item()->standard("Item 3 Title")
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");

    $std_list = $f->panel()->listing()->standard("List Title", array(
        $f->item()->group("Subtitle 1", array(
            $list_item1,
            $list_item2
        )),
        $f->item()->group("Subtitle 2", array(
            $list_item3
        ))
    ))->withActions($actions);


    return $renderer->render($std_list);
}
