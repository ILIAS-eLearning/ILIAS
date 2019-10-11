<?php
/**
 * With lead image
 */
function with_lead_image()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $image = $f->image()->responsive(
        "src/UI/examples/Image/HeaderIconLarge.svg",
        "Thumbnail Example"
    );
    $actions = $f->dropdown()->standard(array(
        $f->button()->shy("ILIAS", "https://www.ilias.de"),
        $f->button()->shy("GitHub", "https://www.github.com")
    ));

    $list_item1 = $f->item()->standard("ILIAS Beginner Course")
        ->withActions($actions)
        ->withProperties(array(
            "Origin" => "Course Title 1",
            "Last Update" => "24.11.2011",
            "Location" => "Room 123, Main Street 44, 3012 Bern"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
        ->withLeadImage($image);

    $list_item2 = $f->item()->standard("ILIAS Advanced Course")
        ->withActions($actions)
        ->withProperties(array(
            "Origin" => "Course Title 1",
            "Last Update" => "24.11.2011",
            "Location" => "Room 123, Main Street 44, 3012 Bern"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
        ->withLeadImage($image);

    $list_item3 = $f->item()->standard("ILIAS User Group")
        ->withActions($actions)
        ->withProperties(array(
            "Origin" => "Course Title 1",
            "Last Update" => "24.11.2011",
            "Location" => "Room 123, Main Street 44, 3012 Bern"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
        ->withLeadImage($image);

    $std_list = $f->panel()->listing()->standard("Content", array(
        $f->item()->group("Courses", array(
            $list_item1,
            $list_item2
        )),
        $f->item()->group("Groups", array(
            $list_item3
        ))
    ));


    return $renderer->render($std_list);
}
