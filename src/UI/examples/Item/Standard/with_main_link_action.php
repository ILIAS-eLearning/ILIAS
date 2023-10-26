<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Item\Standard;

/**
 * Base
 */
function with_main_link_action()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $main_action = $f->link()->standard("Open ILIAS", "https://www.ilias.de");
    $app_item = $f->item()->standard("Item Title")
        ->withMainAction($main_action)
        ->withProperties(array(
            "Origin" => "Course Title 1",
            "Last Update" => "24.11.2011",
            "Location" => "Room 123, Main Street 44, 3012 Bern"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.");
    return $renderer->render($app_item);
}
