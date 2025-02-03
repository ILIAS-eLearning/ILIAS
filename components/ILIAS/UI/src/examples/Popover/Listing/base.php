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

namespace ILIAS\UI\examples\Popover\Listing;

/**
 * ---
 * description: >
 *   Example for rendering a listing popover.
 *
 * expected output: >
 *   ILIAS shows a button titled "Show Listing".
 *   A click onto the button opens the popover including a panel listing with sub items and some content.
 *   You can close the popover by clicking onto the ILIAS background outside of the popover.
 * ---
 */
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
