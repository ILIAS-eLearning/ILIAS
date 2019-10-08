<?php
/**
 * With shy buttons as property values
 */
function with_shy_properties()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $app_item = $f->item()->standard("Item Title")
        ->withProperties(array(
            "LMS" => $f->button()->shy("ILIAS", "https://www.ilias.de"),
            "Code Repo" => $f->button()->shy("GitHub", "https://www.github.com"),
            "Location" => "Room 123, Main Street 44, 3012 Bern"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");
    return $renderer->render($app_item);
}
