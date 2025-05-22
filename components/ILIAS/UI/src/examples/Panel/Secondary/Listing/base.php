<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\UI\examples\Panel\Secondary\Listing;

/**
 * ---
 * description: >
 *   Example for rendering a panel secondary listing.
 *
 * expected output: >
 *   ILIAS shows a panel with a title, an action menu and two item groups. The first item group includes two items and
 *   action menu symbols. The menu opens weblinks. The second item group displays one item. The whole display is more
 *   compact in comparison to the standard listing panel.
 * ---
 */
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
